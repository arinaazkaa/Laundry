<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Dashboard berisi omzet dan laba, hanya untuk pemilik. Role lain diarahkan ke halaman kerjanya
        if (! $user->hasRole('pemilik')) {
            if ($user->hasRole('kasir')) {
                return redirect()->route('transactions.create');
            }
            if ($user->hasRole('pegawai')) {
                return redirect()->route('process.queue');
            }
            abort(403);
        }

        // ---------- Angka hari ini ----------
        $today = today();

        $revenueToday = (float) Payment::valid()->whereDate('created_at', $today)->sum('amount');
        $revenueYesterday = (float) Payment::valid()->whereDate('created_at', $today->copy()->subDay())->sum('amount');
        $expenseToday = (float) Expense::whereDate('date', $today)->sum('amount');

        $revenueBadge = null;
        if ($revenueYesterday > 0) {
            $change = round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100);
            $revenueBadge = ($change >= 0 ? '+' : '') . $change . '% vs kemarin';
        }

        $unpaidQuery = Transaction::notCancelled()->whereColumn('paid_amount', '<', 'total');
        $unpaid = (clone $unpaidQuery)->count();
        $unpaidAmount = (float) (clone $unpaidQuery)->sum(DB::raw('total - paid_amount'));

        // ---------- Grafik sesuai periode terpilih ----------
        ['start' => $start, 'end' => $end, 'period' => $period, 'clamped' => $clamped] = $this->resolvePeriod($request);

        $dates = [];
        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $day) {
            $dates[] = $day->toDateString();
        }

        $revenueByDay = Payment::valid()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as t')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->pluck('t', 'd');

        $countByDay = Transaction::notCancelled()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->pluck('c', 'd');

        // Hari tanpa data tetap ditampilkan dengan nilai 0 supaya grafik tidak melompat
        $short = count($dates) > 31;
        $labels = collect($dates)->map(fn ($d) => Carbon::parse($d)->translatedFormat($short ? 'd/m' : 'd M'))->all();
        $revenueSeries = collect($dates)->map(fn ($d) => (float) ($revenueByDay[$d] ?? 0))->all();
        $countSeries = collect($dates)->map(fn ($d) => (int) ($countByDay[$d] ?? 0))->all();

        $topServices = TransactionItem::whereHas('transaction', fn ($q) => $q->notCancelled()->whereBetween('created_at', [$start, $end]))
            ->selectRaw('service_name, unit_type, SUM(qty) as total_qty')
            ->groupBy('service_name', 'unit_type')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $periodLabel = match ($period) {
            '30d' => '30 Hari Terakhir',
            'month' => 'Bulan Ini',
            'custom' => $start->translatedFormat('d M Y') . ' – ' . $end->translatedFormat('d M Y'),
            default => '7 Hari Terakhir',
        };

        $activities = ActivityLog::with('user.roles')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('dashboard', [
            'revenueToday' => $revenueToday,
            'revenueBadge' => $revenueBadge,
            'expenseToday' => $expenseToday,
            'profitToday' => $revenueToday - $expenseToday,
            'transactionsToday' => Transaction::notCancelled()->whereDate('created_at', $today)->count(),
            'inProcess' => Transaction::whereIn('status', ['diproses', 'dicuci', 'dikeringkan', 'disetrika'])->count(),
            'readyToPickup' => Transaction::where('status', 'siap_diambil')->count(),
            'unpaid' => $unpaid,
            'unpaidAmount' => $unpaidAmount,

            'period' => $period,
            'periodLabel' => $periodLabel,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'clamped' => $clamped,
            'labels' => $labels,
            'revenueSeries' => $revenueSeries,
            'countSeries' => $countSeries,
            'periodRevenue' => array_sum($revenueSeries),
            'periodTransactions' => array_sum($countSeries),
            'topServices' => $topServices,

            'activities' => $activities,
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', '7d');

        if ($period === 'custom' && $request->filled(['start', 'end'])) {
            try {
                $start = Carbon::parse($request->get('start'))->startOfDay();
                $end = Carbon::parse($request->get('end'))->endOfDay();

                if ($end->lt($start)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }

                // Grafik dibatasi 92 hari supaya tetap terbaca
                $clamped = false;
                $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
                if ($days > 91) {
                    $start = $end->copy()->subDays(91)->startOfDay();
                    $clamped = true;
                }

                return ['start' => $start, 'end' => $end, 'period' => 'custom', 'clamped' => $clamped];
            } catch (\Throwable $e) {
                // Tanggal tidak valid, jatuh ke bawah ke 7 hari
            }
        }

        return match ($period) {
            '30d' => ['start' => today()->subDays(29), 'end' => now()->endOfDay(), 'period' => '30d', 'clamped' => false],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfDay(), 'period' => 'month', 'clamped' => false],
            default => ['start' => today()->subDays(6), 'end' => now()->endOfDay(), 'period' => '7d', 'clamped' => false],
        };
    }
}
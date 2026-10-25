<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const METHOD_NAMES = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
    ];

    public function index(Request $request)
    {
        [$start, $end, $period] = $this->resolvePeriod($request->get('period', 'month'), $request);

        // Pemasukan = uang yang benar-benar diterima, tanpa transaksi dibatalkan
        $totalRevenue = (float) Payment::valid()->whereBetween('created_at', [$start, $end])->sum('amount');
        $totalExpense = (float) Expense::whereBetween('date', [$start, $end])->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        // Periode pembanding: rentang waktu yang sama panjang, tepat sebelum periode ini
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subSeconds((int) $start->diffInSeconds($end));
        $prevRevenue = (float) Payment::valid()->whereBetween('created_at', [$prevStart, $prevEnd])->sum('amount');
        $revenueChangePercent = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        // Piutang: kondisi saat ini, tidak tergantung periode
        $receivableQuery = Transaction::notCancelled()->whereColumn('paid_amount', '<', 'total');
        $receivableCount = (clone $receivableQuery)->count();
        $receivableTotal = (float) (clone $receivableQuery)->sum(DB::raw('total - paid_amount'));

        // Tren 6 bulan terakhir
        $monthlyTrend = collect(range(5, 0))->mapWithKeys(function ($monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $total = Payment::valid()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            return [$month->translatedFormat('M') => (float) $total];
        });

        // Distribusi metode pembayaran (berdasarkan nominal uang yang masuk)
        $paymentDistribution = Payment::valid()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method')
            ->mapWithKeys(fn ($total, $method) => [(self::METHOD_NAMES[$method] ?? ucfirst($method)) => (float) $total]);

        $paymentTotal = $paymentDistribution->sum();

        $transactions = Transaction::with('customer')
            ->notCancelled()
            ->whereBetween('created_at', [$start, $end])
            ->when($request->q, function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $sub->where('code', 'like', "%{$request->q}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->q}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $periodLabel = match ($period) {
            'today' => 'hari ini',
            'week' => 'minggu ini',
            'year' => 'tahun ini',
            'custom' => $start->translatedFormat('d M Y') . ' – ' . $end->translatedFormat('d M Y'),
            default => 'bulan ini',
        };

        return view('reports.finance', [
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'margin' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0,
            'revenueChangePercent' => $revenueChangePercent,
            'receivableCount' => $receivableCount,
            'receivableTotal' => $receivableTotal,
            'monthlyTrend' => $monthlyTrend,
            'paymentDistribution' => $paymentDistribution,
            'paymentTotal' => $paymentTotal,
            'transactions' => $transactions,
            'period' => $period,
            'periodLabel' => $periodLabel,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ]);
    }

    public function receivables(Request $request)
    {
        $base = Transaction::notCancelled()->whereColumn('paid_amount', '<', 'total');

        $totalReceivable = (float) (clone $base)->sum(DB::raw('total - paid_amount'));
        $count = (clone $base)->count();
        $oldest = (clone $base)->min('created_at');

        $transactions = (clone $base)
            ->with('customer')
            ->when($request->q, function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $sub->where('code', 'like', "%{$request->q}%")
                        ->orWhereHas('customer', function ($c) use ($request) {
                            $c->where('name', 'like', "%{$request->q}%")
                                ->orWhere('phone', 'like', "%{$request->q}%");
                        });
                });
            })
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('reports.receivables', [
            'transactions' => $transactions,
            'totalReceivable' => $totalReceivable,
            'count' => $count,
            'oldest' => $oldest ? Carbon::parse($oldest) : null,
        ]);
    }

    public function export(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request->get('period', 'month'), $request);

        $filename = 'laporan-keuangan-' . now()->format('Ymd-His') . '.csv';

        $transactions = Transaction::with('customer')
            ->notCancelled()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Tanggal', 'Pelanggan', 'Subtotal', 'Diskon', 'Ongkir', 'Total', 'Dibayar', 'Status Bayar', 'Status']);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->code,
                    $t->created_at->format('Y-m-d H:i'),
                    $t->customer->name ?? 'Pelanggan Umum',
                    $t->subtotal, $t->discount, $t->delivery_fee, $t->total, $t->paid_amount,
                    $t->payment_status, $t->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        if ($period === 'custom' && $request->filled(['start', 'end'])) {
            try {
                $start = Carbon::parse($request->get('start'))->startOfDay();
                $end = Carbon::parse($request->get('end'))->endOfDay();

                if ($end->lt($start)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }

                return [$start, $end, 'custom'];
            } catch (\Throwable $e) {
                // Tanggal tidak valid, pakai bulan ini
            }
        }

        return match ($period) {
            'today' => [today(), now()->endOfDay(), 'today'],
            'week' => [now()->startOfWeek(), now()->endOfWeek(), 'week'],
            'year' => [now()->startOfYear(), now()->endOfYear(), 'year'],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'month'],
        };
    }
}
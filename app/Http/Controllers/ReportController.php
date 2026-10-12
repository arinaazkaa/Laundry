<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return $this->finance($request);
    }

    public function finance(Request $request)
    {
        $period = $request->get('period', 'month'); // month, week, year, custom
        [$start, $end] = $this->resolvePeriod($period, $request);

        $totalRevenue = Transaction::whereBetween('created_at', [$start, $end])->sum('total');
        $totalExpense = Expense::whereBetween('date', [$start, $end])->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        // Perbandingan periode sebelumnya (untuk badge naik/turun)
        $prevLength = $start->diffInDays($end) + 1;
        $prevStart = (clone $start)->subDays($prevLength);
        $prevEnd = (clone $start)->subDay();
        $prevRevenue = Transaction::whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');
        $revenueChangePercent = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

        // Tren 6 bulan terakhir untuk grafik batang
        $monthlyTrend = collect(range(5, 0))->mapWithKeys(function ($monthsAgo) {
            $month = now()->subMonths($monthsAgo);
            $total = Transaction::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');
            return [$month->translatedFormat('M') => $total];
        });

        // Distribusi metode pembayaran
        $paymentDistribution = Transaction::whereBetween('created_at', [$start, $end])
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, COUNT(*) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $totalTransactionsCount = $paymentDistribution->sum();

        $transactions = Transaction::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->when($request->q, function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$request->q}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reports.finance', [
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'margin' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0,
            'revenueChangePercent' => $revenueChangePercent,
            'monthlyTrend' => $monthlyTrend,
            'paymentDistribution' => $paymentDistribution,
            'totalTransactionsCount' => $totalTransactionsCount,
            'transactions' => $transactions,
            'period' => $period,
        ]);
    }

    public function revenue(Request $request)
    {
        return $this->finance($request);
    }

    public function profit(Request $request)
    {
        return $this->finance($request);
    }

    public function export(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request->get('period', 'month'), $request);

        $filename = 'laporan-keuangan-' . now()->format('Ymd-His') . '.csv';

        $transactions = Transaction::with('customer')->whereBetween('created_at', [$start, $end])->get();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Tanggal', 'Pelanggan', 'Subtotal', 'Diskon', 'Ongkir', 'Total', 'Status Bayar', 'Status']);
            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->code,
                    $t->created_at->format('Y-m-d H:i'),
                    $t->customer->name ?? 'Pelanggan Umum',
                    $t->subtotal, $t->discount, $t->delivery_fee, $t->total,
                    $t->payment_status, $t->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function resolvePeriod(string $period, Request $request): array
    {
        return match ($period) {
            'today' => [today(), now()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                \Carbon\Carbon::parse($request->get('start', now()->startOfMonth())),
                \Carbon\Carbon::parse($request->get('end', now()->endOfMonth())),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
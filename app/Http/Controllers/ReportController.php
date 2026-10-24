<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
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
        $period = $request->get('period', 'month'); // today, week, month, year, custom
        [$start, $end] = $this->resolvePeriod($period, $request);

        // Pemasukan = uang yang benar-benar diterima, tanpa transaksi dibatalkan
        $totalRevenue = Payment::valid()->whereBetween('created_at', [$start, $end])->sum('amount');
        $totalExpense = Expense::whereBetween('date', [$start, $end])->sum('amount');
        $netProfit = $totalRevenue - $totalExpense;

        // Periode pembanding: rentang waktu yang sama panjang, tepat sebelum periode ini
        $prevEnd = (clone $start)->subSecond();
        $prevStart = (clone $prevEnd)->subSeconds((int) $start->diffInSeconds($end));
        $prevRevenue = Payment::valid()->whereBetween('created_at', [$prevStart, $prevEnd])->sum('amount');
        $revenueChangePercent = $prevRevenue > 0
            ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : 0;

        // Tren 6 bulan terakhir untuk grafik batang
        $monthlyTrend = collect(range(5, 0))->mapWithKeys(function ($monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $total = Payment::valid()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            return [$month->translatedFormat('M') => $total];
        });

        // Distribusi metode pembayaran (berdasarkan nominal uang yang masuk)
        $paymentDistribution = Payment::valid()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $totalTransactionsCount = $paymentDistribution->sum();

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

        $transactions = Transaction::with('customer')
            ->notCancelled()
            ->whereBetween('created_at', [$start, $end])
            ->get();

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
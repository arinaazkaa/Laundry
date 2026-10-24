<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // Pendapatan = uang yang benar-benar diterima hari ini, tanpa transaksi dibatalkan
        $revenueToday = Payment::valid()->whereDate('created_at', $today)->sum('amount');
        $expenseToday = Expense::whereDate('date', $today)->sum('amount');

        $weeklyRevenue = Payment::valid()
            ->selectRaw('DATE(created_at) as d, SUM(amount) as t')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('t', 'd');

        $topServices = TransactionItem::whereHas('transaction', fn ($q) => $q->notCancelled())
            ->selectRaw('service_name, SUM(qty) as total_qty')
            ->groupBy('service_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', [
            'revenueToday' => $revenueToday,
            'expenseToday' => $expenseToday,
            'profitToday' => $revenueToday - $expenseToday,
            'transactionsToday' => Transaction::notCancelled()->whereDate('created_at', $today)->count(),
            'inProcess' => Transaction::whereIn('status', ['diproses', 'dicuci', 'dikeringkan', 'disetrika'])->count(),
            'readyToPickup' => Transaction::where('status', 'siap_diambil')->count(),
            'unpaid' => Transaction::notCancelled()->where('payment_status', '!=', 'lunas')->count(),
            'weeklyRevenue' => $weeklyRevenue,
            'topServices' => $topServices,
            'recentTransactions' => Transaction::with('cashier')->latest()->take(5)->get(),
        ]);
    }
}
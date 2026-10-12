<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionItem;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $revenueToday = Transaction::whereDate('created_at', $today)->sum('total');
        $expenseToday = Expense::whereDate('date', $today)->sum('amount');

        $weeklyRevenue = Transaction::selectRaw('DATE(created_at) as d, SUM(total) as t')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('t', 'd');

        $topServices = TransactionItem::selectRaw('service_name, SUM(qty) as total_qty')
            ->groupBy('service_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', [
            'revenueToday' => $revenueToday,
            'expenseToday' => $expenseToday,
            'profitToday' => $revenueToday - $expenseToday,
            'transactionsToday' => Transaction::whereDate('created_at', $today)->count(),
            'inProcess' => Transaction::whereIn('status', ['diproses', 'dicuci', 'dikeringkan', 'disetrika'])->count(),
            'readyToPickup' => Transaction::where('status', 'siap_diambil')->count(),
            'unpaid' => Transaction::where('payment_status', '!=', 'lunas')->count(),
            'weeklyRevenue' => $weeklyRevenue,
            'topServices' => $topServices,
            'recentTransactions' => Transaction::with('cashier')->latest()->take(5)->get(),
        ]);
    }
}
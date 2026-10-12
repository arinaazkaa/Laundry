<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public const CATEGORIES = [
        'detergen' => 'Detergen',
        'pewangi' => 'Pewangi',
        'plastik' => 'Plastik',
        'listrik' => 'Listrik',
        'air' => 'Air',
        'gas' => 'Gas',
        'perawatan_mesin' => 'Perawatan Mesin',
        'transportasi' => 'Transportasi',
        'gaji' => 'Gaji',
        'lainnya' => 'Lainnya',
    ];

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = match ($period) {
            'today' => [today(), now()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $expenses = Expense::with('creator')
            ->whereBetween('date', [$start, $end])
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->q, fn ($q) => $q->where('description', 'like', "%{$request->q}%"))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $totalThisPeriod = Expense::whereBetween('date', [$start, $end])->sum('amount');

        $byCategory = Expense::whereBetween('date', [$start, $end])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalThisPeriod' => $totalThisPeriod,
            'byCategory' => $byCategory,
            'categories' => self::CATEGORIES,
            'period' => $period,
        ]);
    }

    public function create()
    {
        return view('expenses.create', ['categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $data['created_by'] = Auth::id();
        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.edit', ['expense' => $expense, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran dihapus.');
    }
}
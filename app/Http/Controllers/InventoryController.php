<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('inventory.index', [
            'items' => $items,
            'totalItems' => InventoryItem::count(),
            'lowStockCount' => InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->count(),
            'safeStockCount' => InventoryItem::whereColumn('current_stock', '>', 'min_stock')->count(),
            'categories' => InventoryItem::whereNotNull('category')->distinct()->pluck('category'),
        ]);
    }

    public function logs()
    {
        $logs = InventoryLog::with(['item', 'creator' => fn ($q) => $q])->latest()->paginate(20);
        return view('inventory.logs', compact('logs'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
        ]);

        InventoryItem::create($data);

        return redirect()->route('inventory.index')->with('success', 'Item inventori ditambahkan.');
    }

    public function edit(InventoryItem $item)
    {
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'required|string|max:20',
            'min_stock' => 'required|numeric|min:0',
        ]);

        $item->update($data);

        return redirect()->route('inventory.index')->with('success', 'Item inventori diperbarui.');
    }

    public function stockIn(Request $request, InventoryItem $item)
    {
        $data = $request->validate(['qty' => 'required|numeric|min:0.01', 'notes' => 'nullable|string']);

        DB::transaction(function () use ($item, $data) {
            $item->increment('current_stock', $data['qty']);
            $item->logs()->create([
                'type' => 'masuk',
                'qty' => $data['qty'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Stok berhasil ditambahkan.');
    }

    public function stockOut(Request $request, InventoryItem $item)
    {
        $data = $request->validate(['qty' => 'required|numeric|min:0.01', 'notes' => 'nullable|string']);

        if ($data['qty'] > $item->current_stock) {
            return back()->with('error', 'Jumlah keluar melebihi stok yang tersedia.');
        }

        DB::transaction(function () use ($item, $data) {
            $item->decrement('current_stock', $data['qty']);
            $item->logs()->create([
                'type' => 'keluar',
                'qty' => $data['qty'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Stok berhasil dikurangi.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('inventory.index')->with('success', 'Item inventori dihapus.');
    }
}
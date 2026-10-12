<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::latest()->paginate(15);
        return view('promos.index', compact('promos'));
    }

    public function active()
    {
        $promos = Promo::where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->paginate(15);
        return view('promos.index', compact('promos'));
    }

    public function history()
    {
        $promos = Promo::whereDate('end_date', '<', today())->latest()->paginate(15);
        return view('promos.index', compact('promos'));
    }

    public function create()
    {
        return view('promos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:gratis_ongkir,diskon_persen,diskon_nominal,berdasarkan_berat,minimal_transaksi,member',
            'value' => 'nullable|numeric|min:0',
            'min_transaction' => 'nullable|numeric|min:0',
            'min_weight' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'quota' => 'nullable|integer|min:1',
        ]);

        Promo::create($data);

        return redirect()->route('promos.index')->with('success', 'Promo berhasil dibuat.');
    }

    public function show(Promo $promo)
    {
        $promo->load('usages.transaction');
        return view('promos.show', compact('promo'));
    }

    public function edit(Promo $promo)
    {
        return view('promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'min_transaction' => 'nullable|numeric|min:0',
            'min_weight' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'quota' => 'nullable|integer|min:1',
        ]);

        $promo->update($data);

        return redirect()->route('promos.index')->with('success', 'Promo diperbarui.');
    }

    public function toggleActive(Promo $promo)
    {
        $promo->update(['is_active' => ! $promo->is_active]);
        return back()->with('success', 'Status promo diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();
        return redirect()->route('promos.index')->with('success', 'Promo dihapus.');
    }
}
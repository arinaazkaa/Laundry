<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('transactions')
            ->when($request->q, function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->q}%")
                        ->orWhere('phone', 'like', "%{$request->q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function members()
    {
        $customers = Customer::where('is_member', true)->paginate(15);
        return view('customers.members', compact('customers'));
    }

    // Dipanggil dari Alpine.js di halaman Transaksi Baru
    public function search(Request $request)
    {
        $q = $request->get('q');
        $customers = Customer::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(8)
            ->get(['id', 'name', 'phone', 'address', 'is_member']);

        // Tambahkan info transaksi & rekomendasi member ke tiap hasil pencarian
        $customers->each(function ($c) {
            $c->total_transactions_count = $c->transactions()->count();
            $c->is_eligible_for_member = $c->is_eligible_for_member;
        });

        return response()->json($customers);
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer = Customer::create($data);

        if ($request->wantsJson()) {
            return response()->json($customer); // otomatis include is_member = false (default)
        }

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['transactions' => fn ($q) => $q->latest()]);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_member' => 'boolean',
        ]);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus.');
    }
    public function promoteMember(Customer $customer)
    {
        $customer->update(['is_member' => true]);

        return back()->with('success', "{$customer->name} berhasil dijadikan member!");
    }
}
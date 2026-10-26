<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $filter = in_array($request->get('filter'), ['member', 'rekomendasi', 'aktif'])
            ? $request->get('filter')
            : 'semua';

        $query = Customer::query()
            ->withCount(['transactions as active_transactions_count' => fn ($q) => $q->notCancelled()])
            ->withSum(['transactions as active_spent' => fn ($q) => $q->notCancelled()], 'total')
            ->when($request->q, function ($query) use ($request) {
                $query->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->q}%")
                        ->orWhere('phone', 'like', "%{$request->q}%");
                });
            });

        if ($filter === 'member') {
            $query->where('is_member', true);
        } elseif ($filter === 'rekomendasi') {
            $minTrx = (int) setting('member_min_transactions', 999999);
            $minSpent = (float) setting('member_min_spent', 999999999999);

            $query->where('is_member', false)->where(function ($q) use ($minTrx, $minSpent) {
                $q->whereRaw('(select count(*) from transactions t where t.customer_id = customers.id and t.status != ?) >= ?', ['dibatalkan', $minTrx])
                  ->orWhereRaw('(select coalesce(sum(t.total), 0) from transactions t where t.customer_id = customers.id and t.status != ?) >= ?', ['dibatalkan', $minSpent]);
            });
        }

        if ($filter === 'aktif') {
            $query->orderByDesc('active_transactions_count')->orderByDesc('active_spent');
        } else {
            $query->latest();
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'filter'));
    }

    // Dipanggil dari halaman Transaksi Baru
    public function search(Request $request)
    {
        $q = $request->get('q');

        $customers = Customer::where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(8)
            ->get();

        return response()->json($customers->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'address' => $c->address,
            'is_member' => (bool) $c->is_member,
            'is_eligible_for_member' => $c->is_eligible_for_member,
        ]));
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
            return response()->json($customer->fresh());
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

        // Status member hanya boleh diubah pemilik
        if (! $request->user()->hasRole('pemilik')) {
            unset($data['is_member']);
        }

        // Nomor HP dipakai sebagai nama pengguna akun online, jadi harus tetap benar dan tidak kembar
        if ($customer->has_online_access) {
            $normalized = Customer::normalizePhone($data['phone'] ?? null);

            if (! $normalized) {
                throw ValidationException::withMessages([
                    'phone' => 'Pelanggan ini punya akses online, nomor HP wajib diisi dengan benar (contoh 0812xxxxxxxx).',
                ]);
            }

            $taken = Customer::where('id', '!=', $customer->id)
                ->where('phone_normalized', $normalized)
                ->whereNotNull('password')
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'phone' => 'Nomor HP ini sudah dipakai akun online pelanggan lain.',
                ]);
            }
        }

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
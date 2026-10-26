<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicePackageController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::with('service')->latest()->paginate(12);
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create', ['services' => $this->kiloanServices()]);
    }

    public function store(Request $request)
    {
        ServicePackage::create($this->validated($request));

        return redirect()->route('packages.index')->with('success', 'Paket laundry dibuat.');
    }

    public function edit(ServicePackage $package)
    {
        return view('packages.edit', [
            'package' => $package,
            'services' => $this->kiloanServices($package->service_id),
        ]);
    }

    public function update(Request $request, ServicePackage $package)
    {
        $package->update($this->validated($request));

        return redirect()->route('packages.index')->with('success', 'Paket laundry diperbarui.');
    }

    public function toggleActive(ServicePackage $package)
    {
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('success', 'Status paket diperbarui.');
    }

    public function destroy(ServicePackage $package)
    {
        if ($package->used_count > 0 || $package->transactionItems()->exists()) {
            return back()->with('error', 'Paket yang sudah pernah dipakai tidak bisa dihapus. Nonaktifkan saja supaya riwayatnya tetap tersimpan.');
        }

        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Paket dihapus.');
    }

    private function kiloanServices(?int $includeId = null)
    {
        return Service::where('type', 'kiloan')
            ->where(function ($q) use ($includeId) {
                $q->where('is_active', true);
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'price']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_id' => ['required', Rule::exists('services', 'id')->where('type', 'kiloan')],
            'weight_kg' => 'required|numeric|min:0.5|max:1000',
            'package_price' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'quota' => 'nullable|integer|min:1',
        ]);

        // Harga normal dicatat dari harga layanan saat disimpan, tidak dari isian form
        $service = Service::findOrFail($data['service_id']);
        $data['normal_price'] = round((float) $service->price * (float) $data['weight_kg']);

        return $data;
    }
}
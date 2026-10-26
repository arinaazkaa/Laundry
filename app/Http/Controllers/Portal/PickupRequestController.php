<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PickupRequestController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user('customer');

        $dateOptions = PickupRequest::availableDates()->mapWithKeys(fn ($d) => [
            $d->toDateString() => ($d->isToday() ? 'Hari ini' : ($d->isTomorrow() ? 'Besok' : $d->translatedFormat('l')))
                . ', ' . $d->translatedFormat('d M'),
        ]);

        return view('portal.pickup', [
            'customer' => $customer,
            'enabled' => (bool) setting('pickup_enabled'),
            'active' => $customer->pickupRequests()->active()->latest()->first(),
            'history' => $customer->pickupRequests()->latest()->take(8)->get(),
            'dateOptions' => $dateOptions,
        ]);
    }

    public function store(Request $request)
    {
        $customer = $request->user('customer');

        if (! setting('pickup_enabled')) {
            return redirect()->route('portal.pickup')->with('error', 'Layanan jemput sedang tidak tersedia.');
        }

        $dates = PickupRequest::availableDates()->map->toDateString()->all();

        $data = $request->validate([
            'address' => 'required|string|min:8|max:255',
            'preferred_date' => ['required', Rule::in($dates)],
            'time_slot' => ['required', Rule::in(array_keys(PickupRequest::TIME_SLOTS))],
            'notes' => 'nullable|string|max:300',
        ], [
            'address.required' => 'Alamat jemput wajib diisi.',
            'address.min' => 'Alamat terlalu singkat. Tulis lengkap: jalan, RT/RW, dan patokan.',
            'preferred_date.required' => 'Pilih tanggal jemput.',
            'preferred_date.in' => 'Tanggal yang dipilih tidak tersedia. Pilih tanggal lain.',
            'time_slot.required' => 'Pilih waktu jemput.',
            'time_slot.in' => 'Waktu jemput tidak valid.',
        ]);

        $created = DB::transaction(function () use ($customer, $data) {
            // Kunci baris pelanggan supaya dua kali tekan tombol tidak membuat dua permintaan
            Customer::whereKey($customer->id)->lockForUpdate()->first();

            if (PickupRequest::where('customer_id', $customer->id)->active()->exists()) {
                return null;
            }

            return PickupRequest::create([
                'customer_id' => $customer->id,
                'address' => $data['address'],
                'preferred_date' => $data['preferred_date'],
                'time_slot' => $data['time_slot'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        if (! $created) {
            return redirect()->route('portal.pickup')
                ->with('error', 'Kamu masih punya permintaan jemput yang aktif. Batalkan dulu kalau mau membuat yang baru.');
        }

        ActivityLog::record('pickup_request', 'menerima permintaan jemput dari ' . $customer->name, null, true);

        return redirect()->route('portal.pickup')
            ->with('success', 'Permintaan jemput terkirim. Kasir akan mengonfirmasi, cek statusnya di halaman ini.');
    }

    public function cancel(Request $request, int $id)
    {
        $customer = $request->user('customer');

        // Hanya permintaan milik pelanggan yang sedang masuk
        $own = $customer->pickupRequests()->whereKey($id)->firstOrFail();

        $error = DB::transaction(function () use ($own, $customer) {
            $pr = PickupRequest::lockForUpdate()->find($own->id);

            if (! in_array($pr->status, ['menunggu', 'dikonfirmasi'], true)) {
                return 'Permintaan ini sudah tidak bisa dibatalkan.';
            }

            $pr->forceFill(['status' => 'dibatalkan'])->save();

            ActivityLog::record('pickup_request', $customer->name . ' membatalkan permintaan jemput', null, true);

            return null;
        });

        if ($error) {
            return back()->with('error', $error);
        }

        return redirect()->route('portal.pickup')->with('success', 'Permintaan jemput dibatalkan.');
    }
}
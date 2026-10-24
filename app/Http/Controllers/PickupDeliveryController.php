<?php

namespace App\Http\Controllers;

use App\Models\PickupDelivery;
use Illuminate\Http\Request;

class PickupDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $tasks = PickupDelivery::with('transaction.customer')
            ->whereNotIn('status', ['selesai', 'dibatalkan'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->get();

        return view('pickups.index', [
            'tasks' => $tasks,
            'waitingCount' => PickupDelivery::where('type', 'pickup')->where('status', 'dijadwalkan')->count(),
            'readyCount' => PickupDelivery::where('type', 'delivery')->where('status', 'dijadwalkan')->count(),
            'onTheWayCount' => PickupDelivery::where('status', 'dalam_perjalanan')->count(),
        ]);
    }

    public function pickupOnly(Request $request)
    {
        $request->merge(['type' => 'pickup']);
        return $this->index($request);
    }

    public function deliveryOnly(Request $request)
    {
        $request->merge(['type' => 'delivery']);
        return $this->index($request);
    }

    public function show(PickupDelivery $pickup)
    {
        $pickup->load('transaction.customer', 'transaction.items');
        return view('pickups.show', compact('pickup'));
    }

    public function updateStatus(Request $request, PickupDelivery $pickup)
    {
        $request->validate([
            'status' => 'required|in:dijadwalkan,dalam_perjalanan,selesai,dibatalkan',
        ]);

        $pickup->update(['status' => $request->status]);

        return back()->with('success', 'Status antar-jemput diperbarui.');
    }
}
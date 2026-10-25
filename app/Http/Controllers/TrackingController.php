<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    public function show(string $token)
    {
        $transaction = Transaction::with(['items', 'customer'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        // Hanya data yang aman untuk publik. Nama ditampilkan nama depan saja
        return view('tracking.show', [
            'code' => $transaction->code,
            'customerName' => $transaction->customer
                ? Str::of($transaction->customer->name)->before(' ')->toString()
                : 'Pelanggan',
            'status' => $transaction->status,
            'paymentStatus' => $transaction->payment_status,
            'total' => $transaction->total,
            'remaining' => $transaction->remaining,
            'items' => $transaction->items->map(fn ($i) => [
                'name' => $i->service_name,
                'qty' => $i->qty,
                'unit' => $i->unit_type,
            ]),
            'createdAt' => $transaction->created_at,
            'estimatedDoneAt' => $transaction->estimated_done_at,
            'usePickup' => $transaction->use_pickup,
            'useDelivery' => $transaction->use_delivery,
        ]);
    }
}
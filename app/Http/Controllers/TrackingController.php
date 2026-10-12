<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show(string $code)
    {
        $transaction = Transaction::with(['items'])
            ->where('code', $code)
            ->firstOrFail();

        // Hanya expose data yang aman untuk publik — TIDAK ada nama kasir,
        // TIDAK ada nomor HP pelanggan, TIDAK ada harga per item (cukup total)
        return view('tracking.show', [
            'code' => $transaction->code,
            'customerName' => $transaction->customer->name ?? 'Pelanggan',
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
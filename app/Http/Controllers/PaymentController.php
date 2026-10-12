<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string',
        ]);

        $transaction->payments()->create([
            'amount' => $data['amount'],
            'method' => $data['method'],
            'received_by' => Auth::id(),
        ]);

        $newPaid = $transaction->paid_amount + $data['amount'];
        $status = $newPaid >= $transaction->total ? 'lunas' : 'dp';

        $transaction->update([
            'paid_amount' => min($newPaid, $transaction->total),
            'payment_status' => $status,
            'payment_method' => $data['method'],
        ]);

        $change = max(0, $newPaid - $transaction->total);

        return back()->with('success', $change > 0
            ? "Pembayaran diterima. Kembalian: Rp " . number_format($change, 0, ',', '.')
            : 'Pembayaran diterima.');
    }
}
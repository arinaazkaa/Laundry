<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'method' => ['required', Rule::in(array_keys(payment_methods()))],
        ]);

        $result = DB::transaction(function () use ($transaction, $data) {
            // Kunci baris transaksi supaya klik ganda tidak mencatat pembayaran dua kali
            $trx = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($trx->status === 'dibatalkan') {
                return ['error' => 'Transaksi sudah dibatalkan, pembayaran tidak dapat dicatat.'];
            }

            $remaining = max(0, (float) $trx->total - (float) $trx->paid_amount);

            if ($remaining <= 0) {
                return ['error' => 'Transaksi ini sudah lunas.'];
            }

            $received = (float) $data['amount'];
            $applied = min($received, $remaining);   // hanya sebesar sisa tagihan yang dicatat sebagai pendapatan
            $change = $received - $applied;

            $trx->payments()->create([
                'amount' => $applied,
                'method' => $data['method'],
                'received_by' => Auth::id(),
            ]);

            $newPaid = (float) $trx->paid_amount + $applied;

            $trx->update([
                'paid_amount' => $newPaid,
                'payment_status' => $newPaid >= (float) $trx->total ? 'lunas' : 'dp',
                'payment_method' => $data['method'],
            ]);

            return ['change' => $change];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        $message = 'Pembayaran diterima.';
        if ($result['change'] > 0) {
            $message .= ' Kembalian: Rp ' . number_format($result['change'], 0, ',', '.');
        }

        return back()->with('success', $message);
    }
}
<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof Transaction) {
            $this->log(
                'transaction_created',
                'membuat transaksi ' . $model->code . ' (' . $this->rupiah($model->total) . ')',
                $model->id
            );
            return;
        }

        if ($model instanceof Payment) {
            $trx = $model->transaction;

            // Pembayaran awal saat transaksi dibuat sudah tercakup di "membuat transaksi", jadi tidak dicatat dua kali
            if ($trx && abs($trx->created_at->diffInSeconds($model->created_at)) > 5) {
                $this->log(
                    'payment_received',
                    'menerima pembayaran ' . $this->rupiah($model->amount) . ' untuk ' . $trx->code,
                    $trx->id
                );
            }
            return;
        }

        if ($model instanceof Expense) {
            $this->log(
                'expense_created',
                'mencatat pengeluaran ' . ucwords(str_replace('_', ' ', $model->category)) . ' ' . $this->rupiah($model->amount)
            );
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof Transaction && $model->wasChanged('status')) {
            $description = $model->status === 'dibatalkan'
                ? 'membatalkan transaksi ' . $model->code
                : 'mengubah status ' . $model->code . ' menjadi ' . ucwords(str_replace('_', ' ', $model->status));

            $this->log('status_changed', $description, $model->id);
        }
    }

    private function rupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    private function log(string $event, string $description, ?int $transactionId = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'event' => $event,
                'description' => $description,
                'transaction_id' => $transactionId,
            ]);
        } catch (\Throwable $e) {
            // Kegagalan mencatat aktivitas tidak boleh menggagalkan transaksi
            report($e);
        }
    }
}
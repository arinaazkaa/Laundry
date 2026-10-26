<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'event', 'description', 'transaction_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Pencatat singkat. $byCustomer = true untuk aksi pelanggan (tanpa pengguna staf)
    public static function record(string $event, string $description, ?int $transactionId = null, bool $byCustomer = false): void
    {
        try {
            static::create([
                'user_id' => $byCustomer ? null : auth()->id(),
                'event' => $event,
                'description' => $description,
                'transaction_id' => $transactionId,
            ]);
        } catch (\Throwable $e) {
            // Gagal mencatat aktivitas tidak boleh menggagalkan proses utama
            report($e);
        }
    }
}
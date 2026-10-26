<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    // Kolom akses online (password, kode aktivasi) sengaja tidak masuk $fillable.
    // Diisi hanya lewat forceFill di kode yang berwenang.
    protected $fillable = ['name', 'phone', 'address', 'notes', 'is_member'];

    protected $hidden = ['password', 'remember_token', 'activation_code_hash'];

    protected $casts = [
        'is_member' => 'boolean',
        'password' => 'hashed',
        'activated_at' => 'datetime',
        'activation_expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        // Nomor HP selalu disimpan juga dalam bentuk rapi (62xxxxxxxxxx) untuk pencocokan login
        static::saving(function (Customer $customer) {
            $customer->phone_normalized = static::normalizePhone($customer->phone);
        });
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);

        if ($d === '') {
            return null;
        }

        if (str_starts_with($d, '62')) {
            // sudah format internasional
        } elseif (str_starts_with($d, '0')) {
            $d = '62' . substr($d, 1);
        } elseif (str_starts_with($d, '8')) {
            $d = '62' . $d;
        } else {
            return null;
        }

        return (strlen($d) >= 10 && strlen($d) <= 15) ? $d : null;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pickupRequests()
    {
        return $this->hasMany(PickupRequest::class);
    }

    public function getHasOnlineAccessAttribute(): bool
    {
        return $this->password !== null;
    }

    // Memakai hasil withCount/withSum kalau sudah dimuat di daftar, supaya tidak query per baris
    public function getTotalTransactionsAttribute(): int
    {
        return (int) ($this->active_transactions_count
            ?? $this->transactions()->notCancelled()->count());
    }

    public function getTotalSpentAttribute(): float
    {
        return (float) ($this->active_spent
            ?? $this->transactions()->notCancelled()->sum('total'));
    }

    public function getIsEligibleForMemberAttribute(): bool
    {
        if ($this->is_member) {
            return false; // sudah member, tidak perlu direkomendasikan lagi
        }

        $minTransactions = (int) setting('member_min_transactions', 999999);
        $minSpent = (float) setting('member_min_spent', 999999999999);

        return $this->total_transactions >= $minTransactions
            || $this->total_spent >= $minSpent;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address', 'notes', 'is_member'];

    protected $casts = ['is_member' => 'boolean'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTotalTransactionsAttribute(): int
    {
        return $this->transactions()->count();
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->transactions()->sum('total');
    }

    public function getIsEligibleForMemberAttribute(): bool
    {
        if ($this->is_member) {
            return false; // sudah member, tidak perlu direkomendasikan lagi
        }

        $minTransactions = (int) setting('member_min_transactions', 999999);
        $minSpent = (float) setting('member_min_spent', PHP_INT_MAX);

        return $this->total_transactions >= $minTransactions
            || $this->total_spent >= $minSpent;
    }
}
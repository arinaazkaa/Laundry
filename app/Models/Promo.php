<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'type', 'value', 'min_transaction',
        'min_weight', 'start_date', 'end_date', 'start_time', 'end_time',
        'quota', 'used_count', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(PromoUsage::class);
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'nonaktif';

        $today = today();
        if ($today->lt($this->start_date)) return 'belum_dimulai';
        if ($today->gt($this->end_date)) return 'kadaluarsa';
        if ($this->quota !== null && $this->used_count >= $this->quota) return 'habis_kuota';

        return 'aktif';
    }

    public function isUsable(float $subtotal, float $totalWeight): bool
    {
        if ($this->status_label !== 'aktif') return false;

        if ($this->start_time && $this->end_time) {
            $now = now()->format('H:i:s');
            if ($now < $this->start_time || $now > $this->end_time) return false;
        }

        if ($this->min_transaction && $subtotal < $this->min_transaction) return false;
        if ($this->min_weight && $totalWeight < $this->min_weight) return false;

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        return match ($this->type) {
            'diskon_persen' => round($subtotal * ($this->value / 100)),
            'diskon_nominal', 'minimal_transaksi', 'berdasarkan_berat', 'member' => (float) $this->value,
            default => 0, // gratis_ongkir tidak memotong subtotal, hanya ongkir
        };
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'type', 'value', 'value_type', 'min_transaction',
        'min_weight', 'start_date', 'end_date', 'start_time', 'end_time',
        'quota', 'used_count', 'is_active', 'stackable',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'stackable' => 'boolean',
    ];

    public function usages()
    {
        return $this->hasMany(PromoUsage::class);
    }

    /**
     * Promo yang sedang berlaku dan boleh dilihat pelanggan ini
     * (promo khusus member disembunyikan dari non-member).
     */
    public function scopeVisibleTo($query, Customer $customer)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->where(fn ($q) => $q->whereNull('quota')->orWhereColumn('used_count', '<', 'quota'))
            ->when(! $customer->is_member, fn ($q) => $q->where('type', '!=', 'member'));
    }

    /**
     * Ringkasan promo untuk ditampilkan di portal pelanggan.
     */
    public function portalSummary(): array
    {
        $value = match (true) {
            $this->type === 'gratis_ongkir' => 'Gratis Ongkir',
            $this->value_type === 'persen' => rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.') . '% off',
            default => 'Potongan Rp ' . number_format((float) $this->value, 0, ',', '.'),
        };

        $syarat = [];
        if ($this->min_transaction) {
            $syarat[] = 'Min. belanja Rp ' . number_format((float) $this->min_transaction, 0, ',', '.');
        }
        if ($this->min_weight) {
            $syarat[] = 'Min. ' . (float) $this->min_weight . ' kg';
        }
        if ($this->start_time && $this->end_time) {
            $syarat[] = 'Pukul ' . substr($this->start_time, 0, 5) . '-' . substr($this->end_time, 0, 5);
        }

        return [
            'name' => $this->name,
            'description' => $this->description,
            'value' => $value,
            'syarat' => implode(' · ', $syarat),
            'until' => $this->end_date->translatedFormat('d M Y'),
            'days_left' => (int) max(0, today()->diffInDays($this->end_date, false)),
            'member_only' => $this->type === 'member',
        ];
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

    /**
     * Alasan promo tidak bisa dipakai. null = lolos semua syarat.
     */
    public function ineligibleReason(float $subtotal, float $totalWeight, ?Customer $customer = null): ?string
    {
        if ($this->status_label !== 'aktif') {
            return match ($this->status_label) {
                'belum_dimulai' => 'Promo ini belum dimulai.',
                'kadaluarsa' => 'Promo ini sudah tidak berlaku.',
                'habis_kuota' => 'Promo sudah mencapai batas penggunaan.',
                default => 'Promo sedang dinonaktifkan.',
            };
        }

        if ($this->type === 'member' && ! ($customer && $customer->is_member)) {
            return 'Promo ini khusus pelanggan member.';
        }

        if ($this->start_time && $this->end_time) {
            $now = now()->format('H:i:s');
            if ($now < $this->start_time || $now > $this->end_time) {
                return 'Promo ini hanya berlaku pukul ' . substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5) . '.';
            }
        }

        if ($this->min_transaction && $subtotal < $this->min_transaction) {
            return 'Minimal transaksi Rp ' . number_format($this->min_transaction, 0, ',', '.') . ' belum terpenuhi.';
        }

        if ($this->min_weight && $totalWeight < $this->min_weight) {
            return 'Minimal berat ' . (float) $this->min_weight . ' kg belum terpenuhi.';
        }

        return null;
    }

    public function isUsable(float $subtotal, float $totalWeight, ?Customer $customer = null): bool
    {
        return $this->ineligibleReason($subtotal, $totalWeight, $customer) === null;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $value = (float) $this->value;

        // Gratis ongkir tidak memotong subtotal, yang digratiskan adalah ongkirnya
        if ($this->type === 'gratis_ongkir' || $value <= 0) {
            return 0;
        }

        $discount = $this->value_type === 'persen'
            ? round($subtotal * ($value / 100))
            : $value;

        // Potongan tidak boleh lebih besar dari subtotal
        return min($discount, $subtotal);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = [
        'name', 'service_id', 'weight_kg', 'description', 'normal_price', 'package_price',
        'start_date', 'end_date', 'quota', 'used_count', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'package_id');
    }

    // Paket yang bisa dipilih kasir hari ini
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->whereHas('service', fn ($q) => $q->where('is_active', true))
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->where(fn ($q) => $q->whereNull('quota')->orWhereColumn('used_count', '<', 'quota'));
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) return 'nonaktif';
        if (! $this->service || ! $this->service->is_active) return 'layanan_nonaktif';

        $today = today();
        if ($this->start_date && $today->lt($this->start_date)) return 'belum_dimulai';
        if ($this->end_date && $today->gt($this->end_date)) return 'kadaluarsa';
        if ($this->quota !== null && $this->used_count >= $this->quota) return 'habis_kuota';

        return 'aktif';
    }

    /**
     * Alasan paket tidak bisa dipakai sebanyak $qty. null = bisa dipakai.
     */
    public function unavailableReason(int $qty = 1): ?string
    {
        return match ($this->status_label) {
            'nonaktif' => 'Paket "' . $this->name . '" sedang dinonaktifkan.',
            'layanan_nonaktif' => 'Layanan untuk paket "' . $this->name . '" sedang tidak aktif.',
            'belum_dimulai' => 'Paket "' . $this->name . '" belum dimulai.',
            'kadaluarsa' => 'Paket "' . $this->name . '" sudah tidak berlaku.',
            'habis_kuota' => 'Kuota paket "' . $this->name . '" sudah habis.',
            default => ($this->quota !== null && $this->used_count + $qty > $this->quota)
                ? 'Sisa kuota paket "' . $this->name . '" hanya ' . ($this->quota - $this->used_count) . '.'
                : null,
        };
    }

    // Harga normal dihitung dari harga layanan saat ini, jadi tidak basi kalau harga layanan berubah
    public function getNormalPriceLiveAttribute(): float
    {
        return $this->service
            ? (float) $this->service->price * (float) $this->weight_kg
            : (float) $this->normal_price;
    }

    public function getSavingAttribute(): float
    {
        return max(0, $this->normal_price_live - (float) $this->package_price);
    }
}
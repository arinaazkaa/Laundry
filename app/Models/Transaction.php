<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'customer_id', 'cashier_id', 'subtotal', 'discount',
        'delivery_fee', 'total', 'paid_amount', 'payment_status',
        'payment_method', 'status', 'promo_id', 'use_pickup',
        'use_delivery', 'estimated_done_at', 'notes',
    ];

    protected $casts = [
        'use_pickup' => 'boolean',
        'use_delivery' => 'boolean',
        'estimated_done_at' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function (Transaction $t) {
            $t->code = $t->code ?: generate_transaction_code();
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pickupDeliveries()
    {
        return $this->hasMany(PickupDelivery::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->total - $this->paid_amount);
    }

    public function getChangeAttribute(): float
    {
        return max(0, $this->paid_amount - $this->total);
    }

    public const STATUS_FLOW = [
        'menunggu', 'diproses', 'dicuci', 'dikeringkan',
        'disetrika', 'siap_diambil', 'selesai',
    ];
}
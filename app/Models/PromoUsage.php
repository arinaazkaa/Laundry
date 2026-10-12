<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoUsage extends Model
{
    protected $fillable = ['promo_id', 'transaction_id', 'discount_amount'];

    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupDelivery extends Model
{
    protected $fillable = ['transaction_id', 'type', 'status', 'scheduled_at', 'address', 'notes'];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
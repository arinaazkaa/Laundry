<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['transaction_id', 'amount', 'method', 'received_by'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
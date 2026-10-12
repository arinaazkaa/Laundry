<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date', 'category', 'description', 'amount', 'notes', 'created_by'];

    protected $casts = ['date' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = [
        'name', 'weight_kg', 'description', 'normal_price', 'package_price',
        'start_date', 'end_date', 'quota', 'used_count', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function getSavingAttribute(): float
    {
        return $this->normal_price - $this->package_price;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'category', 'unit', 'current_stock', 'min_stock'];

    public function logs()
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function getIsLowAttribute(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }
}
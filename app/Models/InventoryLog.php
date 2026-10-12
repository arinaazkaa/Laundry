<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = ['inventory_item_id', 'type', 'qty', 'notes', 'created_by'];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
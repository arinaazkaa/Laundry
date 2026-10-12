<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static function booted()
    {
        static::saved(fn () => cache()->flush());
        static::deleted(fn () => cache()->flush());
    }
}
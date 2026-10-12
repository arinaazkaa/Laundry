<?php

use App\Models\BusinessSetting;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return cache()->rememberForever("setting_{$key}", function () use ($key, $default) {
            $row = BusinessSetting::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }
}

if (! function_exists('generate_transaction_code')) {
    function generate_transaction_code(): string
    {
        $prefix = 'LD';
        $date = now()->format('ymd');
        $countToday = \App\Models\Transaction::whereDate('created_at', today())->count() + 1;
        return sprintf('%s-%s-%03d', $prefix, $date, $countToday);
    }
}
<?php

use App\Models\BusinessSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        // Yang disimpan di cache hanya isi database, bukan nilai default, supaya default tidak "menempel"
        $value = cache()->rememberForever("setting_{$key}", function () use ($key) {
            return BusinessSetting::where('key', $key)->value('value') ?? '__none__';
        });

        return $value === '__none__' ? $default : $value;
    }
}

/*
|--------------------------------------------------------------------------
| Nomor transaksi
|--------------------------------------------------------------------------
*/

if (! function_exists('invoice_format')) {
    function invoice_format(): array
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) setting('invoice_prefix', 'LD')));
        $prefix = $prefix !== '' ? substr($prefix, 0, 6) : 'LD';

        $dateFormat = (string) setting('invoice_date_format', 'ymd');
        if (! in_array($dateFormat, ['ymd', 'ym', 'y', 'none'], true)) {
            $dateFormat = 'ymd';
        }

        $digits = max(3, min(5, (int) setting('invoice_digits', 3)));

        return [$prefix, $dateFormat, $digits];
    }
}

if (! function_exists('generate_transaction_code')) {
    function generate_transaction_code(): string
    {
        [$prefix, $dateFormat, $digits] = invoice_format();

        // Contoh head: "LD-261025-". Urutan di-reset otomatis mengikuti format tanggalnya
        $head = $prefix . '-' . ($dateFormat !== 'none' ? now()->format($dateFormat) . '-' : '');

        $last = Transaction::where('code', 'like', $head . '%')
            ->whereRaw('SUBSTRING(code, ?) REGEXP ?', [strlen($head) + 1, '^[0-9]+$'])
            ->orderByRaw('LENGTH(code) DESC')
            ->orderByDesc('code')
            ->value('code');

        $sequence = $last ? ((int) substr($last, strlen($head))) + 1 : 1;

        do {
            $code = $head . str_pad((string) $sequence, $digits, '0', STR_PAD_LEFT);
            $exists = Transaction::where('code', $code)->exists();
            $sequence++;
        } while ($exists);

        return $code;
    }
}

/*
|--------------------------------------------------------------------------
| Metode pembayaran
|--------------------------------------------------------------------------
*/

if (! function_exists('payment_method_labels')) {
    function payment_method_labels(): array
    {
        return ['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet'];
    }
}

if (! function_exists('payment_methods')) {
    // Metode yang diaktifkan pemilik. Selalu ada minimal satu (Tunai)
    function payment_methods(): array
    {
        $enabled = array_filter(explode(',', (string) setting('payment_methods', 'cash,transfer,qris,ewallet')));
        $methods = array_intersect_key(payment_method_labels(), array_flip($enabled));

        return $methods ?: ['cash' => 'Tunai'];
    }
}

/*
|--------------------------------------------------------------------------
| Jam operasional
|--------------------------------------------------------------------------
*/

if (! function_exists('operating_hours_text')) {
    function operating_hours_text(): ?string
    {
        $open = (string) setting('operating_open', '');
        $close = (string) setting('operating_close', '');

        if ($open === '' || $close === '') {
            return null;
        }

        $names = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        $days = collect(explode(',', (string) setting('operating_days', '1,2,3,4,5,6,7')))
            ->map(fn ($d) => (int) $d)
            ->filter(fn ($d) => isset($names[$d]))
            ->unique()->sort()->values()->all();

        if (empty($days)) {
            return null;
        }

        $first = $days[0];
        $last = $days[count($days) - 1];

        if (count($days) === 7) {
            $dayText = 'Setiap hari';
        } elseif (count($days) >= 3 && $days === range($first, $last)) {
            $dayText = $names[$first] . ' – ' . $names[$last];
        } else {
            $dayText = collect($days)->map(fn ($d) => $names[$d])->join(', ');
        }

        $time = fn ($t) => str_replace(':', '.', substr($t, 0, 5));

        return $dayText . ', ' . $time($open) . ' – ' . $time($close) . ' WIB';
    }
}

/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
*/

if (! function_exists('business_logo_path')) {
    function business_logo_path(): ?string
    {
        $path = setting('business_logo');

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }

        // Cadangan: file yang ditaruh manual di public/images/logo.png
        if (file_exists(public_path('images/logo.png'))) {
            return public_path('images/logo.png');
        }

        return null;
    }
}

if (! function_exists('business_logo_url')) {
    function business_logo_url(): ?string
    {
        $path = setting('business_logo');

        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        if (file_exists(public_path('images/logo.png'))) {
            return asset('images/logo.png');
        }

        return null;
    }
}

if (! function_exists('business_logo_data_uri')) {
    // Dipakai nota PDF supaya gambar pasti tampil di dompdf
    function business_logo_data_uri(): ?string
    {
        $path = business_logo_path();

        if (! $path || ! is_readable($path)) {
            return null;
        }

        $mime = str_ends_with(strtolower($path), '.png') ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
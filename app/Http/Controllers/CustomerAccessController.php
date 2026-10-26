<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerAccessController extends Controller
{
    // Tanpa huruf dan angka yang mudah tertukar (O/0, I/1/L)
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public function store(Request $request, Customer $customer)
    {
        if (! $customer->phone_normalized) {
            return back()->with('error', 'Isi nomor HP pelanggan dulu (contoh 0812xxxxxxxx) sebelum membuat kode aktivasi.');
        }

        $taken = Customer::where('id', '!=', $customer->id)
            ->where('phone_normalized', $customer->phone_normalized)
            ->whereNotNull('password')
            ->exists();

        if ($taken) {
            return back()->with('error', 'Nomor HP ini sudah dipakai akun online pelanggan lain. Periksa apakah ada data pelanggan ganda.');
        }

        $code = $this->makeCode();
        $expires = now()->addDays(7);

        $customer->forceFill([
            'activation_code_hash' => Hash::make(str_replace('-', '', $code)),
            'activation_expires_at' => $expires,
        ])->save();

        $this->log($request, $customer->has_online_access
            ? 'membuat kode reset akses online untuk ' . $customer->name
            : 'membuat kode aktivasi akun online untuk ' . $customer->name);

        // Kode asli hanya dikirim sekali lewat flash. Di database hanya tersimpan hash-nya
        return redirect()->route('customers.show', $customer)
            ->with('activation', ['code' => $code, 'expires' => $expires->toIso8601String()]);
    }

    public function destroy(Request $request, Customer $customer)
    {
        $customer->forceFill([
            'password' => null,
            'remember_token' => null,
            'activation_code_hash' => null,
            'activation_expires_at' => null,
            'activated_at' => null,
        ])->save();

        $this->log($request, 'mencabut akses online ' . $customer->name);

        return back()->with('success', 'Akses online ' . $customer->name . ' dicabut. Sesi login yang sedang aktif tidak berlaku lagi.');
    }

    private function makeCode(): string
    {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return substr($code, 0, 4) . '-' . substr($code, 4);
    }

    private function log(Request $request, string $description): void
    {
        try {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'event' => 'customer_access',
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:30',
            'password' => 'required|string|max:100',
        ]);

        $normalized = Customer::normalizePhone($data['phone']);
        $key = 'portal-login:' . sha1(($normalized ?? $data['phone']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages([
                'phone' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        $customer = $normalized
            ? Customer::where('phone_normalized', $normalized)->whereNotNull('password')->first()
            : null;

        if (! $customer || ! Hash::check($data['password'], $customer->password)) {
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages(['phone' => 'Nomor HP atau kata sandi salah.']);
        }

        RateLimiter::clear($key);

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('portal.home');
    }

    public function showActivate()
    {
        return view('portal.activate');
    }

    public function activate(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:30',
            'code' => 'required|string|max:20',
            'password' => 'required|string|min:8|max:100|confirmed',
        ]);

        $normalized = Customer::normalizePhone($data['phone']);
        $key = 'portal-activate:' . sha1(($normalized ?? $data['phone']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        $failMessage = 'Nomor HP atau kode tidak cocok, atau kode sudah kedaluwarsa. Minta kode baru ke kasir.';
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['code']));

        $candidate = $normalized
            ? Customer::where('phone_normalized', $normalized)
                ->whereNotNull('activation_code_hash')
                ->where('activation_expires_at', '>', now())
                ->get()
                ->first(fn ($c) => Hash::check($code, $c->activation_code_hash))
            : null;

        if (! $candidate) {
            RateLimiter::hit($key, 900);
            throw ValidationException::withMessages(['code' => $failMessage]);
        }

        $result = DB::transaction(function () use ($candidate, $normalized, $data) {
            // Kunci barisnya supaya satu kode tidak bisa dipakai dua kali bersamaan
            $customer = Customer::lockForUpdate()->find($candidate->id);

            if (! $customer->activation_code_hash || ! $customer->activation_expires_at || $customer->activation_expires_at->isPast()) {
                return 'expired';
            }

            $taken = Customer::where('id', '!=', $customer->id)
                ->where('phone_normalized', $normalized)
                ->whereNotNull('password')
                ->exists();

            if ($taken) {
                return 'taken';
            }

            $customer->forceFill([
                'password' => $data['password'],
                'activated_at' => $customer->activated_at ?? now(),
                'activation_code_hash' => null,
                'activation_expires_at' => null,
                'remember_token' => Str::random(60), // sesi "ingat saya" yang lama tidak berlaku lagi
            ])->save();

            return $customer;
        });

        if ($result === 'taken') {
            throw ValidationException::withMessages([
                'code' => 'Nomor HP ini sudah dipakai akun lain. Hubungi toko.',
            ]);
        }

        if ($result === 'expired') {
            throw ValidationException::withMessages(['code' => $failMessage]);
        }

        RateLimiter::clear($key);

        Auth::guard('customer')->login($result);
        $request->session()->regenerate();

        return redirect()->route('portal.home')->with('success', 'Akun aktif. Selamat datang!');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
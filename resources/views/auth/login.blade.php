<x-guest-layout>
<div class="min-h-screen flex" x-data="{ showPassword: false }">

    {{-- Panel Kiri — Foto/Branding --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-500">

        {{--
            GANTI DENGAN FOTO ASLI LAUNDRY KAMU:
            1. Taruh file foto di: public/images/laundry-hero.jpg
            2. Uncomment baris <img> di bawah ini, hapus/comment div gradient di atas
        --}}
        {{-- <img src="{{ asset('images/laundry-hero.jpg') }}" class="absolute inset-0 w-full h-full object-cover"> --}}
        {{-- <div class="absolute inset-0 bg-brand-900/60"></div> --}}

        {{-- Dekorasi lingkaran samar (dipakai selama belum ada foto asli) --}}
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-sm"></div>
        <div class="absolute -bottom-32 -right-16 w-80 h-80 bg-white/10 rounded-full blur-sm"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] border border-white/10 rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[380px] h-[380px] border border-white/10 rounded-full"></div>

        <div class="relative z-10 flex flex-col items-center justify-center w-full text-center px-12">
            <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center mb-5 border border-white/20">
                @if($logoUrl = business_logo_url())
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-10 h-10 object-contain">
                @else
                    <span class="text-3xl">🧺</span>
                @endif
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">{{ setting('business_name', 'Laundry Desa') }}</h1>
            <p class="text-brand-100 max-w-sm">
                Sistem Manajemen Laundry yang Andal dan Mudah Digunakan untuk Bisnis Anda.
            </p>
        </div>
    </div>

    {{-- Panel Kanan — Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white">
        <div class="w-full max-w-sm">

            <div class="lg:hidden text-center mb-8">
                <h1 class="text-2xl font-bold text-brand-600">{{ setting('business_name', 'Laundry Desa') }}</h1>
            </div>

            <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang Kembali</h2>
            <p class="text-gray-500 mb-8">Masuk ke sistem manajemen laundry Anda</p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               autocomplete="username" placeholder="Masukkan email Anda"
                               class="w-full pl-12 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-sm font-semibold text-gray-700">Kata Sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                                Lupa Kata Sandi?
                            </a>
                        @endif
                    </div>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                               autocomplete="current-password" placeholder="Masukkan kata sandi"
                               class="w-full pl-12 pr-12 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full bg-brand-600 text-white rounded-xl py-3.5 font-semibold hover:bg-brand-700 transition shadow-sm shadow-brand-200">
                    Masuk ke Akun
                </button>

                <hr class="border-gray-100">

                <p class="text-center text-sm text-gray-500">
                    Belum punya akun?
                    <span class="text-brand-600 font-semibold">Hubungi Pemilik untuk akses</span>
                    <p class="text-center text-sm text-gray-500">
                        Pelanggan?
                        <a href="{{ route('portal.login') }}" class="text-brand-600 font-semibold">Masuk ke akun pelanggan</a>
                    </p>
                </p>
            </form>
        </div>
    </div>
</div>
</x-guest-layout>
<x-guest-layout>
<div class="min-h-screen flex" x-data="{ showPassword: false, agreed: false }">

    {{-- Panel Kiri — Form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white order-2 lg:order-1">
        <div class="w-full max-w-sm">

            <div class="flex items-center gap-2 mb-8">
                <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white">🧺</div>
                <span class="font-bold text-brand-600 text-lg">{{ setting('business_name', 'Laundry Desa') }}</span>
            </div>

            <h2 class="text-3xl font-bold text-gray-900 mb-2">Daftar Akun Baru</h2>
            <p class="text-gray-500 mb-8">Mulai kelola laundry Anda dengan lebih cerdas.</p>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                               autocomplete="name" placeholder="Masukkan nama lengkap Anda"
                               class="w-full pl-12 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                               autocomplete="username" placeholder="nama@email.com"
                               class="w-full pl-12 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <div>
                    <label for="phone" class="text-sm font-semibold text-gray-700">Nomor WhatsApp <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                        </span>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                               autocomplete="tel" placeholder="08xxxxxxxxxx"
                               class="w-full pl-12 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold text-gray-700">Kata Sandi</label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required
                               autocomplete="new-password" placeholder="Minimal 8 karakter"
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

                <div>
                    <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Konfirmasi Kata Sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           autocomplete="new-password" placeholder="Ulangi kata sandi"
                           class="w-full mt-1.5 border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                </div>

                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" x-model="agreed" required class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 mt-0.5">
                    <span class="text-sm text-gray-600">
                        Saya menyetujui <span class="text-brand-600 font-medium">Syarat & Ketentuan</span> dan <span class="text-brand-600 font-medium">Kebijakan Privasi</span> yang berlaku.
                    </span>
                </label>

                <button type="submit" :disabled="!agreed"
                        class="w-full bg-brand-600 text-white rounded-xl py-3.5 font-semibold hover:bg-brand-700 transition shadow-sm shadow-brand-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    Daftar Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>

                <p class="text-center text-sm text-gray-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-brand-600 font-semibold hover:text-brand-700">Masuk di sini</a>
                </p>
            </form>
        </div>
    </div>

    {{-- Panel Kanan — Foto/Branding --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-bl from-brand-700 via-brand-600 to-brand-500 order-1 lg:order-2">

        {{-- GANTI DENGAN FOTO ASLI: taruh di public/images/laundry-register.jpg lalu uncomment --}}
        {{-- <img src="{{ asset('images/laundry-register.jpg') }}" class="absolute inset-0 w-full h-full object-cover"> --}}
        {{-- <div class="absolute inset-0 bg-brand-900/50"></div> --}}

        <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/10 rounded-full blur-sm"></div>
        <div class="absolute -bottom-24 -left-16 w-72 h-72 bg-white/10 rounded-full blur-sm"></div>

        <div class="relative z-10 flex flex-col items-center justify-center w-full h-full p-12">
            <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center mb-5 border border-white/20 text-3xl">
                🧺
            </div>
            <h2 class="text-2xl font-bold text-white text-center mb-8">
                Kelola laundry kamu<br>dengan lebih mudah.
            </h2>

            <div class="bg-white rounded-2xl p-5 max-w-sm shadow-xl">
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-success-50 text-success-600 flex items-center justify-center flex-shrink-0">
                        ✓
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Solusi Terpercaya</p>
                        <p class="text-xs text-gray-500 mt-1">
                            Bergabung dengan pengusaha laundry lainnya yang telah meningkatkan efisiensi bisnis mereka.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-guest-layout>
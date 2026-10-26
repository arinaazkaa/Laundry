<x-layouts.portal-layout title="Aktivasi Akun">
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mt-4">
        <h1 class="text-2xl font-bold text-gray-900">Aktifkan Akun</h1>
        <p class="text-sm text-gray-500 mt-1 mb-5">Minta kode aktivasi ke kasir, lalu buat kata sandi sendiri di sini. Cukup sekali.</p>

        <form method="POST" action="{{ route('portal.activate.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nomor HP (yang didaftarkan di toko)</label>
                <input type="tel" name="phone" value="{{ old('phone', request('hp')) }}" required inputmode="tel"
                       placeholder="0812xxxxxxxx" class="w-full border-gray-200 rounded-xl mt-1">
                @error('phone') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kode Aktivasi</label>
                <input type="text" name="code" value="{{ old('code', request('kode')) }}" required autocomplete="off"
                       placeholder="XXXX-XXXX" class="w-full border-gray-200 rounded-xl mt-1 uppercase tracking-widest font-semibold">
                @error('code') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kata Sandi Baru</label>
                <input type="password" name="password" required minlength="8" autocomplete="new-password"
                       placeholder="Minimal 8 karakter" class="w-full border-gray-200 rounded-xl mt-1">
                @error('password') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Ulangi Kata Sandi</label>
                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                       class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <button class="w-full bg-brand-600 text-white rounded-xl py-3 font-medium">Aktifkan Akun</button>
        </form>

        <p class="mt-5 pt-4 border-t text-sm text-gray-500">Sudah punya akun? <a href="{{ route('portal.login') }}" class="text-brand-600 font-medium">Masuk</a></p>
    </div>
</x-layouts.portal-layout>
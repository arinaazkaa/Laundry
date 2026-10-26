<x-layouts.portal-layout title="Masuk">
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mt-4">
        <h1 class="text-2xl font-bold text-gray-900">Masuk</h1>
        <p class="text-sm text-gray-500 mt-1 mb-5">Cek status cucian dan riwayat transaksi kamu.</p>

        <form method="POST" action="{{ route('portal.login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nomor HP</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required autofocus autocomplete="tel"
                       inputmode="tel" placeholder="0812xxxxxxxx" class="w-full border-gray-200 rounded-xl mt-1">
                @error('phone') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kata Sandi</label>
                <input type="password" name="password" required autocomplete="current-password" class="w-full border-gray-200 rounded-xl mt-1">
                @error('password') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300"> Ingat saya
            </label>
            <button class="w-full bg-brand-600 text-white rounded-xl py-3 font-medium">Masuk</button>
        </form>

        <div class="mt-5 pt-4 border-t text-sm text-gray-500 space-y-1">
            <p>Belum punya akun? <a href="{{ route('portal.activate') }}" class="text-brand-600 font-medium">Aktifkan dengan kode dari kasir</a></p>
            <p>Lupa kata sandi? Minta kode baru ke kasir.</p>
        </div>
    </div>
</x-layouts.portal-layout>
<x-layouts.portal-layout title="Akun">
    <h1 class="text-2xl font-bold text-gray-900">Akun Saya</h1>

    <div class="bg-white rounded-2xl border border-gray-100 p-5 space-y-3 text-sm">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold
                {{ $customer->is_member ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-600' }}">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </span>
            <div>
                <p class="font-semibold text-gray-900">{{ $customer->name }}</p>
                @if($customer->is_member)<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">⭐ Member</span>@endif
            </div>
        </div>
        <div class="flex justify-between"><span class="text-gray-500">Nomor HP</span><span>{{ $customer->phone }}</span></div>
        <div class="flex justify-between gap-4"><span class="text-gray-500">Alamat</span><span class="text-right">{{ $customer->address ?? '-' }}</span></div>
        <p class="text-xs text-gray-400">Untuk mengubah nomor HP atau alamat, hubungi toko.</p>
    </div>

    <form method="POST" action="{{ route('portal.password') }}" class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
        @csrf @method('PUT')
        <h2 class="font-semibold">Ganti Kata Sandi</h2>

        <div>
            <label class="text-sm font-medium text-gray-700">Kata Sandi Sekarang</label>
            <input type="password" name="current_password" required autocomplete="current-password" class="w-full border-gray-200 rounded-xl mt-1">
            @error('current_password') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Kata Sandi Baru</label>
            <input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full border-gray-200 rounded-xl mt-1">
            @error('password') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Ulangi Kata Sandi Baru</label>
            <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full border-gray-200 rounded-xl mt-1">
        </div>
        <button class="w-full bg-brand-600 text-white rounded-xl py-3 text-sm font-medium">Simpan Kata Sandi</button>
    </form>

    <form method="POST" action="{{ route('portal.logout') }}">
        @csrf
        <button class="w-full border border-danger-200 text-danger-600 bg-white rounded-xl py-3 text-sm font-medium">Keluar</button>
    </form>
</x-layouts.portal-layout>
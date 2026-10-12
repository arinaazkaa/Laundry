<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Pengaturan</h1></x-slot>

    <h1 class="text-3xl font-bold text-gray-900 mb-1">Pengaturan Bisnis</h1>
    <p class="text-gray-500 mb-6">Semua aturan di sini bisa diubah kapan saja tanpa perlu ubah kode program.</p>

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6 max-w-3xl">
        @csrf @method('PUT')

        {{-- Profil Bisnis --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Profil Bisnis</h2>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Nama Laundry</label>
                    <input type="text" name="business_name" value="{{ $settings['business_name'] ?? '' }}" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="business_address" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ $settings['business_address'] ?? '' }}</textarea>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Nomor WhatsApp (format: 62xxxxxxxxxx)</label>
                    <input type="text" name="business_whatsapp" value="{{ $settings['business_whatsapp'] ?? '' }}" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Estimasi Durasi Pengerjaan (hari)</label>
                    <input type="number" name="work_duration_days" value="{{ $settings['work_duration_days'] ?? 2 }}" min="1" class="w-full md:w-40 border-gray-200 rounded-xl mt-1">
                </div>
            </div>
        </div>

        {{-- Antar-Jemput --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Antar-Jemput & Ongkir</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div>
                        <p class="text-sm font-medium">Layanan Jemput</p>
                        <p class="text-xs text-gray-400">Aktifkan opsi jemput di halaman Transaksi Baru</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="pickup_enabled" value="0">
                        <input type="checkbox" name="pickup_enabled" value="1" class="sr-only peer" {{ ($settings['pickup_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full peer transition"></div>
                        <div class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div>
                        <p class="text-sm font-medium">Layanan Antar</p>
                        <p class="text-xs text-gray-400">Aktifkan opsi antar di halaman Transaksi Baru</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="delivery_enabled" value="0">
                        <input type="checkbox" name="delivery_enabled" value="1" class="sr-only peer" {{ ($settings['delivery_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full peer transition"></div>
                        <div class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition"></div>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Biaya Jemput (Rp)</label>
                        <input type="number" name="pickup_fee" value="{{ $settings['pickup_fee'] ?? 0 }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Biaya Antar (Rp)</label>
                        <input type="number" name="delivery_fee" value="{{ $settings['delivery_fee'] ?? 0 }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                </div>

                <div class="flex items-center justify-between bg-gray-50 rounded-xl p-3">
                    <div>
                        <p class="text-sm font-medium">Gratis Ongkir Otomatis</p>
                        <p class="text-xs text-gray-400">Berlaku jika berat laundry memenuhi minimum</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="free_ongkir_enabled" value="0">
                        <input type="checkbox" name="free_ongkir_enabled" value="1" class="sr-only peer" {{ ($settings['free_ongkir_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full peer transition"></div>
                        <div class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition"></div>
                    </label>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Minimal Berat untuk Gratis Ongkir (kg)</label>
                    <input type="number" step="0.1" name="free_ongkir_min_weight" value="{{ $settings['free_ongkir_min_weight'] ?? 0 }}" min="0" class="w-full md:w-40 border-gray-200 rounded-xl mt-1">
                </div>
            </div>
        </div>

        {{-- Member --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h2 class="font-semibold mb-4">Kriteria & Benefit Member</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Rekomendasi Member Setelah (x transaksi)</label>
                        <input type="number" name="member_min_transactions" value="{{ $settings['member_min_transactions'] ?? 5 }}" min="1" class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">ATAU Total Belanja Minimum (Rp)</label>
                        <input type="number" name="member_min_spent" value="{{ $settings['member_min_spent'] ?? 200000 }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Diskon Otomatis untuk Member (%)</label>
                    <input type="number" name="member_discount_percent" value="{{ $settings['member_discount_percent'] ?? 5 }}" min="0" max="100" class="w-full md:w-40 border-gray-200 rounded-xl mt-1">
                    <p class="text-xs text-gray-400 mt-1">Diberikan otomatis tiap transaksi member, kecuali sedang memakai promo lain.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button class="bg-brand-600 text-white px-6 py-3 rounded-xl text-sm font-medium">Simpan Semua Pengaturan</button>
        </div>
    </form>
</x-layouts.app-layout>
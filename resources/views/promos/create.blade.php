<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Buat Promo</h1></x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">Buat Promo Baru</h1>

        <form method="POST" action="{{ route('promos.store') }}"
              x-data="{
                type: '{{ old('type', 'diskon_persen') }}',
                valueType: '{{ old('value_type', 'persen') }}',
                flexible() { return ['berdasarkan_berat', 'minimal_transaksi', 'member'].includes(this.type); },
                isPercent() { return this.type === 'diskon_persen' || (this.flexible() && this.valueType === 'persen'); }
            }"
              class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium text-gray-700">Nama Promo <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Jenis Promo <span class="text-danger-500">*</span></label>
                <select name="type" x-model="type" required class="w-full border-gray-200 rounded-xl mt-1">
                    <option value="gratis_ongkir">Gratis Ongkir</option>
                    <option value="diskon_persen">Diskon Persen</option>
                    <option value="diskon_nominal">Diskon Nominal</option>
                    <option value="berdasarkan_berat">Berdasarkan Berat</option>
                    <option value="minimal_transaksi">Minimal Transaksi</option>
                    <option value="member">Khusus Member</option>
                </select>
            </div>

            <div x-show="type !== 'gratis_ongkir'" class="grid grid-cols-2 gap-4">
                <div x-show="flexible()">
                    <label class="text-sm font-medium text-gray-700">Bentuk Potongan</label>
                    <select name="value_type" x-model="valueType" class="w-full border-gray-200 rounded-xl mt-1">
                        <option value="persen">Persen (%)</option>
                        <option value="nominal">Rupiah (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        Nilai <span x-text="isPercent() ? '(%)' : '(Rp)'"></span>
                    </label>
                    <input type="number" step="any" name="value" value="{{ old('value') }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                    @error('value') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Minimal Transaksi (Rp)</label>
                    <input type="number" name="min_transaction" value="{{ old('min_transaction') }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Minimal Berat (kg)</label>
                    <input type="number" step="0.1" name="min_weight" value="{{ old('min_weight') }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-danger-500">*</span></label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tanggal Berakhir <span class="text-danger-500">*</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Jam Mulai (opsional)</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Jam Berakhir (opsional)</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Kuota (opsional, kosongkan jika tanpa batas)</label>
                <input type="number" name="quota" value="{{ old('quota') }}" min="1" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div class="rounded-xl bg-gray-50 p-3">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="stackable" value="0">
                    <input type="checkbox" name="stackable" value="1" class="mt-1 rounded border-gray-300 text-brand-600"
                        {{ old('stackable', false) ? 'checked' : '' }}>
                    <span>
                        <span class="block text-sm font-medium text-gray-700">Boleh digabung dengan promo lain</span>
                        <span class="block text-xs text-gray-400">Berlaku kalau "Promo dapat digabungkan" diaktifkan di Pengaturan. Contoh: Gratis Ongkir + Diskon Hari Raya, keduanya diberi centang ini.</span>
                    </span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('promos.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Promo</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
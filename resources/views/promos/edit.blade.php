<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Promo</h1></x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">Edit Promo</h1>

        <form method="POST" action="{{ route('promos.update', $promo) }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="text-sm font-medium text-gray-700">Nama Promo</label>
                <input type="text" name="name" value="{{ old('name', $promo->name) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('description', $promo->description) }}</textarea>
            </div>

            @if($promo->type !== 'gratis_ongkir')
                @php $flexible = in_array($promo->type, ['berdasarkan_berat', 'minimal_transaksi', 'member']); @endphp
                <div class="grid grid-cols-2 gap-4">
                    @if($flexible)
                    <div>
                        <label class="text-sm font-medium text-gray-700">Bentuk Potongan</label>
                        <select name="value_type" class="w-full border-gray-200 rounded-xl mt-1">
                            <option value="persen" {{ old('value_type', $promo->value_type) === 'persen' ? 'selected' : '' }}>Persen (%)</option>
                            <option value="nominal" {{ old('value_type', $promo->value_type) === 'nominal' ? 'selected' : '' }}>Rupiah (Rp)</option>
                        </select>
                    </div>
                    @endif
                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Nilai @unless($flexible) ({{ $promo->value_type === 'persen' ? '%' : 'Rp' }}) @endunless
                        </label>
                        <input type="number" step="any" name="value" value="{{ old('value', $promo->value + 0) }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                        @error('value') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Minimal Transaksi (Rp)</label>
                    <input type="number" name="min_transaction" value="{{ old('min_transaction', $promo->min_transaction) }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Minimal Berat (kg)</label>
                    <input type="number" step="0.1" name="min_weight" value="{{ old('min_weight', $promo->min_weight) }}" min="0" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $promo->start_date->format('Y-m-d')) }}" required class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tanggal Berakhir</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $promo->end_date->format('Y-m-d')) }}" required class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Kuota</label>
                <input type="number" name="quota" value="{{ old('quota', $promo->quota) }}" min="1" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('promos.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
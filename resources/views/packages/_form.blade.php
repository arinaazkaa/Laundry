@php
    $isEdit = $package !== null;
    $weightVal = old('weight_kg', $isEdit ? $package->weight_kg + 0 : '');
    $priceVal = old('package_price', $isEdit ? $package->package_price + 0 : '');
    $serviceVal = old('service_id', $isEdit ? $package->service_id : '');
@endphp

<form method="POST" action="{{ $isEdit ? route('packages.update', $package) : route('packages.store') }}"
      x-data="{
          prices: @js($services->pluck('price', 'id')),
          serviceId: '{{ $serviceVal }}',
          weight: '{{ $weightVal }}',
          price: '{{ $priceVal }}',
          rupiah(v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); },
          get normal() { return Number(this.prices[this.serviceId] ?? 0) * Number(this.weight || 0); },
          get saving() { return this.normal - Number(this.price || 0); }
      }"
      class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="text-sm font-medium text-gray-700">Nama Paket <span class="text-danger-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}" required
               placeholder="Contoh: Hemat Keluarga" class="w-full border-gray-200 rounded-xl mt-1">
        @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Layanan Kiloan <span class="text-danger-500">*</span></label>
        <select name="service_id" x-model="serviceId" required class="w-full border-gray-200 rounded-xl mt-1">
            <option value="">Pilih layanan</option>
            @foreach($services as $s)
                <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->price, 0, ',', '.') }}/kg)</option>
            @endforeach
        </select>
        @error('service_id') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Berat (kg) <span class="text-danger-500">*</span></label>
            <input type="number" step="0.5" min="0.5" name="weight_kg" x-model="weight" required
                   class="w-full border-gray-200 rounded-xl mt-1">
            @error('weight_kg') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Harga Paket (Rp) <span class="text-danger-500">*</span></label>
            <input type="number" min="1" name="package_price" x-model="price" required
                   class="w-full border-gray-200 rounded-xl mt-1">
            @error('package_price') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="rounded-xl bg-gray-50 p-3 text-sm" x-show="normal > 0" x-cloak>
        <div class="flex justify-between">
            <span class="text-gray-500">Harga normal</span>
            <span x-text="rupiah(normal)"></span>
        </div>
        <template x-if="Number(price) > 0">
            <div class="flex justify-between mt-1">
                <span class="text-gray-500">Hemat pelanggan</span>
                <span :class="saving >= 0 ? 'text-success-600 font-medium' : 'text-danger-600 font-medium'"
                      x-text="saving >= 0 ? rupiah(saving) : 'Lebih mahal ' + rupiah(-saving)"></span>
            </div>
        </template>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Keterangan</label>
        <textarea name="description" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('description', $package->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Berlaku Mulai (opsional)</label>
            <input type="date" name="start_date" value="{{ old('start_date', $package?->start_date?->format('Y-m-d')) }}"
                   class="w-full border-gray-200 rounded-xl mt-1">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Berlaku Sampai (opsional)</label>
            <input type="date" name="end_date" value="{{ old('end_date', $package?->end_date?->format('Y-m-d')) }}"
                   class="w-full border-gray-200 rounded-xl mt-1">
            @error('end_date') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Kuota (opsional, kosongkan jika tanpa batas)</label>
        <input type="number" min="1" name="quota" value="{{ old('quota', $package->quota ?? '') }}"
               class="w-full border-gray-200 rounded-xl mt-1">
    </div>

    <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('packages.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
        <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Paket' }}
        </button>
    </div>
</form>
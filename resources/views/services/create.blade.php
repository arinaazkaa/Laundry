<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Tambah Layanan</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Tambah Layanan</h1>
        <form method="POST" action="{{ route('services.store') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Layanan <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Tipe <span class="text-danger-500">*</span></label>
                <select name="type" required class="w-full border-gray-200 rounded-xl mt-1">
                    <option value="kiloan" {{ old('type') === 'kiloan' ? 'selected' : '' }}>Kiloan</option>
                    <option value="satuan" {{ old('type') === 'satuan' ? 'selected' : '' }}>Satuan</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Harga (Rp) <span class="text-danger-500">*</span></label>
                <input type="number" name="price" value="{{ old('price') }}" required min="0" class="w-full border-gray-200 rounded-xl mt-1">
                @error('price') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-700">Estimasi Pengerjaan (hari)</label>
                <input type="number" name="duration_days" min="1" max="60" value="{{ old('duration_days') }}"
                    placeholder="Kosong = ikut pengaturan umum ({{ setting('work_duration_days', 2) }} hari)"
                    class="w-full border-gray-200 rounded-xl mt-1">
                <p class="text-xs text-gray-400 mt-1">Kalau satu transaksi berisi beberapa layanan, dipakai estimasi yang paling lama.</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('services.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
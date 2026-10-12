<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Item</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Edit Item Inventori</h1>
        <form method="POST" action="{{ route('inventory.update', $item) }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Item</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kategori</label>
                <input type="text" name="category" value="{{ old('category', $item->category) }}" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Satuan</label>
                <input type="text" name="unit" value="{{ old('unit', $item->unit) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Stok Minimum</label>
                <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', $item->min_stock) }}" required min="0" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <p class="text-xs text-gray-400">Stok saat ini ({{ $item->current_stock }} {{ $item->unit }}) hanya bisa diubah lewat tombol +/- di halaman daftar, bukan lewat form edit — supaya semua perubahan tercatat di Log Penggunaan.</p>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('inventory.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Tambah Item</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Tambah Item Inventori</h1>
        <form method="POST" action="{{ route('inventory.store') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nama Item <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kategori</label>
                <input type="text" name="category" value="{{ old('category') }}" placeholder="Deterjen, Pewangi, Plastik & Hanger, dll"
                       class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Stok Awal <span class="text-danger-500">*</span></label>
                    <input type="number" step="0.01" name="current_stock" value="{{ old('current_stock', 0) }}" required min="0" class="w-full border-gray-200 rounded-xl mt-1">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Satuan <span class="text-danger-500">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit') }}" placeholder="Liter, Kg, Pcs" required class="w-full border-gray-200 rounded-xl mt-1">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Stok Minimum (batas peringatan) <span class="text-danger-500">*</span></label>
                <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', 0) }}" required min="0" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('inventory.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
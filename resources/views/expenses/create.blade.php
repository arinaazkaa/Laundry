<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Catat Pengeluaran</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Catat Pengeluaran</h1>

        <form method="POST" action="{{ route('expenses.store') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Tanggal <span class="text-danger-500">*</span></label>
                <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('date') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Kategori <span class="text-danger-500">*</span></label>
                <select name="category" required class="w-full border-gray-200 rounded-xl mt-1">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Keterangan</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Contoh: Beli sabun cuci 5L" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nominal (Rp) <span class="text-danger-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount') }}" required min="1" class="w-full border-gray-200 rounded-xl mt-1">
                @error('amount') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Catatan Tambahan</label>
                <textarea name="notes" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
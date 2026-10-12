<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Pengeluaran</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Edit Pengeluaran</h1>

        <form method="POST" action="{{ route('expenses.update', $expense) }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Kategori</label>
                <select name="category" required class="w-full border-gray-200 rounded-xl mt-1">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ $expense->category === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Keterangan</label>
                <input type="text" name="description" value="{{ old('description', $expense->description) }}" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nominal (Rp)</label>
                <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" required min="1" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Catatan Tambahan</label>
                <textarea name="notes" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('notes', $expense->notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('expenses.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
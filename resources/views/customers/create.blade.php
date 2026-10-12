<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Tambah Pelanggan</h1></x-slot>

    <div class="max-w-xl">
        <h1 class="text-2xl font-bold mb-6">Tambah Pelanggan</h1>

        <form method="POST" action="{{ route('customers.store') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-gray-700">Nama <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border-gray-200 rounded-xl mt-1">
                @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Alamat</label>
                <textarea name="address" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('address') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('customers.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
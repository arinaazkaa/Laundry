<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Tambah Pengguna</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Tambah Pengguna</h1>

        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf

            <div x-data="{ preview: null }">
                <label class="text-sm font-medium text-gray-700">Foto Profil</label>
                <div class="flex items-center gap-4 mt-2">
                    <template x-if="preview">
                        <img :src="preview" class="w-16 h-16 rounded-full object-cover border">
                    </template>
                    <template x-if="!preview">
                        <div class="w-16 h-16 rounded-full bg-gray-100 border flex items-center justify-center text-gray-400 text-xs">
                            Belum ada
                        </div>
                    </template>

                    <label class="cursor-pointer">
                        <span class="inline-flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            📷 Pilih Foto
                        </span>
                        <input type="file" name="avatar" accept="image/*" class="hidden"
                            @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                </div>
                <p class="text-xs text-gray-400 mt-1">Opsional. Maks 2MB (JPG/PNG). Jika kosong, avatar otomatis dibuat dari inisial nama.</p>
                @error('avatar') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nama <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Email <span class="text-danger-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('email') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Role <span class="text-danger-500">*</span></label>
                <select name="role" required class="w-full border-gray-200 rounded-xl mt-1">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password <span class="text-danger-500">*</span></label>
                <input type="password" name="password" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('password') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Konfirmasi Password <span class="text-danger-500">*</span></label>
                <input type="password" name="password_confirmation" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Pengguna</h1></x-slot>

    <div class="max-w-lg">
        <h1 class="text-2xl font-bold mb-6">Edit Pengguna</h1>

        <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')

            <div x-data="{ preview: null }">
                <label class="text-sm font-medium text-gray-700">Foto Profil</label>
                <div class="flex items-center gap-4 mt-2">
                    <img :src="preview || '{{ $user->avatar_url }}'" alt="{{ $user->name }}"
                        class="w-16 h-16 rounded-full object-cover border">

                    <label class="cursor-pointer">
                        <span class="inline-flex items-center gap-2 border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            📷 Pilih Foto
                        </span>
                        <input type="file" name="avatar" accept="image/*" class="hidden"
                            @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                </div>
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti foto.</p>
                @error('avatar') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Role</label>
                <select name="role" required class="w-full border-gray-200 rounded-xl mt-1">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ $user->hasRole($role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Password Baru (opsional)</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
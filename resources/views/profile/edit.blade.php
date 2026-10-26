<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Profil Saya</h1></x-slot>

    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
            <p class="text-gray-500">Peran kamu: <span class="font-medium text-gray-700">{{ ucfirst($user->roles->first()->name ?? '-') }}</span></p>
        </div>

        {{-- Data diri --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PATCH')
            <h2 class="font-semibold">Data Diri</h2>

            <div x-data="{ preview: null }">
                <label class="text-sm font-medium text-gray-700">Foto Profil</label>
                <div class="flex items-center gap-4 mt-2">
                    <img :src="preview || '{{ $user->avatar_url }}'" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border">
                    <label class="cursor-pointer">
                        <span class="inline-flex items-center border border-gray-200 rounded-xl px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Pilih Foto</span>
                        <input type="file" name="avatar" accept="image/*" class="hidden"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    </label>
                </div>
                @error('avatar') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Nama</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('name') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border-gray-200 rounded-xl mt-1">
                @error('email') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border-gray-200 rounded-xl mt-1">
                @error('phone') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Profil</button>
            </div>
        </form>

        {{-- Ganti kata sandi --}}
        <form method="POST" action="{{ route('password.update') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <h2 class="font-semibold">Ganti Kata Sandi</h2>

            @if(session('status') === 'password-updated')
                <p class="text-sm text-success-600">Kata sandi berhasil diganti.</p>
            @endif

            <div>
                <label class="text-sm font-medium text-gray-700">Kata Sandi Sekarang</label>
                <input type="password" name="current_password" autocomplete="current-password" class="w-full border-gray-200 rounded-xl mt-1">
                @if($errors->updatePassword->has('current_password'))
                    <p class="text-xs text-danger-600 mt-1">{{ $errors->updatePassword->first('current_password') }}</p>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Kata Sandi Baru</label>
                <input type="password" name="password" autocomplete="new-password" class="w-full border-gray-200 rounded-xl mt-1">
                @if($errors->updatePassword->has('password'))
                    <p class="text-xs text-danger-600 mt-1">{{ $errors->updatePassword->first('password') }}</p>
                @endif
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Ulangi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border-gray-200 rounded-xl mt-1">
            </div>

            <div class="flex justify-end">
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Ganti Kata Sandi</button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>
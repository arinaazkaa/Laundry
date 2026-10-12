<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Pengguna</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Pengguna</h1>
            <p class="text-gray-500">Kelola akun kasir, pegawai, dan pemilik.</p>
        </div>
        <a href="{{ route('users.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Tambah Pengguna
        </a>
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..."
               class="w-full md:w-96 border-gray-200 rounded-xl" onchange="this.form.submit()">
    </form>

    @php
        $roleColors = [
            'pemilik' => 'bg-brand-50 text-brand-600',
            'kasir' => 'bg-success-50 text-success-600',
            'pegawai' => 'bg-warning-50 text-warning-600',
        ];
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($users->isEmpty())
            <x-empty-state title="Belum ada pengguna lain" description="Tambahkan akun kasir atau pegawai untuk membantu operasional." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Bergabung</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                    <span class="font-medium">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="text-xs text-gray-400">(kamu)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @foreach($user->roles as $role)
                                    <span class="text-xs px-2.5 py-1 rounded-full {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-500' }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="text-brand-600 text-xs">Edit</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                              onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-danger-600 text-xs">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :paginator="$users" />
            </div>
        @endif
    </div>
</x-layouts.app-layout>
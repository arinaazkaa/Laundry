<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Layanan</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Layanan Laundry</h1>
            <p class="text-gray-500">Kelola jenis layanan dan daftar harga.</p>
        </div>
        @role('pemilik')
        <a href="{{ route('services.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Tambah Layanan
        </a>
        @endrole
    </div>

    <div x-data="{ filter: 'semua' }" class="mb-5 flex items-center gap-2 bg-white border rounded-xl p-1 w-fit text-sm">
        <button @click="filter = 'semua'" :class="filter === 'semua' && 'bg-brand-50 text-brand-700'" class="px-4 py-1.5 rounded-lg">Semua Kategori</button>
        <button @click="filter = 'kiloan'" :class="filter === 'kiloan' && 'bg-brand-50 text-brand-700'" class="px-4 py-1.5 rounded-lg">Kiloan</button>
        <button @click="filter = 'satuan'" :class="filter === 'satuan' && 'bg-brand-50 text-brand-700'" class="px-4 py-1.5 rounded-lg">Satuan</button>
    </div>

    @if($services->isEmpty())
        <x-empty-state title="Belum ada layanan" description="Tambahkan layanan pertama supaya kasir bisa mulai transaksi." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($services as $service)
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center">
                        <x-lucide-shirt class="w-5 h-5 text-brand-600" />
                    </div>
                    @role('pemilik')
                    <div class="flex gap-2 text-xs">
                        <a href="{{ route('services.edit', $service) }}" class="text-brand-600">Edit</a>
                        <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini?')">
                            @csrf @method('DELETE')
                            <button class="text-danger-600">Hapus</button>
                        </form>
                    </div>
                    @endrole
                </div>
                <h3 class="font-semibold text-gray-900">{{ $service->name }}</h3>
                
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ ucfirst($service->type) }}</span>
                <span class="text-xs text-gray-400 ml-1">· Estimasi {{ $service->duration_days ?? setting('work_duration_days', 2) }} hari</span>

                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400">Harga</p>
                        <p class="font-bold text-brand-600">
                            Rp {{ number_format($service->price, 0, ',', '.') }}<span class="text-xs text-gray-400 font-normal">{{ $service->type === 'kiloan' ? '/kg' : '/pcs' }}</span>
                        </p>
                    </div>
                    @role('pemilik')
                    <form method="POST" action="{{ route('services.toggle', $service) }}">
                        @csrf @method('PATCH')
                        <button class="text-xs px-2.5 py-1 rounded-full {{ $service->is_active ? 'bg-success-50 text-success-600' : 'bg-gray-100 text-gray-400' }}">
                            {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                    @endrole
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">
            <x-pagination :paginator="$services" />
        </div>
    @endif
</x-layouts.app-layout>
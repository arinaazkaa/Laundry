<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Paket Laundry</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Paket Laundry</h1>
            <p class="text-gray-500">Harga hemat untuk berat tertentu. Muncul sebagai tab "Paket" di kasir.</p>
        </div>
        <a href="{{ route('packages.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Buat Paket
        </a>
    </div>

    @php
        $statusColors = [
            'aktif' => 'bg-success-50 text-success-600',
            'belum_dimulai' => 'bg-gray-100 text-gray-500',
            'kadaluarsa' => 'bg-danger-50 text-danger-600',
            'habis_kuota' => 'bg-warning-50 text-warning-600',
            'nonaktif' => 'bg-gray-100 text-gray-400',
            'layanan_nonaktif' => 'bg-warning-50 text-warning-600',
        ];
    @endphp

    @if($packages->isEmpty())
        <x-empty-state title="Belum ada paket" description="Contoh: Paket Hemat Keluarga, 10 kg Cuci + Kering + Setrika, Rp80.000." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($packages as $package)
            @php
                $normal = $package->normal_price_live;
                $saving = $normal - (float) $package->package_price;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 p-5 flex flex-col">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full font-medium">
                        {{ (float) $package->weight_kg }} kg
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColors[$package->status_label] }}">
                        {{ ucwords(str_replace('_', ' ', $package->status_label)) }}
                    </span>
                </div>

                <h3 class="font-semibold text-lg text-gray-900">{{ $package->name }}</h3>
                <p class="text-sm text-gray-500">{{ $package->service->name ?? 'Layanan dihapus' }}</p>
                @if($package->description)
                    <p class="text-sm text-gray-400 mt-1">{{ $package->description }}</p>
                @endif

                <div class="mt-3">
                    <p class="text-2xl font-bold text-brand-600">Rp {{ number_format($package->package_price, 0, ',', '.') }}</p>
                    @if($package->service)
                        <p class="text-xs text-gray-400">
                            <span class="line-through">Rp {{ number_format($normal, 0, ',', '.') }}</span>
                            @if($saving > 0)
                                <span class="text-success-600 font-medium ml-1">Hemat Rp {{ number_format($saving, 0, ',', '.') }}</span>
                            @else
                                <span class="text-danger-600 ml-1">Tidak lebih hemat dari harga normal</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="mt-3 text-sm text-gray-500 space-y-1">
                    <p>
                        @if($package->start_date || $package->end_date)
                            {{ $package->start_date?->translatedFormat('d M Y') ?? 'Sejak awal' }}
                            sampai {{ $package->end_date?->translatedFormat('d M Y') ?? 'tanpa batas' }}
                        @else
                            Tanpa batas waktu
                        @endif
                    </p>
                </div>

                @if($package->quota)
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Kuota terpakai</span><span>{{ $package->used_count }}/{{ $package->quota }}</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full">
                            <div class="h-1.5 bg-brand-600 rounded-full" style="width: {{ min(100, ($package->used_count / $package->quota) * 100) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400 mt-3">Terjual: {{ $package->used_count }} paket</p>
                @endif

                <div class="flex gap-2 mt-auto pt-4">
                    <a href="{{ route('packages.edit', $package) }}" class="flex-1 text-center border rounded-xl py-2 text-xs">Edit</a>
                    <form method="POST" action="{{ route('packages.toggle', $package) }}" class="flex-1"
                          @if($package->is_active) onsubmit="return confirm('Nonaktifkan paket ini? Paket tidak akan muncul di kasir.')" @endif>
                        @csrf @method('PATCH')
                        <button class="w-full border rounded-xl py-2 text-xs {{ $package->is_active ? 'text-warning-600' : 'text-success-600' }}">
                            {{ $package->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('packages.destroy', $package) }}" class="flex-1"
                          onsubmit="return confirm('Hapus paket ini?')">
                        @csrf @method('DELETE')
                        <button class="w-full border border-danger-200 rounded-xl py-2 text-xs text-danger-600">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <x-pagination :paginator="$packages" />
        </div>
    @endif
</x-layouts.app-layout>
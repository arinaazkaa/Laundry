<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Promo & Paket</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Promo & Paket Aktif</h1>
            <p class="text-gray-500">Kelola diskon dan penawaran spesial untuk pelanggan.</p>
        </div>
        <a href="{{ route('promos.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Buat Promo Baru
        </a>
    </div>

    <div class="flex bg-white border rounded-xl p-1 text-sm w-fit mb-5">
        <a href="{{ route('promos.index') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('promos.index') ? 'bg-brand-600 text-white' : '' }}">Semua</a>
        <a href="{{ route('promos.active') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('promos.active') ? 'bg-brand-600 text-white' : '' }}">Aktif</a>
        <a href="{{ route('promos.history') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('promos.history') ? 'bg-brand-600 text-white' : '' }}">Riwayat</a>
    </div>

    @php
        $typeLabels = [
            'gratis_ongkir' => 'Gratis Ongkir',
            'diskon_persen' => 'Diskon Persen',
            'diskon_nominal' => 'Potongan Harga',
            'berdasarkan_berat' => 'Diskon Berat',
            'minimal_transaksi' => 'Min. Transaksi',
            'member' => 'Khusus Member',
        ];
        $statusColors = [
            'aktif' => 'bg-success-50 text-success-600',
            'belum_dimulai' => 'bg-gray-100 text-gray-500',
            'kadaluarsa' => 'bg-danger-50 text-danger-600',
            'habis_kuota' => 'bg-warning-50 text-warning-600',
            'nonaktif' => 'bg-gray-100 text-gray-400',
        ];
    @endphp

    @if($promos->isEmpty())
        <x-empty-state title="Belum ada promo" description="Buat promo pertama untuk menarik lebih banyak pelanggan." />
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($promos as $promo)
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full font-medium">
                        {{ $typeLabels[$promo->type] ?? $promo->type }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColors[$promo->status_label] }}">
                        {{ ucwords(str_replace('_', ' ', $promo->status_label)) }}
                    </span>
                </div>

                <h3 class="font-semibold text-lg text-gray-900">{{ $promo->name }}</h3>

                @if($promo->type === 'gratis_ongkir')
                    <p class="text-sm text-gray-500 mt-1">{{ $promo->description ?: 'Ongkir jemput/antar gratis' }}</p>
                @elseif($promo->value_type === 'persen')
                    <p class="text-2xl font-bold text-brand-600 mt-1">
                        {{ rtrim(rtrim(number_format($promo->value, 2, '.', ''), '0'), '.') }}% <span class="text-sm font-normal text-gray-400">Off</span>
                    </p>
                @else
                    <p class="text-2xl font-bold text-success-600 mt-1">Rp {{ number_format($promo->value, 0, ',', '.') }}</p>
                @endif

                <div class="mt-3 space-y-1.5 text-sm text-gray-500">
                    <p>📅 {{ $promo->start_date->translatedFormat('d M Y') }} — {{ $promo->end_date->translatedFormat('d M Y') }}</p>
                    @if($promo->min_transaction)
                        <p>🧾 Min. Transaksi Rp {{ number_format($promo->min_transaction, 0, ',', '.') }}</p>
                    @endif
                    @if($promo->min_weight)
                        <p>⚖️ Min. Berat {{ $promo->min_weight }} kg</p>
                    @endif
                </div>

                @if($promo->quota)
                    <div class="mt-3">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Kuota Terpakai</span>
                            <span>{{ $promo->used_count }}/{{ $promo->quota }}</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full">
                            <div class="h-1.5 bg-brand-600 rounded-full" style="width: {{ min(100, ($promo->used_count / $promo->quota) * 100) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-gray-400 mt-3">Digunakan: {{ $promo->used_count }} kali</p>
                @endif

                <div class="flex gap-2 mt-4 pt-4 border-t">
                    <a href="{{ route('promos.edit', $promo) }}" class="flex-1 text-center border rounded-xl py-2 text-xs">Edit</a>
                    <form method="POST" action="{{ route('promos.toggle', $promo) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <button class="w-full border rounded-xl py-2 text-xs {{ $promo->is_active ? 'text-warning-600' : 'text-success-600' }}">
                            {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('promos.destroy', $promo) }}" class="flex-1"
                          onsubmit="return confirm('Hapus promo ini?')">
                        @csrf @method('DELETE')
                        <button class="w-full border border-danger-200 rounded-xl py-2 text-xs text-danger-600">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">
            <x-pagination :paginator="$promos" />
        </div>
    @endif
</x-layouts.app-layout>
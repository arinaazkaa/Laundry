<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Detail Promo</h1></x-slot>

    @php
        $typeLabels = [
            'gratis_ongkir' => 'Gratis Ongkir',
            'diskon_persen' => 'Diskon Persen',
            'diskon_nominal' => 'Potongan Harga',
            'berdasarkan_berat' => 'Berdasarkan Berat',
            'minimal_transaksi' => 'Minimal Transaksi',
            'member' => 'Khusus Member',
        ];
        $statusColors = [
            'aktif' => 'bg-success-50 text-success-600',
            'belum_dimulai' => 'bg-gray-100 text-gray-500',
            'kadaluarsa' => 'bg-danger-50 text-danger-600',
            'habis_kuota' => 'bg-warning-50 text-warning-600',
            'nonaktif' => 'bg-gray-100 text-gray-400',
        ];
        $valueText = $promo->type === 'gratis_ongkir'
            ? 'Gratis Ongkir'
            : ($promo->value_type === 'persen'
                ? rtrim(rtrim(number_format($promo->value, 2, '.', ''), '0'), '.') . '%'
                : 'Rp ' . number_format($promo->value, 0, ',', '.'));
    @endphp

    <a href="{{ route('promos.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Semua promo</a>

    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mt-2 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $promo->name }}</h1>
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="text-xs bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full font-medium">{{ $typeLabels[$promo->type] ?? $promo->type }}</span>
                <span class="text-xs px-2.5 py-1 rounded-full {{ $statusColors[$promo->status_label] }}">
                    {{ ucwords(str_replace('_', ' ', $promo->status_label)) }}
                </span>
            </div>
            @if($promo->description)
                <p class="text-gray-500 mt-3 max-w-xl">{{ $promo->description }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('promos.edit', $promo) }}" class="border rounded-xl px-4 py-2 text-sm">Edit</a>
            <form method="POST" action="{{ route('promos.toggle', $promo) }}"
                  @if($promo->is_active) onsubmit="return confirm('Nonaktifkan promo ini? Promo tidak akan muncul di kasir.')" @endif>
                @csrf @method('PATCH')
                <button class="border rounded-xl px-4 py-2 text-sm {{ $promo->is_active ? 'text-warning-600' : 'text-success-600' }}">
                    {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 lg:col-span-2">
            <h2 class="font-semibold mb-4">Ketentuan</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-gray-400">Potongan</dt><dd class="font-semibold text-brand-600 text-lg">{{ $valueText }}</dd></div>
                <div>
                    <dt class="text-gray-400">Periode</dt>
                    <dd class="font-medium">{{ $promo->start_date->translatedFormat('d M Y') }} sampai {{ $promo->end_date->translatedFormat('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Jam berlaku</dt>
                    <dd class="font-medium">
                        {{ ($promo->start_time && $promo->end_time) ? substr($promo->start_time, 0, 5) . ' - ' . substr($promo->end_time, 0, 5) : 'Sepanjang hari' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-400">Minimal transaksi</dt>
                    <dd class="font-medium">{{ $promo->min_transaction ? 'Rp ' . number_format($promo->min_transaction, 0, ',', '.') : 'Tidak ada' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Minimal berat</dt>
                    <dd class="font-medium">{{ $promo->min_weight ? (float) $promo->min_weight . ' kg' : 'Tidak ada' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Dipakai</p>
                <p class="text-2xl font-bold mt-1">{{ $promo->used_count }}x
                    @if($promo->quota)<span class="text-sm font-normal text-gray-400">dari {{ $promo->quota }}</span>@endif
                </p>
                @if($promo->quota)
                    <div class="h-1.5 bg-gray-100 rounded-full mt-2">
                        <div class="h-1.5 bg-brand-600 rounded-full" style="width: {{ min(100, ($promo->used_count / $promo->quota) * 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Sisa {{ max(0, $promo->quota - $promo->used_count) }}</p>
                @endif
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Total diskon diberikan</p>
                <p class="text-2xl font-bold mt-1 text-danger-600">Rp {{ number_format($totalDiscount, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5"><h2 class="font-semibold">Riwayat Pemakaian</h2></div>

        @if($usages->isEmpty())
            <x-empty-state title="Promo ini belum pernah dipakai" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Transaksi</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium text-right">Diskon</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usages as $usage)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if($usage->transaction)
                                    <a href="{{ route('transactions.show', $usage->transaction) }}" class="font-medium text-brand-600">{{ $usage->transaction->code }}</a>
                                    @if($usage->transaction->status === 'dibatalkan')
                                        <span class="text-xs text-danger-600 ml-1">dibatalkan</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $usage->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">{{ $usage->transaction?->customer?->name ?? 'Pelanggan Umum' }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($usage->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4"><x-pagination :paginator="$usages" /></div>
        @endif
    </div>
</x-layouts.app-layout>
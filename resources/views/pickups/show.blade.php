<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Detail Antar-Jemput</h1></x-slot>

    <div class="max-w-2xl bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-bold">{{ $pickup->transaction->customer->name ?? 'Pelanggan Umum' }}</h2>
                <p class="text-sm text-gray-400">{{ $pickup->transaction->code }}</p>
            </div>
            <x-badge :status="$pickup->status" />
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Tipe</span><span class="font-medium">{{ $pickup->type === 'pickup' ? 'Jemput' : 'Antar' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Alamat</span><span class="font-medium text-right">{{ $pickup->address ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Jadwal</span><span class="font-medium">{{ $pickup->scheduled_at?->translatedFormat('d M Y, H:i') ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Total Tagihan</span><span class="font-medium">Rp {{ number_format($pickup->transaction->total, 0, ',', '.') }}</span></div>
        </div>

        <div class="mt-6 pt-4 border-t">
            <h3 class="font-semibold mb-2 text-sm">Item Laundry</h3>
            @foreach($pickup->transaction->items as $item)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $item->service_name }} ({{ $item->qty }} {{ $item->unit_type }})</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('pickups.updateStatus', $pickup) }}" class="mt-6 flex gap-2">
            @csrf @method('PATCH')
            <select name="status" class="flex-1 border-gray-200 rounded-xl text-sm">
                <option value="dijadwalkan" {{ $pickup->status === 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                <option value="dalam_perjalanan" {{ $pickup->status === 'dalam_perjalanan' ? 'selected' : '' }}>Dalam Perjalanan</option>
                <option value="selesai" {{ $pickup->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="dibatalkan" {{ $pickup->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button class="bg-brand-600 text-white px-5 rounded-xl text-sm font-medium">Update</button>
        </form>
    </div>
</x-layouts.app-layout>
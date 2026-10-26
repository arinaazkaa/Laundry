<x-layouts.portal-layout title="Riwayat">
    <h1 class="text-2xl font-bold text-gray-900">Riwayat Laundry</h1>

    @forelse($transactions as $t)
        <a href="{{ route('portal.transactions.show', $t->tracking_token) }}"
           class="block bg-white rounded-2xl border border-gray-100 p-4 hover:shadow-sm transition">
            <div class="flex items-start justify-between gap-2">
                <span class="font-semibold text-gray-900">{{ $t->code }}</span>
                <x-badge :status="$t->status" />
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $t->items->pluck('service_name')->join(', ') }}</p>
            <div class="flex items-center justify-between mt-2">
                <span class="text-xs text-gray-400">{{ $t->created_at->translatedFormat('d M Y') }}</span>
                <span class="font-semibold {{ $t->status === 'dibatalkan' ? 'text-gray-400 line-through' : '' }}">Rp {{ number_format($t->total, 0, ',', '.') }}</span>
            </div>
        </a>
    @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-6 text-sm text-gray-400 text-center">Belum ada transaksi.</div>
    @endforelse

    <x-pagination :paginator="$transactions" />
</x-layouts.portal-layout>
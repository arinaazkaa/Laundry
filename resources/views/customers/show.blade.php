<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Detail Pelanggan</h1></x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-2xl font-bold mb-3">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
            <p class="text-gray-500 text-sm">{{ $customer->phone ?? '-' }}</p>
            <p class="text-gray-500 text-sm mt-1">{{ $customer->address ?? '-' }}</p>
            <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Total Transaksi</span><span class="font-medium">{{ $customer->transactions->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Total Belanja</span><span class="font-medium">Rp {{ number_format($customer->transactions->sum('total'), 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Bergabung</span><span class="font-medium">{{ $customer->created_at->translatedFormat('d M Y') }}</span></div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-4">Riwayat Transaksi</h3>
            @forelse($customer->transactions as $t)
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <a href="{{ route('transactions.show', $t) }}" class="font-medium text-brand-600">{{ $t->code }}</a>
                        <p class="text-xs text-gray-400">{{ $t->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <x-badge :status="$t->status" />
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada transaksi" />
            @endforelse
        </div>
    </div>
</x-layouts.app-layout>
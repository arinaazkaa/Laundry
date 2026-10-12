<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Antar-Jemput</h1></x-slot>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Jadwal Antar-Jemput</h1>
            <p class="text-gray-500">Kelola penugasan kurir dan status pengiriman hari ini.</p>
        </div>
        <div class="flex bg-white border rounded-xl p-1 text-sm w-fit">
            <a href="{{ route('pickups.index') }}"
               class="px-4 py-1.5 rounded-lg {{ !request('type') ? 'bg-brand-600 text-white' : '' }}">Semua</a>
            <a href="{{ route('pickups.pickup') }}"
               class="px-4 py-1.5 rounded-lg {{ request('type') === 'pickup' ? 'bg-brand-600 text-white' : '' }}">Jemput</a>
            <a href="{{ route('pickups.delivery') }}"
               class="px-4 py-1.5 rounded-lg {{ request('type') === 'delivery' ? 'bg-brand-600 text-white' : '' }}">Antar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Menunggu Penjemputan" :value="$waitingCount" />
        <x-stat-card label="Siap Diantar" :value="$readyCount" />
        <x-stat-card label="Dalam Perjalanan" :value="$onTheWayCount" />
        <div class="bg-brand-50 rounded-2xl flex items-center justify-center text-brand-700 font-medium text-sm">
            + Tugas Baru
        </div>
    </div>

    @if($tasks->isEmpty())
        <x-empty-state title="Belum ada jadwal antar-jemput" description="Jadwal akan muncul otomatis saat transaksi memilih layanan antar/jemput." />
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($tasks as $task)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($task->transaction->customer->name ?? 'U', 0, 1)) }}
                            </span>
                            <div>
                                <p class="font-semibold">{{ $task->transaction->customer->name ?? 'Pelanggan Umum' }}</p>
                                <p class="text-xs text-gray-400">{{ $task->transaction->code }}</p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <span class="text-xs px-2 py-1 rounded-full {{ $task->type === 'pickup' ? 'bg-brand-50 text-brand-600' : 'bg-gray-100 text-gray-600' }}">
                                {{ $task->type === 'pickup' ? 'Jemput' : 'Antar' }}
                            </span>
                            <x-badge :status="$task->status" />
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 text-sm">
                        <p class="text-gray-700">{{ $task->address ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Total Tagihan: Rp {{ number_format($task->transaction->total, 0, ',', '.') }}
                            ({{ $task->transaction->payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas' }})
                        </p>
                    </div>
                </div>

                <div class="border-t px-5 py-3 flex gap-3">
                    <a href="{{ route('pickups.show', $task) }}" class="flex-1 text-center border rounded-xl py-2 text-sm">Detail</a>
                    <form method="POST" action="{{ route('pickups.updateStatus', $task) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $task->status === 'dijadwalkan' ? 'dalam_perjalanan' : 'selesai' }}">
                        <button class="w-full bg-brand-600 text-white rounded-xl py-2 text-sm font-medium">
                            {{ $task->status === 'dijadwalkan' ? 'Tugaskan' : 'Selesaikan' }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-layouts.app-layout>
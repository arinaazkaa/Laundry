<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Transaksi</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Semua Transaksi</h1>
            <p class="text-gray-500">Cari, pantau status, dan kelola pembayaran laundry.</p>
        </div>
        <a href="{{ route('transactions.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Transaksi Baru
        </a>
    </div>

    {{-- Filter cepat sesuai submenu sidebar --}}
    <div class="flex bg-white border rounded-xl p-1 text-sm w-fit mb-4">
        <a href="{{ route('transactions.index') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('transactions.index') ? 'bg-brand-600 text-white' : '' }}">Semua</a>
        <a href="{{ route('transactions.processing') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('transactions.processing') ? 'bg-brand-600 text-white' : '' }}">Sedang Diproses</a>
        <a href="{{ route('transactions.ready') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('transactions.ready') ? 'bg-brand-600 text-white' : '' }}">Siap Diambil</a>
        <a href="{{ route('transactions.history') }}"
           class="px-4 py-1.5 rounded-lg {{ request()->routeIs('transactions.history') ? 'bg-brand-600 text-white' : '' }}">Riwayat</a>
    </div>

    <form method="GET" class="flex gap-3 mb-4">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Cari kode transaksi, nama, atau No. HP..."
               class="flex-1 border-gray-200 rounded-xl"
               onchange="this.form.submit()">
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($transactions->isEmpty())
            <x-empty-state title="Belum ada transaksi" description="Transaksi yang kamu buat akan muncul di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Pembayaran</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('transactions.show', $t) }}" class="font-medium text-brand-600">{{ $t->code }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($t->customer)
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold
                                            {{ $t->customer->is_member ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-600' }}">
                                            {{ strtoupper(substr($t->customer->name, 0, 1)) }}
                                        </span>
                                        <span>{{ $t->customer->name }}</span>
                                    @else
                                        <span class="text-gray-400">Pelanggan Umum</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $t->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 font-medium">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3"><x-badge :status="$t->payment_status" /></td>
                            <td class="px-4 py-3"><x-badge :status="$t->status" /></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('transactions.show', $t) }}" class="text-brand-600 text-xs">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :paginator="$transactions" />
            </div>
        @endif
    </div>
</x-layouts.app-layout>
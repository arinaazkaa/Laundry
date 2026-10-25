<x-layouts.app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-400">Laporan <span class="mx-1">›</span> <span class="text-gray-700 font-medium">Piutang</span></p>
    </x-slot>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Piutang</h1>
            <p class="text-gray-500">Transaksi yang pembayarannya belum lunas, dari yang paling lama.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="border rounded-xl px-4 py-2.5 text-sm font-medium w-fit">← Kembali ke Laporan</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-warning-50 rounded-2xl p-5">
            <p class="text-sm text-warning-600">Total Piutang</p>
            <p class="text-2xl font-bold text-warning-600 mt-1">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</p>
        </div>
        <x-stat-card label="Jumlah Transaksi" :value="$count . ' transaksi'" />
        <x-stat-card label="Tagihan Terlama" :value="$oldest ? $oldest->translatedFormat('d M Y') : '-'" />
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, atau No. HP..."
               class="w-full md:w-96 border-gray-200 rounded-xl" onchange="this.form.submit()">
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($transactions->isEmpty())
            <x-empty-state title="Tidak ada piutang" description="Semua transaksi sudah lunas." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Transaksi</th>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium">Umur</th>
                            <th class="px-4 py-3 font-medium text-right">Total</th>
                            <th class="px-4 py-3 font-medium text-right">Dibayar</th>
                            <th class="px-4 py-3 font-medium text-right">Sisa</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        @php
                            $remaining = max(0, $t->total - $t->paid_amount);
                            $age = (int) $t->created_at->copy()->startOfDay()->diffInDays(today());
                            $ageClass = $age >= 7 ? 'bg-danger-50 text-danger-600' : ($age >= 3 ? 'bg-warning-50 text-warning-600' : 'bg-gray-100 text-gray-500');
                            $phone = $t->customer?->phone
                                ? preg_replace('/^0/', '62', preg_replace('/\D/', '', $t->customer->phone))
                                : null;
                            $message = $phone
                                ? 'Halo ' . $t->customer->name . ', kami mengingatkan transaksi ' . $t->code . ' di '
                                    . setting('business_name', 'Laundry Desa') . ' masih ada sisa pembayaran Rp '
                                    . number_format($remaining, 0, ',', '.') . '. Terima kasih.'
                                : null;
                        @endphp
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('transactions.show', $t) }}" class="font-medium text-brand-600">{{ $t->code }}</a>
                                <p class="text-xs text-gray-400">{{ $t->created_at->translatedFormat('d M Y') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                {{ $t->customer->name ?? 'Pelanggan Umum' }}
                                @if($t->customer?->phone)
                                    <p class="text-xs text-gray-400">{{ $t->customer->phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2.5 py-1 rounded-full {{ $ageClass }}">{{ $age === 0 ? 'Hari ini' : $age . ' hari' }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($t->paid_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-danger-600">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-3 text-xs">
                                    <a href="{{ route('transactions.show', $t) }}" class="text-brand-600">Terima Bayar</a>
                                    @if($phone)
                                        <a href="https://wa.me/{{ $phone }}?text={{ urlencode($message) }}" target="_blank" class="text-success-600">Ingatkan</a>
                                    @endif
                                </div>
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
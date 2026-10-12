<x-layouts.app-layout>
    <x-slot name="header">
        <p class="text-sm text-gray-400">Laporan <span class="mx-1">›</span> <span class="text-gray-700 font-medium">Keuangan & Laporan</span></p>
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Keuangan</h1>
            <p class="text-gray-500">Ringkasan performa finansial {{ $period === 'month' ? 'bulan ini' : $period }}.</p>
        </div>
        <a href="{{ route('reports.export', request()->query()) }}"
           class="border rounded-xl px-4 py-2.5 text-sm font-medium">⬇ Export Report</a>
    </div>

    <form method="GET" class="flex bg-white border rounded-xl p-1 text-sm w-fit mb-6">
        @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
            <button type="submit" name="period" value="{{ $key }}"
                    class="px-4 py-1.5 rounded-lg {{ $period === $key ? 'bg-brand-600 text-white' : '' }}">{{ $label }}</button>
        @endforeach
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex justify-between items-start mb-3">
                <div class="w-9 h-9 rounded-lg bg-success-50 flex items-center justify-center text-success-600">↑</div>
                <span class="text-xs px-2 py-1 rounded-full {{ $revenueChangePercent >= 0 ? 'bg-success-50 text-success-600' : 'bg-danger-50 text-danger-600' }}">
                    {{ $revenueChangePercent >= 0 ? '↑' : '↓' }} {{ abs($revenueChangePercent) }}%
                </span>
            </div>
            <p class="text-sm text-gray-500">Total Pemasukan</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="w-9 h-9 rounded-lg bg-danger-50 flex items-center justify-center text-danger-600 mb-3">↓</div>
            <p class="text-sm text-gray-500">Total Pengeluaran</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">Bahan baku & operasional</p>
        </div>

        <div class="bg-brand-600 text-white rounded-2xl p-5">
            <p class="text-sm text-brand-100">Laba Bersih</p>
            <p class="text-2xl font-bold mt-1">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
            <p class="text-xs text-brand-100 mt-2">Margin: {{ $margin }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-4">Tren Pendapatan Bulanan</h2>
            <canvas id="trendChart" height="100"></canvas>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-4">Distribusi Pembayaran</h2>
            <canvas id="paymentChart" height="160"></canvas>
            <div class="mt-4 space-y-2 text-sm">
                @foreach($paymentDistribution as $method => $count)
                    <div class="flex justify-between">
                        <span class="capitalize text-gray-600">{{ $method }}</span>
                        <span class="font-medium">{{ $totalTransactionsCount > 0 ? round(($count / $totalTransactionsCount) * 100) : 0 }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 flex items-center justify-between">
            <h2 class="font-semibold">Riwayat Transaksi</h2>
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ID / Nama..."
                       class="border-gray-200 rounded-xl text-sm" onchange="this.form.submit()">
            </form>
        </div>

        @if($transactions->isEmpty())
            <x-empty-state title="Belum ada transaksi pada periode ini" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">ID Transaksi</th>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Pelanggan</th>
                            <th class="px-4 py-3 font-medium">Metode</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $t)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('transactions.show', $t) }}" class="font-medium text-brand-600">#{{ $t->code }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $t->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">{{ $t->customer->name ?? 'Pelanggan Umum' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded-full uppercase">{{ $t->payment_method ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
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

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyTrend->keys()),
                datasets: [{
                    data: @json($monthlyTrend->values()),
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: @json($paymentDistribution->keys()),
                datasets: [{
                    data: @json($paymentDistribution->values()),
                    backgroundColor: ['#2563eb', '#93c5fd', '#dbeafe', '#10b981'],
                }]
            },
            options: { plugins: { legend: { display: false } }, cutout: '70%' }
        });
    });
    </script>
    @endpush
</x-layouts.app-layout>
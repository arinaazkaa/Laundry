<x-layouts.app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold">Dashboard</h1>
    </x-slot>

    <h1 class="text-3xl font-bold text-gray-900">Dashboard Owner</h1>
    <p class="text-gray-500 mb-6">Ringkasan performa bisnis hari ini — {{ now()->translatedFormat('d F Y') }}</p>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card label="Pendapatan Hari Ini" :value="'Rp ' . number_format($revenueToday, 0, ',', '.')" />
            <x-stat-card label="Estimasi Laba Hari Ini" :value="'Rp ' . number_format($profitToday, 0, ',', '.')" />
            <x-stat-card label="Pengeluaran Hari Ini" :value="'Rp ' . number_format($expenseToday, 0, ',', '.')" />
            <x-stat-card label="Transaksi Hari Ini" :value="$transactionsToday . ' Transaksi'" />
        </div>
        <div class="space-y-4">
            <x-mini-stat label="Sedang Diproses" :value="$inProcess" />
            <x-mini-stat label="Siap Diambil" :value="$readyToPickup" tone="success" />
            <x-mini-stat label="Pembayaran Belum Lunas" :value="$unpaid" tone="warning" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-3">Grafik Pendapatan (7 Hari Terakhir)</h2>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-4">Layanan Terlaris</h2>
            @forelse($topServices as $i => $s)
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium">{{ $i + 1 }}. {{ $s->service_name }}</span>
                        <span class="text-gray-500">{{ (int) $s->total_qty }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full mt-1">
                        <div class="h-2 bg-brand-600 rounded-full" style="width: {{ min(100, $s->total_qty) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada data transaksi.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-5 mt-6">
        <h2 class="font-semibold mb-4">Aktivitas Terbaru</h2>
        @forelse($recentTransactions as $t)
            <div class="py-2 border-b last:border-0 text-sm">
                <span class="font-medium">{{ $t->cashier->name }}</span> membuat transaksi
                <a href="{{ route('transactions.show', $t) }}" class="text-brand-600">{{ $t->code }}</a>
                <p class="text-xs text-gray-400">{{ $t->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada aktivitas.</p>
        @endforelse
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: @json(array_keys($weeklyRevenue->toArray())),
                datasets: [{
                    data: @json(array_values($weeklyRevenue->toArray())),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });
    });
    </script>
    @endpush
</x-layouts.app-layout>
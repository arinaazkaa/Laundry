<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Dashboard</h1></x-slot>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Owner</h1>
        <p class="text-gray-500">Ringkasan performa bisnis — {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Angka hari ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-stat-card label="Pendapatan Hari Ini" :value="'Rp ' . number_format($revenueToday, 0, ',', '.')" :badge="$revenueBadge" />
            <x-stat-card label="Estimasi Laba Hari Ini" :value="'Rp ' . number_format($profitToday, 0, ',', '.')" />
            <x-stat-card label="Pengeluaran Hari Ini" :value="'Rp ' . number_format($expenseToday, 0, ',', '.')" />
            <x-stat-card label="Transaksi Hari Ini" :value="$transactionsToday . ' Transaksi'" />
        </div>

        <div class="space-y-4">
            <a href="{{ route('transactions.processing') }}" class="block">
                <x-mini-stat label="Sedang Diproses" :value="$inProcess" />
            </a>
            <a href="{{ route('transactions.ready') }}" class="block">
                <x-mini-stat label="Siap Diambil" :value="$readyToPickup" tone="success" />
            </a>
            <a href="{{ route('reports.receivables') }}"
               class="block bg-warning-50 rounded-2xl p-4 hover:shadow-sm transition">
                <p class="text-xs text-warning-600">Pembayaran Belum Lunas</p>
                <p class="text-xl font-bold text-warning-600">{{ $unpaid }} <span class="text-sm font-normal">transaksi</span></p>
                <p class="text-xs text-gray-500 mt-0.5">Sisa tagihan Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</p>
            </a>
        </div>
    </div>

    {{-- Filter periode untuk grafik --}}
    <div x-data="{ custom: {{ $period === 'custom' ? 'true' : 'false' }} }" class="mt-8 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex bg-white border rounded-xl p-1 text-sm">
                @foreach(['7d' => '7 Hari', '30d' => '30 Hari', 'month' => 'Bulan Ini'] as $key => $label)
                    <a href="{{ route('dashboard', ['period' => $key]) }}"
                       class="px-4 py-1.5 rounded-lg {{ $period === $key ? 'bg-brand-600 text-white' : '' }}">{{ $label }}</a>
                @endforeach
                <button type="button" @click="custom = !custom"
                        class="px-4 py-1.5 rounded-lg {{ $period === 'custom' ? 'bg-brand-600 text-white' : '' }}">Custom</button>
            </div>

            <form x-show="custom" x-cloak method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start" value="{{ $startDate }}" required class="border-gray-200 rounded-xl text-sm">
                <span class="text-gray-400 text-sm">s/d</span>
                <input type="date" name="end" value="{{ $endDate }}" required class="border-gray-200 rounded-xl text-sm">
                <button class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-medium">Terapkan</button>
            </form>
        </div>

        <p class="text-sm text-gray-500 mt-3">
            Periode <span class="font-medium text-gray-700">{{ $periodLabel }}</span>:
            pendapatan <span class="font-medium text-gray-700">Rp {{ number_format($periodRevenue, 0, ',', '.') }}</span>,
            <span class="font-medium text-gray-700">{{ $periodTransactions }}</span> transaksi.
            @if($clamped)
                <span class="text-warning-600">Rentang dipersingkat ke 92 hari terakhir supaya grafik tetap terbaca.</span>
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-3">Grafik Pendapatan ({{ $periodLabel }})</h2>
            <canvas id="revenueChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-4">Layanan Terlaris</h2>
            @php $topMax = max(1, (float) ($topServices->first()->total_qty ?? 1)); @endphp
            @forelse($topServices as $i => $s)
                <div class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium">{{ $i + 1 }}. {{ $s->service_name }}</span>
                        <span class="text-gray-500">{{ rtrim(rtrim(number_format($s->total_qty, 2, ',', '.'), '0'), ',') }} {{ $s->unit_type }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full mt-1">
                        <div class="h-2 bg-brand-600 rounded-full" style="width: {{ min(100, ($s->total_qty / $topMax) * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada transaksi pada periode ini.</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-3">Jumlah Transaksi per Hari ({{ $periodLabel }})</h2>
            <canvas id="countChart" height="110"></canvas>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold mb-3">Aktivitas Terbaru</h2>

            @forelse($activities as $log)
                @php $role = $log->user?->roles->first()?->name; @endphp
                <div class="flex gap-3 py-2.5 border-b last:border-0">
                    @if($log->user)
                        <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 text-xs flex items-center justify-center flex-shrink-0">S</div>
                    @endif
                    <div class="min-w-0 text-sm">
                        <p class="text-gray-700">
                            <span class="font-medium text-gray-900">{{ $role ? ucfirst($role) . ' ' : '' }}{{ $log->user->name ?? 'Sistem' }}</span>
                            {{ $log->description }}
                            @if($log->transaction_id)
                                <a href="{{ route('transactions.show', $log->transaction_id) }}" class="text-brand-600">Lihat</a>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada aktivitas. Aktivitas baru akan muncul di sini.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($labels);
        const rupiah = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
        const singkat = (v) => v >= 1000000 ? (v / 1000000) + ' jt' : (v >= 1000 ? (v / 1000) + ' rb' : v);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: @json($revenueSeries),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: labels.length > 31 ? 0 : 3,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => rupiah(ctx.parsed.y) } }
                },
                scales: { y: { beginAtZero: true, ticks: { callback: singkat } } }
            }
        });

        new Chart(document.getElementById('countChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: @json($countSeries),
                    backgroundColor: '#2563eb',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ctx.parsed.y + ' transaksi' } }
                },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    });
    </script>
    @endpush
</x-layouts.app-layout>
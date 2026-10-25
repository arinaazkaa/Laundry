<div class="p-5">
    <h1 class="text-xl font-bold text-brand-600">{{ setting('business_name', 'Laundry Desa') }}</h1>
    <p class="text-xs text-gray-400">Sistem Manajemen Laundry</p>
</div>

@hasanyrole('pemilik|kasir')
    <a href="{{ route('transactions.create') }}"
       class="mx-4 mb-4 bg-brand-600 text-white text-sm font-medium py-2.5 rounded-xl text-center hover:bg-brand-700 transition">
        + Transaksi Baru
    </a>
@endhasanyrole

<nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-6">
    @role('pemilik')
        @include('layouts.partials.nav-item', ['route' => 'dashboard', 'icon' => 'layout-grid', 'label' => 'Dashboard'])
    @endrole

    @hasanyrole('pemilik|kasir')
        @include('layouts.partials.nav-item', ['route' => 'transactions.index', 'icon' => 'file-text', 'label' => 'Transaksi', 'active' => ['transactions.index', 'transactions.show', 'transactions.processing', 'transactions.ready', 'transactions.history']])
        @include('layouts.partials.nav-item', ['route' => 'customers.index', 'icon' => 'users', 'label' => 'Pelanggan', 'active' => 'customers.*'])
        @include('layouts.partials.nav-item', ['route' => 'services.index', 'icon' => 'shirt', 'label' => 'Layanan', 'active' => 'services.*'])
        @include('layouts.partials.nav-item', ['route' => 'pickups.index', 'icon' => 'truck', 'label' => 'Antar-Jemput', 'active' => 'pickups.*'])
    @endhasanyrole

    @hasanyrole('pemilik|kasir|pegawai')
        @include('layouts.partials.nav-item', ['route' => 'process.queue', 'icon' => 'clipboard-list', 'label' => 'Antrian Proses'])
    @endhasanyrole

    @role('pemilik')
        @include('layouts.partials.nav-item', ['route' => 'promos.index', 'icon' => 'tag', 'label' => 'Promo', 'active' => 'promos.*'])
        @include('layouts.partials.nav-item', ['route' => 'expenses.index', 'icon' => 'receipt', 'label' => 'Pengeluaran', 'active' => 'expenses.*'])
        @include('layouts.partials.nav-item', ['route' => 'inventory.index', 'icon' => 'box', 'label' => 'Inventori', 'active' => 'inventory.*'])
        @include('layouts.partials.nav-item', ['route' => 'reports.index', 'icon' => 'bar-chart-3', 'label' => 'Laporan', 'active' => 'reports.*'])
        @include('layouts.partials.nav-item', ['route' => 'users.index', 'icon' => 'user', 'label' => 'Pengguna', 'active' => 'users.*'])
        @include('layouts.partials.nav-item', ['route' => 'settings.index', 'icon' => 'settings', 'label' => 'Pengaturan', 'active' => 'settings.*'])
    @endrole
</nav>
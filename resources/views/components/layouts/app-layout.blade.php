<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Laundry Desa' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
<div class="min-h-screen flex" x-data="{ mobileNavOpen: false }">

    {{-- Sidebar Desktop --}}
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex md:flex-col">
        <div class="p-5">
            <h1 class="text-xl font-bold text-brand-600">{{ setting('business_name', 'Laundry Desa') }}</h1>
            <p class="text-xs text-gray-400">Sistem Manajemen Laundry</p>
        </div>

        <a href="{{ route('transactions.create') }}"
           class="mx-4 mb-4 bg-brand-600 text-white text-sm font-medium py-2.5 rounded-xl text-center hover:bg-brand-700 transition">
            + Transaksi Baru
        </a>

        <nav class="flex-1 px-3 space-y-1 overflow-y-auto pb-6">
            @include('layouts.partials.nav-item', ['route' => 'dashboard', 'icon' => 'layout-grid', 'label' => 'Dashboard'])
            @include('layouts.partials.nav-item', ['route' => 'transactions.index', 'icon' => 'file-text', 'label' => 'Transaksi'])
            @include('layouts.partials.nav-item', ['route' => 'customers.index', 'icon' => 'users', 'label' => 'Pelanggan'])
            @include('layouts.partials.nav-item', ['route' => 'services.index', 'icon' => 'shirt', 'label' => 'Layanan'])
            @include('layouts.partials.nav-item', ['route' => 'pickups.index', 'icon' => 'truck', 'label' => 'Antar-Jemput'])

            @role('pemilik')
                @include('layouts.partials.nav-item', ['route' => 'promos.index', 'icon' => 'tag', 'label' => 'Promo'])
                @include('layouts.partials.nav-item', ['route' => 'expenses.index', 'icon' => 'receipt', 'label' => 'Pengeluaran'])
                @include('layouts.partials.nav-item', ['route' => 'inventory.index', 'icon' => 'box', 'label' => 'Inventori'])
                @include('layouts.partials.nav-item', ['route' => 'reports.index', 'icon' => 'bar-chart-3', 'label' => 'Laporan'])
                @include('layouts.partials.nav-item', ['route' => 'users.index', 'icon' => 'user', 'label' => 'Pengguna'])
                @include('layouts.partials.nav-item', ['route' => 'settings.index', 'icon' => 'settings', 'label' => 'Pengaturan'])
            @endrole

            @role('pegawai')
                @include('layouts.partials.nav-item', ['route' => 'process.queue', 'icon' => 'clipboard-list', 'label' => 'Antrian Proses'])
            @endrole
        </nav>
    </aside>

    {{-- Konten --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b flex items-center justify-between px-4 md:px-6">
            <button class="md:hidden" @click="mobileNavOpen = true">☰</button>
            <div class="flex-1">{{ $header ?? '' }}</div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-gray-400 hover:text-danger-600">Keluar</button>
                </form>
            </div>
        </header>

        <main class="p-4 md:p-6">
            @if (session('success'))
                <div class="mb-4 bg-success-50 text-success-600 px-4 py-3 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>

    {{-- Mobile bottom navigation --}}
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t flex justify-around py-2 z-40">
        <a href="{{ route('dashboard') }}" class="text-xs text-center px-3">Dashboard</a>
        <a href="{{ route('transactions.create') }}" class="text-xs text-center px-3 text-brand-600 font-medium">Transaksi</a>
        <a href="{{ route('customers.index') }}" class="text-xs text-center px-3">Pelanggan</a>
    </div>
</div>

@stack('scripts')
</body>
</html>
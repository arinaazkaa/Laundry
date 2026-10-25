<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? setting('business_name', 'Laundry Desa') }}</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
<div class="min-h-screen flex" x-data="{ mobileNavOpen: false }" @keydown.escape.window="mobileNavOpen = false">

    {{-- Sidebar desktop --}}
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex md:flex-col flex-shrink-0">
        @include('layouts.partials.nav')
    </aside>

    {{-- Menu geser untuk HP --}}
    <div x-show="mobileNavOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 md:hidden">
        <div class="absolute inset-0 bg-black/40" @click="mobileNavOpen = false"></div>
        <aside class="absolute inset-y-0 left-0 w-72 max-w-[85%] bg-white flex flex-col shadow-xl">
            <button type="button" @click="mobileNavOpen = false" aria-label="Tutup menu"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">&#10005;</button>
            @include('layouts.partials.nav')
        </aside>
    </div>

    {{-- Konten --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b flex items-center gap-3 px-4 md:px-6">
            <button type="button" @click="mobileNavOpen = true" aria-label="Buka menu"
                    class="md:hidden p-2 -ml-2 text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="flex-1 min-w-0 truncate">{{ $header ?? '' }}</div>

            <div class="flex items-center gap-3">
                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover">
                <span class="hidden sm:inline text-sm text-gray-600">{{ auth()->user()->name }}</span>
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
            @if (session('error'))
                <div class="mb-4 bg-danger-50 text-danger-600 px-4 py-3 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
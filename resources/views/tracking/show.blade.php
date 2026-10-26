<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lacak Laundry — {{ $code }}</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-md mx-auto px-4 py-8">
        <div class="text-center mb-6">
            @if($logoUrl = business_logo_url())
                <img src="{{ $logoUrl }}" alt="Logo" class="h-12 w-auto mx-auto mb-2 object-contain">
            @endif
            <h1 class="text-xl font-bold text-brand-600">{{ setting('business_name', 'Laundry Desa') }}</h1>            <p class="text-sm text-gray-400">Lacak status laundry kamu</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-4">
            <div class="flex justify-between items-start mb-1">
                <div>
                    <p class="text-xs text-gray-400">No. Transaksi</p>
                    <p class="font-bold text-lg">{{ $code }}</p>
                </div>
                <x-badge :status="$status" />
            </div>
            <p class="text-sm text-gray-500">Halo, {{ $customerName }} 👋</p>
        </div>

        {{-- Progress Tracker (reuse komponen yang sudah ada) --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-4">
            <x-progress-tracker :status="$status" :vertical="true" />
        </div>

        {{-- Item Laundry --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-4">
            <h2 class="font-semibold text-sm mb-3">Detail Layanan</h2>
            @foreach($items as $item)
                <div class="flex justify-between text-sm py-1.5 border-b last:border-0">
                    <span>{{ $item['name'] }}</span>
                    <span class="text-gray-500">{{ $item['qty'] }} {{ $item['unit'] }}</span>
                </div>
            @endforeach

            @if($usePickup || $useDelivery)
            <div class="flex gap-2 mt-3">
                @if($usePickup)<span class="text-xs bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full">Jemput</span>@endif
                @if($useDelivery)<span class="text-xs bg-brand-50 text-brand-600 px-2.5 py-1 rounded-full">Antar</span>@endif
            </div>
            @endif
        </div>

        {{-- Pembayaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-4">
            <h2 class="font-semibold text-sm mb-3">Pembayaran</h2>
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-500">Total</span>
                <span class="font-bold">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Status</span>
                <x-badge :status="$paymentStatus" />
            </div>
            @if($remaining > 0)
                <div class="bg-warning-50 text-warning-700 text-sm rounded-xl p-3 mt-2">
                    Sisa tagihan: <span class="font-bold">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                    <br>Silakan lunasi saat pengambilan laundry.
                </div>
            @endif
        </div>

        @if($estimatedDoneAt)
        <div class="bg-brand-50 rounded-2xl p-4 text-center mb-4">
            <p class="text-sm text-brand-700">Estimasi Selesai</p>
            <p class="font-bold text-brand-800">{{ $estimatedDoneAt->translatedFormat('d F Y') }}</p>
        </div>
        @endif

        <p class="text-center text-xs text-gray-400 mt-6">
            Diterima: {{ $createdAt->translatedFormat('d M Y, H:i') }} WIB
        </p>
        <p class="text-center text-xs text-gray-400 mt-3">
            Punya akun pelanggan? <a href="{{ route('portal.login') }}" class="text-brand-600">Masuk</a>
        </p>
        @if($hours = operating_hours_text())
            <p class="text-center text-xs text-gray-400 mt-1">Jam buka: {{ $hours }}</p>
        @endif
    </div>
</body>
</html>
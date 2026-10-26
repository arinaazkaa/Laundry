<x-layouts.portal-layout title="Beranda">
    <div>
        <p class="text-sm text-gray-500">Halo,</p>
        <h1 class="text-2xl font-bold text-gray-900">{{ \Illuminate\Support\Str::before($customer->name, ' ') }}</h1>
    </div>

    {{-- Kartu keanggotaan --}}
    @if($customer->is_member)
        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold tracking-wider text-amber-700">MEMBER</p>
                <span class="text-amber-600">⭐</span>
            </div>
            <p class="text-lg font-bold text-gray-900 mt-2">{{ $customer->name }}</p>
            <p class="text-sm text-amber-800 mt-1">
                Diskon otomatis {{ rtrim(rtrim(number_format((float) setting('member_discount_percent', 0), 2, '.', ''), '0'), '.') }}% di setiap transaksi
                <span class="text-xs">(tidak berlaku bersama promo lain)</span>
            </p>
            <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-amber-200">
                <div>
                    <p class="text-xs text-amber-700">Total hemat dari diskon</p>
                    <p class="font-bold text-gray-900">Rp {{ number_format($totalSaved, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-amber-700">Total transaksi</p>
                    <p class="font-bold text-gray-900">{{ $totalTransactions }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-2xl bg-white border border-gray-100 p-5">
            <p class="text-xs font-semibold tracking-wider text-gray-400">MENUJU MEMBER</p>

            @if($eligible)
                <p class="font-semibold text-gray-900 mt-2">Kamu sudah memenuhi syarat member 🎉</p>
                <p class="text-sm text-gray-500 mt-1">Tanyakan ke kasir untuk dijadikan member dan dapatkan diskon otomatis di setiap transaksi.</p>
            @else
                <p class="font-semibold text-gray-900 mt-2">
                    {{ $remainingTrx }} transaksi lagi
                    <span class="font-normal text-gray-500">atau belanja Rp {{ number_format($remainingSpent, 0, ',', '.') }} lagi</span>
                </p>
                <div class="h-2 bg-gray-100 rounded-full mt-3">
                    <div class="h-2 bg-brand-600 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Syarat: {{ $minTrx }} kali transaksi atau total belanja Rp {{ number_format($minSpent, 0, ',', '.') }}.
                    Setelah terpenuhi, pemilik toko yang menjadikan kamu member.
                </p>
            @endif
        </div>
    @endif

        {{-- Minta jemput --}}
    @if(setting('pickup_enabled'))
        @php $pickupActive = $customer->pickupRequests()->active()->latest()->first(); @endphp
        <a href="{{ route('portal.pickup') }}" class="block bg-white rounded-2xl border border-gray-100 p-4 hover:shadow-sm transition">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-900">Minta Jemput Laundry</p>
                    @if($pickupActive)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $pickupActive->preferred_date->translatedFormat('l, d M') }} · {{ $pickupActive->slot_label }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-0.5">Laundry dijemput ke alamatmu.</p>
                    @endif
                </div>
                @if($pickupActive)
                    <x-badge :status="$pickupActive->status" />
                @else
                    <span class="text-brand-600 text-sm font-medium">Buat →</span>
                @endif
            </div>
        </a>
    @endif

    
    {{-- Cucian berjalan --}}
    <div>
        <h2 class="font-semibold text-gray-900 mb-2">Cucian Berjalan</h2>

        @forelse($active as $t)
            <a href="{{ route('portal.transactions.show', $t->tracking_token) }}"
               class="block bg-white rounded-2xl border border-gray-100 p-4 mb-3 hover:shadow-sm transition">
                <div class="flex items-start justify-between gap-2">
                    <span class="font-semibold text-gray-900">{{ $t->code }}</span>
                    <x-badge :status="$t->status" />
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ $t->items->pluck('service_name')->join(', ') }}</p>
                <div class="flex items-center justify-between mt-3 text-sm">
                    <span class="text-gray-400">
                        @if($t->estimated_done_at) Estimasi selesai {{ $t->estimated_done_at->translatedFormat('d M Y') }} @endif
                    </span>
                    @if($t->status !== 'dibatalkan' && $t->remaining > 0)
                        <span class="text-warning-600 font-medium">Sisa Rp {{ number_format($t->remaining, 0, ',', '.') }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-5 text-sm text-gray-400">Tidak ada cucian yang sedang berjalan.</div>
        @endforelse
    </div>

    {{-- Promo --}}
    @if($promos->isNotEmpty())
    <div>
        <h2 class="font-semibold text-gray-900 mb-2">Promo untuk Kamu</h2>
        @foreach($promos as $promo)
            <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-3">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-semibold text-gray-900">{{ $promo['name'] }}</p>
                    @if($promo['member_only'])
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full whitespace-nowrap">Khusus member</span>
                    @endif
                </div>
                <p class="text-lg font-bold text-brand-600 mt-1">{{ $promo['value'] }}</p>
                @if($promo['description'])<p class="text-sm text-gray-500">{{ $promo['description'] }}</p>@endif
                @if($promo['syarat'])<p class="text-xs text-gray-400 mt-1">{{ $promo['syarat'] }}</p>@endif
                <p class="text-xs text-gray-400 mt-1">Sampai {{ $promo['until'] }}. Sebutkan ke kasir saat transaksi.</p>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Hubungi toko --}}
    @if(setting('business_whatsapp'))
        <a href="https://wa.me/{{ setting('business_whatsapp') }}" target="_blank"
           class="block text-center border border-success-200 text-success-600 bg-white rounded-xl py-3 text-sm font-medium">
            Hubungi {{ setting('business_name', 'Laundry Desa') }} lewat WhatsApp
        </a>
    @endif
</x-layouts.portal-layout>
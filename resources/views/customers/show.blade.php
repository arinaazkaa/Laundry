<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Detail Pelanggan</h1></x-slot>

    @php
        $activation = session('activation');
        $hasPendingCode = $customer->activation_code_hash
            && $customer->activation_expires_at
            && $customer->activation_expires_at->isFuture();
        $totalSpent = (float) $customer->transactions->where('status', '!=', 'dibatalkan')->sum('total');
        $totalTrx = $customer->transactions->where('status', '!=', 'dibatalkan')->count();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mb-3
                    {{ $customer->is_member ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-600' }}">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-bold">{{ $customer->name }}</h2>
                    @if($customer->is_member)
                        <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full">⭐ Member</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm">{{ $customer->phone ?? '-' }}</p>
                <p class="text-gray-500 text-sm mt-1">{{ $customer->address ?? '-' }}</p>

                <div class="mt-4 pt-4 border-t space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Total Transaksi</span><span class="font-medium">{{ $totalTrx }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Total Belanja</span><span class="font-medium">Rp {{ number_format($totalSpent, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Bergabung</span><span class="font-medium">{{ $customer->created_at->translatedFormat('d M Y') }}</span></div>
                </div>
            </div>

            {{-- Akses Online --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h3 class="font-semibold mb-1">Akses Online</h3>
                <p class="text-xs text-gray-400 mb-4">Pelanggan bisa memantau cucian dan riwayatnya sendiri lewat HP. Tidak wajib.</p>

                @if($customer->has_online_access)
                    <p class="text-sm"><span class="inline-block w-2 h-2 rounded-full bg-success-500 mr-1.5"></span>
                        Aktif sejak {{ $customer->activated_at?->translatedFormat('d M Y') ?? '-' }}</p>
                @else
                    <p class="text-sm text-gray-500"><span class="inline-block w-2 h-2 rounded-full bg-gray-300 mr-1.5"></span> Belum punya akses online</p>
                @endif

                @if($activation)
                    @php
                        $code = $activation['code'];
                        $expiresAt = \Carbon\Carbon::parse($activation['expires']);
                        $link = route('portal.activate', ['hp' => $customer->phone_normalized, 'kode' => $code]);
                        $waText = "Halo {$customer->name}, ini kode aktivasi akun online " . setting('business_name', 'Laundry Desa')
                            . ": {$code}\nAktifkan di: {$link}\nBerlaku sampai " . $expiresAt->translatedFormat('d M Y') . ". Jangan bagikan kode ini ke orang lain.";
                    @endphp
                    <div x-data="{ copied: false }" class="rounded-xl border border-amber-200 bg-amber-50 p-4 mt-4">
                        <p class="text-xs text-amber-700">Kode aktivasi. Hanya tampil sekali, kirim atau catat sekarang.</p>
                        <p class="text-3xl font-bold tracking-widest text-gray-900 my-2">{{ $code }}</p>
                        <p class="text-xs text-gray-500">Berlaku sampai {{ $expiresAt->translatedFormat('d M Y, H:i') }} WIB, sekali pakai.</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <button type="button"
                                    @click="navigator.clipboard?.writeText('{{ $code }}').then(() => copied = true).catch(() => {})"
                                    class="border bg-white rounded-xl px-3 py-1.5 text-xs">
                                <span x-show="!copied">Salin kode</span><span x-show="copied" x-cloak>Tersalin</span>
                            </button>
                            @if($customer->phone_normalized)
                                <a href="https://wa.me/{{ $customer->phone_normalized }}?text={{ urlencode($waText) }}" target="_blank"
                                   class="border border-success-200 bg-white text-success-600 rounded-xl px-3 py-1.5 text-xs">Kirim lewat WhatsApp</a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-3">Alamat aktivasi: {{ route('portal.activate') }}</p>
                    </div>
                @elseif($hasPendingCode)
                    <p class="text-xs text-gray-500 mt-3">Ada kode aktivasi yang belum dipakai, berlaku sampai {{ $customer->activation_expires_at->translatedFormat('d M Y, H:i') }} WIB. Kode lama tidak bisa ditampilkan lagi, buat kode baru kalau perlu.</p>
                @endif

                @if(! $customer->phone_normalized)
                    <p class="text-xs text-danger-600 mt-3">Nomor HP belum diisi atau tidak valid. Isi dulu lewat Edit Pelanggan (contoh 0812xxxxxxxx).</p>
                @else
                    <div class="flex flex-wrap gap-2 mt-4">
                        <form method="POST" action="{{ route('customers.accessCode', $customer) }}">
                            @csrf
                            <button class="bg-brand-600 text-white rounded-xl px-4 py-2 text-sm font-medium">
                                {{ $customer->has_online_access ? 'Reset Kata Sandi (Kode Baru)' : 'Buat Kode Aktivasi' }}
                            </button>
                        </form>

                        @role('pemilik')
                            @if($customer->has_online_access || $hasPendingCode)
                            <form method="POST" action="{{ route('customers.revokeAccess', $customer) }}"
                                  onsubmit="return confirm('Cabut akses online {{ $customer->name }}? Pelanggan tidak bisa masuk lagi sampai diaktifkan ulang dengan kode baru.')">
                                @csrf @method('DELETE')
                                <button class="border border-danger-200 text-danger-600 rounded-xl px-4 py-2 text-sm">Cabut Akses</button>
                            </form>
                            @endif
                        @endrole
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 h-fit">
            <h3 class="font-semibold mb-4">Riwayat Transaksi</h3>
            @forelse($customer->transactions as $t)
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <a href="{{ route('transactions.show', $t) }}" class="font-medium text-brand-600">{{ $t->code }}</a>
                        <p class="text-xs text-gray-400">{{ $t->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium">Rp {{ number_format($t->total, 0, ',', '.') }}</p>
                        <x-badge :status="$t->status" />
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada transaksi" />
            @endforelse
        </div>
    </div>
</x-layouts.app-layout>
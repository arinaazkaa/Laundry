<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Detail Transaksi</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $transaction->code }}</h1>
            <p class="text-gray-500 text-sm">{{ $transaction->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('transactions.nota', $transaction) }}" target="_blank"
                class="border rounded-xl px-4 py-2 text-sm">🖨️ Cetak Nota</a>
            <a href="{{ route('transactions.nota.download', $transaction) }}"
                class="border rounded-xl px-4 py-2 text-sm">⬇ Download PDF</a>
            @if($transaction->status !== 'dibatalkan' && $transaction->status !== 'selesai')
            <form method="POST" action="{{ route('transactions.cancel', $transaction) }}"
                  onsubmit="return confirm('Batalkan transaksi ini?')">
                @csrf @method('PATCH')
                <button class="border border-danger-200 text-danger-600 rounded-xl px-4 py-2 text-sm">Batalkan</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Progress Tracker --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
        <x-progress-tracker :status="$transaction->status" />

        @role('pemilik|pegawai')
        @if(!in_array($transaction->status, ['selesai', 'dibatalkan']))
        <form method="POST" action="{{ route('transactions.updateStatus', $transaction) }}" class="flex gap-2 mt-5 pt-4 border-t">
            @csrf @method('PATCH')
            <select name="status" class="flex-1 border-gray-200 rounded-xl text-sm">
                @foreach(\App\Models\Transaction::STATUS_FLOW as $s)
                    <option value="{{ $s }}" {{ $transaction->status === $s ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
            <button class="bg-brand-600 text-white px-5 rounded-xl text-sm font-medium">Update Status</button>
        </form>
        @endif
        @endrole
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kiri: Info Pelanggan & Item --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h3 class="font-semibold mb-3">Informasi Pelanggan</h3>
                @if($transaction->customer)
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full flex items-center justify-center font-semibold
                            {{ $transaction->customer->is_member ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-600' }}">
                            {{ strtoupper(substr($transaction->customer->name, 0, 1)) }}
                        </span>
                        <div>
                            <a href="{{ route('customers.show', $transaction->customer) }}" class="font-medium text-brand-600">
                                {{ $transaction->customer->name }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $transaction->customer->phone ?? '-' }}</p>
                        </div>
                        @if($transaction->customer->is_member)
                            <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full ml-auto">⭐ Member</span>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Pelanggan Umum (tanpa data terdaftar)</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h3 class="font-semibold mb-3">Daftar Layanan</h3>
                @foreach($transaction->items as $item)
                    <div class="flex justify-between items-center py-2.5 border-b last:border-0 text-sm">
                        <div>
                            <p class="font-medium">{{ $item->service_name }}</p>
                            <p class="text-gray-400 text-xs">{{ $item->qty }} {{ $item->unit_type }} × Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</p>
                        </div>
                        <span class="font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            @if($transaction->use_pickup || $transaction->use_delivery)
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h3 class="font-semibold mb-2">Layanan Antar-Jemput</h3>
                <div class="flex gap-2 text-sm">
                    @if($transaction->use_pickup)<span class="bg-brand-50 text-brand-600 px-3 py-1 rounded-full text-xs">Jemput</span>@endif
                    @if($transaction->use_delivery)<span class="bg-brand-50 text-brand-600 px-3 py-1 rounded-full text-xs">Antar</span>@endif
                </div>
            </div>
            @endif
        </div>

        {{-- Kanan: Ringkasan Pembayaran --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 h-fit">
            <h3 class="font-semibold mb-4">Ringkasan Pembayaran</h3>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between text-danger-600"><span>Diskon</span><span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Ongkir</span><span>Rp {{ number_format($transaction->delivery_fee, 0, ',', '.') }}</span></div>
                <div class="flex justify-between font-bold text-base pt-2 border-t">
                    <span>Total</span><span class="text-brand-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2"><span class="text-gray-500">Sudah Dibayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
                @if($transaction->remaining > 0)
                    <div class="flex justify-between text-danger-600 font-medium"><span>Sisa Tagihan</span><span>Rp {{ number_format($transaction->remaining, 0, ',', '.') }}</span></div>
                @endif
            </div>

            <div class="mt-3">
                <x-badge :status="$transaction->payment_status" />
            </div>

                        {{-- QR Code Tracking --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center mt-4">
                <h3 class="font-semibold text-sm mb-3">QR Code Tracking</h3>
                <div class="flex justify-center">
                    {!! QrCode::size(160)->generate(route('tracking.show', $transaction->code)) !!}
                </div>
                <p class="text-xs text-gray-400 mt-2">Pelanggan bisa scan untuk cek status tanpa install app</p>
                <a href="{{ route('tracking.show', $transaction->code) }}" target="_blank" class="text-xs text-brand-600 mt-1 inline-block break-all">
                    {{ route('tracking.show', $transaction->code) }}
                </a>
            </div>

            {{-- Form Terima Pembayaran Tambahan (untuk status DP) --}}
            @if($transaction->payment_status !== 'lunas')
            <form method="POST" action="{{ route('payments.store', $transaction) }}" class="mt-4 pt-4 border-t space-y-2">
                @csrf
                <label class="text-sm font-medium text-gray-700">Terima Pembayaran</label>
                <input type="number" name="amount" min="1" max="{{ $transaction->remaining }}" required
                       placeholder="Jumlah dibayar" class="w-full border-gray-200 rounded-xl text-sm">
                <select name="method" class="w-full border-gray-200 rounded-xl text-sm">
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
                <button class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-medium">Catat Pembayaran</button>
            </form>
            @endif

            {{-- Tombol WhatsApp --}}
            @if($transaction->customer && $transaction->customer->phone)
                @php
                    $phone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $transaction->customer->phone));
                    $message = $transaction->status === 'siap_diambil' || $transaction->status === 'selesai'
                        ? "Halo {$transaction->customer->name}, laundry dengan nomor transaksi {$transaction->code} sudah selesai dan dapat diambil. Total pembayaran Rp " . number_format($transaction->total, 0, ',', '.') . "."
                        : "Halo {$transaction->customer->name}, transaksi {$transaction->code} sedang kami proses. Total Rp " . number_format($transaction->total, 0, ',', '.') . ", sisa pembayaran Rp " . number_format($transaction->remaining, 0, ',', '.') . ".";
                @endphp
                <a href="https://wa.me/{{ $phone }}?text={{ urlencode($message) }}" target="_blank"
                   class="mt-3 w-full border border-success-200 text-success-600 rounded-xl py-2.5 text-sm font-medium flex items-center justify-center gap-2">
                    💬 Kirim WhatsApp
                </a>
            @endif
        </div>
    </div>
</x-layouts.app-layout>
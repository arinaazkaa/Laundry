<x-layouts.portal-layout title="Minta Jemput">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Minta Jemput</h1>
        <p class="text-sm text-gray-500">Laundry dijemput ke alamatmu. Kasir akan mengonfirmasi permintaanmu.</p>
    </div>

    @if(! $enabled)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-sm text-gray-500">
            Layanan jemput sedang tidak tersedia. Hubungi toko kalau perlu bantuan.
        </div>

    @elseif($active)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs text-gray-400">Permintaan aktif</p>
                    <p class="font-semibold text-gray-900">{{ $active->preferred_date->translatedFormat('l, d F Y') }} · {{ $active->slot_label }}</p>
                </div>
                <x-badge :status="$active->status" />
            </div>

            <p class="text-sm text-gray-600 mt-3 whitespace-pre-line">{{ $active->address }}</p>
            @if($active->notes)
                <p class="text-xs text-gray-400 mt-2">Catatanmu: {{ $active->notes }}</p>
            @endif
            @if($active->staff_note)
                <p class="text-sm bg-brand-50 text-brand-700 rounded-xl px-3 py-2 mt-3">Dari kasir: {{ $active->staff_note }}</p>
            @endif

            <p class="text-xs text-gray-400 mt-3">
                @if($active->status === 'menunggu')
                    Menunggu konfirmasi kasir. Biasanya dibalas pada jam buka toko.
                @else
                    Sudah dikonfirmasi. Siapkan cucianmu di alamat di atas.
                @endif
            </p>

            <form method="POST" action="{{ route('portal.pickup.cancel', $active->id) }}" class="mt-4"
                  onsubmit="return confirm('Batalkan permintaan jemput ini?')">
                @csrf @method('DELETE')
                <button class="w-full border border-danger-200 text-danger-600 rounded-xl py-2.5 text-sm font-medium">Batalkan Permintaan</button>
            </form>
        </div>

    @elseif($dateOptions->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 p-5 text-sm text-gray-500">
            Tidak ada tanggal yang tersedia dalam 7 hari ke depan. Hubungi toko untuk mengatur jemput.
        </div>

    @else
        <form method="POST" action="{{ route('portal.pickup.store') }}" class="bg-white rounded-2xl border border-gray-100 p-5 space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium text-gray-700">Alamat jemput</label>
                <textarea name="address" rows="3" required minlength="8" maxlength="255"
                          placeholder="Jalan, RT/RW, patokan"
                          class="w-full border-gray-200 rounded-xl mt-1">{{ old('address', $customer->address) }}</textarea>
                @error('address') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Tanggal</label>
                <select name="preferred_date" required class="w-full border-gray-200 rounded-xl mt-1">
                    @foreach($dateOptions as $value => $label)
                        <option value="{{ $value }}" {{ old('preferred_date') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('preferred_date') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Waktu</label>
                <div class="grid grid-cols-3 gap-2 mt-1">
                    @foreach(\App\Models\PickupRequest::TIME_SLOTS as $key => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="time_slot" value="{{ $key }}" class="sr-only peer"
                                   {{ old('time_slot', 'pagi') === $key ? 'checked' : '' }}>
                            <span class="block text-center rounded-xl border px-3 py-3 text-sm text-gray-600 peer-checked:bg-brand-50 peer-checked:border-brand-500 peer-checked:text-brand-700 peer-checked:font-semibold">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-1">Jam pastinya disepakati dengan kasir.</p>
                @error('time_slot') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Catatan (opsional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" maxlength="300"
                       placeholder="Contoh: rumah pagar hijau, hubungi dulu"
                       class="w-full border-gray-200 rounded-xl mt-1">
                @error('notes') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <p class="text-xs text-gray-400">
                Biaya jemput Rp {{ number_format((float) setting('pickup_fee', 0), 0, ',', '.') }}, dibayar bersama tagihan laundry.
                @if(setting('free_ongkir_enabled'))
                    Gratis kalau laundry minimal {{ (float) setting('free_ongkir_min_weight', 0) }} kg.
                @endif
            </p>

            <button class="w-full bg-brand-600 text-white rounded-xl py-3 font-medium">Kirim Permintaan</button>
        </form>
    @endif

    @if($history->isNotEmpty())
        <div>
            <h2 class="font-semibold text-gray-900 mb-2">Riwayat Permintaan</h2>
            @foreach($history as $h)
                <div class="bg-white rounded-2xl border border-gray-100 p-4 mb-3">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">{{ $h->preferred_date->translatedFormat('d M Y') }} · {{ $h->slot_label }}</p>
                        <x-badge :status="$h->status" />
                    </div>
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $h->address }}</p>
                    @if($h->staff_note && $h->status !== 'dikonfirmasi')
                        <p class="text-xs text-gray-500 mt-2">Dari kasir: {{ $h->staff_note }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.portal-layout>
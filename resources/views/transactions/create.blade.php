<x-layouts.app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold">Transaksi Baru</h1>
    </x-slot>

    <div x-data="transactionForm({
            services: @js($services),
            promos: @js($promos),
            pickupEnabled: {{ setting('pickup_enabled') ? 'true' : 'false' }},
            deliveryEnabled: {{ setting('delivery_enabled') ? 'true' : 'false' }},
            pickupFee: {{ (float) setting('pickup_fee', 0) }},
            deliveryFee: {{ (float) setting('delivery_fee', 0) }},
            freeOngkirEnabled: {{ setting('free_ongkir_enabled') ? 'true' : 'false' }},
            freeOngkirMinWeight: {{ (float) setting('free_ongkir_min_weight', 0) }}
        })"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            {{-- Pilih Pelanggan --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h2 class="font-semibold mb-3">Pilih Pelanggan</h2>
                <div class="flex gap-3 relative">
                    <input type="text" placeholder="Nama atau No. HP..."
                           x-model="customerQuery"
                           class="flex-1 border-gray-200 rounded-xl focus:ring-brand-500 focus:border-brand-500"
                           @input.debounce.400ms="searchCustomer($event.target.value)">
                    <button type="button" @click="openQuickAdd()"
                            class="bg-brand-600 text-white w-11 rounded-xl">+</button>

                    <div x-show="customerResults.length > 0" x-cloak
                         class="absolute top-12 left-0 right-14 bg-white border rounded-xl shadow-lg z-10 max-h-72 overflow-y-auto">
                        <template x-for="c in customerResults" :key="c.id">
                            <button type="button" @click="selectCustomer(c)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-50 border-b last:border-0 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold"
                                          :class="c.is_member ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500'"
                                          x-text="c.name.charAt(0).toUpperCase()"></span>
                                    <div>
                                        <p class="font-medium text-sm" x-text="c.name"></p>
                                        <p class="text-xs text-gray-400" x-text="c.phone"></p>
                                    </div>
                                </div>
                                <template x-if="c.is_member">
                                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full whitespace-nowrap">⭐ Member</span>
                                </template>
                                <template x-if="!c.is_member && c.is_eligible_for_member">
                                    <span class="text-xs bg-brand-50 text-brand-600 px-2 py-0.5 rounded-full whitespace-nowrap">🌟 Rekomendasi</span>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Pelanggan terpilih --}}
                <template x-if="selectedCustomer">
                    <div class="mt-3 rounded-xl p-3"
                         :class="selectedCustomer.is_member ? 'bg-amber-50 border border-amber-200' : 'bg-brand-50'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold"
                                      :class="selectedCustomer.is_member ? 'bg-amber-200 text-amber-800' : 'bg-brand-100 text-brand-700'"
                                      x-text="selectedCustomer.name.charAt(0).toUpperCase()"></span>
                                <div>
                                    <p class="font-medium text-sm" x-text="selectedCustomer.name"></p>
                                    <p class="text-xs text-gray-500" x-text="selectedCustomer.phone || 'Tanpa nomor HP'"></p>
                                </div>
                            </div>
                            <button type="button" @click="selectedCustomer = null; recalculate()" class="text-gray-400">&times;</button>
                        </div>
                        <template x-if="selectedCustomer.is_member">
                            <p class="text-xs text-amber-700 mt-2">⭐ Pelanggan member — otomatis dapat diskon.</p>
                        </template>
                        <template x-if="!selectedCustomer.is_member && selectedCustomer.is_eligible_for_member">
                            <p class="text-xs text-brand-700 mt-2">🌟 Pelanggan ini sudah layak jadi member. Ajukan lewat halaman Pelanggan.</p>
                        </template>
                    </div>
                </template>

                <template x-if="!selectedCustomer">
                    <p class="text-xs text-gray-400 mt-2">Tidak dipilih = Pelanggan Umum</p>
                </template>
            </div>

            {{-- Pilih Layanan --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold">Pilih Layanan</h2>
                    <div class="flex bg-gray-100 rounded-xl p-1 text-sm">
                        <button type="button" @click="tab = 'kiloan'"
                                :class="tab === 'kiloan' ? 'bg-white shadow rounded-lg' : ''"
                                class="px-3 py-1">Kiloan</button>
                        <button type="button" @click="tab = 'satuan'"
                                :class="tab === 'satuan' ? 'bg-white shadow rounded-lg' : ''"
                                class="px-3 py-1">Satuan</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <template x-for="s in services.filter(s => s.type === tab)" :key="s.id">
                        <button type="button" @click="addItem(s)"
                                class="border rounded-xl p-4 text-left hover:border-brand-500 transition">
                            <p class="font-medium text-sm" x-text="s.name"></p>
                            <p class="text-xs text-gray-500"
                               x-text="'Rp ' + Number(s.price).toLocaleString('id-ID') + (s.type === 'kiloan' ? '/kg' : '')"></p>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Pilih Promo --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h2 class="font-semibold mb-3">Promo Tersedia</h2>
                <p class="text-xs text-gray-400 mb-2">Server: {{ $promos->count() }} promo | Browser: <span x-text="promos === undefined ? 'undefined' : promos.length"></span></p>

                @if($promos->isEmpty())
                    <p class="text-sm text-gray-500">
                        @if($totalPromo === 0)
                            Belum ada promo. Buat promo lewat menu
                            <a href="{{ route('promos.create') }}" class="text-brand-600 font-medium">Promo</a>.
                        @else
                            Tidak ada promo yang aktif hari ini. Ada {{ $totalPromo }} promo tersimpan, tapi tanggal berlakunya
                            sudah lewat, belum dimulai, atau promonya dinonaktifkan. Cek di menu
                            <a href="{{ route('promos.index') }}" class="text-brand-600 font-medium">Promo</a>.
                        @endif
                    </p>
                @else
                    <div class="flex flex-wrap gap-2">
                        <template x-for="promo in promos" :key="promo.id">
                            <button type="button" @click="selectPromo(promo.id)"
                                    class="border rounded-xl px-4 py-2.5 text-left transition"
                                    :class="selectedPromoId === promo.id ? 'border-brand-500 bg-brand-50' : 'hover:border-gray-300'">
                                <p class="text-sm font-medium" x-text="promo.name"></p>
                             <p class="text-xs text-gray-500">
                                    <template x-if="promo.type === 'gratis_ongkir'">
                                        <span>Gratis Ongkir</span>
                                    </template>
                                    <template x-if="promo.type !== 'gratis_ongkir' && promo.value_type === 'persen'">
                                        <span x-text="Number(promo.value) + '% off'"></span>
                                    </template>
                                    <template x-if="promo.type !== 'gratis_ongkir' && promo.value_type !== 'persen'">
                                        <span x-text="'Rp ' + Number(promo.value ?? 0).toLocaleString('id-ID')"></span>
                                    </template>
                                    <template x-if="promo.type === 'member'">
                                        <span class="text-amber-600"> · ⭐ Khusus member</span>
                                    </template>
                                </p>
                            </button>
                        </template>
                    </div>

                    <template x-if="selectedPromoId && promoUsable === false">
                        <p class="text-xs text-danger-600 mt-3" x-text="promoReason"></p>
                    </template>
                    <template x-if="selectedPromoId && promoUsable === true">
                        <p class="text-xs text-success-600 mt-3">✓ Promo berhasil diterapkan.</p>
                    </template>
                @endif
            </div>
        </div>

        {{-- Ringkasan Pesanan --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 h-fit sticky top-6">
            <h2 class="font-semibold mb-4">Ringkasan Pesanan</h2>

            <template x-if="cart.length === 0">
                <p class="text-sm text-gray-400">Belum ada layanan dipilih.</p>
            </template>

            <template x-for="(item, idx) in cart" :key="idx">
                <div class="flex justify-between items-center py-2 border-b">
                    <div>
                        <p class="font-medium text-sm" x-text="item.name"></p>
                        <p class="text-xs text-gray-500"
                           x-text="'Rp ' + item.price.toLocaleString('id-ID') + (item.type === 'kiloan' ? '/kg' : '')"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.1" min="0.1" x-model.number="item.qty"
                               @input="recalculate()" class="w-16 border-gray-200 rounded-lg text-right text-sm">
                        <button type="button" @click="removeItem(idx)" class="text-danger-500 text-xs">&times;</button>
                    </div>
                </div>
            </template>

            <label class="flex items-center gap-2 mt-4 bg-gray-50 rounded-xl p-3 cursor-pointer" x-show="pickupEnabled">
                <input type="checkbox" x-model="usePickup" @change="recalculate()">
                <span class="text-sm">Jemput <span class="text-gray-400" x-text="'+ Rp ' + Number({{ (float) setting('pickup_fee', 0) }}).toLocaleString('id-ID')"></span></span>
            </label>
            <label class="flex items-center gap-2 mt-2 bg-gray-50 rounded-xl p-3 cursor-pointer" x-show="deliveryEnabled">
                <input type="checkbox" x-model="useDelivery" @change="recalculate()">
                <span class="text-sm">Antar <span class="text-gray-400" x-text="'+ Rp ' + Number({{ (float) setting('delivery_fee', 0) }}).toLocaleString('id-ID')"></span></span>
            </label>

            <template x-if="freeOngkirApplied">
                <p class="text-sm text-success-600 mt-2">🎉 Anda mendapatkan GRATIS ONGKIR!</p>
            </template>

            <div class="mt-4 space-y-1 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span></div>
                <div class="flex justify-between text-danger-600">
                    <span>
                        Diskon
                        <template x-if="memberDiscountApplied"><span class="text-xs">(Member)</span></template>
                    </span>
                    <span x-text="'- Rp ' + discount.toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between"><span>Ongkir</span><span x-text="'Rp ' + deliveryFee.toLocaleString('id-ID')"></span></div>
                <div class="flex justify-between font-bold text-lg pt-2 border-t">
                    <span>Total</span><span class="text-brand-600" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                </div>
            </div>

            <div class="mt-4">
                <label class="text-sm text-gray-500">Jumlah Dibayar</label>
                <input type="number" x-model.number="paidAmount" @input="recalculate()"
                       class="w-full border-gray-200 rounded-xl mt-1" placeholder="0">
                <template x-if="change > 0">
                    <p class="text-xs text-success-600 mt-1" x-text="'Kembalian: Rp ' + change.toLocaleString('id-ID')"></p>
                </template>
            </div>

            <select x-model="paymentMethod" class="w-full border-gray-200 rounded-xl mt-3 text-sm">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
                <option value="ewallet">E-Wallet</option>
            </select>

            <template x-if="previewing">
                <p class="text-xs text-gray-400 text-center mt-2">Menghitung ulang...</p>
            </template>

            <button type="button" @click="submit()" :disabled="submitting"
                    class="w-full bg-brand-600 text-white rounded-xl py-3 mt-4 font-medium disabled:opacity-50">
                <span x-show="!submitting">Simpan &amp; Bayar</span>
                <span x-show="submitting">Menyimpan...</span>
            </button>
        </div>

        {{-- Modal Quick Add Pelanggan --}}
        <div x-show="showQuickAddModal" x-cloak
             class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
             @keydown.escape.window="showQuickAddModal = false">
            <div @click.outside="showQuickAddModal = false"
                 class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="font-semibold text-lg mb-4">Tambah Pelanggan Baru</h3>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nama <span class="text-danger-500">*</span></label>
                        <input type="text" x-model="newCustomer.name" placeholder="Nama pelanggan"
                               class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nomor HP</label>
                        <input type="text" x-model="newCustomer.phone" placeholder="08xxxxxxxxxx"
                               class="w-full border-gray-200 rounded-xl mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Alamat</label>
                        <textarea x-model="newCustomer.address" rows="2" placeholder="Opsional"
                                  class="w-full border-gray-200 rounded-xl mt-1"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showQuickAddModal = false"
                            class="flex-1 border rounded-xl py-2.5 text-sm">Batal</button>
                    <button type="button" @click="saveNewCustomer()" :disabled="savingCustomer"
                            class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-medium disabled:opacity-50">
                        <span x-show="!savingCustomer">Simpan &amp; Pilih</span>
                        <span x-show="savingCustomer">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app-layout>
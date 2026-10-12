<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Inventori</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Inventori Stok</h1>
            <p class="text-gray-500">Kelola persediaan barang dan bahan baku laundry.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.logs') }}" class="border rounded-xl px-4 py-2.5 text-sm font-medium">↻ Log Penggunaan</a>
            <a href="{{ route('inventory.create') }}" class="bg-brand-600 text-white rounded-xl px-4 py-2.5 text-sm font-medium">+ Tambah Stok</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Total Item" :value="$totalItems" />
        <div class="bg-danger-50 rounded-2xl p-5">
            <p class="text-sm text-danger-600">Stok Menipis</p>
            <p class="text-2xl font-bold text-danger-600 mt-1">{{ $lowStockCount }}</p>
        </div>
        <div class="bg-success-50 rounded-2xl p-5">
            <p class="text-sm text-success-600">Stok Aman</p>
            <p class="text-2xl font-bold text-success-600 mt-1">{{ $safeStockCount }}</p>
        </div>
    </div>

    <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari item..."
               class="flex-1 border-gray-200 rounded-xl" onchange="this.form.submit()">
        <div class="flex gap-2 flex-wrap">
            <button type="submit" name="category" value=""
                    class="px-3 py-1.5 rounded-xl text-sm border {{ !request('category') ? 'bg-brand-50 text-brand-700 border-brand-200' : '' }}">Semua Kategori</button>
            @foreach($categories as $cat)
                <button type="submit" name="category" value="{{ $cat }}"
                        class="px-3 py-1.5 rounded-xl text-sm border {{ request('category') === $cat ? 'bg-brand-50 text-brand-700 border-brand-200' : '' }}">{{ $cat }}</button>
            @endforeach
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($items->isEmpty())
            <x-empty-state title="Belum ada item inventori" description="Tambahkan bahan baku seperti deterjen, pewangi, dan plastik." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Nama Item</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium text-right">Stok Saat Ini</th>
                            <th class="px-4 py-3 font-medium text-right">Stok Min.</th>
                            <th class="px-4 py-3 font-medium">Satuan</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->category ?? '-' }}</td>
                            <td class="px-4 py-3 text-right {{ $item->is_low ? 'text-danger-600 font-semibold' : '' }}">{{ rtrim(rtrim(number_format($item->current_stock, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ (int) $item->min_stock }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->unit }}</td>
                            <td class="px-4 py-3">
                                @if($item->is_low)
                                    <span class="text-xs bg-danger-50 text-danger-600 px-2.5 py-1 rounded-full">● Stok Menipis</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full">Aman</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('inventory.stockOut', $item) }}" onsubmit="return promptQty(this, 'keluar')">
                                        @csrf
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="w-7 h-7 rounded-lg border flex items-center justify-center">−</button>
                                    </form>
                                    <form method="POST" action="{{ route('inventory.stockIn', $item) }}" onsubmit="return promptQty(this, 'masuk')">
                                        @csrf
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="w-7 h-7 rounded-lg border flex items-center justify-center">+</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :paginator="$items" />
            </div>
        @endif
    </div>

    <script>
        function promptQty(form, type) {
            const qty = prompt(`Jumlah stok ${type}:`, '1');
            if (qty === null || qty === '' || isNaN(qty) || Number(qty) <= 0) return false;
            form.querySelector('input[name=qty]').value = qty;
            return true;
        }
    </script>
</x-layouts.app-layout>
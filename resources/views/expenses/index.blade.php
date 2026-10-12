<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Pengeluaran</h1></x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pengeluaran</h1>
            <p class="text-gray-500">Catat biaya operasional laundry sehari-hari.</p>
        </div>
        <a href="{{ route('expenses.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Catat Pengeluaran
        </a>
    </div>

    <form method="GET" class="flex bg-white border rounded-xl p-1 text-sm w-fit mb-6">
        @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini'] as $key => $label)
            <button type="submit" name="period" value="{{ $key }}"
                    class="px-4 py-1.5 rounded-lg {{ $period === $key ? 'bg-brand-600 text-white' : '' }}">{{ $label }}</button>
        @endforeach
    </form>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Total Pengeluaran Periode Ini</p>
            <p class="text-3xl font-bold text-danger-600 mt-1">Rp {{ number_format($totalThisPeriod, 0, ',', '.') }}</p>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5">
            <p class="text-sm font-medium mb-3">Breakdown per Kategori</p>
            @forelse($byCategory as $cat)
                <div class="mb-2">
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $categories[$cat->category] ?? $cat->category }}</span>
                        <span class="font-medium">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full">
                        <div class="h-1.5 bg-danger-500 rounded-full"
                             style="width: {{ $totalThisPeriod > 0 ? min(100, ($cat->total / $totalThisPeriod) * 100) : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada data pengeluaran.</p>
            @endforelse
        </div>
    </div>

    <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari keterangan..."
               class="flex-1 border-gray-200 rounded-xl" onchange="this.form.submit()">
        <select name="category" onchange="this.form.submit()" class="border-gray-200 rounded-xl text-sm">
            <option value="">Semua Kategori</option>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($expenses->isEmpty())
            <x-empty-state title="Belum ada pengeluaran" description="Catat pengeluaran pertama seperti pembelian detergen atau bayar listrik." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Keterangan</th>
                            <th class="px-4 py-3 font-medium">Dicatat Oleh</th>
                            <th class="px-4 py-3 font-medium text-right">Nominal</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $expense->date->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-gray-100 px-2.5 py-1 rounded-full">{{ $categories[$expense->category] ?? $expense->category }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $expense->description ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $expense->creator->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-danger-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="text-brand-600 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                          onsubmit="return confirm('Hapus data pengeluaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-danger-600 text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :paginator="$expenses" />
            </div>
        @endif
    </div>
</x-layouts.app-layout>
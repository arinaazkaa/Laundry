<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Log Penggunaan</h1></x-slot>

    <h1 class="text-2xl font-bold mb-6">Riwayat Perubahan Stok</h1>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($logs->isEmpty())
            <x-empty-state title="Belum ada riwayat stok" />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Item</th>
                            <th class="px-4 py-3 font-medium">Tipe</th>
                            <th class="px-4 py-3 font-medium text-right">Jumlah</th>
                            <th class="px-4 py-3 font-medium">Oleh</th>
                            <th class="px-4 py-3 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr class="border-t">
                            <td class="px-4 py-3 text-gray-500">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->item->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2.5 py-1 rounded-full {{ $log->type === 'masuk' ? 'bg-success-50 text-success-600' : 'bg-danger-50 text-danger-600' }}">
                                    {{ $log->type === 'masuk' ? 'Masuk' : 'Keluar' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">{{ $log->qty }} {{ $log->item->unit ?? '' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $log->creator->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $log->notes ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <x-pagination :paginator="$logs" />
            </div>
        @endif
    </div>
</x-layouts.app-layout>
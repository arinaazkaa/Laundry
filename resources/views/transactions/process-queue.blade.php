<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Antrian Proses</h1></x-slot>

    <h1 class="text-3xl font-bold text-gray-900 mb-1">Antrian Proses Laundry</h1>
    <p class="text-gray-500 mb-6">Update status laundry yang sedang dikerjakan.</p>

    @if($transactions->isEmpty())
        <x-empty-state title="Tidak ada antrian" description="Semua laundry sudah selesai diproses." />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($transactions as $t)
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold">{{ $t->code }}</p>
                        <p class="text-sm text-gray-500">{{ $t->customer->name ?? 'Pelanggan Umum' }}</p>
                    </div>
                    <x-badge :status="$t->status" />
                </div>

                <div class="text-sm text-gray-500 space-y-1 mb-4">
                    @foreach($t->items as $item)
                        <p>• {{ $item->service_name }} ({{ $item->qty }} {{ $item->unit_type }})</p>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('transactions.updateStatus', $t) }}" class="flex gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="flex-1 border-gray-200 rounded-xl text-sm">
                        @foreach(\App\Models\Transaction::STATUS_FLOW as $s)
                            <option value="{{ $s }}" {{ $t->status === $s ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                    <button class="bg-brand-600 text-white px-4 rounded-xl text-sm font-medium">Update</button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</x-layouts.app-layout>
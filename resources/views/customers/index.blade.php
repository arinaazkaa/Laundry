<x-layouts.app-layout>
    <x-slot name="header">
        <h1 class="text-lg font-semibold">Pelanggan</h1>
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Data Pelanggan</h1>
            <p class="text-gray-500">Kelola informasi pelanggan dan status keanggotaan.</p>
        </div>
        <a href="{{ route('customers.create') }}"
           class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-brand-700">
            + Tambah Pelanggan
        </a>
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari data pelanggan..."
               class="w-full md:w-96 border-gray-200 rounded-xl"
               onchange="this.form.submit()">
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($customers->isEmpty())
            <x-empty-state title="Belum ada pelanggan" description="Tambahkan pelanggan pertama untuk mulai bertransaksi." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">No</th>
                            <th class="px-4 py-3 font-medium">Nama Pelanggan</th>
                            <th class="px-4 py-3 font-medium">No. HP</th>
                            <th class="px-4 py-3 font-medium">Alamat</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Total Transaksi</th>
                            <th class="px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $i => $customer)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $customers->firstItem() + $i }}</td>

                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $customer) }}" class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-xs
                                        {{ $customer->is_member ? 'bg-amber-100 text-amber-700' : 'bg-brand-50 text-brand-600' }}">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                                </a>
                            </td>

                            <td class="px-4 py-3 text-gray-600">{{ $customer->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $customer->address ?? '-' }}</td>

                            <td class="px-4 py-3">
                                @if($customer->is_member)
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 font-medium">⭐ Member</span>
                                @elseif($customer->is_eligible_for_member)
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-brand-50 text-brand-600 font-medium">🌟 Rekomendasi</span>
                                @else
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">Reguler</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 font-medium">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2 items-center">
                                    @if(!$customer->is_member)
                                        @role('pemilik')
                                        <form method="POST" action="{{ route('customers.promoteMember', $customer) }}"
                                              onsubmit="return confirm('Jadikan {{ $customer->name }} sebagai member?')">
                                            @csrf @method('PATCH')
                                            <button class="text-xs {{ $customer->is_eligible_for_member ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                                                Jadikan Member
                                            </button>
                                        </form>
                                        @endrole
                                    @endif
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-brand-600 text-xs">Edit</a>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                          onsubmit="return confirm('Hapus pelanggan ini?')">
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
                <x-pagination :paginator="$customers" />
            </div>
        @endif
    </div>
</x-layouts.app-layout>
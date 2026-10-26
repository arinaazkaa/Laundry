<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Pelanggan</h1></x-slot>

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

    @php
        $tabs = ['semua' => 'Semua', 'member' => 'Member', 'rekomendasi' => 'Layak Jadi Member', 'aktif' => 'Paling Aktif'];
    @endphp

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="flex flex-wrap bg-white border rounded-xl p-1 text-sm w-fit">
            @foreach($tabs as $key => $label)
                <a href="{{ route('customers.index', array_filter(['filter' => $key === 'semua' ? null : $key, 'q' => request('q')])) }}"
                   class="px-4 py-1.5 rounded-lg {{ $filter === $key ? 'bg-brand-600 text-white' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" class="flex-1 min-w-[200px] md:max-w-sm">
            @if($filter !== 'semua')
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau nomor HP..."
                   class="w-full border-gray-200 rounded-xl" onchange="this.form.submit()">
        </form>
    </div>

    @if($filter === 'aktif')
        <p class="text-sm text-gray-500 mb-3">Diurutkan dari pelanggan dengan transaksi paling banyak (tidak termasuk yang dibatalkan).</p>
    @elseif($filter === 'rekomendasi')
        <p class="text-sm text-gray-500 mb-3">
            Pelanggan yang sudah memenuhi kriteria member
            ({{ setting('member_min_transactions', 5) }}x transaksi atau belanja Rp {{ number_format((float) setting('member_min_spent', 200000), 0, ',', '.') }}) tapi belum dijadikan member.
        </p>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($customers->isEmpty())
            <x-empty-state
                :title="match($filter) {
                    'member' => 'Belum ada member',
                    'rekomendasi' => 'Belum ada pelanggan yang layak jadi member',
                    default => 'Belum ada pelanggan',
                }"
                description="Pelanggan baru bisa ditambahkan langsung dari halaman Transaksi Baru." />
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
                            <th class="px-4 py-3 font-medium">Total Belanja</th>
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

                            <td class="px-4 py-3">
                                <p class="font-medium">Rp {{ number_format($customer->total_spent, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400">{{ $customer->total_transactions }} transaksi</p>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2 items-center">
                                    @role('pemilik')
                                        @if(! $customer->is_member)
                                        <form method="POST" action="{{ route('customers.promoteMember', $customer) }}"
                                              onsubmit="return confirm('Jadikan {{ $customer->name }} sebagai member?')">
                                            @csrf @method('PATCH')
                                            <button class="text-xs {{ $customer->is_eligible_for_member ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                                                Jadikan Member
                                            </button>
                                        </form>
                                        @endif
                                    @endrole
                                    <a href="{{ route('customers.edit', $customer) }}" class="text-brand-600 text-xs">Edit</a>
                                    @role('pemilik')
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                          onsubmit="return confirm('Hapus pelanggan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-danger-600 text-xs">Hapus</button>
                                    </form>
                                    @endrole
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
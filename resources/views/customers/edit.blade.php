<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Pelanggan</h1></x-slot>

    <div class="max-w-xl">
        <h1 class="text-2xl font-bold mb-6">Edit Pelanggan</h1>

        <form method="POST" action="{{ route('customers.update', $customer) }}"
              onsubmit="return confirmMemberRemoval(this)"
              class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-medium text-gray-700">Nama <span class="text-danger-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                       class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Nomor HP</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full border-gray-200 rounded-xl mt-1">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Alamat</label>
                <textarea name="address" rows="2" class="w-full border-gray-200 rounded-xl mt-1">{{ old('address', $customer->address) }}</textarea>
            </div>

            @role('pemilik')
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_member" value="0">
                <input type="checkbox" id="is_member_checkbox" name="is_member" value="1"
                       data-was-member="{{ $customer->is_member ? '1' : '0' }}"
                       {{ old('is_member', $customer->is_member) ? 'checked' : '' }}>
                <span class="text-sm">Jadikan Member</span>
            </label>
            @endrole

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('customers.index') }}" class="px-4 py-2.5 rounded-xl border text-sm">Batal</a>
                <button class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        function confirmMemberRemoval(form) {
            const checkbox = document.getElementById('is_member_checkbox');

            // Kasir tidak punya kotak ini, jadi tidak ada yang perlu dikonfirmasi
            if (!checkbox) return true;

            const wasMember = checkbox.dataset.wasMember === '1';
            const isNowMember = checkbox.checked;

            if (wasMember && !isNowMember) {
                return confirm('Pelanggan ini akan kehilangan status member dan benefit diskon otomatisnya. Lanjutkan?');
            }
            return true;
        }
    </script>
</x-layouts.app-layout>
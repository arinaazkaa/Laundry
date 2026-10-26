<x-layouts.app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold">Edit Paket Laundry</h1></x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">Edit Paket Laundry</h1>
        @include('packages._form', ['package' => $package, 'services' => $services])
    </div>
</x-layouts.app-layout>
@props(['title' => 'Belum ada data', 'description' => ''])
<div class="text-center py-16">
    <p class="text-gray-900 font-medium">{{ $title }}</p>
    @if($description)
        <p class="text-sm text-gray-400 mt-1">{{ $description }}</p>
    @endif
</div>
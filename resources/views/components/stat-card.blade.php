@props(['label', 'value', 'badge' => null, 'tone' => 'default'])
<div class="bg-white rounded-2xl border border-gray-100 p-5">
    @if($badge)
        <div class="flex justify-end mb-2">
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">{{ $badge }}</span>
        </div>
    @endif
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="text-2xl font-bold mt-1 text-gray-900">{{ $value }}</p>
</div>
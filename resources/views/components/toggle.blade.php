@props(['name', 'label', 'hint' => null, 'checked' => false])
<div class="flex items-center justify-between bg-gray-50 rounded-xl p-3 gap-4">
    <div>
        <p class="text-sm font-medium text-gray-800">{{ $label }}</p>
        @if($hint)<p class="text-xs text-gray-400">{{ $hint }}</p>@endif
    </div>
    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1" class="sr-only peer" {{ $checked ? 'checked' : '' }}>
        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-brand-600 rounded-full transition"></div>
        <div class="absolute w-4 h-4 bg-white rounded-full left-1 top-1 peer-checked:translate-x-5 transition"></div>
    </label>
</div>
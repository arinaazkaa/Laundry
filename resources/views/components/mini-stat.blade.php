@props(['label', 'value', 'tone' => 'default'])
@php
$toneClasses = [
    'default' => 'bg-brand-50 text-brand-600',
    'success' => 'bg-success-50 text-success-600',
    'warning' => 'bg-warning-50 text-warning-600',
    'danger'  => 'bg-danger-50 text-danger-600',
][$tone];
@endphp
<div class="bg-white rounded-2xl border border-gray-100 p-4 flex items-center gap-3">
    <div class="w-10 h-10 rounded-full {{ $toneClasses }} flex items-center justify-center font-bold">
        {{ $value }}
    </div>
    <p class="text-sm text-gray-600">{{ $label }}</p>
</div>
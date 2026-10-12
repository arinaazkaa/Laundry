@props(['route', 'label', 'icon'])
@php $isActive = request()->routeIs($route); @endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
   {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50' }}">
    <x-dynamic-component :component="'lucide-' . $icon" class="w-5 h-5" />
    {{ $label }}
</a>
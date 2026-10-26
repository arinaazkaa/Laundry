@php
    $patterns = (array) ($active ?? $route);
    $isActive = request()->routeIs(...$patterns);
    $count = (int) ($navBadge ?? 0);
@endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
   {{ $isActive ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50' }}">
    <x-dynamic-component :component="'lucide-' . $icon" class="w-5 h-5" />
    <span class="flex-1">{{ $label }}</span>
    @if($count > 0)
        <span class="min-w-5 h-5 px-1.5 rounded-full bg-danger-500 text-white text-[11px] font-semibold flex items-center justify-center">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</a>
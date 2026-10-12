@props(['paginator'])
@if ($paginator->hasPages())
<div class="flex items-center justify-between px-2 pt-2">
    <p class="text-sm text-gray-500">
        Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }} hasil
    </p>
    <div class="flex gap-1">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 rounded-lg border text-gray-300">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">&lsaquo;</a>
        @endif

        @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="px-3 py-1.5 rounded-lg bg-brand-50 text-brand-700 border border-brand-200 font-medium">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg border hover:bg-gray-50">&rsaquo;</a>
        @else
            <span class="px-3 py-1.5 rounded-lg border text-gray-300">&rsaquo;</span>
        @endif
    </div>
</div>
@endif
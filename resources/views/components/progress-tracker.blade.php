@props(['status', 'vertical' => false])
@php
    $steps = [
        'menunggu' => 'Diterima',
        'diproses' => 'Diproses',
        'dicuci' => 'Dicuci',
        'dikeringkan' => 'Dikeringkan',
        'disetrika' => 'Disetrika',
        'siap_diambil' => 'Siap Diambil',
        'selesai' => 'Selesai',
    ];
    $keys = array_keys($steps);
    $currentIndex = array_search($status, $keys);
    $isCancelled = $status === 'dibatalkan';
@endphp

@if($isCancelled)
    <div class="bg-danger-50 text-danger-600 rounded-xl p-4 text-sm font-medium text-center">
        Transaksi ini telah dibatalkan.
    </div>
@elseif($vertical)
    <ol>
        @foreach($steps as $key => $label)
            @php
                $stepIndex = array_search($key, $keys);
                $isDone = $currentIndex !== false && $stepIndex <= $currentIndex;
                $isCurrent = $stepIndex === $currentIndex;
            @endphp
            <li class="relative flex gap-3 {{ $loop->last ? '' : 'pb-6' }}">
                @if(! $loop->last)
                    <span class="absolute left-4 top-8 bottom-0 w-0.5 -translate-x-1/2
                        {{ $isDone && $stepIndex < $currentIndex ? 'bg-brand-600' : 'bg-gray-200' }}"></span>
                @endif

                <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0
                    {{ $isDone ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400' }}
                    {{ $isCurrent ? 'ring-4 ring-brand-100' : '' }}">
                    @if($isDone && ! $isCurrent) ✓ @else {{ $loop->iteration }} @endif
                </div>

                <div class="pt-1">
                    <p class="text-sm {{ $isDone ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ $label }}</p>
                    @if($isCurrent)
                        <p class="text-xs text-brand-600">Sedang di tahap ini</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@else
    <div class="flex items-center overflow-x-auto pb-2">
        @foreach($steps as $key => $label)
            @php
                $stepIndex = array_search($key, $keys);
                $isDone = $currentIndex !== false && $stepIndex <= $currentIndex;
                $isCurrent = $stepIndex === $currentIndex;
            @endphp
            <div class="flex items-center flex-shrink-0">
                <div class="flex flex-col items-center gap-1.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold
                        {{ $isDone ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400' }}
                        {{ $isCurrent ? 'ring-4 ring-brand-100' : '' }}">
                        @if($isDone && ! $isCurrent) ✓ @else {{ $loop->iteration }} @endif
                    </div>
                    <span class="text-xs whitespace-nowrap {{ $isDone ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                        {{ $label }}
                    </span>
                </div>
                @if(! $loop->last)
                    <div class="w-10 md:w-16 h-0.5 mx-1 {{ $isDone && $stepIndex < $currentIndex ? 'bg-brand-600' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
@endif
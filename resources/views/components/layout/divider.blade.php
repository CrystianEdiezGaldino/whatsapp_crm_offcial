@props([
    'text' => null,
    'orientation' => 'horizontal',
    'class' => '',
])

@if($orientation === 'horizontal')
    <div class="flex items-center gap-4 {{ $class }}">
        <div class="flex-1 border-t border-outline-variant"></div>
        @if($text)
            <span class="text-xs text-on-surface-variant uppercase px-2">{{ $text }}</span>
            <div class="flex-1 border-t border-outline-variant"></div>
        @endif
    </div>
@else
    <div class="h-full border-l border-outline-variant {{ $class }}"></div>
@endif

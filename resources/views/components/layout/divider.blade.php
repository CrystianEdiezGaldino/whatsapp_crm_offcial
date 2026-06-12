@props([
    'text' => null,
    'orientation' => 'horizontal',
    'class' => '',
])

@if($orientation === 'horizontal')
    <div class="flex items-center gap-4 {{ $class }}">
        <div class="flex-1 border-t border-gray-200"></div>
        @if($text)
            <span class="text-xs text-gray-600 uppercase px-2">{{ $text }}</span>
            <div class="flex-1 border-t border-gray-200"></div>
        @endif
    </div>
@else
    <div class="h-full border-l border-gray-200 {{ $class }}"></div>
@endif

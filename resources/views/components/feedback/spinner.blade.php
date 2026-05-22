@props([
    'size' => 'md',
    'color' => 'secondary',
    'text' => null,
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-4 h-4',
        'md' => 'w-8 h-8',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16',
        default => 'w-8 h-8',
    };

    $colorClasses = match($color) {
        'primary' => 'border-primary',
        'error' => 'border-error',
        'warning' => 'border-yellow-500',
        'success' => 'border-green-500',
        'secondary' => 'border-secondary',
        default => 'border-secondary',
    };
@endphp

<div class="flex flex-col items-center gap-3 {{ $class }}">
    <div class="{{ $sizeClasses }} border-4 {{ $colorClasses }} border-transparent rounded-full animate-spin" style="border-top-color: currentColor;"></div>
    @if($text)
        <p class="text-sm text-on-surface-variant">{{ $text }}</p>
    @endif
</div>

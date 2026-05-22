@props([
    'src' => null,
    'name' => null,
    'size' => 'md',
    'status' => null,
    'class' => '',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
        default => 'w-10 h-10 text-sm',
    };

    $statusColors = match($status) {
        'online' => 'bg-green-500',
        'offline' => 'bg-gray-400',
        'away' => 'bg-yellow-500',
        'busy' => 'bg-red-500',
        default => null,
    };
@endphp

<div class="relative inline-flex {{ $class }}">
    @if($src)
        <img src="{{ $src }}" alt="{{ $name ?? 'Avatar' }}" class="{{ $sizeClasses }} rounded-full object-cover border-2 border-outline-variant" @if($name) title="{{ $name }}" @endif>
    @else
        <div class="{{ $sizeClasses }} rounded-full bg-secondary text-on-secondary flex items-center justify-center font-semibold border-2 border-outline-variant">
            {{ substr($name ?? 'U', 0, 1) }}
        </div>
    @endif

    @if($status && $statusColors)
        <span class="absolute bottom-0 right-0 w-3 h-3 {{ $statusColors }} rounded-full border-2 border-white"></span>
    @endif
</div>

{{-- Button Component --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'disabled' => false,
    'href' => null,
    'class' => '',
])

@php
    $baseClasses = 'px-4 py-2 rounded-lg text-sm font-semibold transition-all';
    $variantClasses = match($variant) {
        'primary' => 'bg-secondary text-on-secondary hover:bg-secondary/90 active:scale-95',
        'secondary' => 'bg-gray-100 text-on-surface hover:bg-gray-100/80',
        'danger' => 'bg-error text-on-error hover:bg-error/90',
        'text' => 'text-secondary hover:bg-secondary/10',
        default => 'bg-secondary text-on-secondary hover:bg-secondary/90',
    };
    $disabledClasses = $disabled ? 'opacity-60 cursor-not-allowed' : '';
    $allClasses = "$baseClasses $variantClasses $disabledClasses $class";
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $allClasses }}" {{ $disabled ? 'style="pointer-events:none;"' : '' }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $allClasses }}" {{ $disabled ? 'disabled' : '' }}>
        {{ $slot }}
    </button>
@endif

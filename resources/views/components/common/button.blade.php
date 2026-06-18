{{-- Button Component --}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'href' => null,
    'class' => '',
])

@php
    $variantClasses = match($variant) {
        'primary' => 'btn-nm-primary',
        'secondary' => 'btn-nm-secondary',
        'ghost' => 'btn-nm-ghost',
        'danger' => 'btn-nm-danger',
        'text' => 'btn-nm-text',
        default => 'btn-nm-primary',
    };
    $sizeClass = match($size) {
        'sm' => 'btn-nm-sm',
        'lg' => 'btn-nm-lg',
        default => '',
    };
    $disabledClasses = $disabled ? 'opacity-60 cursor-not-allowed pointer-events-none' : '';
    $allClasses = trim("$variantClasses $sizeClass $disabledClasses $class");
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $allClasses }}" {{ $disabled ? 'aria-disabled=true' : '' }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $allClasses }}" {{ $disabled ? 'disabled' : '' }}>
        {{ $slot }}
    </button>
@endif

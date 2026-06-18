{{-- Input Component --}}
@props([
    'name' => null,
    'type' => 'text',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'variant' => 'default',
    'icon' => null,
    'class' => '',
])

@php
    $value = $value ?? ($name ? old($name) : null);
    $fieldId = $attributes->get('id') ?? ($name ? $name : null);
    $isInset = $variant === 'inset';
    $labelClass = 'form-label' . ($error ? ' form-label-error' : '');
    $wrapClass = 'input-inset-wrap' . ($error ? ' is-error' : '');
@endphp

<div class="form-field {{ $class }}">
    @if($label)
        <label @if($fieldId) for="{{ $fieldId }}" @endif class="{{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    @if($isInset && $icon)
        <div class="{{ $wrapClass }}">
            <span class="material-symbols-outlined text-gray-400 text-[18px] shrink-0">{{ $icon }}</span>
            <input
                type="{{ $type }}"
                @if($name) name="{{ $name }}" @endif
                @if($fieldId) id="{{ $fieldId }}" @endif
                class="input-inset-inner"
                @if($value !== null && $value !== '') value="{{ $value }}" @endif
                @if($placeholder) placeholder="{{ $placeholder }}" @endif
                @if($required) required @endif
                {{ $attributes->except(['class', 'variant', 'icon', 'label', 'error']) }}
            >
        </div>
    @elseif($isInset)
        <input
            type="{{ $type }}"
            @if($name) name="{{ $name }}" @endif
            @if($fieldId) id="{{ $fieldId }}" @endif
            class="input-inset {{ $error ? 'is-error' : '' }}"
            @if($value !== null && $value !== '') value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            {{ $attributes->except(['class', 'variant', 'icon', 'label', 'error']) }}
        >
    @else
        <input
            type="{{ $type }}"
            @if($name) name="{{ $name }}" @endif
            @if($fieldId) id="{{ $fieldId }}" @endif
            class="input-nm {{ $error ? 'border-error !bg-red-50' : '' }}"
            @if($value !== null && $value !== '') value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            {{ $attributes->except(['class', 'variant', 'icon', 'label', 'error']) }}
        >
    @endif

    @if($error)
        <p class="form-error-msg">{{ $error }}</p>
    @endif
</div>

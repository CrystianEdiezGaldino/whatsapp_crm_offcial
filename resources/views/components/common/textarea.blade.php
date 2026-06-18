{{-- Textarea Component --}}
@props([
    'name' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'rows' => 4,
    'error' => null,
    'maxlength' => null,
    'required' => false,
    'variant' => 'default',
    'class' => '',
])

@php
    $value = $value ?? ($name ? old($name) : null);
    $fieldId = $attributes->get('id') ?? ($name ? $name : null);
    $isInset = $variant === 'inset';
    $labelClass = 'form-label' . ($error ? ' form-label-error' : '');
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

    <textarea
        @if($name) name="{{ $name }}" @endif
        @if($fieldId) id="{{ $fieldId }}" @endif
        rows="{{ $rows }}"
        @class([
            $isInset ? 'textarea-inset' : 'textarea-nm',
            'is-error' => $error && $isInset,
            'border-error' => $error && !$isInset,
        ])
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($required) required @endif
        {{ $attributes->except(['class', 'variant', 'label', 'error']) }}
    >{{ $value }}</textarea>

    @if($error)
        <p class="form-error-msg">{{ $error }}</p>
    @elseif($maxlength && $name)
        <p class="form-hint text-right">
            <span id="current-{{ $name }}">{{ strlen($value ?? '') }}</span> / {{ $maxlength }}
        </p>
    @endif
</div>

@if($maxlength && $name)
    <script>
        document.querySelector('textarea[name="{{ $name }}"]')?.addEventListener('input', function() {
            document.getElementById('current-{{ $name }}').textContent = this.value.length;
        });
    </script>
@endif

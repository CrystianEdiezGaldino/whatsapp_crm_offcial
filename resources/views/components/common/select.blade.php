{{-- Select Component --}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'error' => null,
    'required' => false,
    'variant' => 'default',
    'placeholder' => '-- Selecionar --',
    'class' => '',
])

@php
    $value = $value ?? old($name);
    $fieldId = $attributes->get('id') ?? $name;
    $isInset = $variant === 'inset';
    $labelClass = 'form-label' . ($error ? ' form-label-error' : '');
    $selectClass = $isInset
        ? 'input-inset ' . ($error ? 'is-error' : '')
        : 'select-nm ' . ($error ? 'border-error' : '');
@endphp

<div class="form-field {{ $class }}">
    @if($label)
        <label for="{{ $fieldId }}" class="{{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-error">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $fieldId }}"
        class="{{ $selectClass }}"
        @if($required) required @endif
        {{ $attributes->except(['class', 'variant', 'label', 'error', 'options', 'placeholder']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $key => $optionLabel)
            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if($error)
        <p class="form-error-msg">{{ $error }}</p>
    @endif
</div>

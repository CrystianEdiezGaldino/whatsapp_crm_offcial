{{-- Checkbox Component --}}
@props([
    'name',
    'type' => 'checkbox',
    'value' => '1',
    'label' => null,
    'checked' => false,
    'disabled' => false,
    'class' => '',
])

@php
    $checked = $checked || old($name) == $value;
@endphp

<label class="flex items-center gap-2 cursor-pointer">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        class="w-4 h-4 rounded border-gray-200 cursor-pointer accent-secondary {{ $class }}"
        @if($checked) checked @endif
        @if($disabled) disabled @endif
    >
    @if($label)
        <span class="text-sm text-on-surface">{{ $label }}</span>
    @endif
</label>

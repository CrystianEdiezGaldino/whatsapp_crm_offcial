@props([
    'value' => 0,
    'type' => 'primary',
    'label' => null,
    'showPercent' => true,
    'class' => '',
])

@php
    $value = min(100, max(0, $value));
    $bgColor = match($type) {
        'success' => 'bg-green-500',
        'warning' => 'bg-yellow-500',
        'error' => 'bg-red-500',
        'primary' => 'bg-secondary',
        default => 'bg-secondary',
    };
@endphp

<div class="{{ $class }}">
    @if($label)
        <div class="flex justify-between items-center mb-2">
            <label class="text-sm font-semibold text-on-surface">{{ $label }}</label>
            @if($showPercent)
                <span class="text-xs text-gray-600">{{ $value }}%</span>
            @endif
        </div>
    @endif
    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
        <div class="{{ $bgColor }} h-full transition-all duration-300 rounded-full" style="width: {{ $value }}%"></div>
    </div>
</div>

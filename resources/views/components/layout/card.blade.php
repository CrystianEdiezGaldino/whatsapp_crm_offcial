@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
])

<div class="bg-white border border-gray-200 rounded-xl p-5 {{ $class }}">
    @if($title)
        <div class="mb-4">
            <h3 class="text-lg font-bold text-on-surface">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div>{{ $slot }}</div>
</div>

@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
])

<div class="card-nm {{ $class }}">
    @if($title)
        <div class="mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
    <div>{{ $slot }}</div>
</div>

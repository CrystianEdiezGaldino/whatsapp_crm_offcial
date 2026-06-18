@props([
    'title' => null,
])

<section {{ $attributes->merge(['class' => 'page-section']) }}>
    @if($title)
        <p class="page-section-title">{{ $title }}</p>
    @endif
    {{ $slot }}
</section>

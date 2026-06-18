@props([
    'title',
    'subtitle' => null,
])

<header {{ $attributes->merge(['class' => 'page-header sticky top-0 z-40']) }}>
    <div class="page-header__main">
        <div class="page-header__title-group">
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($search))
            <div class="page-header__search">{{ $search }}</div>
        @endif
    </div>

    <div class="page-header__end">
        @if(isset($tabs))
            <div class="page-header__tabs">{{ $tabs }}</div>
        @endif
        {{ $slot }}
    </div>
</header>

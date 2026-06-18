@props([
    'title' => null,
    'subtitle' => null,
])

<div class="card-nm card-nm-flush data-table-wrap {{ $attributes->get('class') }}">
    @if($title)
        <div class="card-nm-head">
            <div>
                <h3 class="text-base font-extrabold text-gray-900">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 font-medium mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="overflow-x-auto design-scrollbar">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>

    @isset($footer)
        <div class="data-table__footer">{{ $footer }}</div>
    @endisset
</div>

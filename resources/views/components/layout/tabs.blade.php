@props([
    'tabs' => [],
    'class' => '',
])

<div class="{{ $class }}">
    <div class="flex border-b border-gray-200">
        @foreach($tabs as $tab)
            <button
                onclick="showTab('{{ $tab['id'] }}')"
                class="px-4 py-3 text-sm font-semibold {{ ($tab['active'] ?? false) ? 'border-b-2 border-secondary text-secondary' : 'text-gray-600 hover:text-on-surface' }} transition-colors"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    <div class="mt-4">{{ $slot }}</div>
</div>

<script>
function showTab(id) {
    document.querySelectorAll('[data-tab]').forEach(tab => tab.classList.add('hidden'));
    document.getElementById(id)?.classList.remove('hidden');
}
</script>

@extends('layouts.app')

@section('title', 'Macros')

@push('head')
<style>
    .macro-card { transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
    .macro-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,0.12); }
    .macro-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
    @media (min-width: 1280px) {
        .macro-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
    }
    .category-pill[data-active="true"] { background: var(--tw-ring-color, #006d2f); color: #fff; border-color: transparent; }
</style>
@endpush

@section('content')
<header class="flex flex-wrap justify-between items-center gap-4 h-auto min-h-16 py-3 px-6 w-full sticky top-0 z-40 bg-surface/90 backdrop-blur-md border-b border-gray-200">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-secondary/15 flex items-center justify-center">
            <span class="material-symbols-outlined text-secondary">bolt</span>
        </div>
        <div>
            <h2 class="text-xl font-bold text-primary leading-tight">Macros</h2>
            <p class="text-xs text-gray-600">Respostas rápidas para agilizar o atendimento</p>
        </div>
    </div>
    <button type="button" onclick="document.getElementById('newMacroModal').classList.remove('hidden')"
        class="bg-primary text-on-primary text-xs font-semibold px-4 py-2.5 flex items-center gap-1.5 rounded-xl shadow-sm hover:opacity-90 active:scale-95 transition-all">
        <span class="material-symbols-outlined text-base">add</span>
        Nova Macro
    </button>
</header>

<div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm flex items-center gap-2">
        <span class="material-symbols-outlined text-lg">check_circle</span>
        {{ session('success') }}
    </div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="material-symbols-outlined text-primary text-xl">library_books</span>
            <p class="text-[10px] font-semibold text-gray-600 uppercase mt-2 tracking-wide">Total</p>
            <p class="text-2xl font-bold text-on-surface">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="material-symbols-outlined text-blue-600 text-xl">attach_file</span>
            <p class="text-[10px] font-semibold text-gray-600 uppercase mt-2 tracking-wide">Com mídia</p>
            <p class="text-2xl font-bold text-on-surface">{{ $stats['with_files'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="material-symbols-outlined text-secondary text-xl">category</span>
            <p class="text-[10px] font-semibold text-gray-600 uppercase mt-2 tracking-wide">Categorias</p>
            <p class="text-2xl font-bold text-on-surface">{{ $stats['categories'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <span class="material-symbols-outlined text-amber-600 text-xl">keyboard</span>
            <p class="text-[10px] font-semibold text-gray-600 uppercase mt-2 tracking-wide">Com atalho</p>
            <p class="text-2xl font-bold text-on-surface">{{ $stats['with_shortcut'] }}</p>
        </div>
    </div>

    {{-- Busca + filtros --}}
    @php
        $categoryMeta = [
            'saudacao' => ['label' => 'Saudação', 'icon' => 'waving_hand', 'accent' => 'border-l-emerald-500'],
            'util' => ['label' => 'Útil', 'icon' => 'build', 'accent' => 'border-l-blue-500'],
            'encerramento' => ['label' => 'Encerramento', 'icon' => 'logout', 'accent' => 'border-l-violet-500'],
            'financeiro' => ['label' => 'Financeiro', 'icon' => 'payments', 'accent' => 'border-l-amber-500'],
            'logistica' => ['label' => 'Logística', 'icon' => 'local_shipping', 'accent' => 'border-l-orange-500'],
            'general' => ['label' => 'Geral', 'icon' => 'folder', 'accent' => 'border-l-slate-400'],
        ];
    @endphp

    @if($stats['total'] > 0)
    <div class="mb-6 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 text-lg">search</span>
            <input type="search" id="macroSearch" placeholder="Buscar por nome, atalho ou conteúdo..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-secondary/30 focus:border-secondary">
        </div>
        <div class="flex flex-wrap gap-2" id="categoryFilters">
            <button type="button" data-category="all" class="category-pill px-3 py-1.5 text-xs font-semibold rounded-full border border-secondary bg-secondary text-on-secondary transition-colors" data-active="true">Todas</button>
            @foreach($macros->keys() as $cat)
            @php $meta = $categoryMeta[$cat] ?? ['label' => ucfirst($cat), 'icon' => 'label']; @endphp
            <button type="button" data-category="{{ $cat }}" class="category-pill px-3 py-1.5 text-xs font-semibold rounded-full border border-gray-200 bg-white text-gray-600 hover:border-secondary transition-colors">
                {{ $meta['label'] }}
            </button>
            @endforeach
        </div>
    </div>
    @endif

    <div id="macrosContainer">
    @forelse($macros as $category => $items)
    @php
        $meta = $categoryMeta[$category] ?? ['label' => ucfirst($category), 'icon' => 'label', 'accent' => 'border-l-slate-400'];
    @endphp
    <section class="macro-category-section mb-8" data-category="{{ $category }}">
        <div class="flex items-center gap-3 mb-4 px-1">
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-secondary text-xl">{{ $meta['icon'] }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-on-surface">{{ $meta['label'] }}</h3>
                <p class="text-xs text-gray-600">{{ $items->count() }} {{ $items->count() === 1 ? 'macro' : 'macros' }}</p>
            </div>
            <span class="text-xs font-mono text-gray-600/60 hidden sm:inline">{{ $category }}</span>
        </div>

        <div class="macro-grid">
            @foreach($items as $macro)
            <article class="macro-card macro-item bg-white border border-gray-200 rounded-2xl overflow-hidden flex flex-col border-l-4 {{ $meta['accent'] }}"
                data-name="{{ strtolower($macro->name) }}"
                data-shortcut="{{ strtolower($macro->shortcut ?? '') }}"
                data-content="{{ strtolower(Str::limit($macro->content, 200)) }}"
                data-category="{{ $category }}">
                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-on-surface truncate" title="{{ $macro->name }}">{{ $macro->name }}</h4>
                            @if($macro->shortcut)
                            <code class="inline-block mt-1.5 text-[11px] bg-gray-100 px-2 py-0.5 rounded-md text-secondary font-mono">{{ $macro->shortcut }}</code>
                            @endif
                        </div>
                        <div class="flex gap-0.5 shrink-0">
                            <button type="button" onclick="editMacro({{ $macro->id }}, @json($macro->name), @json($macro->content), @json($macro->shortcut), @json($macro->category))"
                                class="p-1.5 hover:bg-gray-100-high text-gray-600 rounded-lg transition-colors" title="Editar">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <form method="POST" action="{{ route('macros.destroy', $macro) }}" class="inline" onsubmit="return confirm('Remover macro?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 hover:bg-red-50 text-error rounded-lg transition-colors" title="Excluir">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-4 flex-1">{{ $macro->content }}</p>

                    <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-gray-200/60">
                        @if($macro->files_count > 0)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-blue-50 text-blue-700 px-2 py-1 rounded-md">
                            <span class="material-symbols-outlined text-[14px]">attach_file</span>
                            {{ $macro->files_count }} {{ $macro->files_count === 1 ? 'arquivo' : 'arquivos' }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-1 rounded-md">
                            <span class="material-symbols-outlined text-[14px]">chat</span>
                            Texto
                        </span>
                        @endif
                        <button type="button" onclick="copyMacroContent(@json($macro->content))"
                            class="ml-auto text-[11px] font-semibold text-secondary hover:underline flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[14px]">content_copy</span>
                            Copiar
                        </button>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @empty
    <div class="col-span-full flex flex-col items-center justify-center py-20 px-6 bg-white rounded-2xl border border-dashed border-gray-200">
        <div class="w-16 h-16 rounded-2xl bg-secondary/10 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-4xl text-secondary">bolt</span>
        </div>
        <h3 class="text-lg font-bold text-on-surface">Nenhuma macro ainda</h3>
        <p class="text-sm text-gray-600 mt-1 text-center max-w-sm">Crie respostas prontas com atalhos para usar nos chats.</p>
        <button type="button" onclick="document.getElementById('newMacroModal').classList.remove('hidden')"
            class="mt-6 bg-secondary text-on-secondary px-5 py-2.5 rounded-xl text-sm font-semibold active:scale-95 transition-transform shadow-sm">
            Criar primeira macro
        </button>
    </div>
    @endforelse
    </div>

    <p id="noResults" class="hidden text-center py-12 text-gray-600 text-sm">
        <span class="material-symbols-outlined text-3xl block mb-2 opacity-50">search_off</span>
        Nenhuma macro encontrada para esta busca.
    </p>
</div>

<!-- New Macro Modal -->
<div id="newMacroModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-on-surface">Nova Macro</h3>
            <button type="button" onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="p-1 text-gray-600 hover:text-on-surface rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('macros.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Nome</label>
                    <input name="name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30 focus:border-secondary" placeholder="Ex: Saudação inicial">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Atalho</label>
                    <input name="shortcut" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-secondary/30" placeholder="/oi">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Categoria</label>
                    <select name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30">
                        <option value="saudacao">Saudação</option>
                        <option value="util">Útil</option>
                        <option value="encerramento">Encerramento</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="logistica">Logística</option>
                        <option value="general">Geral</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Conteúdo</label>
                    <textarea name="content" required rows="5" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30" placeholder="Mensagem da macro... Use {nome} para variáveis."></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase">Arquivos (Imagem, Vídeo, PDF)</label>
                    <label class="flex items-center justify-center gap-2 p-4 border-2 border-dashed border-secondary/40 rounded-xl cursor-pointer hover:bg-secondary/5 transition-colors">
                        <span class="material-symbols-outlined text-secondary">cloud_upload</span>
                        <span class="text-sm text-on-surface">Selecionar arquivo</span>
                        <input type="file" id="new_macro_file_input" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </label>
                    <div id="new_macro_files_list" class="mt-3 text-xs text-gray-600">Nenhum arquivo selecionado</div>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2.5 rounded-xl text-sm font-semibold active:scale-95">Criar Macro</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Macro Modal -->
<div id="editMacroModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-bold text-on-surface">Editar Macro</h3>
            <button type="button" onclick="document.getElementById('editMacroModal').classList.add('hidden')" class="p-1 text-gray-600 hover:text-on-surface rounded-lg">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMacroForm" method="POST" action="">
            @csrf @method('PUT')
            <input type="hidden" id="edit_macro_id">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Nome</label>
                    <input id="edit_macro_name" name="name" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Atalho</label>
                    <input id="edit_macro_shortcut" name="shortcut" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-secondary/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Categoria</label>
                    <select id="edit_macro_category" name="category" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30">
                        <option value="saudacao">Saudação</option>
                        <option value="util">Útil</option>
                        <option value="encerramento">Encerramento</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="logistica">Logística</option>
                        <option value="general">Geral</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase">Conteúdo</label>
                    <textarea id="edit_macro_content" name="content" required rows="5" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-secondary/30"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase">Arquivos</label>
                    <div id="macro_files_list" class="grid grid-cols-1 gap-2 mb-3 max-h-40 overflow-y-auto"></div>
                    <label class="flex items-center justify-center gap-2 p-4 border-2 border-dashed border-secondary/40 rounded-xl cursor-pointer hover:bg-secondary/5 transition-colors">
                        <span class="material-symbols-outlined text-secondary">cloud_upload</span>
                        <span class="text-sm text-on-surface">Adicionar arquivo</span>
                        <input type="file" id="macro_file_input" class="hidden" accept="audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,image/*">
                    </label>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('editMacroModal').classList.add('hidden')" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2.5 rounded-xl text-sm font-semibold active:scale-95">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentMacroId = null;
let macroFiles = {};
let activeCategory = 'all';

function copyMacroContent(text) {
    navigator.clipboard.writeText(text).then(() => {
        window.Feedback?.success('Conteúdo copiado!') || alert('Copiado!');
    }).catch(() => alert('Não foi possível copiar.'));
}

function filterMacros() {
    const q = (document.getElementById('macroSearch')?.value || '').trim().toLowerCase();
    const items = document.querySelectorAll('.macro-item');
    const sections = document.querySelectorAll('.macro-category-section');
    let visibleCount = 0;

    items.forEach(el => {
        const matchCat = activeCategory === 'all' || el.dataset.category === activeCategory;
        const matchQ = !q || el.dataset.name.includes(q) || el.dataset.shortcut.includes(q) || el.dataset.content.includes(q);
        const show = matchCat && matchQ;
        el.classList.toggle('hidden', !show);
        if (show) visibleCount++;
    });

    sections.forEach(sec => {
        const visibleInSection = sec.querySelectorAll('.macro-item:not(.hidden)').length;
        sec.classList.toggle('hidden', visibleInSection === 0);
    });

    const noResults = document.getElementById('noResults');
    if (noResults) noResults.classList.toggle('hidden', visibleCount > 0 || items.length === 0);
}

document.getElementById('macroSearch')?.addEventListener('input', filterMacros);

document.querySelectorAll('.category-pill').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.category-pill').forEach(b => {
            b.dataset.active = 'false';
            b.classList.remove('bg-secondary', 'text-on-secondary', 'border-secondary');
        });
        btn.dataset.active = 'true';
        btn.classList.add('bg-secondary', 'text-on-secondary', 'border-secondary');
        activeCategory = btn.dataset.category;
        filterMacros();
    });
});

function editMacro(id, name, content, shortcut, category) {
    currentMacroId = id;
    document.getElementById('edit_macro_id').value = id;
    document.getElementById('edit_macro_name').value = name;
    document.getElementById('edit_macro_content').value = content;
    document.getElementById('edit_macro_shortcut').value = shortcut || '';
    document.getElementById('edit_macro_category').value = category;
    document.getElementById('editMacroForm').action = '{{ url("/macros") }}/' + id;
    document.getElementById('editMacroModal').classList.remove('hidden');
    loadMacroFiles(id);
}

function loadMacroFiles(macroId) {
    fetch(`{{ url('/macros') }}/${macroId}/preview`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then(r => r.json())
        .then(data => {
            macroFiles = {};
            if (data.success && data.files?.length) {
                data.files.forEach(file => { macroFiles[file.id] = file; });
            }
            renderMacroFiles();
        })
        .catch(e => console.error('Erro ao carregar arquivos:', e));
}

function renderMacroFiles() {
    const filesList = document.getElementById('macro_files_list');
    const files = Object.values(macroFiles);

    if (files.length === 0) {
        filesList.innerHTML = '<p class="text-xs text-gray-600 text-center py-3 col-span-full">Nenhum arquivo</p>';
        return;
    }

    filesList.innerHTML = files.map(f => `
        <div class="flex items-center gap-2 p-2.5 bg-gray-100-low rounded-xl group">
            <span class="material-symbols-outlined text-secondary text-lg">${f.icon}</span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-on-surface truncate">${f.name}</p>
                <p class="text-[10px] text-gray-600">${f.size}</p>
            </div>
            <button type="button" onclick="deleteFile(${f.id})" class="p-1 text-gray-600 hover:text-error rounded-lg">
                <span class="material-symbols-outlined text-base">delete</span>
            </button>
        </div>
    `).join('');
}

function deleteFile(fileId) {
    if (!confirm('Remover arquivo?')) return;

    fetch(`{{ url('/macros') }}/${currentMacroId}/files/${fileId}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            delete macroFiles[fileId];
            renderMacroFiles();
            window.Feedback?.success('Arquivo removido');
        } else {
            window.Feedback?.error(data.message) || alert(data.message);
        }
    })
    .catch(e => alert('Erro: ' + e.message));
}

const macroFileInput = document.getElementById('macro_file_input');
if (macroFileInput) {
    macroFileInput.addEventListener('change', function() {
        if (!this.files[0] || !currentMacroId) return;

        const formData = new FormData();
        formData.append('file', this.files[0]);

        fetch(`{{ url('/macros') }}/${currentMacroId}/files`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                macroFiles[data.file.id] = data.file;
                renderMacroFiles();
                this.value = '';
                window.Feedback?.success('Arquivo adicionado');
            } else {
                window.Feedback?.error(data.message) || alert(data.message);
            }
        })
        .catch(e => alert('Erro: ' + e.message));
    });
}

let newMacroSelectedFile = null;
const newMacroFileInput = document.getElementById('new_macro_file_input');
if (newMacroFileInput) {
    newMacroFileInput.addEventListener('change', function() {
        if (this.files[0]) {
            newMacroSelectedFile = this.files[0];
            const filesList = document.getElementById('new_macro_files_list');
            filesList.innerHTML = `<p class="text-xs">📎 ${this.files[0].name} (${(this.files[0].size / 1024).toFixed(2)} KB)</p>`;
        }
    });
}

// Interceptar submit do formulário de nova macro para fazer upload após criar
const newMacroForm = document.querySelector('#newMacroModal form');
if (newMacroForm) {
    newMacroForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else if (response.status === 422) {
                const errors = data.errors || {};
                const errorMsg = Object.values(errors).flat().join(', ');
                window.Feedback?.error(errorMsg) || alert('Erro: ' + errorMsg);
            } else {
                window.Feedback?.error(data.message) || alert('Erro ao criar macro');
            }
        } catch (e) {
            console.error('Erro:', e);
            alert('Erro ao criar macro: ' + e.message);
        }
    });
}
</script>
@endpush

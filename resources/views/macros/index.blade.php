@extends('layouts.app')

@section('title', 'Macros')

@section('content')
<header class="flex justify-between items-center h-16 px-6 w-full sticky top-0 z-40 bg-surface/80 backdrop-blur-md border-b border-outline-variant">
    <div class="flex items-center gap-4">
        <h2 class="text-xl font-bold text-primary">Macros</h2>
        <p class="text-sm text-on-surface-variant hidden md:block">Respostas rapidas para agilizar o atendimento</p>
    </div>
    <button onclick="document.getElementById('newMacroModal').classList.remove('hidden')" class="bg-primary text-on-primary text-xs font-semibold px-4 py-2 flex items-center gap-1 rounded-lg active:scale-95 transition-transform">
        <span class="material-symbols-outlined text-base">add</span>
        Nova Macro
    </button>
</header>

<div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    @forelse($macros as $category => $items)
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-3 px-1">{{ ucfirst($category) }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($items as $macro)
            <div class="bg-white border border-outline-variant rounded-xl p-5 hover:border-secondary transition-colors group">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-on-surface">{{ $macro->name }}</h4>
                            @if($macro->hasFiles())
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded flex items-center gap-0.5" title="Contém arquivos">
                                <span class="material-symbols-outlined text-[12px]">attach_file</span>
                                {{ $macro->files()->count() }}
                            </span>
                            @else
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded">📝 Texto</span>
                            @endif
                        </div>
                        @if($macro->shortcut)
                        <code class="text-xs bg-surface-container px-2 py-0.5 rounded text-secondary font-mono mt-1 block w-fit">{{ $macro->shortcut }}</code>
                        @endif
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="editMacro({{ $macro->id }}, @json($macro->name), @json($macro->content), @json($macro->shortcut), @json($macro->category))" class="p-1 hover:bg-surface-container-high text-on-surface-variant rounded transition-colors">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <form method="POST" action="{{ route('macros.destroy', $macro) }}" class="inline" onsubmit="return confirm('Remover macro?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1 hover:bg-red-50 text-error rounded transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-sm text-on-surface-variant leading-relaxed">{{ Str::limit($macro->content, 120) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="text-center py-16">
        <span class="material-symbols-outlined text-5xl text-outline-variant">bolt</span>
        <p class="text-on-surface-variant mt-3">Nenhuma macro criada ainda.</p>
        <button onclick="document.getElementById('newMacroModal').classList.remove('hidden')" class="mt-4 bg-secondary text-on-secondary px-4 py-2 rounded-lg text-sm font-semibold active:scale-95 transition-transform">
            Criar primeira macro
        </button>
    </div>
    @endforelse
</div>

<!-- New Macro Modal -->
<div id="newMacroModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-on-surface">Nova Macro</h3>
            <button onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('macros.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Nome</label>
                    <input name="name" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Ex: Saudacao inicial">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Atalho</label>
                    <input name="shortcut" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary font-mono" placeholder="/oi">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Categoria</label>
                    <select name="category" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                        <option value="saudacao">Saudacao</option>
                        <option value="util">Util</option>
                        <option value="encerramento">Encerramento</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="logistica">Logistica</option>
                        <option value="general">Geral</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Conteudo da Mensagem</label>
                    <textarea name="content" required rows="4" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary" placeholder="Digite a mensagem da macro... Use {variavel} para placeholders."></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="flex-1 py-2 border border-outline-variant rounded-lg text-sm text-on-surface-variant hover:bg-surface-container">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2 rounded-lg text-sm font-semibold active:scale-95 transition-transform">Criar Macro</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Macro Modal -->
<div id="editMacroModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-on-surface">Editar Macro</h3>
            <button onclick="document.getElementById('editMacroModal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMacroForm" method="POST" action="">
            @csrf @method('PUT')
            <div class="space-y-4">
                <input type="hidden" id="edit_macro_id">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Nome</label>
                    <input id="edit_macro_name" name="name" required class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Atalho</label>
                    <input id="edit_macro_shortcut" name="shortcut" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Categoria</label>
                    <select id="edit_macro_category" name="category" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary">
                        <option value="saudacao">Saudacao</option>
                        <option value="util">Util</option>
                        <option value="encerramento">Encerramento</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="logistica">Logistica</option>
                        <option value="general">Geral</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-1 uppercase">Conteudo</label>
                    <textarea id="edit_macro_content" name="content" required rows="4" class="w-full border border-outline-variant rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-secondary"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">Arquivos (Audio, Video, PDF)</label>
                    <div id="macro_files_list" class="space-y-2 mb-3 max-h-48 overflow-y-auto"></div>
                    <label class="flex items-center gap-2 p-3 border-2 border-dashed border-secondary-container rounded-lg cursor-pointer hover:bg-secondary-container/5 transition-colors">
                        <span class="material-symbols-outlined text-secondary">cloud_upload</span>
                        <span class="text-sm text-on-surface">Adicionar arquivo</span>
                        <input type="file" id="macro_file_input" class="hidden" accept="audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,image/*">
                    </label>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('editMacroModal').classList.add('hidden')" class="flex-1 py-2 border border-outline-variant rounded-lg text-sm text-on-surface-variant hover:bg-surface-container">Cancelar</button>
                <button type="submit" class="flex-1 bg-secondary text-on-secondary py-2 rounded-lg text-sm font-semibold active:scale-95 transition-transform">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentMacroId = null;
let macroFiles = {};

function editMacro(id, name, content, shortcut, category) {
    currentMacroId = id;
    document.getElementById('edit_macro_id').value = id;
    document.getElementById('edit_macro_name').value = name;
    document.getElementById('edit_macro_content').value = content;
    document.getElementById('edit_macro_shortcut').value = shortcut;
    document.getElementById('edit_macro_category').value = category;
    document.getElementById('editMacroForm').action = '{{ url("/macros") }}/' + id;
    document.getElementById('editMacroModal').classList.remove('hidden');

    // Load macro files
    loadMacroFiles(id);
}

function loadMacroFiles(macroId) {
    fetch(`{{ url('/macros') }}/${macroId}/preview`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                macroFiles = {};
                data.files.forEach(file => {
                    macroFiles[file.id] = file;
                });
                renderMacroFiles();
            } else {
                macroFiles = {};
                renderMacroFiles();
            }
        })
        .catch(e => console.error('Erro ao carregar arquivos:', e));
}

function renderMacroFiles() {
    const filesList = document.getElementById('macro_files_list');
    const files = Object.values(macroFiles);

    if (files.length === 0) {
        filesList.innerHTML = '<p class="text-xs text-on-surface-variant text-center py-2">Nenhum arquivo</p>';
        return;
    }

    filesList.innerHTML = files.map(f => `
        <div class="flex items-center gap-2 p-2 bg-surface-container-low rounded-lg group">
            <span class="material-symbols-outlined text-secondary text-lg">${f.icon}</span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-on-surface truncate">${f.name}</p>
                <p class="text-[10px] text-on-surface-variant">${f.size}</p>
            </div>
            <button type="button" onclick="deleteFile(${f.id})" class="p-1 text-on-surface-variant hover:text-error opacity-0 group-hover:opacity-100 transition-opacity">
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
        } else {
            alert(data.message);
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
            } else {
                alert(data.message);
            }
        })
        .catch(e => alert('Erro: ' + e.message));
    });
}
</script>
@endpush

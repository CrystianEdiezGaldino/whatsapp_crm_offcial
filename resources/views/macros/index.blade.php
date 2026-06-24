@extends('layouts.app', ['fullHeight' => true])

@section('title', 'Macros')

@section('content')
<div class="master-detail">
    <!-- Left Panel: Macro List -->
    <div class="master-panel" style="width: 380px;">
        <div class="master-panel-header">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-extrabold text-gray-900">Macros</h2>
                <button type="button" onclick="document.getElementById('newMacroModal').classList.remove('hidden')" class="w-[38px] h-[38px] rounded-[11px] bg-secondary text-white flex items-center justify-center hover:opacity-90 transition-opacity" title="Nova Macro">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                </button>
            </div>
            <div class="relative">
                <span class="material-symbols-outlined text-gray-400 text-[16px] absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input type="search" id="macroSearch" placeholder="Buscar macro..." class="input-primary !pl-9 !h-[38px] !text-[13px]">
            </div>
        </div>

        <div class="master-panel-list design-scrollbar" id="macroListPanel">
            @php $allMacros = $macros->flatten(); @endphp
            @forelse($allMacros as $macro)
            <div class="master-panel-item macro-list-entry" data-macro-id="{{ $macro->id }}" data-name="{{ strtolower($macro->name) }}" data-shortcut="{{ strtolower($macro->shortcut ?? '') }}" data-content="{{ strtolower(Str::limit($macro->content, 200)) }}" onclick="selectMacro(this)">
                <div class="flex items-start gap-3">
                    @if($macro->shortcut)
                    <span class="shortcut-badge mt-0.5">/{{ $macro->shortcut }}</span>
                    @else
                    <span class="w-6 h-6 rounded-[6px] bg-gray-100 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-gray-400 text-[14px]">bolt</span>
                    </span>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[13.5px] font-bold text-gray-900 truncate">{{ $macro->name }}</h3>
                        <p class="text-[12px] text-gray-400 font-medium line-clamp-1 mt-0.5">{{ Str::limit($macro->content, 60) }}</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">bolt</span>
                <p class="text-sm text-gray-400 font-semibold">Nenhuma macro criada</p>
                <button type="button" onclick="document.getElementById('newMacroModal').classList.remove('hidden')" class="mt-3 text-sm text-secondary font-semibold hover:underline">Criar primeira macro</button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Panel: Macro Detail/Edit -->
    <div class="detail-panel design-scrollbar" id="macroDetailPanel">
        <div class="flex flex-col items-center justify-center h-full text-center" id="macroEmptyState">
            <span class="material-symbols-outlined text-6xl text-gray-200 mb-4">bolt</span>
            <h3 class="text-lg font-bold text-gray-400 mb-1">Selecione uma macro</h3>
            <p class="text-sm text-gray-400">Escolha uma macro na lista para editar</p>
        </div>

        <div class="max-w-xl mx-auto hidden" id="macroEditForm">
            <form id="editMacroFormInline" method="POST" action="" class="space-y-5">
                @csrf @method('PUT')
                <input type="hidden" id="inline_macro_id" name="macro_id">

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Título</label>
                    <input type="text" id="inline_macro_name" name="name" class="input-primary" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Atalho</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-mono">/</span>
                        <input type="text" id="inline_macro_shortcut" name="shortcut" class="input-primary !pl-7 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Categoria</label>
                    <select id="inline_macro_category" name="category" class="select-primary">
                        <option value="saudacao">Saudação</option>
                        <option value="util">Útil</option>
                        <option value="encerramento">Encerramento</option>
                        <option value="financeiro">Financeiro</option>
                        <option value="logistica">Logística</option>
                        <option value="general">Geral</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-gray-500">Conteúdo</label>
                        <button type="button" onclick="openMacroImproveModal()" class="flex items-center gap-1 px-2.5 py-1 rounded-[8px] bg-gradient-to-r from-purple-500/10 to-secondary/10 hover:from-purple-500/20 hover:to-secondary/20 border border-purple-300/30 text-[11px] font-bold text-purple-600 transition-all" title="Melhorar texto com IA">
                            <span class="material-symbols-outlined text-[14px]">auto_awesome</span>
                            Melhorar com IA
                        </button>
                    </div>
                    <textarea id="inline_macro_content" name="content" rows="8" class="textarea-primary" required></textarea>
                    <p class="text-[11px] text-gray-400 mt-1.5" id="charCount">0 caracteres</p>
                </div>

                <!-- Info box -->
                <div class="bg-[#EEF0FE] border border-secondary/20 rounded-[11px] p-3.5 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-secondary text-[18px] mt-0.5">info</span>
                    <p class="text-[12.5px] text-secondary font-medium leading-relaxed">
                        Use <span class="shortcut-badge text-[10px]">/atalho</span> no campo de mensagem para inserir esta macro rapidamente. Variáveis: <code class="text-[11px] font-mono">{nome}</code>, <code class="text-[11px] font-mono">{telefone}</code>.
                    </p>
                </div>

                <!-- Files -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">Arquivos</label>
                    <div id="macro_files_list" class="space-y-2 mb-3"></div>
                    <label class="flex items-center justify-center gap-2 p-4 border-2 border-dashed border-[#E2E5EE] rounded-[11px] cursor-pointer hover:border-secondary/40 hover:bg-[#EEF0FE]/30 transition-colors">
                        <span class="material-symbols-outlined text-gray-400 text-[18px]">cloud_upload</span>
                        <span class="text-sm text-gray-500 font-medium">Adicionar arquivo</span>
                        <input type="file" id="macro_file_input" class="hidden" accept="audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,image/*">
                    </label>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-[#F2F4F8]">
                    <button type="button" id="deleteMacroBtn" onclick="document.getElementById('deleteMacroForm').submit()" class="bg-white border border-error/30 text-error px-4 py-2 rounded-[11px] text-xs font-bold hover:bg-error/5 flex items-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">delete</span>
                        Excluir
                    </button>
                    <button type="submit" class="bg-secondary text-white px-6 py-2 rounded-[11px] text-xs font-bold hover:opacity-90 flex items-center gap-1.5 transition-all">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Salvar
                    </button>
                </div>
            </form>
            <form id="deleteMacroForm" method="POST" action="" class="hidden" onsubmit="return confirm('Excluir esta macro?')">
                @csrf @method('DELETE')
            </form>
        </div>
    </div>
</div>

<!-- New Macro Modal -->
<div id="newMacroModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="modal-card w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-extrabold text-gray-900">Nova Macro</h3>
            <button type="button" onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="modal-close-btn">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
        <form method="POST" action="{{ route('macros.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nome</label>
                    <input name="name" required class="input-primary" placeholder="Ex: Saudação inicial">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Atalho</label>
                        <input name="shortcut" class="input-primary font-mono" placeholder="/oi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">Categoria</label>
                        <select name="category" class="select-primary">
                            <option value="saudacao">Saudação</option>
                            <option value="util">Útil</option>
                            <option value="encerramento">Encerramento</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="logistica">Logística</option>
                            <option value="general">Geral</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Conteúdo</label>
                    <textarea name="content" required rows="5" class="textarea-primary" placeholder="Mensagem da macro... Use {nome} para variáveis."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2">Arquivos</label>
                    <label class="flex items-center justify-center gap-2 p-4 border-2 border-dashed border-[#E2E5EE] rounded-[11px] cursor-pointer hover:border-secondary/40 transition-colors">
                        <span class="material-symbols-outlined text-gray-400">cloud_upload</span>
                        <span class="text-sm text-gray-500">Selecionar arquivo</span>
                        <input type="file" id="new_macro_file_input" class="hidden" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx">
                    </label>
                    <div id="new_macro_files_list" class="mt-2 text-xs text-gray-400">Nenhum arquivo selecionado</div>
                </div>
            </div>
            <div class="flex gap-2 mt-6">
                <button type="button" onclick="document.getElementById('newMacroModal').classList.add('hidden')" class="btn-secondary flex-1 text-sm">Cancelar</button>
                <button type="submit" class="bg-secondary text-white flex-1 py-2.5 rounded-[12px] text-sm font-bold hover:opacity-90 transition-opacity">Criar Macro</button>
            </div>
        </form>
    </div>
</div>

<!-- Improve Text Modal -->
<div id="macroImproveModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="modal-card w-full max-w-lg">
        <div class="flex justify-between items-center mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[10px] bg-gradient-to-br from-purple-500/15 to-secondary/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600 text-[18px]">auto_awesome</span>
                </div>
                <h3 class="text-lg font-extrabold text-gray-900">Melhorar com IA</h3>
            </div>
            <button type="button" onclick="closeMacroImproveModal()" class="modal-close-btn">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <div class="space-y-4">
            <!-- Type selector -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Tipo de melhoria</label>
                <select id="macroImproveType" class="select-primary" onchange="refreshMacroImprovement()">
                    <option value="grammar">Corrigir gramática e ortografia</option>
                    <option value="professional">Tom profissional</option>
                    <option value="both">Gramática + Tom profissional</option>
                </select>
            </div>

            <!-- Original text -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Texto original</label>
                <div id="macroImproveOriginal" class="p-3 bg-[#F7F8FB] rounded-[11px] text-sm text-gray-700 max-h-[100px] overflow-y-auto"></div>
            </div>

            <!-- Result -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1.5">Texto melhorado</label>
                <div id="macroImproveLoading" class="p-6 bg-[#F7F8FB] rounded-[11px] flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-secondary" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-sm text-gray-500 font-medium">Processando...</span>
                </div>
                <div id="macroImproveResult" class="hidden p-3 bg-green-50 border border-green-200/60 rounded-[11px] text-sm text-gray-800 max-h-[150px] overflow-y-auto"></div>
                <div id="macroImproveError" class="hidden p-3 bg-red-50 border border-red-200/60 rounded-[11px] text-sm text-error"></div>
            </div>
        </div>

        <div class="flex gap-2 mt-6">
            <button type="button" onclick="closeMacroImproveModal()" class="btn-secondary flex-1 text-sm">Cancelar</button>
            <button type="button" id="macroImproveUseBtn" onclick="applyMacroImprovement()" disabled class="bg-secondary text-white flex-1 py-2.5 rounded-[12px] text-sm font-bold hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">check</span>
                Usar texto melhorado
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentMacroId = null;
let macroFiles = {};

function selectMacro(el) {
    document.querySelectorAll('.macro-list-entry').forEach(e => e.classList.remove('active'));
    el.classList.add('active');

    const macroId = el.dataset.macroId;
    currentMacroId = macroId;

    fetch(`{{ url('/macros') }}/${macroId}/preview`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const m = data.macro || {};
            document.getElementById('inline_macro_id').value = m.id;
            document.getElementById('inline_macro_name').value = m.name || '';
            document.getElementById('inline_macro_shortcut').value = (m.shortcut || '').replace(/^\//, '');
            document.getElementById('inline_macro_category').value = m.category || 'general';
            document.getElementById('inline_macro_content').value = m.content || '';
            document.getElementById('editMacroFormInline').action = `{{ url('/macros') }}/${m.id}`;
            document.getElementById('deleteMacroForm').action = `{{ url('/macros') }}/${m.id}`;
            updateCharCount();

            macroFiles = {};
            if (data.files?.length) {
                data.files.forEach(file => { macroFiles[file.id] = file; });
            }
            renderMacroFiles();

            document.getElementById('macroEmptyState').classList.add('hidden');
            document.getElementById('macroEditForm').classList.remove('hidden');
        }
    })
    .catch(e => console.error('Erro:', e));
}

function updateCharCount() {
    const content = document.getElementById('inline_macro_content')?.value || '';
    document.getElementById('charCount').textContent = content.length + ' caracteres';
}
document.getElementById('inline_macro_content')?.addEventListener('input', updateCharCount);

function renderMacroFiles() {
    const filesList = document.getElementById('macro_files_list');
    const files = Object.values(macroFiles);

    if (files.length === 0) {
        filesList.innerHTML = '<p class="text-xs text-gray-400 py-2">Nenhum arquivo</p>';
        return;
    }

    filesList.innerHTML = files.map(f => `
        <div class="flex items-center gap-2 p-2.5 bg-[#F7F8FB] rounded-[10px]">
            <span class="material-symbols-outlined text-secondary text-lg">${f.icon || 'description'}</span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-900 truncate">${f.name}</p>
                <p class="text-[10px] text-gray-400">${f.size}</p>
            </div>
            <button type="button" onclick="deleteFile(${f.id})" class="p-1 text-gray-400 hover:text-error rounded-lg transition-colors">
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

// Search filter
document.getElementById('macroSearch')?.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.macro-list-entry').forEach(el => {
        const match = !q || el.dataset.name.includes(q) || el.dataset.shortcut.includes(q) || el.dataset.content.includes(q);
        el.classList.toggle('hidden', !match);
    });
});

// New macro form
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

let newMacroSelectedFile = null;
const newMacroFileInput = document.getElementById('new_macro_file_input');
if (newMacroFileInput) {
    newMacroFileInput.addEventListener('change', function() {
        if (this.files[0]) {
            newMacroSelectedFile = this.files[0];
            const filesList = document.getElementById('new_macro_files_list');
            filesList.innerHTML = `<p class="text-xs text-gray-700 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">attach_file</span> ${this.files[0].name} (${(this.files[0].size / 1024).toFixed(1)} KB)</p>`;
        }
    });
}

// ── Improve Text with AI ──
let macroImprovedText = '';

function openMacroImproveModal() {
    const textarea = document.getElementById('inline_macro_content');
    const text = textarea?.value?.trim();
    if (!text) {
        alert('Digite algum conteúdo antes de melhorar com IA.');
        return;
    }

    macroImprovedText = '';
    document.getElementById('macroImproveModal').classList.remove('hidden');
    document.getElementById('macroImproveOriginal').textContent = text;
    document.getElementById('macroImproveType').value = 'grammar';
    document.getElementById('macroImproveLoading').classList.remove('hidden');
    document.getElementById('macroImproveResult').classList.add('hidden');
    document.getElementById('macroImproveError').classList.add('hidden');
    document.getElementById('macroImproveUseBtn').disabled = true;

    refreshMacroImprovement();
}

function closeMacroImproveModal() {
    document.getElementById('macroImproveModal').classList.add('hidden');
    macroImprovedText = '';
}

function refreshMacroImprovement() {
    const type = document.getElementById('macroImproveType').value;
    const text = document.getElementById('macroImproveOriginal').textContent;
    if (!text) return;

    document.getElementById('macroImproveLoading').classList.remove('hidden');
    document.getElementById('macroImproveResult').classList.add('hidden');
    document.getElementById('macroImproveError').classList.add('hidden');
    document.getElementById('macroImproveUseBtn').disabled = true;

    fetch('{{ route("macros.improve-text") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ content: text, type: type }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Erro ao processar texto');
        return r.json();
    })
    .then(data => {
        if (data.success) {
            macroImprovedText = data.improved;
            document.getElementById('macroImproveLoading').classList.add('hidden');
            document.getElementById('macroImproveResult').textContent = data.improved;
            document.getElementById('macroImproveResult').classList.remove('hidden');
            document.getElementById('macroImproveUseBtn').disabled = false;
        } else {
            throw new Error(data.message || 'Erro desconhecido');
        }
    })
    .catch(error => {
        document.getElementById('macroImproveLoading').classList.add('hidden');
        document.getElementById('macroImproveError').textContent = error.message;
        document.getElementById('macroImproveError').classList.remove('hidden');
    });
}

function applyMacroImprovement() {
    if (!macroImprovedText) return;
    const textarea = document.getElementById('inline_macro_content');
    textarea.value = macroImprovedText;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    updateCharCount();
    closeMacroImproveModal();
}
</script>
@endpush

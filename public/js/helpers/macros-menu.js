/**
 * Menu de macros ao digitar / no chat.
 */
(function (global) {
    function initMacrosMenu(options) {
        const messageInput = document.getElementById('messageInput');
        const macrosMenu = document.getElementById('macrosMenu');
        const macrosMenuItems = document.getElementById('macrosMenuItems');
        const url = options?.url;

        if (!messageInput || !macrosMenu || !macrosMenuItems) {
            return;
        }

        let allMacros = Array.isArray(options?.initialMacros) ? options.initialMacros : [];
        let selectedMacroIndex = -1;

        function applyVariables(content) {
            if (!content || !options?.variables) return content || '';
            let out = content;
            for (const [key, val] of Object.entries(options.variables)) {
                const v = val == null ? '' : String(val);
                out = out.split('{' + key + '}').join(v);
                out = out.split('{{' + key + '}}').join(v);
            }
            return out;
        }

        function normalizeShortcut(s) {
            return (s || '').replace(/^\//, '').toLowerCase();
        }

        function getMacrosQuery() {
            const text = messageInput.value;
            const slashIndex = text.lastIndexOf('/');
            if (slashIndex === -1) return null;

            const afterSlash = text.substring(slashIndex + 1);
            const beforeSlash = text.substring(0, slashIndex);

            // Comando só no início ou após espaço/quebra de linha
            if (beforeSlash && !/[\s\n]$/.test(beforeSlash)) return null;
            // Não abrir no meio de URL (ex: https://)
            if (afterSlash.includes('://') || afterSlash.includes('.')) return null;

            return { query: afterSlash.toLowerCase(), slashPos: slashIndex };
        }

        function filterMacros(query) {
            if (!allMacros.length) return [];
            if (query === '') return allMacros;

            return allMacros.filter((macro) => {
                const name = (macro.name || '').toLowerCase();
                const content = (macro.content || '').toLowerCase();
                const shortcut = normalizeShortcut(macro.shortcut);
                return name.includes(query) || shortcut.includes(query) || content.includes(query);
            });
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text == null ? '' : String(text);
            return d.innerHTML;
        }

        function renderMacrosMenu(query) {
            const filtered = filterMacros(query);

            if (filtered.length === 0) {
                macrosMenuItems.innerHTML =
                    '<div class="px-3 py-2 text-xs text-gray-600 text-center">Nenhuma macro encontrada</div>';
                selectedMacroIndex = -1;
                return;
            }

            selectedMacroIndex = -1;
            macrosMenuItems.innerHTML = filtered
                .map((macro, index) => {
                    const preview = (macro.content || '').substring(0, 60);
                    const sc = normalizeShortcut(macro.shortcut);
                    return `<button type="button"
                        class="macro-menu-item w-full text-left px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors text-sm"
                        data-index="${index}">
                        <div class="font-semibold text-on-surface text-sm">${escapeHtml(macro.name)}</div>
                        <div class="text-xs text-gray-600 line-clamp-1">${escapeHtml(preview)}${preview.length >= 60 ? '...' : ''}</div>
                        ${sc ? `<div class="text-[10px] text-secondary font-mono mt-0.5">/${escapeHtml(sc)}</div>` : ''}
                    </button>`;
                })
                .join('');

            macrosMenuItems.querySelectorAll('.macro-menu-item').forEach((item) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    selectMacro(parseInt(item.dataset.index, 10));
                });
            });
        }

        function showMacrosMenu() {
            const query = getMacrosQuery();
            if (!query) {
                macrosMenu.classList.add('hidden');
                return;
            }
            renderMacrosMenu(query.query);
            macrosMenu.classList.remove('hidden');
        }

        function selectMacro(index) {
            const query = getMacrosQuery();
            if (!query) return;

            const filtered = filterMacros(query.query);
            if (index < 0 || index >= filtered.length) return;

            const macro = filtered[index];
            messageInput.value =
                messageInput.value.substring(0, query.slashPos) + applyVariables(macro.content || '');
            messageInput.focus();
            macrosMenu.classList.add('hidden');
        }

        function updateMenuSelection(direction) {
            const query = getMacrosQuery();
            if (!query) return;

            const filtered = filterMacros(query.query);
            if (!filtered.length) return;

            if (direction === 'down') {
                selectedMacroIndex = (selectedMacroIndex + 1) % filtered.length;
            } else if (direction === 'up') {
                selectedMacroIndex =
                    selectedMacroIndex === -1 ? filtered.length - 1 : selectedMacroIndex - 1;
            }

            macrosMenuItems.querySelectorAll('.macro-menu-item').forEach((item, idx) => {
                item.classList.toggle('bg-gray-100', idx === selectedMacroIndex);
            });
        }

        async function refreshMacros() {
            if (!url) return;
            try {
                const res = await fetch(url, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    const data = await res.json();
                    if (Array.isArray(data) && data.length) {
                        allMacros = data;
                    }
                }
            } catch (_) {}
        }

        refreshMacros().finally(() => {
            messageInput.addEventListener('input', showMacrosMenu);

            messageInput.addEventListener('keydown', (e) => {
                if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
                    setTimeout(showMacrosMenu, 0);
                }

                const query = getMacrosQuery();
                if (!query || macrosMenu.classList.contains('hidden')) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    updateMenuSelection('down');
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    updateMenuSelection('up');
                } else if (e.key === 'Enter' && selectedMacroIndex >= 0) {
                    e.preventDefault();
                    selectMacro(selectedMacroIndex);
                } else if (e.key === 'Escape') {
                    macrosMenu.classList.add('hidden');
                }
            });

            document.addEventListener('click', (e) => {
                if (!messageInput.contains(e.target) && !macrosMenu.contains(e.target)) {
                    macrosMenu.classList.add('hidden');
                }
            });
        });
    }

    global.initMacrosMenu = initMacrosMenu;
})(window);

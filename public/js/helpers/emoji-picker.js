/**
 * Seletor de emojis (UTF-8 via escapes Unicode ??? evita corrupcao no deploy).
 */
(function (global) {
    const EMOJIS = {
        Reacoes: [
            '\u{1F44D}', '\u{1F44E}', '\u{2764}\u{FE0F}', '\u{1F9E1}', '\u{1F49B}', '\u{1F49A}',
            '\u{1F499}', '\u{1F49C}', '\u{1F90D}', '\u{1F90E}', '\u{1F494}', '\u{1F495}',
            '\u{1F44F}', '\u{1F64C}', '\u{1F389}', '\u{1F38A}', '\u{1F60D}', '\u{1F970}',
            '\u{1F618}', '\u{1F48B}',
        ],
        Sentimentos: [
            '\u{1F600}', '\u{1F603}', '\u{1F604}', '\u{1F601}', '\u{1F606}', '\u{1F605}',
            '\u{1F602}', '\u{1F923}', '\u{1F60A}', '\u{1F607}', '\u{1F642}', '\u{1F609}',
            '\u{1F60C}', '\u{1F60D}', '\u{1F970}', '\u{1F618}', '\u{1F617}', '\u{1F619}',
            '\u{1F61A}', '\u{1F60B}', '\u{1F61B}', '\u{1F61D}', '\u{1F61C}', '\u{1F92A}',
            '\u{1F928}', '\u{1F9D0}', '\u{1F914}', '\u{1F910}', '\u{1F644}', '\u{1F60F}',
            '\u{1F612}', '\u{1F61E}', '\u{1F614}', '\u{1F622}', '\u{1F62D}', '\u{1F624}',
            '\u{1F620}', '\u{1F621}', '\u{1F92C}', '\u{1F633}', '\u{1F975}', '\u{1F976}',
            '\u{1F974}', '\u{1F635}', '\u{1F92F}', '\u{1F631}', '\u{1F628}', '\u{1F630}',
            '\u{1F625}', '\u{1F613}', '\u{1F917}', '\u{1F929}', '\u{1F973}',
        ],
        Gestos: [
            '\u{1F44B}', '\u{1F91A}', '\u{1F590}', '\u{270B}', '\u{1F596}', '\u{1F44C}',
            '\u{1F90C}', '\u{1F90F}', '\u{270C}\u{FE0F}', '\u{1F91E}', '\u{1F91F}', '\u{1F918}',
            '\u{1F919}', '\u{1F448}', '\u{1F449}', '\u{1F446}', '\u{1F447}', '\u{261D}\u{FE0F}',
            '\u{1F44D}', '\u{1F44E}', '\u{1F44A}', '\u{1F91B}', '\u{1F91C}', '\u{1F64F}',
            '\u{1F4AA}', '\u{1F9BE}', '\u{1F9BF}',
        ],
        Objetos: [
            '\u{1F4F1}', '\u{1F4BB}', '\u{1F4DA}', '\u{1F4DD}', '\u{1F4CE}', '\u{1F4C1}',
            '\u{1F4E7}', '\u{1F4E8}', '\u{1F4AC}', '\u{1F4AD}', '\u{1F514}', '\u{1F3A4}',
            '\u{1F3B5}', '\u{1F3B6}', '\u{1F3A5}', '\u{1F4F7}', '\u{1F4F9}', '\u{1F4FA}',
            '\u{1F4DE}', '\u{1F50D}', '\u{1F511}', '\u{1F512}', '\u{1F513}', '\u{1F6E0}',
            '\u{1F527}', '\u{1F528}', '\u{1F9F0}', '\u{1F4A1}', '\u{1F4B0}', '\u{1F4B3}',
            '\u{1F4B8}', '\u{1F381}', '\u{1F392}', '\u{1F3C6}', '\u{1F3C5}', '\u{1F947}',
        ],
        Natureza: [
            '\u{1F331}', '\u{1F33B}', '\u{1F339}', '\u{1F33A}', '\u{1F337}', '\u{1F340}',
            '\u{1F341}', '\u{1F342}', '\u{1F343}', '\u{1F334}', '\u{1F335}', '\u{1F30D}',
            '\u{1F30E}', '\u{1F30F}', '\u{1F31E}', '\u{1F31D}', '\u{1F31C}', '\u{2B50}',
            '\u{1F31F}', '\u{26A1}', '\u{1F4A7}', '\u{1F4A6}', '\u{2744}\u{FE0F}', '\u{1F308}',
            '\u{1F30A}', '\u{1F3D4}', '\u{1F3D5}', '\u{1F3DE}', '\u{1F3DD}',
        ],
        Atividades: [
            '\u{26BD}', '\u{1F3C0}', '\u{1F3C8}', '\u{26BE}', '\u{1F3BE}', '\u{1F3D0}',
            '\u{1F3C9}', '\u{1F3B1}', '\u{1F3AE}', '\u{1F3AF}', '\u{1F3B2}', '\u{1F3B3}',
            '\u{1F3A8}', '\u{1F3AA}', '\u{1F3AD}', '\u{1F3A3}', '\u{1F3A4}', '\u{1F3B9}',
            '\u{1F3B7}', '\u{1F3B8}', '\u{1F3BA}', '\u{1F3BB}', '\u{1F3BC}',
        ],
        Viagem: [
            '\u{1F697}', '\u{1F695}', '\u{1F68C}', '\u{1F682}', '\u{2708}\u{FE0F}', '\u{1F6A2}',
            '\u{26F5}', '\u{1F6A4}', '\u{1F6B2}', '\u{1F6F8}', '\u{1F680}', '\u{1F6EB}',
            '\u{1F6EC}', '\u{1F3E0}', '\u{1F3E2}', '\u{1F3E5}', '\u{1F3EB}', '\u{26FA}',
            '\u{1F3D6}', '\u{1F3DD}', '\u{1F5FA}', '\u{1F5FB}', '\u{1F30B}',
        ],
        Simbolos: [
            '\u{2705}', '\u{274C}', '\u{2757}', '\u{2753}', '\u{2049}\u{FE0F}', '\u{203C}\u{FE0F}',
            '\u{1F4AF}', '\u{1F198}', '\u{1F4A2}', '\u{1F525}', '\u{1F4A5}', '\u{2728}',
            '\u{1F31F}', '\u{1F4AB}', '\u{1F4A4}', '\u{1F4A8}', '\u{1F573}', '\u{1F4B2}',
            '\u{267B}\u{FE0F}', '\u{2699}\u{FE0F}', '\u{1F6AB}', '\u{1F6B7}', '\u{1F6B8}',
        ],
        Bandeiras: [
            '\u{1F1E7}\u{1F1F7}', '\u{1F1FA}\u{1F1F8}', '\u{1F1E6}\u{1F1F8}', '\u{1F1EA}\u{1F1F8}',
            '\u{1F1EC}\u{1F1E7}', '\u{1F1E9}\u{1F1EA}', '\u{1F1EB}\u{1F1F7}', '\u{1F1EE}\u{1F1F9}',
            '\u{1F1EF}\u{1F1F5}', '\u{1F1F0}\u{1F1F7}',
        ],
    };

    function initEmojiPicker() {
        const btn = document.getElementById('emojiBtn');
        const picker = document.getElementById('emojiPicker');
        const grid = document.getElementById('emojiGrid');
        const categories = document.getElementById('emojiCategories');
        const input = document.getElementById('messageInput');

        if (!btn || !picker || !grid || !categories) {
            return;
        }

        const categoryNames = Object.keys(EMOJIS);

        categories.innerHTML = categoryNames
            .map(
                (name, i) =>
                    `<button type="button" data-category="${name}" class="emoji-category-btn px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors ${i === 0 ? 'bg-primary text-on-primary' : 'hover:bg-gray-100 text-gray-600'}">${name}</button>`
            )
            .join('');

        function showCategory(name) {
            const list = EMOJIS[name] || [];
            grid.innerHTML = '';
            list.forEach((emoji) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'text-2xl p-2 rounded-lg hover:bg-gray-100 transition-colors';
                b.textContent = emoji;
                b.addEventListener('click', () => {
                    if (input && !input.disabled) {
                        input.value += emoji;
                        input.focus();
                        picker.classList.add('hidden');
                    }
                });
                grid.appendChild(b);
            });
        }

        showCategory(categoryNames[0]);

        categories.querySelectorAll('.emoji-category-btn').forEach((catBtn) => {
            catBtn.addEventListener('click', () => {
                categories.querySelectorAll('.emoji-category-btn').forEach((b) => {
                    b.classList.remove('bg-primary', 'text-on-primary');
                    b.classList.add('hover:bg-gray-100', 'text-gray-600');
                });
                catBtn.classList.add('bg-primary', 'text-on-primary');
                catBtn.classList.remove('hover:bg-gray-100', 'text-gray-600');
                showCategory(catBtn.dataset.category);
            });
        });

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (btn.disabled) return;
            picker.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !picker.contains(e.target)) {
                picker.classList.add('hidden');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmojiPicker);
    } else {
        initEmojiPicker();
    }

    global.initEmojiPicker = initEmojiPicker;
})(window);


/**
 * Flow Variables System
 * Handles real-time validation, preview, and variable insertion for flow messages
 */

class FlowVariables {
    constructor(config = {}) {
        this.textareaSelector = config.textareaSelector || '#message-text';
        this.warningsSelector = config.warningsSelector || '#validation-warnings';
        this.previewSelector = config.previewSelector || '#message-preview';
        this.insertButtonSelector = config.insertButtonSelector || '.btn-insert-variable';
        this.conversationId = config.conversationId || null;
        this.apiBaseUrl = config.apiBaseUrl || '/admin/flows';

        this.init();
    }

    init() {
        this.textarea = document.querySelector(this.textareaSelector);
        this.warningsContainer = document.querySelector(this.warningsSelector);
        this.previewContainer = document.querySelector(this.previewSelector);
        this.insertButton = document.querySelector(this.insertButtonSelector);

        if (!this.textarea) {
            console.warn('FlowVariables: Textarea not found');
            return;
        }

        // Load available variables
        this.loadAvailableVariables();

        // Real-time validation and preview
        this.textarea.addEventListener('input', () => this.handleInputChange());

        // Insert variable button
        if (this.insertButton) {
            this.insertButton.addEventListener('click', (e) => this.showVariableDropdown(e));
        }

        // Initial validation
        this.handleInputChange();
    }

    handleInputChange() {
        const text = this.textarea.value;

        // Validate
        this.validateVariables(text);

        // Preview
        this.previewVariables(text);
    }

    validateVariables(text) {
        fetch(`${this.apiBaseUrl}/validate-variables?text=${encodeURIComponent(text)}`)
            .then(response => response.json())
            .then(data => {
                this.displayWarnings(data.warnings || []);
            })
            .catch(error => console.error('Validation error:', error));
    }

    previewVariables(text) {
        let url = `${this.apiBaseUrl}/preview-variables?text=${encodeURIComponent(text)}`;
        if (this.conversationId) {
            url += `&conversation_id=${this.conversationId}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (this.previewContainer) {
                    this.previewContainer.textContent = data.preview || text;
                }
            })
            .catch(error => console.error('Preview error:', error));
    }

    displayWarnings(warnings) {
        if (!this.warningsContainer) return;

        if (warnings.length === 0) {
            this.warningsContainer.classList.add('hidden');
            this.warningsContainer.innerHTML = '';
            return;
        }

        this.warningsContainer.classList.remove('hidden');
        this.warningsContainer.innerHTML = warnings
            .map(w => `<div class="warning-item text-sm">⚠️ ${this.escapeHtml(w)}</div>`)
            .join('');
    }

    loadAvailableVariables() {
        fetch(`${this.apiBaseUrl}/available-variables`)
            .then(response => response.json())
            .then(data => {
                this.availableVariables = data.variables || {};
            })
            .catch(error => console.error('Failed to load variables:', error));
    }

    showVariableDropdown(e) {
        e.preventDefault();

        // Check if dropdown already exists
        const existingDropdown = document.querySelector('.variable-dropdown');
        if (existingDropdown) {
            existingDropdown.remove();
            return;
        }

        const dropdown = document.createElement('div');
        dropdown.className = 'variable-dropdown absolute bg-white border border-gray-300 rounded shadow-lg z-10 mt-2';

        if (this.availableVariables && Object.keys(this.availableVariables).length > 0) {
            Object.entries(this.availableVariables).forEach(([key, description]) => {
                const item = document.createElement('div');
                item.className = 'variable-item px-4 py-2 cursor-pointer hover:bg-gray-100 text-sm';
                item.textContent = `{{${key}}} - ${this.escapeHtml(description)}`;
                item.addEventListener('click', () => this.insertVariable(key));
                dropdown.appendChild(item);
            });
        } else {
            const item = document.createElement('div');
            item.className = 'px-4 py-2 text-gray-500 text-sm';
            item.textContent = 'Carregando variáveis...';
            dropdown.appendChild(item);
        }

        // Position dropdown
        const rect = this.insertButton.getBoundingClientRect();
        dropdown.style.left = rect.left + 'px';
        dropdown.style.top = (rect.bottom + 5) + 'px';

        document.body.appendChild(dropdown);

        // Close dropdown when clicking outside
        document.addEventListener('click', function closeDropdown(e) {
            if (e.target !== this.insertButton && !dropdown.contains(e.target)) {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            }
        });
    }

    insertVariable(varName) {
        const textarea = this.textarea;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        const before = text.substring(0, start);
        const after = text.substring(end);
        const newText = before + `{{${varName}}}` + after;

        textarea.value = newText;
        textarea.selectionStart = textarea.selectionEnd = start + varName.length + 4; // 4 = {{ + }}

        // Trigger input event for validation and preview
        textarea.dispatchEvent(new Event('input', { bubbles: true }));

        // Close dropdown
        const dropdown = document.querySelector('.variable-dropdown');
        if (dropdown) {
            dropdown.remove();
        }
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize if element exists
    if (document.querySelector('#message-text')) {
        window.flowVariables = new FlowVariables({
            textareaSelector: '#message-text',
            warningsSelector: '#validation-warnings',
            previewSelector: '#message-preview',
            insertButtonSelector: '.btn-insert-variable'
        });
    }
});

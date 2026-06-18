// Store current text being improved
let currentImprovedText = {
    original: '',
    improved: '',
    type: 'grammar'
};

/**
 * Open improve text modal
 */
function openImproveTextModal() {
    const textarea = document.getElementById('messageInput');
    const text = textarea.value.trim();

    if (!text) {
        alert('Digite algo para melhorar');
        return;
    }

    currentImprovedText.original = text;

    // Show modal
    document.getElementById('improveTextModal').classList.remove('hidden');
    document.getElementById('improveOriginalText').textContent = text;
    document.getElementById('improveTypeSelect').value = 'grammar';

    // Clear previous content
    document.getElementById('improveImprovedText').classList.add('hidden');
    document.getElementById('improveLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveErrorMessage').classList.add('hidden');
    document.getElementById('improveUseBtn').disabled = true;

    // Fetch improvement
    refreshImprovement();
}

/**
 * Close improve text modal
 */
function closeImproveTextModal() {
    document.getElementById('improveTextModal').classList.add('hidden');
    currentImprovedText = { original: '', improved: '', type: 'grammar' };
}

/**
 * Refresh improvement based on selected type
 */
function refreshImprovement() {
    const type = document.getElementById('improveTypeSelect').value;
    const text = currentImprovedText.original;
    const conversationId = document.querySelector('input[name="conversation_id"]')?.value;

    if (!conversationId || !text) {
        return;
    }

    // Show loading, hide content
    document.getElementById('improveLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveImprovedText').classList.add('hidden');
    document.getElementById('improveErrorMessage').classList.add('hidden');
    document.getElementById('improveUseBtn').disabled = true;

    // Call API
    fetch(`/conversations/${conversationId}/improve-text`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            content: text,
            type: type
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erro ao processar requisição');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            currentImprovedText.improved = data.improved;
            currentImprovedText.type = type;

            // Show improved text
            document.getElementById('improveLoadingSpinner').classList.add('hidden');
            document.getElementById('improveImprovedText').textContent = data.improved;
            document.getElementById('improveImprovedText').classList.remove('hidden');
            document.getElementById('improveUseBtn').disabled = false;
        } else {
            throw new Error(data.message || 'Erro desconhecido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('improveLoadingSpinner').classList.add('hidden');
        document.getElementById('improveErrorMessage').textContent = 'Erro: ' + error.message;
        document.getElementById('improveErrorMessage').classList.remove('hidden');
    });
}

/**
 * Apply improved text to message input
 */
function applyImprovedText() {
    const textarea = document.getElementById('messageInput');
    textarea.value = currentImprovedText.improved;
    textarea.focus();

    // Close modal
    closeImproveTextModal();

    // Show native notification
    alert('Texto melhorado aplicado!');
}

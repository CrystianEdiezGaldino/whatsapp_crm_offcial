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
}

// Contact Notes Improvement Functions
let currentImprovedContactNotes = {
    original: '',
    improved: '',
    type: 'grammar'
};

function openImproveContactNotesModal() {
    const textarea = document.getElementById('contactNotes');
    const text = textarea.value.trim();

    if (!text) {
        return;
    }

    currentImprovedContactNotes.original = text;

    document.getElementById('improveContactNotesModal').classList.remove('hidden');
    document.getElementById('improveContactNotesOriginalText').textContent = text;
    document.getElementById('improveContactNotesTypeSelect').value = 'grammar';

    document.getElementById('improveContactNotesImprovedText').classList.add('hidden');
    document.getElementById('improveContactNotesLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveContactNotesErrorMessage').classList.add('hidden');
    document.getElementById('improveContactNotesUseBtn').disabled = true;

    refreshImproveContactNotes();
}

function closeImproveContactNotesModal() {
    document.getElementById('improveContactNotesModal').classList.add('hidden');
    currentImprovedContactNotes = { original: '', improved: '', type: 'grammar' };
}

function refreshImproveContactNotes() {
    const type = document.getElementById('improveContactNotesTypeSelect').value;
    const text = currentImprovedContactNotes.original;
    const conversationId = document.querySelector('[data-conversation-id]')?.getAttribute('data-conversation-id');

    if (!conversationId || !text) {
        return;
    }

    document.getElementById('improveContactNotesLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveContactNotesImprovedText').classList.add('hidden');
    document.getElementById('improveContactNotesErrorMessage').classList.add('hidden');
    document.getElementById('improveContactNotesUseBtn').disabled = true;

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
            currentImprovedContactNotes.improved = data.improved;
            currentImprovedContactNotes.type = type;

            document.getElementById('improveContactNotesLoadingSpinner').classList.add('hidden');
            document.getElementById('improveContactNotesImprovedText').textContent = data.improved;
            document.getElementById('improveContactNotesImprovedText').classList.remove('hidden');
            document.getElementById('improveContactNotesUseBtn').disabled = false;
        } else {
            throw new Error(data.message || 'Erro desconhecido');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('improveContactNotesLoadingSpinner').classList.add('hidden');
        document.getElementById('improveContactNotesErrorMessage').textContent = 'Erro: ' + error.message;
        document.getElementById('improveContactNotesErrorMessage').classList.remove('hidden');
    });
}

function applyImprovedContactNotes() {
    const textarea = document.getElementById('contactNotes');
    textarea.value = currentImprovedContactNotes.improved;
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles: true }));

    closeImproveContactNotesModal();
}

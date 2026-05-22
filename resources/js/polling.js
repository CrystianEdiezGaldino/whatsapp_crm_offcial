/**
 * List Polling - Updates conversation list every 5-10 seconds
 * Handles: conversation status, pending count, claim info, last message times
 */

class ConversationListPoller {
    constructor(options = {}) {
        this.pollInterval = options.interval || 7000; // 7 seconds
        this.pollerId = null;
        this.lastState = null;
        this.isRunning = false;
    }

    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        console.log('[Polling] Starting list polling...');
        this.poll();
    }

    stop() {
        if (this.pollerId) {
            clearInterval(this.pollerId);
            this.pollerId = null;
        }
        this.isRunning = false;
        console.log('[Polling] Stopped list polling');
    }

    async poll() {
        try {
            const response = await fetch('/conversations', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();

            // Extract conversation list from HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Update pending badge count
            const newBadge = doc.querySelector('.text-\\[10px\\]')?.textContent;
            if (newBadge) {
                this.updatePendingBadge(newBadge);
            }

            // Update conversation items
            this.updateConversationItems(doc);

        } catch (error) {
            console.error('[Polling] Error fetching conversations:', error);
        } finally {
            this.pollerId = setTimeout(() => this.poll(), this.pollInterval);
        }
    }

    updatePendingBadge(count) {
        const badge = document.querySelector('[data-pending-count]');
        if (badge) {
            badge.textContent = count;
        }
    }

    updateConversationItems(doc) {
        const items = doc.querySelectorAll('a[data-conversation-id]');
        items.forEach(newItem => {
            const convId = newItem.dataset.conversationId;
            const oldItem = document.querySelector(`a[data-conversation-id="${convId}"]`);

            if (oldItem) {
                // Update status classes, timestamps, etc
                oldItem.className = newItem.className;

                // Update last message time
                const newTime = newItem.querySelector('[data-last-message-time]')?.textContent;
                const oldTime = oldItem.querySelector('[data-last-message-time]');
                if (newTime && oldTime) {
                    oldTime.textContent = newTime;
                }

                // Update last message preview
                const newPreview = newItem.querySelector('[data-last-message-preview]')?.textContent;
                const oldPreview = oldItem.querySelector('[data-last-message-preview]');
                if (newPreview && oldPreview) {
                    oldPreview.textContent = newPreview;
                }

                // Update claim badge
                const newClaim = newItem.querySelector('[data-claim-info]')?.textContent;
                const oldClaim = oldItem.querySelector('[data-claim-info]');
                if (newClaim && oldClaim) {
                    oldClaim.textContent = newClaim;
                }
            }
        });
    }
}

// Initialize and start on page load
document.addEventListener('DOMContentLoaded', () => {
    window.conversationListPoller = new ConversationListPoller({ interval: 7000 });
    window.conversationListPoller.start();
});

// Stop polling when page unloads
window.addEventListener('beforeunload', () => {
    if (window.conversationListPoller) {
        window.conversationListPoller.stop();
    }
});

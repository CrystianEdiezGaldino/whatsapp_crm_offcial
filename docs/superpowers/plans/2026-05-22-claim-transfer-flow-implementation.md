# Claim/Transfer Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement polling-based claim/transfer flow without Pusher, with browser notifications and dual polling rates (list 5-10s, active 2-3s).

**Architecture:** Remove broadcast system, add client-side polling with dual refresh rates, add browser notifications API, clarify permission model in UI/backend.

**Tech Stack:** JavaScript fetch polling, Laravel controllers, browser Notification API, existing endpoints

---

## File Structure

**Files to Modify:**
- `resources/views/conversations/index.blade.php` - Remove Echo/broadcast, add polling JS, improve UI
- `app/Http/Controllers/ConversationController.php` - Add index endpoint for polling
- `app/Models/Conversation.php` - No changes needed (claim methods already exist)
- `app/Http/Controllers/ConversationClaimController.php` - No changes (already correct)

**Files to Create:**
- `resources/js/polling.js` - List polling logic
- `resources/js/conversation-polling.js` - Active conversation polling logic
- `resources/js/notifications.js` - Browser notification + sound logic

**Files to Remove:**
- Nothing (keep all code, just disable functionality)

---

## Phase 1: Remove Broadcast/Pusher System

### Task 1: Remove Echo Listeners from Blade

**Files:**
- Modify: `resources/views/conversations/index.blade.php`

- [ ] **Step 1: Find Echo listener section**

Search for `Echo.channel` in the file around line 610-630. You'll find:
```blade
if (typeof window.Echo !== 'undefined' && ...) {
    Echo.channel('conversations.all')
        .listen('message.received', ...);
    
    if (...active conversation...) {
        Echo.channel(`conversation.${conversationId}`)
            .listen('message.received', ...);
    }
}
```

- [ ] **Step 2: Remove global Echo listener**

Delete the entire first Echo block (lines ~573-587) that listens on `conversations.all`:
```blade
// DELETE THIS SECTION:
if (typeof window.Echo !== 'undefined') {
    Echo.channel('conversations.all')
        .listen('message.received', (event) => {
            console.log('[Echo Global] Nova mensagem recebida');
            setTimeout(() => window.location.reload(), 1500);
        });
}
```

- [ ] **Step 3: Remove active conversation Echo listener**

Keep only the `@if($activeConversation)` block that starts around line 589, but REMOVE the Echo listener inside it. Remove this code:
```blade
Echo.channel(`conversation.${conversationId}`)
    .listen('message.received', (event) => {
        console.log('[Echo] Nova mensagem recebida:', event);
        playNotificationSound();
        showDesktopNotification(event.sender_name, event.content);
        showNotificationToast(event.sender_name, event.content);
        setTimeout(() => window.location.reload(), 1500);
    });
```

Replace with comment:
```blade
// Polling will handle message notifications
```

- [ ] **Step 4: Keep notification functions**

Keep `playNotificationSound()`, `showDesktopNotification()`, and `showNotificationToast()` functions - we'll reuse them in polling.

- [ ] **Step 5: Commit**

```bash
git add resources/views/conversations/index.blade.php
git commit -m "feat: remover Echo broadcast listeners, manter funções de notificação"
```

---

## Phase 2: Create Polling JavaScript Files

### Task 2: Create List Polling Module

**Files:**
- Create: `resources/js/polling.js`

- [ ] **Step 1: Create polling.js file**

Create new file `resources/js/polling.js`:

```javascript
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
```

- [ ] **Step 2: Add data attributes to conversation list items in blade**

Modify `resources/views/conversations/index.blade.php` (around line 74):

Change:
```blade
<a href="{{ route('conversations.index', ['conversation' => $conv->id] + request()->all()) }}" class="...">
```

To:
```blade
<a href="{{ route('conversations.index', ['conversation' => $conv->id] + request()->all()) }}" 
   data-conversation-id="{{ $conv->id }}" 
   class="...">
```

Add data attributes to elements that change:
```blade
<span data-last-message-time class="text-[11px] text-on-surface-variant shrink-0">{{ $conv->last_message_at?->diffForHumans(short: true) }}</span>
<p data-last-message-preview class="text-sm text-on-surface-variant truncate mt-0.5">{{ $conv->lastMessage?->content ?? 'Sem mensagens' }}</p>
<span data-claim-info class="text-xs">{{ $activeClaim?->user->name ?? 'Pendente' }}</span>
```

- [ ] **Step 3: Add pending count badge data attribute**

In the header (around line 47):
```blade
<span class="bg-error text-on-error text-xs font-bold px-2.5 py-1 rounded-full animate-pulse" data-pending-count>
    {{ $pendingCount }} pendente{{ $pendingCount !== 1 ? 's' : '' }}
</span>
```

- [ ] **Step 4: Include polling.js in blade**

At the end of `resources/views/conversations/index.blade.php` (in the `@push('scripts')` section), add:

```blade
<script src="{{ asset('js/polling.js') }}"></script>
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/polling.js resources/views/conversations/index.blade.php
git commit -m "feat: adicionar polling da lista de conversas (5-10s)"
```

---

### Task 3: Create Active Conversation Polling

**Files:**
- Create: `resources/js/conversation-polling.js`

- [ ] **Step 1: Create conversation-polling.js**

Create `resources/js/conversation-polling.js`:

```javascript
/**
 * Active Conversation Polling - Updates active conversation every 2-3 seconds
 * Uses existing /conversations/{id}/poll endpoint
 */

class ActiveConversationPoller {
    constructor(conversationId, options = {}) {
        this.conversationId = conversationId;
        this.pollInterval = options.interval || 2500; // 2.5 seconds
        this.pollerId = null;
        this.lastMessageId = options.lastMessageId || 0;
        this.isRunning = false;
    }

    start() {
        if (this.isRunning || !this.conversationId) return;
        this.isRunning = true;
        console.log(`[Polling] Starting conversation ${this.conversationId} polling...`);
        this.poll();
    }

    stop() {
        if (this.pollerId) {
            clearInterval(this.pollerId);
            this.pollerId = null;
        }
        this.isRunning = false;
        console.log('[Polling] Stopped active conversation polling');
    }

    async poll() {
        try {
            const response = await fetch(
                `/conversations/${this.conversationId}/poll`,
                {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            // Handle new messages
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(message => {
                    if (message.id > this.lastMessageId) {
                        this.addMessageToUI(message);
                        this.lastMessageId = message.id;
                        
                        // Show notification if this is the active conversation
                        if (window.showNotificationToast) {
                            window.showNotificationToast(
                                message.contact_name || 'Novo contato',
                                message.content || 'Mensagem recebida'
                            );
                        }
                    }
                });

                // Auto-scroll to latest message
                this.scrollToBottom();
            }

        } catch (error) {
            console.error('[Polling] Error polling conversation:', error);
        } finally {
            this.pollerId = setTimeout(() => this.poll(), this.pollInterval);
        }
    }

    addMessageToUI(message) {
        const chatEl = document.getElementById('chatEl');
        if (!chatEl) return;

        // Create message element (match existing format)
        const messageEl = document.createElement('div');
        messageEl.className = 'flex gap-3 mb-4 ' + (message.direction === 'inbound' ? 'justify-start' : 'justify-end');
        messageEl.innerHTML = `
            <div class="flex gap-2 max-w-xs">
                <div class="bg-${message.direction === 'inbound' ? 'surface-container' : 'primary'} px-4 py-2 rounded-lg">
                    <p class="text-sm text-${message.direction === 'inbound' ? 'on-surface' : 'on-primary'}">${escapeHtml(message.content)}</p>
                    <p class="text-xs text-${message.direction === 'inbound' ? 'on-surface-variant' : 'on-primary'} opacity-70 mt-1">${formatTime(message.timestamp)}</p>
                </div>
            </div>
        `;

        chatEl.appendChild(messageEl);
    }

    scrollToBottom() {
        const chatEl = document.getElementById('chatEl');
        if (chatEl) {
            chatEl.scrollTop = chatEl.scrollHeight;
        }
    }
}

// Helper functions
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatTime(timestamp) {
    const date = new Date(timestamp * 1000);
    return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const conversationId = window.activeConversationId;
    const lastMessageId = window.lastMessageId || 0;
    
    if (conversationId) {
        window.conversationPoller = new ActiveConversationPoller(
            conversationId,
            { 
                interval: 2500,
                lastMessageId: lastMessageId
            }
        );
        window.conversationPoller.start();
    }
});

// Stop polling when navigating away
window.addEventListener('beforeunload', () => {
    if (window.conversationPoller) {
        window.conversationPoller.stop();
    }
});
```

- [ ] **Step 2: Set window variables in blade**

In `resources/views/conversations/index.blade.php`, add near the top of the script section:

```blade
<script>
    window.activeConversationId = @if($activeConversation) {{ $activeConversation->id }} @else null @endif;
    window.lastMessageId = @if($activeConversation) {{ $activeConversation->messages->last()->id ?? 0 }} @else 0 @endif;
</script>
```

- [ ] **Step 3: Include conversation-polling.js**

Add to `@push('scripts')` in blade:

```blade
<script src="{{ asset('js/conversation-polling.js') }}"></script>
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/conversation-polling.js resources/views/conversations/index.blade.php
git commit -m "feat: adicionar polling da conversa ativa (2-3s)"
```

---

## Phase 3: Add Browser Notifications

### Task 4: Implement Notification + Sound System

**Files:**
- Create: `resources/js/notifications.js`

- [ ] **Step 1: Create notifications.js**

Create `resources/js/notifications.js`:

```javascript
/**
 * Browser Notifications and Sound System
 * Requests permission, plays sound, shows browser notifications
 */

class NotificationManager {
    constructor() {
        this.soundUrl = '/sounds/notification.mp3';
        this.permissionGranted = false;
        this.permissionDenied = false;
    }

    init() {
        this.checkPermission();
        this.requestPermissionIfNeeded();
    }

    checkPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                this.permissionGranted = true;
            } else if (Notification.permission === 'denied') {
                this.permissionDenied = true;
            }
        }
    }

    async requestPermissionIfNeeded() {
        if (!('Notification' in window)) {
            console.log('[Notifications] Notification API not supported');
            return;
        }

        // If permission already granted or denied, don't ask again
        if (Notification.permission !== 'default') {
            return;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                this.permissionGranted = true;
                console.log('[Notifications] Permission granted');
            } else if (permission === 'denied') {
                this.permissionDenied = true;
                console.log('[Notifications] Permission denied');
            }
        } catch (error) {
            console.error('[Notifications] Error requesting permission:', error);
        }
    }

    playSound() {
        try {
            const audio = new Audio(this.soundUrl);
            audio.volume = 0.5;
            audio.play().catch(err => {
                console.log('[Notifications] Audio autoplay blocked:', err);
            });
        } catch (error) {
            console.error('[Notifications] Error playing sound:', error);
        }
    }

    showBrowserNotification(title, options = {}) {
        if (!this.permissionGranted) {
            return;
        }

        try {
            const notification = new Notification(title, {
                icon: '/images/whatsapp-icon.png',
                badge: '/images/badge.png',
                tag: 'whatsapp-notification',
                ...options
            });

            // Auto-close after 5 seconds
            setTimeout(() => notification.close(), 5000);

            // Focus window on click
            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        } catch (error) {
            console.error('[Notifications] Error showing notification:', error);
        }
    }

    notify(title, options = {}) {
        // Always play sound
        this.playSound();

        // Show browser notification if permission granted
        this.showBrowserNotification(title, options);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
    window.notificationManager.init();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
```

- [ ] **Step 2: Add sound file**

Ensure `/public/sounds/notification.mp3` exists. If not, create a simple beep sound or download one.

Check:
```bash
ls -la public/sounds/notification.mp3
```

If missing, you can create a placeholder or ensure the file exists.

- [ ] **Step 3: Update polling to use notification manager**

Modify `resources/js/conversation-polling.js`, update the `addMessageToUI` function to use notifications:

```javascript
addMessageToUI(message) {
    const chatEl = document.getElementById('chatEl');
    if (!chatEl) return;

    // ... existing code to create messageEl ...

    chatEl.appendChild(messageEl);

    // Show notification
    if (window.notificationManager && message.direction === 'inbound') {
        window.notificationManager.notify(
            message.contact_name || 'Novo contato',
            { body: message.content || 'Mensagem recebida' }
        );
    }
}
```

- [ ] **Step 4: Include notifications.js in blade**

Add to `@push('scripts')`:

```blade
<script src="{{ asset('js/notifications.js') }}"></script>
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/notifications.js resources/views/conversations/index.blade.php
git commit -m "feat: adicionar notificações do navegador e som"
```

---

## Phase 4: UI Improvements

### Task 5: Improve Status Badges and Claim Display

**Files:**
- Modify: `resources/views/conversations/index.blade.php`

- [ ] **Step 1: Update claim status badge**

Find the section around line 150-160 where claim status is shown. Update to:

```blade
@if($activeClaim)
    <span class="text-[11px] bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded flex items-center gap-1">
        <span class="material-symbols-outlined text-xs">lock</span>
        Clamado por: <strong>{{ $activeClaim->user->name }}</strong>
        @if($hasMyClaim)
            <span class="text-[10px] text-green-600">(Você)</span>
        @endif
    </span>
@else
    <span class="text-[11px] bg-red-100 text-red-800 px-2 py-0.5 rounded flex items-center gap-1">
        <span class="material-symbols-outlined text-xs">schedule</span>
        Aguardando atendimento
    </span>
@endif
```

- [ ] **Step 2: Add data-claim-info attribute to list items**

In conversation list (around line 84-90), ensure all claim info has data attributes:

```blade
@php
    $activeClaim = $conv->getActiveClaim();
@endphp
<span data-claim-info class="text-xs text-on-surface-variant">
    @if($activeClaim)
        🔒 {{ $activeClaim->user->name }}
    @else
        ⏱️ Pendente
    @endif
</span>
```

- [ ] **Step 3: Ensure textarea bloocking works**

Verify textarea has correct attributes (should be from earlier task):

```blade
<textarea id="messageInput" ... 
    @if(!$hasMyClaim && $isAdmin) disabled title="Clique em 'Transferir para mim'" @endif
    @if(!$hasMyClaim && !$isAdmin) disabled title="Este atendimento foi clamado por {{ $activeClaim?->user->name }}" @endif
>
```

- [ ] **Step 4: Test UI visually**

Open browser and verify:
- Pending conversations show red "⏱️ Aguardando"
- Claimed conversations show yellow "🔒 Name"
- Your claimed conversations show "(Você)" badge
- Admin sees "Transferir Para" button
- Regular agents see blocked UI on other's conversations

- [ ] **Step 5: Commit**

```bash
git add resources/views/conversations/index.blade.php
git commit -m "feat: melhorar badges de status e informação de claim"
```

---

## Phase 5: Backend Validation

### Task 6: Verify Backend Permissions

**Files:**
- Review: `app/Http/Controllers/ConversationClaimController.php`
- Review: `app/Models/Conversation.php`

- [ ] **Step 1: Review claim controller**

Check `app/Http/Controllers/ConversationClaimController.php` - verify:
- `claim()` allows admin to override existing claims ✓ (already fixed)
- `release()` only allows the user who claimed or admin ✓
- `reassign()` requires admin role ✓

No changes needed - controller is already correct.

- [ ] **Step 2: Review Conversation model**

Check `app/Models/Conversation.php` - verify:
- `getActiveClaim()` returns current claim ✓
- `hasActiveClaim()` checks if claim exists ✓
- `claim()` creates new claim ✓
- `releaseClaim()` releases claim ✓

No changes needed - model is correct.

- [ ] **Step 3: Test backend with curl**

Test claiming:
```bash
curl -X POST http://localhost:8000/conversations/20/claim \
  -H "X-CSRF-TOKEN: your_token" \
  -H "Accept: application/json" \
  --cookie "LARAVEL_SESSION=your_session"
```

Expected: 200 with success message

Test transferring:
```bash
curl -X PATCH http://localhost:8000/conversations/20/reassign \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_token" \
  -H "Accept: application/json" \
  -d '{"user_id": 2}' \
  --cookie "LARAVEL_SESSION=your_session"
```

Expected: 200 with success message

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "docs: verificar backend permissions"
```

---

## Phase 6: Integration Testing

### Task 7: Test Full Flow

**Files:**
- Test manually in browser

- [ ] **Step 1: Test pending conversation**

1. Load `/conversations`
2. Verify pending conversations show "⏱️ Aguardando"
3. Click "Clamar Atendimento"
4. Verify UI updates to show "✓ Clamado por: Você"
5. Verify textarea is enabled

- [ ] **Step 2: Test blocked access**

1. As regular agent, view conversation claimed by other agent
2. Verify textarea is disabled
3. Verify "Transferir" button not visible
4. Refresh page, verify state persists

- [ ] **Step 3: Test admin transfer**

1. As admin, view conversation claimed by someone else
2. Click "Transferir Para"
3. Select different agent
4. Verify page reloads
5. Verify new agent is shown

- [ ] **Step 4: Test polling updates**

1. Keep conversation list open
2. Send message to conversation from WhatsApp
3. Wait 5-10 seconds
4. Verify list updates (new message shows, time updates)
5. If conversation is active, verify message appears in 2-3 seconds

- [ ] **Step 5: Test notifications**

1. Allow browser notifications when prompted
2. Send WhatsApp message
3. Verify sound plays
4. Verify browser notification shows
5. Verify toast appears

- [ ] **Step 6: Document findings**

If anything fails, note it and fix the specific issue.

- [ ] **Step 7: Commit test results**

```bash
git add -A
git commit -m "test: validar fluxo completo de claim/transfer/polling"
```

---

## Summary

**What Gets Built:**
1. ✅ Remove Pusher/broadcast completely
2. ✅ Add dual polling (list 5-10s, conversation 2-3s)
3. ✅ Add browser notifications + sound
4. ✅ Clarify permissions (only claimer can respond, admin can transfer)
5. ✅ Improve UI status indicators
6. ✅ Backend validation already in place

**Key Files:**
- `resources/js/polling.js` - List polling
- `resources/js/conversation-polling.js` - Active conversation polling
- `resources/js/notifications.js` - Browser notifications + sound
- Updated `resources/views/conversations/index.blade.php`

**Testing:**
- Manual testing of all user flows
- Verify polling intervals work
- Verify notifications fire
- Verify permissions enforced

---

## Verification Checklist

After all tasks complete:

- [ ] No Echo/broadcast code remains in blade
- [ ] Polling starts automatically on page load
- [ ] List updates every 5-10 seconds without reload
- [ ] Active conversation updates every 2-3 seconds
- [ ] Sound plays on new message
- [ ] Browser notification shows with permission
- [ ] Claim badges show correctly
- [ ] Admin can transfer
- [ ] Regular agents blocked from non-owned conversations
- [ ] Textarea disabled appropriately
- [ ] All tests pass

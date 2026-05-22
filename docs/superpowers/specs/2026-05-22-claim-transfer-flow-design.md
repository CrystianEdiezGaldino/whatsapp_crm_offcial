# Claim/Transfer Flow Design

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:writing-plans to create implementation plan after this spec is approved.

**Goal:** Redesign the conversation claim and transfer flow to be logical, intuitive, and responsive without external dependencies (Pusher).

**Architecture:** Polling-based system with dual refresh rates (list: 5-10s, active conversation: 2-3s) + browser notifications + sound alerts. No Pusher/broadcast required.

**Tech Stack:** JavaScript polling, existing `/conversations.poll` endpoint, browser Notification API, Laravel backend validation

---

## 1. Claim/Transfer Control Flow

### States
- **Pending:** No claim active → conversation awaits agent
- **Claimed:** Has active claim → assigned to specific agent

### Permissions by Role

**Regular Agent:**
- Can claim a pending conversation
- Cannot claim if already claimed by someone else
- Cannot transfer (only admin)
- Cannot respond if claimed by someone else (UI blocks, backend validates)

**Admin:**
- Can claim pending conversations
- Can transfer between any agents (automatic, no acceptance needed)
- Can override existing claims
- Can respond to any conversation (always has access)

### Claim/Transfer Flow

**Claiming a Pending Conversation:**
1. Agent clicks "Clamar Atendimento"
2. Frontend: `POST /conversations/{id}/claim`
3. Backend: creates ConversationClaim, returns success
4. Frontend: updates UI immediately to show "Clamado por: [your name]"
5. Agent can now respond

**Transferring Conversation (Admin Only):**
1. Admin clicks "Transferir Para"
2. Modal opens with list of all agents
3. Admin selects new agent
4. Frontend: `PATCH /conversations/{id}/reassign` with new user_id
5. Backend: 
   - Releases previous claim (if exists)
   - Creates new claim for selected agent
   - Records in AuditLog
   - Returns success
6. Frontend: reloads page to show new agent
7. New agent can respond, previous agent sees as blocked

**UI State Indicators:**
- No claim: Badge "⏱️ Aguardando atendimento" + "Clamar" button
- Claimed by you: Badge "✓ Clamado por: Você" + "Liberar" button
- Claimed by other (regular agent): Grayed out + "🔒 Clamado por: [name]" + textarea disabled
- Claimed by other (admin view): "🔒 Clamado por: [name]" + "Transferir Para" button + textarea enabled

---

## 2. Polling Strategy

### List Polling (5-10 seconds)
**Endpoint:** `GET /conversations`

**Refreshes:**
- Conversation statuses (pending/claimed)
- Who claimed each conversation
- Conversation order (most recent first)
- Pending count in header badge
- Last message timestamps

**Behavior:**
- Continuous while page is open
- Silent updates (no page reload)
- Updates sidebar without interrupting active chat

**Implementation:**
- JavaScript setInterval every 5-10 seconds
- Compare with previous state, update only changed items
- No flash/blink on update

### Active Conversation Polling (2-3 seconds)
**Endpoint:** `GET /conversations/{id}/poll` (existing)

**Refreshes:**
- New messages since last check
- Message statuses

**Behavior:**
- Only active when conversation is open
- Stops when user navigates away or closes browser tab
- Faster than list polling for responsive feel

**Implementation:**
- Separate setInterval, faster cadence
- Auto-scroll to newest message
- Doesn't interfere with list polling

### Polling Lifecycle
```
Page loads
  ↓
Request permission for notifications
  ↓
Start list polling (5-10s) + start active conversation polling if one selected (2-3s)
  ↓
User navigates to different conversation
  ↓
Stop old conversation polling, start new one
  ↓
User leaves page
  ↓
Stop all polling
```

---

## 3. Notifications (Sound + Browser)

### When to Notify
- Any new message arrives in any conversation
- Only once per message (no duplicates)

### Notification Stack
1. **Sound/Audio:** Discrete "ping" plays
2. **Browser Notification:** Shows contact name + message preview
3. **Toast on Page:** Slide-in banner, bottom-right corner

### Permission Handling
**First Load:**
- Page requests notification permission
- "Allow notifications to receive alerts about new messages?"
- Saves user preference in localStorage

**Permission Denied:**
- System works normally
- Sound still plays (doesn't need permission)
- Browser notifications just don't show
- User can enable later in browser settings

### Notification Behavior by Context
**If message is in active conversation:**
- Tones sound
- Message appears via polling (2-3s)
- Timeline auto-scrolls to new message

**If message is in different conversation:**
- Tone sounds
- Browser notification shows with contact name + preview
- Toast appears on page
- List updates on next polling cycle (5-10s)
- Notification auto-dismisses after 5 seconds

---

## 4. UI/UX Presentation

### Active Conversation Header
```
Contact: João Silva
Phone: +55 11 9999-9999

[Status Badge - one of:]
⏱️ Aguardando atendimento          [Clamar]
✓ Clamado por: Você               [Liberar]
🔒 Clamado por: Maria Santos      [Transferir...]
```

### Conversation List Styling
- **Pending:** Red background + "⏱️" icon + red text + "Aguardando atendimento" label
- **Claimed by you:** Normal background + "✓" badge
- **Claimed by other:** Gray background + "🔒" icon + agent name in small text below contact name
- **All:** Last message time + last message preview

### Input Area State
**If conversation is claimed by you or pending:**
- Textarea enabled
- Buttons functional (send, attach, record)
- Macros menu works

**If conversation is claimed by someone else and you're not admin:**
- Textarea disabled (gray, not clickable)
- Buttons disabled
- Message: "Este atendimento foi clamado por [name]"
- "Reivindicar" button not visible (you can't take from someone else)

**If you're admin and conversation is claimed by someone else:**
- Textarea enabled (admin always can respond)
- "Transferir Para" button visible
- Can respond or transfer

### Header Badges
- "Pendentes: 2" badge shows count of unclaimed conversations
- Updates with list polling
- Can click to filter to pending only

---

## 5. Data Flow Sequence

### On Page Load
```
1. Backend renders initial state (conversations list + active conversation)
2. Frontend JavaScript boots:
   - Request notification permission
   - Start list polling (5-10s)
   - If active conversation: start its polling (2-3s)
   - Set up message listeners
```

### When Agent Claims
```
1. Agent clicks "Clamar"
2. Frontend: POST /conversations/{id}/claim
3. Backend: 
   - Validates no active claim exists (or admin override)
   - Creates ConversationClaim
   - Returns success + claim details
4. Frontend:
   - Updates UI immediately
   - Shows "Clamado por: Você"
   - Enables textarea
   - Shows "Liberar" button instead of "Clamar"
```

### When Admin Transfers
```
1. Admin clicks "Transferir Para"
2. Modal shows agent list (from /api/agents)
3. Admin selects new agent
4. Frontend: PATCH /conversations/{id}/reassign {user_id}
5. Backend:
   - Validates admin role
   - Validates new user exists
   - Releases old claim
   - Creates new claim
   - Records AuditLog entry
   - Returns success
6. Frontend: location.reload() to show new state
7. New agent sees conversation as claimed by them
8. Old agent sees as blocked
```

### When Message Arrives
```
1. WhatsApp webhook → backend processes → MessageReceived event
2. Next polling cycle (max 3s for active, 10s for list):
   - Active conversation polling: fetches new message, displays it
   - List polling: updates last_message_at timestamp
3. Notification fires:
   - Sound plays
   - Browser notification shows
   - Toast slides in
```

---

## 6. Error Handling

### Claim Conflicts
**Error:** User tries to claim already-claimed conversation (not admin)
- Backend: Returns 409 "Este atendimento já foi clamado por João"
- Frontend: Shows friendly error message
- List updates on next polling to show correct claim

### Invalid Transfer
**Error:** Admin tries to transfer to non-existent agent
- Backend: Returns 422 "Agente não encontrado"
- Frontend: Modal shows error, doesn't close

### Unauthorized Response
**Error:** Regular agent tries to respond to conversation claimed by other
- Backend: Validates claim before accepting message, returns 403
- Frontend: Textarea already disabled (preventative)

### Deleted Conversation
**Error:** Conversation deleted while user is viewing it
- Next polling detects conversation missing
- Frontend: Redirects to `/conversations` with message "Este atendimento foi removido"

### Network Failure
**Error:** Polling request fails (network down)
- Silently retries next polling cycle
- If fails 3x in a row: Show toast "Perdeu conexão, reconectando..."
- Resume normal when network returns

### Permission Denied
**Error:** User denies notification permission
- System continues normally
- Sound still plays
- No browser notifications
- User can enable in browser settings later

---

## 7. No External Dependencies

**Why no Pusher/Broadcast:**
- Polling is sufficient for your use case (< 10s latency acceptable)
- Eliminates dependency on external service
- No configuration needed
- Simpler debugging
- Cheaper (no 3rd party service)

**Trade-off:** 10 second max latency on list updates (< 3s on active conversation)

---

## 8. Testing Strategy

**Unit Tests:**
- Claim creation only if no active claim (or admin override)
- Transfer releases old claim and creates new one
- Regular agents can't override other claims

**Integration Tests:**
- Full claim flow: pending → claimed → can respond
- Full transfer flow: existing claim → transferred → new agent can respond
- Blocking: regular agent can't respond to other's conversation
- Admin always can respond

**Manual Testing:**
- Polling intervals working (check Network tab)
- Notifications firing with sound + browser popup
- List updating without page reload
- Active conversation showing new messages in 2-3s
- Transfer modal showing correct agent list
- Permission request only on first load

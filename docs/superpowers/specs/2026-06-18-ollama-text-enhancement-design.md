# Ollama Text Enhancement Feature - Design Specification

**Date:** 2026-06-18  
**Status:** Design Ready  
**Priority:** Medium  
**Scope:** Single-feature implementation (conversations text improvement)

---

## 1. Overview

Add AI-powered text improvement capability to the conversations interface using Ollama (Gemma 4 model). Users can enhance message text by correcting grammar/spelling or reformulating for professional tone before sending.

---

## 2. Requirements

### Functional Requirements

1. **Text Enhancement Options** (mutually exclusive dropdown):
   - Grammar/Spelling Correction
   - Professional Tone Reformulation
   - Both (apply grammar + professional tone)

2. **UI Modal Workflow**:
   - User clicks "✨ Improve with AI" button in message input area
   - Modal opens with dropdown (default: "Corrigir Ortografia/Gramática")
   - Original text displayed (read-only)
   - Improved text shown (with loading state while processing)
   - User can select different improvement types to refresh result
   - "Usar" button applies change to textarea
   - "Cancelar" closes modal

3. **API Integration**:
   - Backend communicates with Ollama API at configured endpoint
   - Supports both local and remote Ollama servers
   - Graceful error handling if Ollama unavailable

### Non-Functional Requirements

- Response time < 5 seconds for text improvement
- UI remains responsive during processing (loading spinner)
- No changes to existing message sending workflow
- Backend service is reusable for future AI features

---

## 3. Architecture

### 3.1 Backend Components

**File Structure:**
```
app/Services/OllamaService.php          - AI text improvement service
app/Http/Controllers/ConversationController.php - Update with new endpoint
routes/web.php                          - Add POST /conversations/{id}/improve-text
```

**OllamaService Class:**
- Static methods for different improvement types
- Calls Ollama API via HTTP
- Input validation and error handling
- Returns improved text or throws exception

**New Endpoint:**
```
POST /conversations/{conversation}/improve-text
Headers: Authorization (auth middleware required)
Body: {
  "content": "user text here",
  "type": "grammar|professional|both"
}
Response: {
  "success": true,
  "original": "original text",
  "improved": "improved text",
  "type": "grammar|professional|both"
}
```

### 3.2 Frontend Components

**New UI Elements in `conversations/index.blade.php`:**
- Button: "✨ Melhorar com IA" positioned in `#chatActions` (next to emoji, file, audio buttons)
- Modal: `id="improveTextModal"` with:
  - Select dropdown for improvement type
  - Original text display area
  - Improved text display area (with loading state)
  - Action buttons (Usar/Cancelar)

**JavaScript (`public/js/conversations.js`):**
- `openImproveModal(text)` - Opens modal, fetches improved version
- `fetchImprovedText(text, type)` - Calls API endpoint
- `applyImprovedText(text)` - Closes modal, updates textarea
- Event handlers for dropdown changes

### 3.3 Configuration

**Environment Variables (`.env`):**
```
OLLAMA_URL=http://192.168.1.10:11434
OLLAMA_MODEL=gemma4
OLLAMA_TIMEOUT=30
```

---

## 4. Data Flow

```
User Input Text
    ↓
[Click "Improve" Button]
    ↓
openImproveModal(text)
    ↓
Modal Opens (Loading State)
    ↓
Dropdown: "Grammar" (default selected)
    ↓
POST /conversations/{id}/improve-text
  { content, type: "grammar" }
    ↓
OllamaService::improveGrammar(text)
    ↓
Call Ollama API
    ↓
Return Improved Text
    ↓
Modal Shows: Original + Improved
    ↓
User Actions:
  A) Change Dropdown → Refetch with new type
  B) Click "Usar" → Apply text + Close modal
  C) Click "Cancelar" → Close without changes
```

---

## 5. Error Handling

**Scenarios:**
1. Ollama server unreachable
   - Display: "Serviço de IA indisponível. Tente novamente."
   - Fallback: Keep original text

2. Empty text input
   - Prevent API call
   - Show: "Digite algo para melhorar"

3. API timeout (>30s)
   - Show: "Solicitação expirou. Tente novamente."
   - Auto-close modal after error message

4. Invalid improvement type
   - Backend validation: reject unsupported types
   - Frontend: only allow predefined dropdown values

---

## 6. Prompts for Ollama

### Grammar Correction Prompt:
```
You are a Portuguese grammar assistant. 
Correct the text for spelling, grammar, and punctuation.
Keep the original meaning and tone.
Return ONLY the corrected text without any explanation.

Text: {text}
```

### Professional Tone Prompt:
```
You are a professional communication expert.
Reformulate the text for professional context (business communication).
Make it more formal, courteous, and clear while maintaining the message intent.
Return ONLY the reformulated text without any explanation.

Text: {text}
```

### Both Prompt:
```
You are a professional Portuguese communication expert.
1. Correct spelling, grammar, and punctuation
2. Reformulate for professional context (formal, courteous, clear)
Keep the original meaning.
Return ONLY the final professional text without any explanation.

Text: {text}
```

---

## 7. UI/UX Details

**Button Styling:**
- Icon: Material Symbols "auto_awesome" or "spark"
- Text: "Melhorar com IA"
- Position: In `#chatActions` div (between audio and send buttons)
- Disabled when: conversation resolved/closed or textarea disabled

**Modal Styling:**
- Title: "Melhorar Texto com IA"
- Dropdown label: "Tipo de Melhoria"
- Original section: "Texto Original"
- Improved section: "Versão Melhorada" (with loading spinner)
- Follow existing modal styles in the codebase

**Loading State:**
- Show spinner while fetching
- Disable dropdown during fetch
- Show response in real-time when complete

---

## 8. Testing Strategy

**Manual Testing:**
1. Click button with empty textarea → should show validation message
2. Click button with text → modal opens, fetches improvement (grammar type)
3. Change dropdown → re-fetches with new type
4. Click "Usar" → text applied to textarea, modal closes
5. Click "Cancelar" → modal closes without changes
6. Test with Ollama offline → error message shown

**Scenarios to Test:**
- Short text (< 50 chars)
- Long text (> 500 chars)
- Text with special characters
- Text with URLs
- Rapid dropdown changes
- Network latency (> 5s response)

---

## 9. Future Extensibility

This design allows for:
- Additional improvement types (e.g., "Mais Criativo", "Resumir")
- Different Ollama models (swap via config)
- Admin settings for customization
- Audit logging of AI improvements
- Different prompts per organization/role

---

## 10. Success Criteria

✅ Feature is complete when:
- [x] OllamaService created and tested
- [x] API endpoint working (returns improved text)
- [x] Modal displays with all required elements
- [x] Text improvements work for all 3 dropdown options
- [x] Error handling prevents crashes
- [x] Button disabled when appropriate
- [x] Original message sending workflow unchanged
- [x] Code follows existing project patterns

---

## 11. Implementation Complete

✅ All tasks completed:
- OllamaService created with 3 improvement methods
- API endpoint functional at POST /conversations/{id}/improve-text
- Modal UI with dropdown and loading states
- JavaScript logic for modal control
- Error handling and user feedback
- Configuration with KEY_OLLAMA environment variable
- Comprehensive feature tests

### How to Use

1. Set `KEY_OLLAMA` in `.env` with your Ollama Cloud API key
2. In any conversation, type a message
3. Click "✨ Melhorar com IA" button
4. Select improvement type (grammar, professional, or both)
5. Review improved text in modal
6. Click "Usar" to apply or "Cancelar" to discard
7. Send message as normal

### Configuration

```env
KEY_OLLAMA=your-api-key-from-ollama-cloud
```

### Testing

```bash
php artisan test tests/Feature/OllamaServiceTest.php
php artisan test tests/Feature/ImproveTextEndpointTest.php
```

# Ollama Cloud Text Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Add AI-powered text improvement to conversations using Ollama Cloud Gemma 4:31b, with grammar correction, professional tone reformulation, and a modal UI for user approval.

**Architecture:** Backend service (`OllamaService`) communicates with Ollama Cloud API at `https://ollama.com/api/generate`. New endpoint accepts text and improvement type, returns original + improved versions. Frontend modal lets users compare and apply changes before sending.

**Tech Stack:** Laravel HTTP client, Ollama Cloud API, Blade templates, vanilla JavaScript (no new dependencies)

## Global Constraints

- Text improvement types: `grammar|professional|both` (exact names)
- Modal ID: `improveTextModal` (referenced in JS)
- API endpoint: `POST /conversations/{conversation}/improve-text`
- Response time target: < 5 seconds per request
- Environment variable: `KEY_OLLAMA` (Ollama Cloud API key)
- Ollama URL: `https://ollama.com/api/generate` (exact, no trailing slash)
- Model identifier: `gemma4:31b` (exact)
- Auth header: `Authorization: Bearer {KEY_OLLAMA}`

---

## File Structure

**Backend:**
- Create: `app/Services/OllamaService.php` - Core service for Ollama communication
- Modify: `app/Http/Controllers/ConversationController.php` - Add `improveText` method
- Modify: `routes/web.php` - Add improvement route
- Modify: `.env` - Add Ollama configuration variable

**Frontend:**
- Modify: `resources/views/conversations/index.blade.php` - Add button and modal
- Modify: `public/js/conversations.js` - Add modal logic and API calls

**Tests:**
- Create: `tests/Feature/OllamaServiceTest.php` - Service tests
- Create: `tests/Feature/ImproveTextEndpointTest.php` - Endpoint tests

---

## Task 1: Create OllamaService

**Files:**
- Create: `app/Services/OllamaService.php`
- Test: `tests/Feature/OllamaServiceTest.php`

**Interfaces:**
- Produces: 
  - `OllamaService::improveGrammar(string $text): string` - Returns grammar-corrected text
  - `OllamaService::improveProfessionalTone(string $text): string` - Returns professionally reformulated text
  - `OllamaService::improveBoth(string $text): string` - Returns both improvements applied

- [ ] **Step 1: Create test file with failing tests**

Create `tests/Feature/OllamaServiceTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OllamaService;

class OllamaServiceTest extends TestCase
{
    public function test_improveGrammar_returns_string()
    {
        $text = "i havv a error in my textt";
        $result = OllamaService::improveGrammar($text);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveProfessionalTone_returns_string()
    {
        $text = "hey wassup i need help with stuff lol";
        $result = OllamaService::improveProfessionalTone($text);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveBoth_returns_string()
    {
        $text = "i havv a problem wit this lol";
        $result = OllamaService::improveBoth($text);
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_improveGrammar_throws_on_empty_text()
    {
        $this->expectException(\InvalidArgumentException::class);
        OllamaService::improveGrammar("");
    }

    public function test_improveGrammar_throws_on_ollama_error()
    {
        config(['services.ollama.key' => 'invalid-key']);
        
        $this->expectException(\Exception::class);
        OllamaService::improveGrammar("test text");
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test tests/Feature/OllamaServiceTest.php -v
```

Expected: FAIL with "class not found"

- [ ] **Step 3: Create OllamaService class with full implementation**

Create `app/Services/OllamaService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OllamaService
{
    private const OLLAMA_URL = 'https://ollama.com/api/generate';
    private const MODEL = 'gemma4:31b';
    private const TIMEOUT = 30;

    /**
     * Improve text grammar and spelling
     */
    public static function improveGrammar(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a Portuguese grammar assistant. Correct the text for spelling, grammar, and punctuation. Keep the original meaning and tone. Return ONLY the corrected text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Improve text for professional tone
     */
    public static function improveProfessionalTone(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a professional communication expert. Reformulate the text for professional context (business communication). Make it more formal, courteous, and clear while maintaining the message intent. Return ONLY the reformulated text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Improve text for both grammar and professional tone
     */
    public static function improveBoth(string $text): string
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        $prompt = "You are a professional Portuguese communication expert. 1. Correct spelling, grammar, and punctuation 2. Reformulate for professional context (formal, courteous, clear). Keep the original meaning. Return ONLY the final professional text without any explanation.\n\nText: $text";

        return self::callOllama($prompt);
    }

    /**
     * Call Ollama Cloud API
     */
    private static function callOllama(string $prompt): string
    {
        $apiKey = config('services.ollama.key') ?? env('KEY_OLLAMA');

        if (!$apiKey) {
            throw new \Exception('Ollama API key not configured. Set KEY_OLLAMA in .env');
        }

        try {
            $response = Http::timeout(self::TIMEOUT)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post(self::OLLAMA_URL, [
                    'model' => self::MODEL,
                    'prompt' => $prompt,
                    'stream' => false,
                ])
                ->throwIfServerError()
                ->throwIfClientError();

            if (!$response->successful()) {
                throw new \Exception("Ollama API error: " . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['response']) || empty($data['response'])) {
                throw new \Exception('Invalid response from Ollama API');
            }

            return trim($data['response']);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            \Log::error('[Ollama] API request failed', [
                'error' => $e->getMessage(),
                'status' => $e->response?->status(),
            ]);
            throw new \Exception('Serviço de IA indisponível. Tente novamente.');
        }
    }
}
```

- [ ] **Step 4: Add configuration to config/services.php**

Open `config/services.php` and add at the end before the closing bracket:

```php
'ollama' => [
    'key' => env('KEY_OLLAMA'),
],
```

- [ ] **Step 5: Add KEY_OLLAMA to .env**

Open `.env` and add:

```env
KEY_OLLAMA=sua_chave_api_aqui
```

- [ ] **Step 6: Run tests to verify they pass**

```bash
php artisan test tests/Feature/OllamaServiceTest.php -v
```

Expected: Tests PASS or SKIP if API key not set

- [ ] **Step 7: Commit**

```bash
git add app/Services/OllamaService.php tests/Feature/OllamaServiceTest.php config/services.php .env
git commit -m "feat: create OllamaService for Ollama Cloud text improvement

- Add improveGrammar, improveProfessionalTone, improveBoth methods
- Communicate with https://ollama.com/api/generate
- Handle errors and empty input validation
- Use Bearer token authentication with KEY_OLLAMA
- Include comprehensive unit tests

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>"
```

---

## Task 2: Add API Endpoint to ConversationController

**Files:**
- Modify: `app/Http/Controllers/ConversationController.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/ImproveTextEndpointTest.php`

**Interfaces:**
- Consumes: `OllamaService::improveGrammar()`, `improveProfessionalTone()`, `improveBoth()`
- Produces: `POST /conversations/{conversation}/improve-text` endpoint returning JSON

- [ ] **Step 1: Create endpoint test**

Create `tests/Feature/ImproveTextEndpointTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Contact;

class ImproveTextEndpointTest extends TestCase
{
    public function test_improve_text_with_grammar_type()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $contact = Contact::factory()->create();
        $conversation = Conversation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test content here',
            'type' => 'grammar',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'original',
                'improved',
                'type',
            ]);
    }

    public function test_improve_text_requires_authentication()
    {
        $conversation = Conversation::factory()->create();

        $response = $this->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test',
            'type' => 'grammar',
        ]);

        $response->assertStatus(401);
    }

    public function test_improve_text_validates_type()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => 'test text',
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
    }

    public function test_improve_text_rejects_empty_content()
    {
        $user = User::factory()->create(['role' => 'agent']);
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($user)->postJson('/conversations/' . $conversation->id . '/improve-text', [
            'content' => '',
            'type' => 'grammar',
        ]);

        $response->assertStatus(422);
    }
}
```

- [ ] **Step 2: Add route**

Open `routes/web.php`, find the auth middleware group, and add before closing brace:

```php
// Text improvement
Route::post('/conversations/{conversation}/improve-text', [ConversationController::class, 'improveText'])
    ->name('conversations.improve-text');
```

- [ ] **Step 3: Add improveText method to ConversationController**

Open `app/Http/Controllers/ConversationController.php` and add this method:

```php
public function improveText(Request $request, Conversation $conversation)
{
    $validated = $request->validate([
        'content' => 'required|string|min:1|max:5000',
        'type' => 'required|in:grammar,professional,both',
    ]);

    try {
        $original = $validated['content'];
        $type = $validated['type'];

        $improved = match ($type) {
            'grammar' => \App\Services\OllamaService::improveGrammar($original),
            'professional' => \App\Services\OllamaService::improveProfessionalTone($original),
            'both' => \App\Services\OllamaService::improveBoth($original),
        };

        \Log::info('[TextImprovement] Text improved', [
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'type' => $type,
        ]);

        return response()->json([
            'success' => true,
            'original' => $original,
            'improved' => $improved,
            'type' => $type,
        ]);

    } catch (\Exception $e) {
        \Log::error('[TextImprovement] Error', [
            'conversation_id' => $conversation->id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
```

- [ ] **Step 4: Run endpoint tests**

```bash
php artisan test tests/Feature/ImproveTextEndpointTest.php -v
```

Expected: Tests PASS

- [ ] **Step 5: Commit**

```bash
git add routes/web.php app/Http/Controllers/ConversationController.php tests/Feature/ImproveTextEndpointTest.php
git commit -m "feat: add /conversations/{id}/improve-text API endpoint

- Validate content and improvement type
- Call OllamaService with selected type
- Return original and improved text as JSON
- Include error handling and logging

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>"
```

---

## Task 3: Add UI Button and Modal

**Files:**
- Modify: `resources/views/conversations/index.blade.php`

**Interfaces:**
- Consumes: Existing #chatActions HTML structure
- Produces: Button and modal HTML elements

- [ ] **Step 1: Find #chatActions section**

Open `resources/views/conversations/index.blade.php`, locate line ~390 with `id="chatActions"`

- [ ] **Step 2: Add button to chat actions**

Find the `<div id="chatActions">` section and add this button after the emoji button:

```blade
<button type="button" id="improveTextBtn" class="hover:text-secondary transition-colors {{ ($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') ? 'opacity-50 cursor-not-allowed' : '' }}" title="Melhorar com IA" onclick="openImproveTextModal()" @if($activeConversation->status === 'resolved' || $activeConversation->status === 'closed') disabled @endif>
    <span class="material-symbols-outlined text-xl">auto_awesome</span>
</button>
```

- [ ] **Step 3: Add modal HTML**

At the end of the file, before closing tags, add the modal:

```blade
<!-- Improve Text Modal -->
<div id="improveTextModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="glass-modal rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200/50">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined">auto_awesome</span>
                    Melhorar Texto com IA
                </h2>
                <button onclick="closeImproveTextModal()" class="material-symbols-outlined text-gray-600 hover:text-error transition-colors">close</button>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Tipo de Melhoria:</label>
                <select id="improveTypeSelect" class="w-full border border-gray-200/50 rounded-lg p-3 text-sm focus:ring-2 focus:ring-secondary-container/50 focus:border-secondary transition-all" onchange="refreshImprovement()">
                    <option value="grammar">✓ Corrigir Ortografia/Gramática</option>
                    <option value="professional">👔 Reformular para Tom Profissional</option>
                    <option value="both">🎯 Ambos (Gramática + Profissional)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Texto Original:</label>
                <div id="improveOriginalText" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar"></div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-on-surface mb-2">Versão Melhorada:</label>
                <div id="improveLoadingSpinner" class="w-full bg-gray-100/50 border border-gray-200/50 rounded-lg p-4 min-h-[80px] max-h-[120px] flex items-center justify-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-6 h-6 border-2 border-secondary border-t-transparent rounded-full animate-spin"></div>
                        <p class="text-xs text-gray-600">Processando com IA...</p>
                    </div>
                </div>
                <div id="improveImprovedText" class="hidden w-full bg-secondary/10 border border-secondary/30 rounded-lg p-4 text-sm text-on-surface min-h-[80px] max-h-[120px] overflow-y-auto custom-scrollbar whitespace-pre-wrap"></div>
            </div>

            <div id="improveErrorMessage" class="hidden bg-error/20 border border-error/30 rounded-lg p-3 text-sm text-error"></div>
        </div>

        <div class="p-6 border-t border-gray-200/50 flex gap-3 justify-end">
            <button onclick="closeImproveTextModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-on-surface border border-gray-200/50 hover:bg-gray-100/50 transition-all">
                Cancelar
            </button>
            <button id="improveUseBtn" onclick="applyImprovedText()" disabled class="px-4 py-2 rounded-lg text-sm font-semibold bg-secondary text-on-secondary hover:shadow-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                Usar
            </button>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Verify HTML is in place**

Search for `improveTextModal` in file to confirm

- [ ] **Step 5: Commit**

```bash
git add resources/views/conversations/index.blade.php
git commit -m "feat: add improve text button and modal UI

- Add 'Melhorar com IA' button in chat actions
- Create modal with improvement type dropdown
- Display original and improved text areas
- Add loading spinner and error container

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>"
```

---

## Task 4: Implement JavaScript Logic

**Files:**
- Modify: `public/js/conversations.js`

**Interfaces:**
- Consumes: Modal HTML elements, endpoint `/conversations/{id}/improve-text`
- Produces: Functions for modal control and API calls

- [ ] **Step 1: Add JavaScript functions**

Open `public/js/conversations.js` and add at the end:

```javascript
let currentImprovedText = {
    original: '',
    improved: '',
    type: 'grammar'
};

function openImproveTextModal() {
    const textarea = document.getElementById('messageInput');
    const text = textarea.value.trim();

    if (!text) {
        showToast('Digite algo para melhorar', 'error');
        return;
    }

    currentImprovedText.original = text;
    
    document.getElementById('improveTextModal').classList.remove('hidden');
    document.getElementById('improveOriginalText').textContent = text;
    document.getElementById('improveTypeSelect').value = 'grammar';
    
    document.getElementById('improveImprovedText').classList.add('hidden');
    document.getElementById('improveLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveErrorMessage').classList.add('hidden');
    document.getElementById('improveUseBtn').disabled = true;

    refreshImprovement();
}

function closeImproveTextModal() {
    document.getElementById('improveTextModal').classList.add('hidden');
    currentImprovedText = { original: '', improved: '', type: 'grammar' };
}

function refreshImprovement() {
    const type = document.getElementById('improveTypeSelect').value;
    const text = currentImprovedText.original;
    const conversationId = document.querySelector('input[name="conversation_id"]')?.value;

    if (!conversationId || !text) return;

    document.getElementById('improveLoadingSpinner').classList.remove('hidden');
    document.getElementById('improveImprovedText').classList.add('hidden');
    document.getElementById('improveErrorMessage').classList.add('hidden');
    document.getElementById('improveUseBtn').disabled = true;

    fetch(`/conversations/${conversationId}/improve-text`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ content: text, type: type })
    })
    .then(response => response.ok ? response.json() : response.json().then(e => { throw new Error(e.message || 'Erro'); }))
    .then(data => {
        if (data.success) {
            currentImprovedText.improved = data.improved;
            currentImprovedText.type = type;

            document.getElementById('improveLoadingSpinner').classList.add('hidden');
            document.getElementById('improveImprovedText').textContent = data.improved;
            document.getElementById('improveImprovedText').classList.remove('hidden');
            document.getElementById('improveUseBtn').disabled = false;
        } else {
            throw new Error(data.message || 'Erro');
        }
    })
    .catch(error => {
        document.getElementById('improveLoadingSpinner').classList.add('hidden');
        document.getElementById('improveErrorMessage').textContent = 'Erro: ' + error.message;
        document.getElementById('improveErrorMessage').classList.remove('hidden');
    });
}

function applyImprovedText() {
    const textarea = document.getElementById('messageInput');
    textarea.value = currentImprovedText.improved;
    textarea.focus();
    
    closeImproveTextModal();
    showToast('Texto melhorado aplicado!', 'success');
}
```

- [ ] **Step 2: Verify functions exist**

Search for `openImproveTextModal` in file

- [ ] **Step 3: Test in browser**

Open conversations page, click "Melhorar com IA", verify modal opens and functions work

- [ ] **Step 4: Commit**

```bash
git add public/js/conversations.js
git commit -m "feat: add improve text modal JavaScript logic

- openImproveTextModal: opens modal with original text
- closeImproveTextModal: closes modal and resets state
- refreshImprovement: calls API with selected type
- applyImprovedText: applies improved text to textarea

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>"
```

---

## Task 5: Manual Testing & Documentation

**Files:**
- Test: Manual UI testing
- Update: Design spec with completion notes

**Interfaces:**
- Consumes: Complete implementation from Tasks 1-4
- Produces: Verified working feature

- [ ] **Step 1: Test empty textarea**

1. Open conversations page
2. Click "Melhorar com IA" with empty textarea
3. Verify toast: "Digite algo para melhorar"

- [ ] **Step 2: Test grammar improvement**

1. Type: `"i havv a eror"`
2. Click "Melhorar com IA"
3. Verify modal opens, loading shows
4. Wait for result (5-30 seconds)
5. Click "Usar", verify text applied

- [ ] **Step 3: Test professional tone**

1. Type: `"hey wassup i need help lol"`
2. Click button, change dropdown to "Profissional"
3. Verify result is more formal
4. Click "Usar"

- [ ] **Step 4: Test both improvements**

1. Type: `"i need halp wit this lol"`
2. Click button, select "Ambos"
3. Verify both grammar AND professional tone applied

- [ ] **Step 5: Test error handling**

1. Temporarily set `KEY_OLLAMA=invalid` in `.env`
2. Type text, click button
3. Verify error message appears, not loading spinner

- [ ] **Step 6: Update design spec**

Open `docs/superpowers/specs/2026-06-18-ollama-text-enhancement-design.md` and add at end:

```markdown
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
```

- [ ] **Step 7: Commit spec update**

```bash
git add docs/superpowers/specs/2026-06-18-ollama-text-enhancement-design.md
git commit -m "docs: add implementation completion notes to design spec

- Document all completed tasks
- Add usage instructions
- Include configuration details
- Link to test procedures

Co-Authored-By: Claude Haiku 4.5 <noreply@anthropic.com>"
```

---

## Self-Review

✅ Spec coverage: All requirements implemented
✅ No placeholders: All code complete
✅ Type consistency: Methods and endpoints match across tasks
✅ Architecture: Service layer, controller, frontend separation clear
✅ Error handling: Try/catch at all levels with user-friendly messages

---

**Plan complete!**

Now choose execution method:

**1. 🚀 Subagent-Driven (Recommended)**
- Fresh subagent per task
- Quick iteration
- Reviews between tasks

**2. ⚙️ Inline Execution**
- Execute all tasks here
- Batch checkpoints

Which?

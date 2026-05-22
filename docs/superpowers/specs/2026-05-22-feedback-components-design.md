# Design Spec: Sistema de Feedback + Biblioteca de Componentes Reutilizáveis

**Data:** 22 de maio de 2026  
**Versão:** 1.0  
**Status:** Aprovado para implementação

---

## 1. Visão Geral

Criar um **sistema de feedback unificado** + **biblioteca de componentes Blade reutilizáveis** que:
- Funcione em todas as páginas do app (Dashboard, Chats, Contatos, Macros)
- Seja fácil de usar para devs (helpers simples)
- Seja consistente visualmente (Material Design 3 + logo)
- Tenha documentação pública (Storybook local)

### Objetivos

1. **Feedback completo:** Capturar tanto feedback do usuário (ações completadas) quanto notificações do sistema (erros, avisos)
2. **Componentes core:** 15-20 componentes reutilizáveis em Blade
3. **Consistência visual:** Paleta integrada, uso de logo, animações suaves
4. **Documentação:** Storybook local acessível publicamente

---

## 2. Arquitetura

### 2.1 Estrutura de Pastas

```
resources/
├── views/
│   ├── components/
│   │   ├── common/
│   │   │   ├── button.blade.php
│   │   │   ├── input.blade.php
│   │   │   ├── select.blade.php
│   │   │   ├── textarea.blade.php
│   │   │   ├── checkbox.blade.php
│   │   │   └── radio.blade.php
│   │   ├── layout/
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── tabs.blade.php
│   │   │   ├── accordion.blade.php
│   │   │   ├── breadcrumb.blade.php
│   │   │   └── divider.blade.php
│   │   ├── feedback/
│   │   │   ├── alert.blade.php
│   │   │   ├── toast.blade.php
│   │   │   ├── badge.blade.php
│   │   │   ├── progress.blade.php
│   │   │   └── spinner.blade.php
│   │   └── bonus/
│   │       ├── avatar.blade.php
│   │       ├── chip.blade.php
│   │       └── dropdown.blade.php
│   ├── documentation/
│   │   ├── components.blade.php
│   │   ├── getting-started.blade.php
│   │   ├── colors.blade.php
│   │   └── changelog.blade.php
│   └── layouts/
│       └── app.blade.php
├── css/
│   ├── components.css       # Estilos dos componentes
│   └── app.css
└── js/
    ├── feedback-system.js   # Sistema de feedback global
    └── app.js
```

### 2.2 Como Componentes se Comunicam

```
Usuário interage
    ↓
Componente Blade renderiza HTML
    ↓
JS listeners (data attributes)
    ↓
Feedback system JS (window.Feedback)
    ↓
Toast/Modal/Alert renderiza
    ↓
Usuário vê feedback
```

---

## 3. Componentes Core (15-20)

### 3.1 Básicos (5)

#### Button
- Props: `variant` (primary, secondary, danger, tertiary, outline), `size` (sm, md, lg), `icon`, `disabled`, `loading`
- Exemplo: `<x-button variant="primary" size="md" icon="check">Confirmar</x-button>`

#### Input
- Props: `type` (text, email, password), `name`, `placeholder`, `error`, `disabled`, `required`
- Suporta: Focus states, error inline, icon prefix/suffix
- Exemplo: `<x-input type="email" name="email" placeholder="seu@email.com" :error="$errors->first('email')" />`

#### Select
- Props: `name`, `options` (array), `placeholder`, `error`, `multiple`, `searchable`
- Exemplo: `<x-select name="category" :options="$categories" placeholder="Escolha..." />`

#### Textarea
- Props: `name`, `placeholder`, `rows`, `maxlength`, `error`, `disabled`
- Suporta: Contador de caracteres, resize desabilitado
- Exemplo: `<x-textarea name="message" placeholder="Digite..." maxlength="500" />`

#### Checkbox / Radio
- Props: `name`, `value`, `label`, `disabled`, `checked`
- Exemplo: `<x-checkbox name="terms" label="Concordo com os termos" />`

### 3.2 Layout (6)

#### Card
- Props: `variant` (elevated, outline), `padding`, `hover`
- Wrapper genérico para conteúdo
- Exemplo: `<x-card><p>Conteúdo</p></x-card>`

#### Modal
- Props: `title`, `size` (sm, md, lg), `closeButton`, `footer`
- Slots: `header`, `body`, `footer`
- Exemplo: `<x-modal title="Confirmar"><x-slot:body>Tem certeza?</x-slot:body></x-modal>`

#### Tabs
- Props: `tabs` (array com label/content), `defaultActive`
- Exemplo: `<x-tabs :tabs="[['label' => 'Tab 1', 'content' => '...'], ...]" />`

#### Accordion
- Props: `items` (array), `allowMultiple`
- Exemplo: `<x-accordion :items="[['title' => 'Título', 'content' => '...'], ...]" />`

#### Breadcrumb
- Props: `items` (array com label/url)
- Exemplo: `<x-breadcrumb :items="[['label' => 'Home', 'url' => '/'], ...]" />`

#### Divider
- Props: `direction` (horizontal, vertical), `margin`
- Exemplo: `<x-divider direction="horizontal" />`

### 3.3 Feedback (5)

#### Alert
- Props: `type` (info, success, warning, error), `title`, `dismissible`
- Exemplo: `<x-alert type="success" title="Sucesso!">Operação concluída</x-alert>`

#### Toast (JS)
- Função JS global: `Feedback.success/error/warning/info(message, duration)`
- Renderiza flutuante no canto inferior direito
- Auto-desaparece após duration

#### Badge
- Props: `type` (primary, secondary, success, error), `size` (sm, md)
- Exemplo: `<x-badge type="success">Ativo</x-badge>`

#### Progress
- Props: `percentage`, `color` (primary, success, warning, error), `label`
- Exemplo: `<x-progress percentage="65" label="Progresso" />`

#### Spinner
- Props: `size` (sm, md, lg), `color`
- Exemplo: `<x-spinner size="md" />`

### 3.4 Bonus (3)

#### Avatar
- Props: `initials`, `url`, `size` (sm, md, lg)
- Fallback para iniciais se URL não existir
- Exemplo: `<x-avatar initials="JS" size="md" />`

#### Chip
- Props: `label`, `removable`, `onRemove` (JS callback)
- Exemplo: `<x-chip label="Tag 1" :removable="true" />`

#### Dropdown
- Props: `trigger` (button label), `items` (array com label/action)
- Exemplo: `<x-dropdown trigger="Ações" :items="[['label' => 'Editar', 'action' => 'edit()'], ...]" />`

---

## 4. Sistema de Feedback Unificado

### 4.1 Interface JS Global

```javascript
window.Feedback = {
  // Toast que desaparece automaticamente
  success(message, duration = 3000),
  error(message, duration = 5000),
  warning(message, duration = 4000),
  info(message, duration = 3000),
  
  // Modal que requer interação
  confirm(title, message, onConfirm, onCancel),
  alert(title, message),
}
```

### 4.2 Interface PHP

```php
// Em controller
return redirect()->with('success', 'Ação concluída!');
return redirect()->with('error', 'Erro ao processar');

// Helper
feedback()->success('mensagem');
feedback()->error('mensagem');
```

### 4.3 Trigger por Contexto

| Ação | Tipo | Formato | Exemplo |
|------|------|---------|---------|
| Mensagem enviada | success | Toast 3s | "Mensagem enviada" |
| Upload concluído | success | Toast 3s | "Arquivo salvo" |
| Claim realizado | success | Toast 3s | "Conversa clamada" |
| Validação falhou | error | Toast 5s | "Email inválido" |
| Ação bloqueada | error | Toast 5s | "Sem permissão" |
| Será deletado | warning | Modal | "Tem certeza?" |
| Claim expirando | warning | Alert inline | "Claim expira em 2min" |
| Info contextual | info | Alert inline | "Você é o único claim" |
| Ação crítica | confirm | Modal 2 botões | "Deletar conversa?" |

### 4.4 Implementação em AJAX

```javascript
// Em qualquer página com fetch/axios
fetch('/conversations/1/claim', {method: 'POST'})
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      Feedback.success(data.message); // "Conversa clamada!"
      // opcional: reload após 500ms
      setTimeout(() => location.reload(), 500);
    } else {
      Feedback.error(data.message);   // "Já foi clamado por João"
    }
  })
  .catch(err => Feedback.error('Erro de conexão'));
```

---

## 5. Visual Identity

### 5.1 Paleta de Cores

**Cores já existentes (Material Design 3):**
- Primary: `#000000` (preto)
- Secondary: `#006d2f` (verde)
- Error: `#ba1a1a` (vermelho)
- Tertiary: `#000000`

**Novas cores feedback:**
- Success: `#006d2f` (verde secondary)
- Warning: `#FFC300` (amarelo da logo)
- Info: `#131b2e` (azul da logo)
- Loading: `#001E50` (azul escuro da logo)

### 5.2 Logo Integration

Usar em:
- Modal de confirmação (header com logo)
- Alert crítico (lado esquerdo com logo)
- Toast de sucesso (ícone checkmark com amarelo)
- Página de documentação (topo)

### 5.3 Tipografia

- Font: Inter (já carregada)
- Sizes: sm (12px), base (14px), md (16px), lg (18px), xl (20px)
- Weight: 400 (regular), 500 (medium), 600 (semibold), 700 (bold)

### 5.4 Animações

- Transitions: `transition-all duration-200` (padrão)
- Hover: Scale 1.05 em botões
- Loading: Spinner com rotação contínua
- Toast: Slide-in-up, fade-out

### 5.5 Espaçamento

- Base: 4px (Tailwind)
- Scales: 1x (4px), 2x (8px), 3x (12px), 4x (16px), 6x (24px)
- Padding componentes: 12px (sm), 16px (md), 20px (lg)

---

## 6. Documentação - Storybook Local

### 6.1 Estrutura

Rota: `/docs/components` (pública, sem auth)

Páginas:
1. **components.blade.php** - Hub principal
   - Logo no topo
   - Grid com cards por componente
   - Cada card tem: preview, props, código (copy), variações

2. **getting-started.blade.php**
   - Como usar componentes em views
   - Exemplos de feedback system
   - Convenções

3. **colors.blade.php**
   - Paleta completa com hex codes
   - Uso recomendado por tipo
   - Acessibilidade (contrast ratios)

4. **changelog.blade.php**
   - Histórico de adições/mudanças
   - Versão atual

### 6.2 Exemplo de Card de Componente

```
┌─ Button ──────────────────────────────────┐
│                                           │
│  Preview:                                 │
│  [Primary] [Secondary] [Danger]           │
│  [Outline] [Disabled]                     │
│                                           │
│  Props:                                   │
│  - variant: primary|secondary|danger|...  │
│  - size: sm|md|lg                         │
│  - icon: string (Material symbol)         │
│  - disabled: boolean                      │
│  - loading: boolean                       │
│                                           │
│  [Copy Code] ← button to copy             │
│  <x-button variant="primary">...</x-button>
│                                           │
└───────────────────────────────────────────┘
```

### 6.3 Controller

```php
// DocumentationController
public function components() {
  return view('documentation.components', [
    'components' => [
      'button' => [...],
      'input' => [...],
      // ... todos os componentes
    ]
  ]);
}
```

---

## 7. Data Flow & Error Handling

### 7.1 Happy Path (Sucesso)

```
Usuário clica "Enviar mensagem"
    ↓
Button desabilitado + spinner
    ↓
fetch POST /conversations/1/send
    ↓
Response: {success: true, message: "Mensagem enviada"}
    ↓
Feedback.success("Mensagem enviada")
    ↓
Toast aparece 3s e desaparece
    ↓
UI atualiza (novo message no chat)
```

### 7.2 Error Handling

| Cenário | Status | Feedback | Ação Usuário |
|---------|--------|----------|---|
| Validação falhou | 422 | Toast error (5s) | Corrige e resubmete |
| Sem permissão | 403 | Toast error + Modal info | Fazer claim primeiro |
| Erro servidor | 500 | Toast error | Contata admin |
| Timeout | 0 | Toast warning | Retry manual |
| Conflito (claim já existe) | 422 | Toast error com nome | Nenhuma ação |

### 7.3 Exemplo Completo (Claim Conversation)

```javascript
// Em conversations/index.blade.php
async function claimConversation(conversationId) {
  const btn = document.querySelector('[data-claim-btn]');
  btn.disabled = true;
  btn.innerHTML = '<x-spinner size="sm" />';

  try {
    const response = await fetch(`/conversations/${conversationId}/claim`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
      },
    });
    
    const data = await response.json();
    
    if (data.success) {
      Feedback.success(data.message);
      setTimeout(() => location.reload(), 500);
    } else {
      Feedback.error(data.message);
      btn.disabled = false;
      btn.innerHTML = 'Clamar';
    }
  } catch (error) {
    Feedback.error('Erro de conexão. Tente novamente.');
    btn.disabled = false;
    btn.innerHTML = 'Clamar';
  }
}
```

---

## 8. Testing Strategy

### 8.1 Unit Tests (Laravel)

```php
// Tests/Unit/ComponentTest.php
test('button renders with primary variant', function() {
  $html = Blade::render('<x-button variant="primary">Click</x-button>');
  expect($html)->toContain('bg-primary');
});

test('input renders with error state', function() {
  $html = Blade::render('<x-input name="email" error="Email inválido" />');
  expect($html)->toContain('border-error');
  expect($html)->toContain('Email inválido');
});
```

### 8.2 Integration Tests

```php
// Tests/Feature/FeedbackTest.php
test('claim conversation shows success feedback', function() {
  $user = User::factory()->create();
  $conversation = Conversation::factory()->create();
  
  $this->actingAs($user)
    ->post("/conversations/{$conversation->id}/claim")
    ->assertSessionHas('success');
});

test('claim fails without permission', function() {
  $user = User::factory()->agent()->create();
  $conversation = Conversation::factory()->assigned()->create();
  
  $this->actingAs($user)
    ->post("/conversations/{$conversation->id}/claim")
    ->assertJsonValidationErrors();
});
```

### 8.3 Manual Checklist

- [ ] Componente renderiza corretamente em todos os breakpoints
- [ ] Acessibilidade: navegável com keyboard
- [ ] Acessibilidade: lido corretamente por screen readers
- [ ] Feedback messages aparecem no lugar certo
- [ ] Animações são suaves (sem lag)
- [ ] Mobile responsivo (< 768px)
- [ ] Documentação atualizada

### 8.4 Coverage Goals

- Componentes core: 80%+ coverage
- Feedback system: 100% coverage (crítico)
- Documentação: 100% (cada componente)

---

## 9. Roadmap de Implementação

### Fase 1: Foundation (Dia 1)
- [ ] Scaffold estrutura de pastas
- [ ] Criar feedback system JS + helpers PHP
- [ ] Componentes básicos (5): Button, Input, Select, Textarea, Checkbox/Radio

### Fase 2: Componentes Layout (Dia 2)
- [ ] Componentes layout (6): Card, Modal, Tabs, Accordion, Breadcrumb, Divider
- [ ] Integrar em páginas existentes

### Fase 3: Feedback & Bonus (Dia 3)
- [ ] Componentes feedback (5): Alert, Toast, Badge, Progress, Spinner
- [ ] Componentes bonus (3): Avatar, Chip, Dropdown
- [ ] Testes

### Fase 4: Documentação (Dia 4)
- [ ] Storybook local
- [ ] Getting started
- [ ] Changelog

---

## 10. Critérios de Sucesso

✅ Todos os 20 componentes implementados e funcionando  
✅ Feedback system funcionando em todas as páginas  
✅ Documentação pública e completa  
✅ 80%+ test coverage  
✅ Mobile responsivo  
✅ Acessibilidade atendida  
✅ Sem breaking changes em páginas existentes  

---

## 11. Riscos & Mitigações

| Risco | Impacto | Mitigação |
|-------|--------|-----------|
| Refatoração quebra páginas | Alto | Começar com novos componentes, depois refatorar |
| Inconsistência visual | Médio | Documentação + design tokens centralizados |
| Performance (muitos componentes) | Baixo | CSS otimizado, JS lazy-load |
| Falta de testes | Médio | Checklist manual + unit tests |

---

**Próximo passo:** Writing-plans skill para criar plano detalhado de implementação.

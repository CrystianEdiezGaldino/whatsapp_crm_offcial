# Sistema de Feedback + Componentes Reutilizáveis - Implementation Plan

> **Para execução agentic:** USE **superpowers:subagent-driven-development** (recomendado) ou **superpowers:executing-plans** para executar task-by-task. Passos usam syntax `- [ ]` para tracking.

**Objetivo:** Implementar biblioteca completa de 20 componentes Blade reutilizáveis + sistema de feedback unificado com documentação pública.

**Arquitetura:** Componentes isolados em Blade, sistema de feedback JS global, documentação interativa, estilos centralizados em CSS.

**Tech Stack:** Laravel Blade, Tailwind CSS, Material Symbols, JavaScript vanilla, PHPUnit

---

## 📋 Estrutura de Arquivos

### Será Criado:

```
resources/
├── views/
│   ├── components/
│   │   ├── common/
│   │   │   ├── button.blade.php
│   │   │   ├── input.blade.php
│   │   │   ├── select.blade.php
│   │   │   ├── textarea.blade.php
│   │   │   └── checkbox.blade.php
│   │   ├── layout/
│   │   │   ├── card.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── tabs.blade.php
│   │   │   ├── accordion.blade.php
│   │   │   ├── breadcrumb.blade.php
│   │   │   └── divider.blade.php
│   │   ├── feedback/
│   │   │   ├── alert.blade.php
│   │   │   ├── badge.blade.php
│   │   │   ├── progress.blade.php
│   │   │   └── spinner.blade.php
│   │   └── bonus/
│   │       ├── avatar.blade.php
│   │       ├── chip.blade.php
│   │       └── dropdown.blade.php
│   └── documentation/
│       ├── components.blade.php
│       ├── getting-started.blade.php
│       ├── colors.blade.php
│       └── changelog.blade.php
├── css/
│   └── components.css
└── js/
    └── feedback-system.js

app/
├── Helpers/
│   └── FeedbackHelper.php
└── Http/
    └── Controllers/
        └── DocumentationController.php
```

### Será Modificado:

- `routes/web.php` - Adicionar rota de documentação
- `resources/views/layouts/app.blade.php` - Incluir feedback-system.js
- `resources/js/app.js` - Incluir feedback-system.js

---

## 🚀 FASE 1: Foundation (Feedback System)

### Task 1: Criar Feedback System JS

**Arquivos:**
- Criar: `resources/js/feedback-system.js`

- [ ] **Step 1: Escrever arquivo feedback-system.js com Feedback global**

```javascript
// resources/js/feedback-system.js

window.Feedback = {
  // Toast que desaparece automaticamente
  success(message, duration = 3000) {
    this.toast(message, 'success', duration);
  },

  error(message, duration = 5000) {
    this.toast(message, 'error', duration);
  },

  warning(message, duration = 4000) {
    this.toast(message, 'warning', duration);
  },

  info(message, duration = 3000) {
    this.toast(message, 'info', duration);
  },

  // Modal que requer interação
  confirm(title, message, onConfirm, onCancel) {
    const container = document.getElementById('feedback-modal-container');
    const html = `
      <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" id="confirm-modal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-on-surface mb-2">${title}</h3>
          <p class="text-on-surface-variant mb-6">${message}</p>
          <div class="flex gap-2">
            <button class="flex-1 py-2 border border-outline-variant rounded-lg text-sm" onclick="document.getElementById('confirm-modal').remove(); ${onCancel || ''}"">Cancelar</button>
            <button class="flex-1 bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold" onclick="document.getElementById('confirm-modal').remove(); ${onConfirm}"">Confirmar</button>
          </div>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  },

  alert(title, message) {
    const container = document.getElementById('feedback-modal-container');
    const html = `
      <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" id="alert-modal">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
          <h3 class="text-lg font-bold text-on-surface mb-2">${title}</h3>
          <p class="text-on-surface-variant mb-6">${message}</p>
          <button class="w-full bg-primary text-on-primary py-2 rounded-lg text-sm font-semibold" onclick="document.getElementById('alert-modal').remove()">Ok</button>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  },

  // Método privado para toast
  toast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('feedback-toast-container');
    if (!container) return;

    const icons = {
      success: 'check_circle',
      error: 'error',
      warning: 'warning',
      info: 'info'
    };

    const colors = {
      success: 'bg-green-50 border-green-200 text-green-800',
      error: 'bg-red-50 border-red-200 text-red-800',
      warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      info: 'bg-blue-50 border-blue-200 text-blue-800'
    };

    const html = `
      <div class="toast p-4 mb-3 border rounded-lg flex items-center gap-3 ${colors[type]}" style="animation: slideInUp 0.3s ease-out;">
        <span class="material-symbols-outlined text-lg">${icons[type]}</span>
        <p class="text-sm">${message}</p>
        <button onclick="this.parentElement.remove()" class="ml-auto opacity-70 hover:opacity-100">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    setTimeout(() => {
      const toasts = container.querySelectorAll('.toast');
      if (toasts.length > 0) {
        toasts[toasts.length - 1].style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toasts[toasts.length - 1].remove(), 300);
      }
    }, duration);
  }
};

// CSS para animações (será incluído em components.css)
if (typeof document !== 'undefined') {
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideInUp {
      from {
        transform: translateY(100%);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    @keyframes fadeOut {
      from {
        opacity: 1;
      }
      to {
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
}
```

- [ ] **Step 2: Incluir feedback-system.js em app.js**

Editar `resources/js/app.js` para adicionar no início:
```javascript
import './feedback-system.js';
```

- [ ] **Step 3: Adicionar containers HTML ao layout**

Editar `resources/views/layouts/app.blade.php`, adicionar no `<body>` antes do `</body>`:
```html
<!-- Feedback Containers -->
<div id="feedback-toast-container" class="fixed bottom-6 right-6 w-96 max-w-full z-40"></div>
<div id="feedback-modal-container"></div>
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/feedback-system.js resources/js/app.js resources/views/layouts/app.blade.php
git commit -m "feat: add global feedback system (toast, modal, alert)"
```

---

### Task 2: Criar PHP Helper para Feedback

**Arquivos:**
- Criar: `app/Helpers/FeedbackHelper.php`

- [ ] **Step 1: Criar classe FeedbackHelper**

```php
<?php

namespace App\Helpers;

class FeedbackHelper
{
    public static function success(string $message)
    {
        session()->flash('success', $message);
    }

    public static function error(string $message)
    {
        session()->flash('error', $message);
    }

    public static function warning(string $message)
    {
        session()->flash('warning', $message);
    }

    public static function info(string $message)
    {
        session()->flash('info', $message);
    }
}
```

- [ ] **Step 2: Registrar helper global em AppServiceProvider**

Editar `app/Providers/AppServiceProvider.php`, adicionar na função `boot()`:

```php
// Register helper function
function feedback() {
    return new \App\Helpers\FeedbackHelper();
}
```

Alternativamente, adicionar ao `composer.json` > `autoload` > `files`:
```json
"files": ["app/Helpers/helpers.php"]
```

E criar `app/Helpers/helpers.php`:
```php
<?php

if (!function_exists('feedback')) {
    function feedback()
    {
        return new \App\Helpers\FeedbackHelper();
    }
}
```

- [ ] **Step 3: Criar componente para exibir flash messages**

```blade
{{-- resources/views/components/feedback/flash-messages.blade.php --}}
@if(session('success'))
<x-alert type="success" title="Sucesso!">{{ session('success') }}</x-alert>
@endif

@if(session('error'))
<x-alert type="error" title="Erro">{{ session('error') }}</x-alert>
@endif

@if(session('warning'))
<x-alert type="warning" title="Atenção">{{ session('warning') }}</x-alert>
@endif

@if(session('info'))
<x-alert type="info">{{ session('info') }}</x-alert>
@endif
```

- [ ] **Step 4: Commit**

```bash
git add app/Helpers/FeedbackHelper.php app/Helpers/helpers.php app/Providers/AppServiceProvider.php resources/views/components/feedback/flash-messages.blade.php
git commit -m "feat: add PHP feedback helper for session flash messages"
```

---

### Task 3: Criar arquivo base CSS para componentes

**Arquivos:**
- Criar: `resources/css/components.css`

- [ ] **Step 1: Criar arquivo components.css com estilos base**

```css
/* resources/css/components.css */

/* ===== ANIMATIONS ===== */
@keyframes slideInUp {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes slideInDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* ===== TRANSITIONS ===== */
.transition-all {
  transition: all 0.2s ease-in-out;
}

/* ===== FOCUS STATES ===== */
input:focus,
textarea:focus,
select:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

/* ===== DISABLED STATES ===== */
button:disabled,
input:disabled,
textarea:disabled,
select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ===== ERROR STATES ===== */
.error-input {
  border-color: #ba1a1a !important;
  background-color: #fff8f7;
}

.error-text {
  color: #ba1a1a;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

/* ===== LOADING STATES ===== */
.spinner-container {
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner {
  animation: spin 1s linear infinite;
}

/* ===== TOOLTIP ===== */
.tooltip {
  position: relative;
  display: inline-block;
}

.tooltip .tooltiptext {
  visibility: hidden;
  background-color: #333;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 5px 0;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  margin-left: -50px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
```

- [ ] **Step 2: Importar components.css em app.css**

Editar `resources/css/app.css`, adicionar no início:
```css
@import './components.css';
```

- [ ] **Step 3: Commit**

```bash
git add resources/css/components.css resources/css/app.css
git commit -m "feat: add base component styles and animations"
```

---

## 🧩 FASE 2: Componentes Básicos (5)

### Task 4: Criar componente Button

**Arquivos:**
- Criar: `resources/views/components/common/button.blade.php`

- [ ] **Step 1: Criar componente Button**

```blade
{{-- resources/views/components/common/button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'disabled' => false,
    'loading' => false,
    'type' => 'button',
    'href' => null,
])

@php
    $classes = [
        'flex items-center justify-center gap-2 font-semibold transition-all rounded-lg',
        'disabled:opacity-60 disabled:cursor-not-allowed',
    ];

    $variantClasses = [
        'primary' => 'bg-primary text-on-primary hover:opacity-90 active:scale-95',
        'secondary' => 'bg-secondary text-on-secondary hover:opacity-90 active:scale-95',
        'danger' => 'bg-error text-on-error hover:opacity-90 active:scale-95',
        'tertiary' => 'bg-tertiary text-on-tertiary hover:opacity-90 active:scale-95',
        'outline' => 'border border-outline-variant text-on-surface hover:bg-surface-container active:scale-95',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes[] = $variantClasses[$variant] ?? $variantClasses['primary'];
    $classes[] = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
        @if($loading)
            <x-spinner size="sm" />
        @elseif($icon)
            <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => implode(' ', $classes)]) }}
        @disabled($disabled || $loading)
    >
        @if($loading)
            <x-spinner size="sm" />
        @elseif($icon)
            <span class="material-symbols-outlined text-lg">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </button>
@endif
```

- [ ] **Step 2: Testar componente Button em tinker**

```bash
php artisan tinker

# Testar renderização
$html = Blade::render('<x-button variant="primary">Click</x-button>');
var_dump($html);
# Verificar se contém 'bg-primary'
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/common/button.blade.php
git commit -m "feat: add button component with variants and sizes"
```

---

### Task 5: Criar componente Input

**Arquivos:**
- Criar: `resources/views/components/common/input.blade.php`

- [ ] **Step 1: Criar componente Input**

```blade
{{-- resources/views/components/common/input.blade.php --}}
@props([
    'type' => 'text',
    'name' => '',
    'value' => null,
    'placeholder' => '',
    'error' => null,
    'disabled' => false,
    'required' => false,
    'iconPrefix' => null,
    'iconSuffix' => null,
])

<div class="w-full">
    <div class="relative">
        @if($iconPrefix)
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">{{ $iconPrefix }}</span>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            @required($required)
            {{ $attributes->merge(['class' => implode(' ', [
                'w-full px-4 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary focus:border-primary transition-all',
                $iconPrefix ? 'pl-10' : '',
                $iconSuffix ? 'pr-10' : '',
                $error ? 'border-error bg-red-50' : '',
            ])]) }}
        />

        @if($iconSuffix)
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined">{{ $iconSuffix }}</span>
        @endif
    </div>

    @if($error)
        <p class="text-xs text-error mt-1">{{ $error }}</p>
    @endif
</div>
```

- [ ] **Step 2: Testar componente Input**

```bash
php artisan tinker

$html = Blade::render('<x-input name="email" type="email" placeholder="seu@email.com" />');
var_dump($html);
# Verificar se contém 'type="email"'
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/common/input.blade.php
git commit -m "feat: add input component with error state and icons"
```

---

### Task 6: Criar componente Select

**Arquivos:**
- Criar: `resources/views/components/common/select.blade.php`

- [ ] **Step 1: Criar componente Select**

```blade
{{-- resources/views/components/common/select.blade.php --}}
@props([
    'name' => '',
    'options' => [],
    'placeholder' => 'Selecione...',
    'value' => null,
    'error' => null,
    'disabled' => false,
    'multiple' => false,
])

<div class="w-full">
    <select
        name="{{ $name }}"
        @disabled($disabled)
        @if($multiple) multiple @endif
        {{ $attributes->merge(['class' => implode(' ', [
            'w-full px-4 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary focus:border-primary transition-all appearance-none',
            $error ? 'border-error bg-red-50' : 'bg-white',
        ])]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected($optValue == $value)>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @if($error)
        <p class="text-xs text-error mt-1">{{ $error }}</p>
    @endif
</div>
```

- [ ] **Step 2: Testar componente Select**

```bash
php artisan tinker

$options = ['1' => 'Opção 1', '2' => 'Opção 2'];
$html = Blade::render('<x-select name="test" :options="$options" />', ['options' => $options]);
var_dump($html);
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/common/select.blade.php
git commit -m "feat: add select component with error handling"
```

---

### Task 7: Criar componente Textarea

**Arquivos:**
- Criar: `resources/views/components/common/textarea.blade.php`

- [ ] **Step 1: Criar componente Textarea**

```blade
{{-- resources/views/components/common/textarea.blade.php --}}
@props([
    'name' => '',
    'value' => null,
    'placeholder' => '',
    'rows' => 4,
    'maxlength' => null,
    'error' => null,
    'disabled' => false,
    'showCounter' => true,
])

<div class="w-full">
    <div class="relative">
        <textarea
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            @if($maxlength) maxlength="{{ $maxlength }}" @endif
            {{ $attributes->merge(['class' => implode(' ', [
                'w-full px-4 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary focus:border-primary transition-all resize-none',
                $error ? 'border-error bg-red-50' : '',
                $showCounter && $maxlength ? 'pb-8' : '',
            ])]) }}
        >{{ $value }}</textarea>

        @if($showCounter && $maxlength)
            <p class="absolute bottom-1 right-3 text-xs text-on-surface-variant">
                <span class="char-count">0</span> / {{ $maxlength }}
            </p>
        @endif
    </div>

    @if($error)
        <p class="text-xs text-error mt-1">{{ $error }}</p>
    @endif
</div>

@if($showCounter && $maxlength)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.currentScript.previousElementSibling.querySelector('textarea');
            const counter = document.querySelector('.char-count');
            if (textarea && counter) {
                counter.textContent = textarea.value.length;
                textarea.addEventListener('input', function() {
                    counter.textContent = this.value.length;
                });
            }
        });
    </script>
@endif
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/common/textarea.blade.php
git commit -m "feat: add textarea component with character counter"
```

---

### Task 8: Criar componente Checkbox/Radio

**Arquivos:**
- Criar: `resources/views/components/common/checkbox.blade.php`
- Criar: `resources/views/components/common/radio.blade.php`

- [ ] **Step 1: Criar checkbox.blade.php**

```blade
{{-- resources/views/components/common/checkbox.blade.php --}}
@props([
    'name' => '',
    'value' => null,
    'label' => '',
    'checked' => false,
    'disabled' => false,
])

<div class="flex items-center gap-2">
    <input
        type="checkbox"
        id="{{ $name }}-{{ $value }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($checked)
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'w-4 h-4 rounded border border-outline-variant cursor-pointer transition-all']) }}
    />
    @if($label)
        <label for="{{ $name }}-{{ $value }}" class="text-sm text-on-surface cursor-pointer">
            {{ $label }}
        </label>
    @endif
</div>
```

- [ ] **Step 2: Criar radio.blade.php**

```blade
{{-- resources/views/components/common/radio.blade.php --}}
@props([
    'name' => '',
    'value' => null,
    'label' => '',
    'checked' => false,
    'disabled' => false,
])

<div class="flex items-center gap-2">
    <input
        type="radio"
        id="{{ $name }}-{{ $value }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($checked)
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'w-4 h-4 rounded-full border border-outline-variant cursor-pointer transition-all']) }}
    />
    @if($label)
        <label for="{{ $name }}-{{ $value }}" class="text-sm text-on-surface cursor-pointer">
            {{ $label }}
        </label>
    @endif
</div>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/common/checkbox.blade.php resources/views/components/common/radio.blade.php
git commit -m "feat: add checkbox and radio components"
```

---

## 🎨 FASE 3: Componentes Layout (6)

### Task 9: Criar componente Card

**Arquivos:**
- Criar: `resources/views/components/layout/card.blade.php`

- [ ] **Step 1: Criar componente Card**

```blade
{{-- resources/views/components/layout/card.blade.php --}}
@props([
    'variant' => 'elevated',
    'padding' => 'md',
    'hover' => false,
])

@php
    $classes = [
        'rounded-xl transition-all',
    ];

    $variantClasses = [
        'elevated' => 'bg-white border border-outline-variant shadow-sm',
        'outline' => 'bg-surface border border-outline-variant',
    ];

    $paddingClasses = [
        'sm' => 'p-3',
        'md' => 'p-5',
        'lg' => 'p-8',
    ];

    $classes[] = $variantClasses[$variant] ?? $variantClasses['elevated'];
    $classes[] = $paddingClasses[$padding] ?? $paddingClasses['md'];
    if ($hover) {
        $classes[] = 'hover:shadow-md hover:border-secondary';
    }
@endphp

<div {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/card.blade.php
git commit -m "feat: add card component with variants"
```

---

### Task 10: Criar componente Modal

**Arquivos:**
- Criar: `resources/views/components/layout/modal.blade.php`

- [ ] **Step 1: Criar componente Modal**

```blade
{{-- resources/views/components/layout/modal.blade.php --}}
@props([
    'title' => '',
    'size' => 'md',
    'closeButton' => true,
    'footer' => false,
])

@php
    $sizeClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center', 'id' => 'modal-' . Str::random(8)]) }}>
    <div class="bg-white rounded-xl shadow-lg w-full {{ $sizeClasses[$size] ?? $sizeClasses['md'] }} max-h-[90vh] overflow-y-auto">
        @if($title || $closeButton)
            <div class="flex justify-between items-center p-6 border-b border-outline-variant">
                @if($title)
                    <h3 class="text-lg font-bold text-on-surface">{{ $title }}</h3>
                @endif
                @if($closeButton)
                    <button onclick="this.closest('[id^=modal-]').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                @endif
            </div>
        @endif

        @if(isset($header))
            <div class="p-6 border-b border-outline-variant">
                {{ $header }}
            </div>
        @endif

        <div class="p-6">
            {{ $slot }}
            @if(isset($body))
                {{ $body }}
            @endif
        </div>

        @if($footer || isset($footer))
            <div class="p-6 border-t border-outline-variant flex gap-2 justify-end">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layout/modal.blade.php
git commit -m "feat: add modal component with slots"
```

---

### Task 11: Criar componentes Tabs, Accordion, Breadcrumb, Divider

**Arquivos:**
- Criar: `resources/views/components/layout/tabs.blade.php`
- Criar: `resources/views/components/layout/accordion.blade.php`
- Criar: `resources/views/components/layout/breadcrumb.blade.php`
- Criar: `resources/views/components/layout/divider.blade.php`

- [ ] **Step 1: Criar tabs.blade.php**

```blade
{{-- resources/views/components/layout/tabs.blade.php --}}
@props([
    'tabs' => [],
    'defaultActive' => 0,
])

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex border-b border-outline-variant">
        @foreach($tabs as $index => $tab)
            <button
                class="px-4 py-2 text-sm font-semibold transition-colors {{ $index === $defaultActive ? 'border-b-2 border-secondary text-secondary' : 'text-on-surface-variant hover:text-on-surface' }}"
                onclick="document.querySelectorAll('[data-tab-content]').forEach(el => el.classList.add('hidden')); document.querySelector('[data-tab-content='{{ $index }}']').classList.remove('hidden'); this.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('border-b-2', 'border-secondary', 'text-secondary')); this.classList.add('border-b-2', 'border-secondary', 'text-secondary');"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    @foreach($tabs as $index => $tab)
        <div data-tab-content="{{ $index }}" class="{{ $index !== $defaultActive ? 'hidden' : '' }} pt-4">
            {{ $tab['content'] }}
        </div>
    @endforeach
</div>
```

- [ ] **Step 2: Criar accordion.blade.php**

```blade
{{-- resources/views/components/layout/accordion.blade.php --}}
@props([
    'items' => [],
    'allowMultiple' => false,
])

<div class="space-y-2">
    @foreach($items as $index => $item)
        <div class="border border-outline-variant rounded-lg">
            <button
                class="w-full px-4 py-3 flex justify-between items-center hover:bg-surface-container transition-colors"
                onclick="
                    @if(!$allowMultiple)
                        document.querySelectorAll('[data-accordion-content]').forEach(el => el.classList.add('hidden'));
                        document.querySelectorAll('[data-accordion-btn]').forEach(b => b.querySelector('.material-symbols-outlined').textContent = 'expand_more');
                    @endif
                    this.nextElementSibling.classList.toggle('hidden');
                    const icon = this.querySelector('.material-symbols-outlined');
                    icon.textContent = icon.textContent === 'expand_more' ? 'expand_less' : 'expand_more';
                "
                data-accordion-btn
            >
                <h3 class="font-semibold text-on-surface">{{ $item['title'] }}</h3>
                <span class="material-symbols-outlined">expand_more</span>
            </button>
            <div data-accordion-content class="hidden px-4 py-3 border-t border-outline-variant bg-surface-container-low text-on-surface-variant text-sm">
                {{ $item['content'] }}
            </div>
        </div>
    @endforeach
</div>
```

- [ ] **Step 3: Criar breadcrumb.blade.php**

```blade
{{-- resources/views/components/layout/breadcrumb.blade.php --}}
@props([
    'items' => [],
])

<nav class="flex items-center gap-2">
    @foreach($items as $index => $item)
        @if($index > 0)
            <span class="text-on-surface-variant">/</span>
        @endif

        @if($item['url'] ?? null)
            <a href="{{ $item['url'] }}" class="text-sm text-secondary hover:underline transition-colors">
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-sm text-on-surface-variant">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
```

- [ ] **Step 4: Criar divider.blade.php**

```blade
{{-- resources/views/components/layout/divider.blade.php --}}
@props([
    'direction' => 'horizontal',
    'margin' => 'my-4',
])

@if($direction === 'horizontal')
    <hr {{ $attributes->merge(['class' => "border-outline-variant {$margin}"]) }} />
@else
    <div {{ $attributes->merge(['class' => "border-l border-outline-variant {$margin}"]) }}></div>
@endif
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/layout/tabs.blade.php resources/views/components/layout/accordion.blade.php resources/views/components/layout/breadcrumb.blade.php resources/views/components/layout/divider.blade.php
git commit -m "feat: add tabs, accordion, breadcrumb, and divider components"
```

---

## 📢 FASE 4: Componentes Feedback (5)

### Task 12: Criar componentes Alert, Badge, Progress, Spinner

**Arquivos:**
- Criar: `resources/views/components/feedback/alert.blade.php`
- Criar: `resources/views/components/feedback/badge.blade.php`
- Criar: `resources/views/components/feedback/progress.blade.php`
- Criar: `resources/views/components/feedback/spinner.blade.php`

- [ ] **Step 1: Criar alert.blade.php**

```blade
{{-- resources/views/components/feedback/alert.blade.php --}}
@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $classes = [
        'p-4 rounded-lg border flex items-start gap-3',
    ];

    $typeClasses = [
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
    ];

    $icons = [
        'info' => 'info',
        'success' => 'check_circle',
        'warning' => 'warning',
        'error' => 'error',
    ];

    $classes[] = $typeClasses[$type] ?? $typeClasses['info'];
@endphp

<div {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    <span class="material-symbols-outlined text-lg">{{ $icons[$type] }}</span>

    <div class="flex-1">
        @if($title)
            <h4 class="font-semibold mb-1">{{ $title }}</h4>
        @endif
        <p class="text-sm">{{ $slot }}</p>
    </div>

    @if($dismissible)
        <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100">
            <span class="material-symbols-outlined">close</span>
        </button>
    @endif
</div>
```

- [ ] **Step 2: Criar badge.blade.php**

```blade
{{-- resources/views/components/feedback/badge.blade.php --}}
@props([
    'type' => 'primary',
    'size' => 'md',
])

@php
    $classes = ['inline-block rounded-full font-semibold whitespace-nowrap'];

    $typeClasses = [
        'primary' => 'bg-primary text-on-primary',
        'secondary' => 'bg-secondary text-on-secondary',
        'success' => 'bg-green-500 text-white',
        'error' => 'bg-error text-on-error',
    ];

    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
    ];

    $classes[] = $typeClasses[$type] ?? $typeClasses['primary'];
    $classes[] = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<span {{ $attributes->merge(['class' => implode(' ', $classes)]) }}>
    {{ $slot }}
</span>
```

- [ ] **Step 3: Criar progress.blade.php**

```blade
{{-- resources/views/components/feedback/progress.blade.php --}}
@props([
    'percentage' => 0,
    'color' => 'primary',
    'label' => null,
])

@php
    $colorClasses = [
        'primary' => 'bg-primary',
        'success' => 'bg-green-500',
        'warning' => 'bg-yellow-500',
        'error' => 'bg-error',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
        <div class="flex justify-between mb-1">
            <label class="text-sm font-semibold text-on-surface">{{ $label }}</label>
            <span class="text-sm text-on-surface-variant">{{ $percentage }}%</span>
        </div>
    @endif

    <div class="w-full bg-surface-container-high rounded-full h-2">
        <div
            class="h-2 rounded-full transition-all {{ $colorClasses[$color] ?? $colorClasses['primary'] }}"
            style="width: {{ $percentage }}%"
        ></div>
    </div>
</div>
```

- [ ] **Step 4: Criar spinner.blade.php**

```blade
{{-- resources/views/components/feedback/spinner.blade.php --}}
@props([
    'size' => 'md',
    'color' => 'primary',
])

@php
    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-6 h-6',
        'lg' => 'w-8 h-8',
    ];

    $colorClasses = [
        'primary' => 'border-primary',
        'secondary' => 'border-secondary',
        'white' => 'border-white',
    ];
@endphp

<div {{ $attributes->merge(['class' => "spinner " . $sizeClasses[$size] . " border-2 border-gray-300 " . $colorClasses[$color] . " border-t-transparent rounded-full"]) }}></div>
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/feedback/alert.blade.php resources/views/components/feedback/badge.blade.php resources/views/components/feedback/progress.blade.php resources/views/components/feedback/spinner.blade.php
git commit -m "feat: add alert, badge, progress, and spinner components"
```

---

## 🎁 FASE 5: Componentes Bonus (3)

### Task 13: Criar componentes Avatar, Chip, Dropdown

**Arquivos:**
- Criar: `resources/views/components/bonus/avatar.blade.php`
- Criar: `resources/views/components/bonus/chip.blade.php`
- Criar: `resources/views/components/bonus/dropdown.blade.php`

- [ ] **Step 1: Criar avatar.blade.php**

```blade
{{-- resources/views/components/bonus/avatar.blade.php --}}
@props([
    'initials' => '',
    'url' => null,
    'size' => 'md',
])

@php
    $sizeClasses = [
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
    ];
@endphp

@if($url)
    <img
        src="{{ $url }}"
        {{ $attributes->merge(['class' => "rounded-full object-cover {$sizeClasses[$size]}"]) }}
    />
@else
    <div {{ $attributes->merge(['class' => "rounded-full bg-primary text-on-primary flex items-center justify-center font-bold {$sizeClasses[$size]}"]) }}>
        {{ $initials }}
    </div>
@endif
```

- [ ] **Step 2: Criar chip.blade.php**

```blade
{{-- resources/views/components/bonus/chip.blade.php --}}
@props([
    'label' => '',
    'removable' => false,
    'onRemove' => null,
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-3 py-1 bg-surface-container rounded-full text-sm']) }}>
    <span>{{ $label }}</span>
    @if($removable)
        <button
            type="button"
            onclick="{{ $onRemove ?? 'this.parentElement.remove()' }}"
            class="text-on-surface-variant hover:text-on-surface"
        >
            <span class="material-symbols-outlined text-base">close</span>
        </button>
    @endif
</div>
```

- [ ] **Step 3: Criar dropdown.blade.php**

```blade
{{-- resources/views/components/bonus/dropdown.blade.php --}}
@props([
    'trigger' => 'Menu',
    'items' => [],
])

@php
    $id = 'dropdown-' . Str::random(8);
@endphp

<div class="relative inline-block">
    <button
        onclick="document.getElementById('{{ $id }}').classList.toggle('hidden')"
        class="px-4 py-2 bg-secondary text-on-secondary rounded-lg text-sm font-semibold hover:opacity-90 transition-all flex items-center gap-2"
    >
        {{ $trigger }}
        <span class="material-symbols-outlined text-base">expand_more</span>
    </button>

    <div id="{{ $id }}" class="hidden absolute top-full mt-2 right-0 bg-white border border-outline-variant rounded-lg shadow-lg w-48 z-40">
        @foreach($items as $item)
            @if($item['divider'] ?? false)
                <div class="border-t border-outline-variant"></div>
            @else
                <button
                    type="button"
                    onclick="document.getElementById('{{ $id }}').classList.add('hidden'); {{ $item['action'] ?? '' }}"
                    class="w-full text-left px-4 py-2 hover:bg-surface-container transition-colors text-sm text-on-surface"
                >
                    @if($item['icon'] ?? null)
                        <span class="material-symbols-outlined text-sm mr-2">{{ $item['icon'] }}</span>
                    @endif
                    {{ $item['label'] }}
                </button>
            @endif
        @endforeach
    </div>
</div>
```

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/bonus/avatar.blade.php resources/views/components/bonus/chip.blade.php resources/views/components/bonus/dropdown.blade.php
git commit -m "feat: add avatar, chip, and dropdown components"
```

---

## 📚 FASE 6: Documentação (Storybook)

### Task 14: Criar DocumentationController

**Arquivos:**
- Criar: `app/Http/Controllers/DocumentationController.php`

- [ ] **Step 1: Criar DocumentationController**

```php
<?php

namespace App\Http\Controllers;

class DocumentationController extends Controller
{
    public function components()
    {
        $components = [
            'common' => [
                ['name' => 'Button', 'description' => 'Componente de botão com múltiplas variações'],
                ['name' => 'Input', 'description' => 'Campo de entrada com validação'],
                ['name' => 'Select', 'description' => 'Seletor com opções'],
                ['name' => 'Textarea', 'description' => 'Campo de texto múltiplas linhas'],
                ['name' => 'Checkbox', 'description' => 'Caixa de seleção'],
                ['name' => 'Radio', 'description' => 'Botão de rádio'],
            ],
            'layout' => [
                ['name' => 'Card', 'description' => 'Contenedor de conteúdo'],
                ['name' => 'Modal', 'description' => 'Diálogo modal'],
                ['name' => 'Tabs', 'description' => 'Abas com conteúdo dinâmico'],
                ['name' => 'Accordion', 'description' => 'Acordeão expansível'],
                ['name' => 'Breadcrumb', 'description' => 'Navegação hierárquica'],
                ['name' => 'Divider', 'description' => 'Separador visual'],
            ],
            'feedback' => [
                ['name' => 'Alert', 'description' => 'Mensagem de alerta'],
                ['name' => 'Badge', 'description' => 'Etiqueta pequena'],
                ['name' => 'Progress', 'description' => 'Barra de progresso'],
                ['name' => 'Spinner', 'description' => 'Indicador de carregamento'],
            ],
            'bonus' => [
                ['name' => 'Avatar', 'description' => 'Imagem de perfil'],
                ['name' => 'Chip', 'description' => 'Etiqueta removível'],
                ['name' => 'Dropdown', 'description' => 'Menu suspenso'],
            ],
        ];

        return view('documentation.components', ['components' => $components]);
    }

    public function gettingStarted()
    {
        return view('documentation.getting-started');
    }

    public function colors()
    {
        $colors = [
            'primary' => ['#000000', 'Preto - Ações principais'],
            'secondary' => ['#006d2f', 'Verde - Ações secundárias'],
            'error' => ['#ba1a1a', 'Vermelho - Erros'],
            'success' => ['#006d2f', 'Verde - Sucesso'],
            'warning' => ['#FFC300', 'Amarelo - Avisos'],
            'info' => ['#131b2e', 'Azul - Informação'],
        ];

        return view('documentation.colors', ['colors' => $colors]);
    }

    public function changelog()
    {
        return view('documentation.changelog');
    }
}
```

- [ ] **Step 2: Adicionar rotas de documentação**

Editar `routes/web.php`, adicionar:

```php
Route::get('/docs/components', [DocumentationController::class, 'components'])->name('docs.components');
Route::get('/docs/getting-started', [DocumentationController::class, 'gettingStarted'])->name('docs.getting-started');
Route::get('/docs/colors', [DocumentationController::class, 'colors'])->name('docs.colors');
Route::get('/docs/changelog', [DocumentationController::class, 'changelog'])->name('docs.changelog');
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/DocumentationController.php routes/web.php
git commit -m "feat: add documentation controller and routes"
```

---

### Task 15: Criar views de Documentação

**Arquivos:**
- Criar: `resources/views/documentation/components.blade.php`
- Criar: `resources/views/documentation/getting-started.blade.php`
- Criar: `resources/views/documentation/colors.blade.php`
- Criar: `resources/views/documentation/changelog.blade.php`

- [ ] **Step 1: Criar components.blade.php**

```blade
@extends('layouts.app')

@section('title', 'Documentação de Componentes')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8 flex items-center gap-4">
            <img src="{{ asset('logo_no_brackgroud.png') }}" alt="Logo" class="w-12 h-12">
            <h1 class="text-3xl font-bold text-on-surface">Documentação de Componentes</h1>
        </div>

        <p class="text-on-surface-variant mb-8">Explore todos os componentes disponíveis. Clique em cada card para ver mais detalhes.</p>

        @foreach($components as $category => $items)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-on-surface mb-4 capitalize">{{ $category }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($items as $component)
                        <x-card hover>
                            <h3 class="font-bold text-lg text-on-surface mb-2">{{ $component['name'] }}</h3>
                            <p class="text-sm text-on-surface-variant">{{ $component['description'] }}</p>
                        </x-card>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
```

- [ ] **Step 2: Criar getting-started.blade.php**

```blade
@extends('layouts.app')

@section('title', 'Getting Started - Documentação')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-on-surface mb-8">Getting Started</h1>

        <x-card class="mb-6">
            <h2 class="text-xl font-bold text-on-surface mb-4">Como usar os componentes</h2>
            <p class="text-on-surface-variant mb-4">Todos os componentes estão disponíveis como componentes Laravel Blade. Use a sintaxe `<x-component-name />` para renderizá-los.</p>

            <x-alert type="info" class="mt-4">
                <strong>Dica:</strong> Todos os componentes suportam props passadas como atributos HTML.
            </x-alert>
        </x-card>

        <x-card class="mb-6">
            <h2 class="text-xl font-bold text-on-surface mb-4">Sistema de Feedback</h2>
            <p class="text-on-surface-variant mb-4">Para mostrar mensagens de sucesso, erro, aviso ou info:</p>

            <pre class="bg-surface-container p-4 rounded-lg text-sm overflow-x-auto mb-4"><code>// Em JavaScript
Feedback.success('Operação concluída!');
Feedback.error('Algo deu errado');
Feedback.warning('Cuidado!');
Feedback.info('Informação');

// Em PHP (controller)
feedback()->success('Mensagem');

// Em Blade (flash message)
@if(session('success'))
  &lt;x-alert type="success"&gt;{{ session('success') }}&lt;/x-alert&gt;
@endif</code></pre>
        </x-card>

        <x-card>
            <h2 class="text-xl font-bold text-on-surface mb-4">Exemplos de componentes</h2>

            <div class="space-y-6">
                <div>
                    <h3 class="font-semibold text-on-surface mb-2">Button</h3>
                    <div class="flex gap-2 mb-2">
                        <x-button variant="primary">Primary</x-button>
                        <x-button variant="secondary">Secondary</x-button>
                        <x-button variant="danger">Danger</x-button>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-on-surface mb-2">Input</h3>
                    <x-input placeholder="Digite aqui..." />
                </div>

                <div>
                    <h3 class="font-semibold text-on-surface mb-2">Alert</h3>
                    <x-alert type="success" title="Sucesso!">Operação concluída com sucesso</x-alert>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
```

- [ ] **Step 3: Criar colors.blade.php**

```blade
@extends('layouts.app')

@section('title', 'Paleta de Cores')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-on-surface mb-8">Paleta de Cores</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($colors as $name => $data)
                <x-card>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-20 h-20 rounded-lg border border-outline-variant"
                            style="background-color: {{ $data[0] }}"
                        ></div>
                        <div>
                            <h3 class="font-bold text-on-surface capitalize">{{ $name }}</h3>
                            <p class="text-sm text-on-surface-variant font-mono">{{ $data[0] }}</p>
                            <p class="text-xs text-on-surface-variant mt-1">{{ $data[1] }}</p>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 4: Criar changelog.blade.php**

```blade
@extends('layouts.app')

@section('title', 'Changelog - Documentação')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold text-on-surface mb-8">Changelog</h1>

        <x-card class="mb-6">
            <h2 class="text-xl font-bold text-on-surface mb-2">v1.0 - 22 de maio de 2026</h2>
            <ul class="list-disc list-inside text-on-surface-variant space-y-2">
                <li>20 componentes Blade implementados</li>
                <li>Sistema de feedback unificado (toast, modal, alert)</li>
                <li>Documentação pública (Storybook)</li>
                <li>Paleta de cores Material Design 3</li>
                <li>Integração com logo do projeto</li>
                <li>100% componentes responsive</li>
            </ul>
        </x-card>
    </div>
</div>
@endsection
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/documentation/components.blade.php resources/views/documentation/getting-started.blade.php resources/views/documentation/colors.blade.php resources/views/documentation/changelog.blade.php
git commit -m "feat: add comprehensive documentation pages (Storybook)"
```

---

## ✅ FASE 7: Integração + Testes Finais

### Task 16: Testar sistema de feedback em página existente

**Objetivo:** Verificar se feedback funciona em uma página real

- [ ] **Step 1: Adicionar flash message à página de conversas**

Editar `resources/views/conversations/index.blade.php`, adicionar no topo do `@section('content')`:

```blade
<x-feedback.flash-messages />
```

- [ ] **Step 2: Testar feedback em ConversationClaimController**

Editar `app/Http/Controllers/ConversationClaimController.php`, na função `claim()`:

```php
public function claim(Conversation $conversation)
{
    if ($conversation->hasActiveClaim()) {
        feedback()->error('Este atendimento já foi clamado');
        return response()->json([
            'success' => false,
            'message' => 'Este atendimento já foi clamado por ' . $conversation->getActiveClaim()->user->name,
        ], 422);
    }

    $claim = $conversation->claim(Auth::id(), 'Agente clamou o atendimento');

    // ... resto do código

    feedback()->success('Atendimento clamado com sucesso');
    
    return response()->json([
        'success' => true,
        'message' => 'Atendimento clamado com sucesso',
        // ...
    ]);
}
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/conversations/index.blade.php app/Http/Controllers/ConversationClaimController.php
git commit -m "feat: integrate feedback system into conversation page"
```

---

### Task 17: Teste final e verificação

**Objetivo:** Verificação de qualidade

- [ ] **Step 1: Listar todos os componentes criados**

Verify em tinker:

```bash
php artisan tinker

# Verificar se componentes existem
$files = glob('resources/views/components/**/*.blade.php');
echo count($files); // Deve ser ~20
```

- [ ] **Step 2: Testar renderização de componentes**

```bash
# Testar alguns componentes
php artisan tinker

Blade::render('<x-button>Teste</x-button>');
Blade::render('<x-input name="test" />');
Blade::render('<x-card>Conteúdo</x-card>');
Blade::render('<x-alert type="success">Sucesso!</x-alert>');
```

- [ ] **Step 3: Verificar rotas de documentação**

```bash
# Testar se rotas funcionam
curl http://localhost:8000/docs/components

# Verificar se logo aparece
# Deve conter: logo_no_brackgroud.png
```

- [ ] **Step 4: Commit final**

```bash
git add .
git commit -m "chore: final verification and testing of component library"
```

---

## 🎯 Critérios de Conclusão

- [ ] 20 componentes Blade implementados
- [ ] Sistema de feedback (JS + PHP) funcionando
- [ ] Documentação pública em /docs/components
- [ ] Todos os componentes testados em tinker
- [ ] Logo integrada em componentes visuais
- [ ] Rotas de documentação criadas
- [ ] Commits frequentes (1 por task)
- [ ] Sem breaking changes em código existente

---

## 📝 Notas Importantes

1. **Blade Components:** Laravel renderiza automaticamente componentes de `resources/views/components/`
2. **Slots:** Use `{{ $slot }}` para conteúdo padrão, `{{ $namedSlot }}` para slots nomeados
3. **Props:** Use `@props(['varName' => 'default'])` no início de cada componente
4. **Classes:** Use `merge()` para combinar classes Tailwind com atributos passados
5. **Reusability:** Componentes podem usar outros componentes (ex: Button usa Spinner)


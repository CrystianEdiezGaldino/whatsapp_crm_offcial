# Flow Builder: Sistema de Variáveis Dinâmicas

**Data:** 2026-06-11  
**Status:** Aprovado  
**Autor:** Claude Code

## 📋 Visão Geral

Sistema para permitir uso de variáveis de contato (nome, telefone, setor) em mensagens de fluxo usando sintaxe de template `{{variavel}}`. Inclui validação em tempo real, preview, e avisos sobre variáveis desconhecidas.

## 🎯 Requisitos

### Funcionais
1. Suporte a sintaxe `{{nome}}`, `{{telefone}}`, `{{setor}}` em textos de mensagens
2. Validação em tempo real detectando variáveis desconhecidas
3. Preview em tempo real mostrando como a mensagem fica com dados reais
4. Botão para inserir variáveis via dropdown (UX melhorada)
5. Validação no backend ao salvar (segurança)

### Não-Funcionais
1. Nenhuma mudança no comportamento existente do FlowService
2. Dados já são capturados do WhatsApp (não há captura adicional)
3. Suportar variáveis nulas/vazias graciosamente (ex: setor não atribuído)

## 🏗️ Arquitetura

### Componentes

#### 1. **VariableResolver** (novo)
Responsável por resolver variáveis a partir de uma Conversation.

```php
namespace App\Services;

class VariableResolver
{
    /**
     * Resolve todas as variáveis disponíveis para uma conversa
     */
    public function resolve(Conversation $conversation): array
    {
        return [
            'nome' => $conversation->contact->name ?? '',
            'telefone' => $conversation->contact->phone ?? '',
            'setor' => $conversation->sector->name ?? ''
        ];
    }

    /**
     * Lista de variáveis disponíveis (para UI)
     */
    public function getAvailableVariables(): array
    {
        return [
            'nome' => 'Nome do contato',
            'telefone' => 'Telefone do contato',
            'setor' => 'Setor de atendimento'
        ];
    }
}
```

#### 2. **VariableValidator** (novo)
Valida texto contendo referências a variáveis.

```php
namespace App\Services;

class VariableValidator
{
    public function __construct(private VariableResolver $resolver) {}

    /**
     * Valida variáveis em um texto
     * Retorna array com warnings e status de validade
     */
    public function validate(string $text): array
    {
        $availableVars = $this->resolver->getAvailableVariables();
        $matches = [];
        
        preg_match_all('/\{\{(\w+)\}\}/', $text, $matches);
        
        $warnings = [];
        foreach ($matches[1] as $varName) {
            if (!isset($availableVars[$varName])) {
                $warnings[] = "Variável desconhecida: {{$varName}}";
            }
        }
        
        return [
            'valid' => count($warnings) === 0,
            'warnings' => $warnings,
            'variables_found' => $matches[1] ?? []
        ];
    }
}
```

#### 3. **FlowService** (modificado)
Refatora `replaceVariables()` para usar `VariableResolver`.

```php
private function replaceVariables(string $text, Conversation $conversation): string
{
    $variables = app(VariableResolver::class)->resolve($conversation);
    
    foreach ($variables as $key => $value) {
        $text = str_replace("{{$key}}", $value, $text);
    }
    
    return $text;
}
```

## 🎨 Interface do Usuário

### Fluxo de Edição de Mensagem

Nas telas `admin/flows/create.blade.php` e `admin/flows/edit.blade.php`:

```html
<div class="mb-6">
    <label>Texto da Mensagem</label>
    <div class="relative">
        <textarea 
            id="message-text"
            name="message"
            class="w-full p-3 border rounded"
            placeholder="Use {{nome}}, {{telefone}}, {{setor}}"
        ></textarea>
        
        <!-- Botão para inserir variável -->
        <button type="button" class="absolute top-2 right-2 bg-gray-200 px-3 py-1 rounded text-sm">
            + Inserir Variável
        </button>
    </div>
    
    <!-- Aviso de validação -->
    <div id="validation-warnings" class="mt-2 text-red-600 text-sm hidden">
        <!-- Warnings aparecem aqui -->
    </div>
    
    <!-- Preview em tempo real -->
    <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
        <p class="text-xs text-gray-600 font-semibold">Preview:</p>
        <p id="message-preview" class="text-gray-800">
            <!-- Preview com dados reais -->
        </p>
    </div>
</div>
```

### Interações JavaScript

1. **Validação em tempo real** (ao digitar)
   - Chama endpoint `/admin/flows/validate-variables` com o texto
   - Mostra/oculta warnings conforme necessário

2. **Preview em tempo real** (ao digitar)
   - Chama endpoint `/admin/flows/preview-variables` com texto
   - Retorna preview renderizado

3. **Dropdown de inserção**
   - Clique em "Inserir Variável"
   - Menu com: Nome | Telefone | Setor
   - Insere `{{variavel}}` na posição do cursor

## 🔌 Endpoints Necessários

### 1. Validação
```
GET /admin/flows/validate-variables
Parameters: 
  - text: string (o texto a validar)
Returns:
  {
    "valid": true/false,
    "warnings": ["Variável desconhecida: {{xyz}}"]
  }
```

### 2. Preview
```
GET /admin/flows/preview-variables
Parameters:
  - text: string (o texto para preview)
  - conversation_id?: int (opcional, para usar dados reais)
Returns:
  {
    "preview": "Olá João Silva, seu setor é Suporte"
  }
```

### 3. Variáveis Disponíveis
```
GET /admin/flows/available-variables
Returns:
  {
    "variables": {
      "nome": "Nome do contato",
      "telefone": "Telefone do contato",
      "setor": "Setor de atendimento"
    }
  }
```

## ⚠️ Tratamento de Erros

### Frontend
- Variável desconhecida: Aviso em vermelho
- Sintaxe inválida `{{nome`: Aviso "Variável incompleta"
- Variável vazia `{{}}`: Aviso "Variável vazia"

### Backend
- Revalidação ao salvar fluxo
- Se houver erro, retorna resposta com detalhes
- Impede salvar fluxo com variáveis inválidas

### Runtime (Execução)
- Se variável não existir (ex: setor é null): substitui por string vazia
- Log do evento para debugging
- Mensagem é enviada normalmente (não falha)

## 🧪 Testes

### Testes Unitários Necessários

```php
// tests/Unit/Services/VariableResolverTest.php
- testResolveWithAllVariables()
- testResolveWithNullValues()
- testGetAvailableVariables()

// tests/Unit/Services/VariableValidatorTest.php
- testValidateValidVariables()
- testValidateInvalidVariables()
- testValidateMultipleVariablesInText()
- testValidateNoVariables()

// tests/Unit/Services/FlowServiceTest.php (existente)
- testReplaceVariablesWithNewResolver()
- testHandleMessageWithVariables()
```

## 📁 Arquivos a Modificar/Criar

### Criar
- `app/Services/VariableResolver.php`
- `app/Services/VariableValidator.php`
- `tests/Unit/Services/VariableResolverTest.php`
- `tests/Unit/Services/VariableValidatorTest.php`
- `resources/views/admin/flows/partials/variable-help.blade.php`
- `public/js/flow-variables.js` (validação e preview)

### Modificar
- `app/Services/FlowService.php` (refatorar replaceVariables)
- `app/Http/Controllers/Admin/FlowController.php` (adicionar endpoints)
- `resources/views/admin/flows/create.blade.php` (UI)
- `resources/views/admin/flows/edit.blade.php` (UI)
- `routes/web.php` (adicionar rotas)

## ✅ Critérios de Sucesso

1. ✅ Variáveis `{{nome}}`, `{{telefone}}`, `{{setor}}` funcionam em mensagens
2. ✅ Validação em tempo real mostra avisos para variáveis inválidas
3. ✅ Preview em tempo real mostra como a mensagem fica
4. ✅ Botão "Inserir Variável" funciona e insere syntaxe corretamente
5. ✅ Backend revalida ao salvar
6. ✅ Testes unitários cobrem os novos serviços
7. ✅ Fluxos existentes continuam funcionando (sem breaking changes)

## 🚀 Próximas Fases (Futuro)

- Variáveis customizadas capturadas durante fluxo
- Lógica condicional (`if {{nome}} contains "João"`)
- Variáveis de data/hora
- Sistema de "variable groups" (ex: contato, atendimento, etc)

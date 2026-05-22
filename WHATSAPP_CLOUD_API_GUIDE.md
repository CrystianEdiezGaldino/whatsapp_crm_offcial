# WhatsApp Cloud API - Guia Completo para ERP de Atendimento

> Documento de referência técnica baseado na documentacao oficial Meta for Developers.
> Ultima atualizacao: 2026-05-21 | API Version: v23.0

---

## Sumario

1. [Visao Geral da Plataforma](#1-visao-geral-da-plataforma)
2. [Onboarding e Configuracao Inicial](#2-onboarding-e-configuracao-inicial)
3. [Chaves API, Tokens e Autenticacao](#3-chaves-api-tokens-e-autenticacao)
4. [Endpoints Base e Headers](#4-endpoints-base-e-headers)
5. [Envio de Mensagens - Todos os Tipos](#5-envio-de-mensagens---todos-os-tipos)
6. [Templates de Mensagens](#6-templates-de-mensagens)
7. [Botoes Interativos (Reply Buttons, Lists, CTA)](#7-botoes-interativos)
8. [Envio de Midia (Imagens, Audio, Documentos, Video, Stickers)](#8-envio-de-midia)
9. [Upload e Gerenciamento de Midia](#9-upload-e-gerenciamento-de-midia)
10. [Webhooks - Recebendo Mensagens](#10-webhooks---recebendo-mensagens)
11. [Janela de Servico ao Cliente (24h)](#11-janela-de-servico-ao-cliente)
12. [Marcando Mensagens como Lidas e Indicadores de Digitacao](#12-marcando-mensagens-como-lidas)
13. [Respostas Contextuais (Quote/Reply)](#13-respostas-contextuais)
14. [Reacoes (Emoji Reactions)](#14-reacoes)
15. [Localizacao](#15-localizacao)
16. [Contatos](#16-contatos)
17. [Gerenciamento de Numeros de Telefone](#17-gerenciamento-de-numeros)
18. [Permissoes e App Review](#18-permissoes-e-app-review)
19. [Precos e Limites](#19-precos-e-limites)
20. [Codigos de Erro Comuns](#20-codigos-de-erro-comuns)
21. [Fluxo Recomendado para ERP de Atendimento](#21-fluxo-recomendado-para-erp)

---

## 1. Visao Geral da Plataforma

A **WhatsApp Business Platform** e composta por:

| Componente | Descricao |
|---|---|
| **Cloud API** | API hospedada pela Meta para enviar/receber mensagens e chamadas |
| **Business Management API** | Gerenciar conta WABA, numeros, templates programaticamente |
| **Marketing Messages API** | Otimizacoes automaticas para mensagens de marketing |
| **Webhooks** | Notificacoes em tempo real de eventos (mensagens recebidas, status de entrega, etc.) |

### Conceitos-chave

- **WABA** - WhatsApp Business Account (conta business)
- **Phone Number ID** - Identificador do numero de telefone business
- **Business Portfolio** - Portfolio empresarial que agrupa contas
- **System User** | Usuario de sistema para tokens de longa duracao
- **Customer Service Window** | Janela de 24h que se abre quando o cliente envia uma mensagem

---

## 2. Onboarding e Configuracao Inicial

### Passo a Passo

1. **Criar conta Meta Developer**: https://developers.facebook.com
2. **Criar um App**: My Apps > Create App > Business
3. **Adicionar produto WhatsApp**: No App Dashboard, selecionar WhatsApp > Set up
4. Isso cria automaticamente:
   - Uma WABA de teste
   - Um numero de telefone business de teste
   - Um template `hello_world` pre-aprovado
5. **Gerar Access Token** (ver secao 3)
6. **Adicionar numeros de destino** (ate 5 para teste)
7. **Enviar mensagem teste** via template `hello_world`
8. **Configurar Webhook** para receber mensagens

### Ambiente de Teste vs Producao

| Aspecto | Teste | Producao |
|---|---|---|
| Mensagens | Gratis (ate 5 numeros) | Pago por conversa |
| Templates | Pre-aprovados (`hello_world`) | Precisam de aprovacao |
| Numero | Fornecido pela Meta | Seu numero real |
| Verificacao | Nao necessaria | Necessaria |

---

## 3. Chaves API, Tokens e Autenticacao

### Tipos de Token

| Tipo | Duracao | Uso |
|---|---|---|
| **User Access Token** | Curta (1-2 horas) | Desenvolvimento/teste |
| **System User Token** | Longa (ate 60 dias, renovavel) | Producao (recomendado) |
| **Business Token** | Longa | Solution Partners |

### Gerar Token de Teste (Desenvolvimento)

1. App Dashboard > WhatsApp > API Setup
2. Clicar "Generate access token"
3. Copiar o token (ele aparece uma unica vez)

### Gerar Token de Sistema (Producao)

1. Business Settings > Users > System Users > Add
2. Atribuir assets (WABA, numero de telefone)
3. Gerar token com permissoes: `whatsapp_business_messaging`, `whatsapp_business_management`

### Permissoes Necessarias

| Permissao | Escopo |
|---|---|
| `whatsapp_business_messaging` | Enviar/receber mensagens |
| `whatsapp_business_management` | Gerenciar templates, conta, numeros |
| `business_management` | Gerenciar portfolio business |

### Formato do Token

```
EAAJBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx...
```

Sempre enviado no header `Authorization: Bearer <TOKEN>`.

---

## 4. Endpoints Base e Headers

### URL Base

```
https://graph.facebook.com/v23.0/
```

### Endpoint Principal de Mensagens

```
POST https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages
```

### Endpoint de Midia (Upload)

```
POST https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/media
```

### Endpoint de Midia (Download)

```
GET https://graph.facebook.com/v23.0/<MEDIA_ID>
```

### Headers Padrao (TODAS as requisicoes)

```
Authorization: Bearer <ACCESS_TOKEN>
Content-Type: application/json
```

### Headers para Upload de Midia

```
Authorization: Bearer <ACCESS_TOKEN>
Content-Type: multipart/form-data   (ou application/octet-stream)
```

### Formato do Numero de Telefone

- Incluir `+` e codigo do pais: `+5511999999999`
- Hifens, parenteses e espacos sao aceitos: `+55 (11) 99999-9999`
- **Sem o `+`**, o codigo do pais do numero business e adicionado automaticamente (pode causar erros)

---

## 5. Envio de Mensagens - Todos os Tipos

### Payload Base (Comum a todos os tipos)

```json
{
  "messaging_product": "whatsapp",
  "recipient_type": "individual",
  "to": "<WHATSAPP_USER_PHONE_NUMBER>",
  "type": "<MESSAGE_TYPE>",
  "<MESSAGE_TYPE>": { "<CONTeUDO>" }
}
```

### 5.1 Mensagem de Texto Simples

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "+5511999999999",
    "type": "text",
    "text": {
      "preview_url": true,
      "body": "Ola! Seu pedido #12345 foi confirmado. Acompanhe: https://meusite.com/pedido/12345"
    }
  }'
```

| Campo | Tipo | Obrigatorio | Descricao |
|---|---|---|---|
| `body` | string | Sim | Texto da mensagem (max 4096 caracteres) |
| `preview_url` | boolean | Nao | Gerar preview de links (default: false) |

### 5.2 Mensagem de Imagem

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "image",
  "image": {
    "id": "<MEDIA_ID>",
    "caption": "Foto do produto X",
    "filename": "produto-x.jpg"
  }
}
```

Ou via link:

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "image",
  "image": {
    "link": "https://seuservidor.com/imagem.jpg",
    "caption": "Foto do produto X"
  }
}
```

| Formato | Tipos suportados | Tamanho max |
|---|---|---|
| Imagem | JPEG, PNG | 5 MB |

### 5.3 Mensagem de Audio

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "audio",
  "audio": {
    "id": "<MEDIA_ID>"
  }
}
```

Ou via link:

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "audio",
  "audio": {
    "link": "https://seuservidor.com/audio.mp3"
  }
}
```

| Formato | Tipos suportados | Tamanho max |
|---|---|---|
| Audio | AAC, MP4, AMR, MP3, OGG (opus) | 16 MB |

### 5.4 Mensagem de Documento

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "document",
  "document": {
    "id": "<MEDIA_ID>",
    "caption": "Contrato de servico",
    "filename": "contrato.pdf"
  }
}
```

| Formato | Tipos suportados | Tamanho max |
|---|---|---|
| Documento | PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT | 100 MB |

### 5.5 Mensagem de Video

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "video",
  "video": {
    "id": "<MEDIA_ID>",
    "caption": "Tutorial de uso do produto"
  }
}
```

| Formato | Tipos suportados | Tamanho max |
|---|---|---|
| Video | MP4, 3GP | 16 MB |

### 5.6 Mensagem de Sticker

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "sticker",
  "sticker": {
    "id": "<MEDIA_ID>"
  }
}
```

| Formato | Tipos suportados | Tamanho max |
|---|---|---|
| Sticker | WEBP (animado ou estatico) | 100 KB estatico, 500 KB animado |

### 5.7 Mensagem de Localizacao

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "location",
  "location": {
    "latitude": -23.5505,
    "longitude": -46.6333,
    "name": "Loja Centro",
    "address": "Rua Augusta, 1000 - Sao Paulo, SP"
  }
}
```

### 5.8 Mensagem de Contatos

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "contacts",
  "contacts": [
    {
      "name": {
        "formatted_name": "Joao Silva",
        "first_name": "Joao",
        "last_name": "Silva"
      },
      "phones": [
        {
          "phone": "+5511999998888",
          "type": "CELL",
          "wa_id": "5511999998888"
        }
      ],
      "emails": [
        {
          "email": "joao@email.com",
          "type": "WORK"
        }
      ]
    }
  ]
}
```

### Resposta de Sucesso (comum a todos os tipos)

```json
{
  "messaging_product": "whatsapp",
  "contacts": [
    {
      "input": "+5511999999999",
      "wa_id": "5511999999999"
    }
  ],
  "messages": [
    {
      "id": "wamid.HBgMNTUxMTk5OTk5OTk5OSAAFhCMODI2RkQwNDlBNjllN0ZBMjM3AA=="
    }
  ]
}
```

> **Importante**: A resposta indica apenas que a API **aceitou** o request. O status de entrega real vem via **webhook**.

---

## 6. Templates de Mensagens

Templates sao **obrigatorios** para iniciar conversas fora da janela de 24h. Toda mensagem que abre uma conversa deve usar um template aprovado.

### Categorias de Template

| Categoria | Uso | Exemplos |
|---|---|---|
| **MARKETING** | Promocoes, ofertas, novos produtos | "Confira nossas ofertas da semana!" |
| **UTILITY** | Notificacoes transacionais | "Seu pedido foi enviado", "Lembrete de consulta" |
| **AUTHENTICATION** | Codigos de verificacao | "Seu codigo e 123456" |

### 6.1 Criar Template via API

```
POST https://graph.facebook.com/v23.0/<WABA_ID>/message_templates
```

#### Template de Texto Simples

```json
{
  "name": "order_confirmation",
  "category": "UTILITY",
  "body_text": [
    {
      "text": "Ola {{1}}, seu pedido {{2}} foi confirmado! Previsao de entrega: {{3}}."
    }
  ],
  "language": "pt_BR"
}
```

#### Template com Header de Imagem + Body + Footer + Botoes

```json
{
  "name": "promo_banner",
  "category": "MARKETING",
  "components": [
    {
      "type": "HEADER",
      "format": "IMAGE",
      "example": {
        "header_handle": [
          "<MEDIA_HANDLE_DO_UPLOAD>"
        ]
      }
    },
    {
      "type": "BODY",
      "text": "Ola {{1}}, aproveite {{2}}% de desconto em toda a loja!",
      "example": {
        "body_text": [
          ["Maria", "30"]
        ]
      }
    },
    {
      "type": "FOOTER",
      "text": "Oferta valida ate 30/12/2026"
    },
    {
      "type": "BUTTONS",
      "buttons": [
        {
          "type": "QUICK_REPLY",
          "text": "Ver ofertas"
        },
        {
          "type": "QUICK_REPLY",
          "text": "Falar com atendente"
        }
      ]
    }
  ],
  "language": "pt_BR"
}
```

### Componentes de Template

| Componente | Descricao | Limite |
|---|---|---|
| **HEADER** | Titulo com imagem/video/documento/texto/localizacao | 1 por template |
| **BODY** | Texto principal (suporta variaveis `{{1}}`, `{{2}}`...) | 1 por template, max 1024 chars |
| **FOOTER** | Texto rodape | 1 por template, max 60 chars |
| **BUTTONS** | Botoes de acao (Quick Reply, URL, Phone, OTP) | Max 10 botoes |

### Tipos de Botao em Templates

| Tipo | Descricao | Limite |
|---|---|---|
| `QUICK_REPLY` | Retorna texto pre-definido como resposta | Max 3 |
| `URL` | Abre URL no navegador | Max 2 |
| `PHONE_NUMBER` | Liga para numero | Max 1 |
| `OTP` | Botao de senha unica (auto-fill) | Max 1 |

### 6.2 Listar Templates

```
GET https://graph.facebook.com/v23.0/<WABA_ID>/message_templates
```

### 6.3 Enviar Template (Parametros Posicionais)

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "to": "+5511999999999",
    "type": "template",
    "template": {
      "name": "order_confirmation",
      "language": {
        "code": "pt_BR"
      },
      "components": [
        {
          "type": "body",
          "parameters": [
            {
              "type": "text",
              "text": "Maria"
            },
            {
              "type": "text",
              "text": "#12345"
            },
            {
              "type": "text",
              "text": "3 dias uteis"
            }
          ]
        }
      ]
    }
  }'
```

### 6.4 Enviar Template com Parametros Nomeados

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "template",
  "template": {
    "name": "order_confirmation",
    "language": { "code": "pt_BR" },
    "components": [
      {
        "type": "body",
        "parameters": [
          {
            "type": "text",
            "parameter_name": "customer_name",
            "text": "Maria"
          },
          {
            "type": "text",
            "parameter_name": "order_id",
            "text": "#12345"
          },
          {
            "type": "text",
            "parameter_name": "delivery_estimate",
            "text": "3 dias uteis"
          }
        ]
      }
    ]
  }
}
```

### 6.5 Enviar Template com Midia (Header Image)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "template",
  "template": {
    "name": "promo_banner",
    "language": { "code": "pt_BR" },
    "components": [
      {
        "type": "header",
        "parameters": [
          {
            "type": "image",
            "image": {
              "link": "https://seuservidor.com/banner-promo.jpg"
            }
          }
        ]
      },
      {
        "type": "body",
        "parameters": [
          { "type": "text", "text": "Maria" },
          { "type": "text", "text": "30" }
        ]
      }
    ]
  }
}
```

### Tipos de Parametro em Components

| Tipo | Uso |
|---|---|
| `text` | Texto simples |
| `currency` | Valor monetario (code, amount_1000, fallback_value) |
| `date_time` | Data/hora (fallback_value) |
| `image` | Imagem no header |
| `video` | Video no header |
| `document` | Documento no header |
| `location` | Localizacao no header |

### Status de Aprovacao do Template

| Status | Significado |
|---|---|
| `PENDING` | Aguardando revisao |
| `APPROVED` | Aprovado e pronto para uso |
| `REJECTED` | Rejeitado (viola guidelines) |
| `PAUSED` | Pausado por pacing ou qualidade |

### 6.6 Deletar Template

```
DELETE https://graph.facebook.com/v23.0/<WABA_ID>/message_templates?name=<TEMPLATE_NAME>
```

---

## 7. Botoes Interativos

Botoes interativos podem ser enviados **sem template** (dentro da janela de 24h) usando `type: "interactive"`.

### 7.1 Reply Buttons (Ate 3 botoes)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "interactive",
  "interactive": {
    "type": "button",
    "body": {
      "text": "Como posso ajudar?"
    },
    "action": {
      "buttons": [
        {
          "type": "reply",
          "reply": {
            "id": "btn_financeiro",
            "title": "Financeiro"
          }
        },
        {
          "type": "reply",
          "reply": {
            "id": "btn_suporte",
            "title": "Suporte Tecnico"
          }
        },
        {
          "type": "reply",
          "reply": {
            "id": "btn_vendas",
            "title": "Vendas"
          }
        }
      ]
    }
  }
}
```

- Max **3 botoes**
- `id`: identificador (retornado no webhook quando clicado)
- `title`: texto do botao (max 20 caracteres)

### 7.2 List Buttons (Menu de Opcoes)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "interactive",
  "interactive": {
    "type": "list",
    "header": {
      "type": "text",
      "text": "Menu de Opcoes"
    },
    "body": {
      "text": "Selecione uma das opcoes abaixo:"
    },
    "footer": {
      "text": "SMCC Atendimento"
    },
    "action": {
      "button": "Ver opcoes",
      "sections": [
        {
          "title": "Setores",
          "rows": [
            {
              "id": "row_financeiro",
              "title": "Financeiro",
              "description": "Dvidas sobre pagamentos e faturas"
            },
            {
              "id": "row_suporte",
              "title": "Suporte",
              "description": "Problemas tecnicos e duvidas"
            }
          ]
        },
        {
          "title": "Outros",
          "rows": [
            {
              "id": "row_falar_atendente",
              "title": "Falar com atendente",
              "description": "Atendimento humano"
            }
          ]
        }
      ]
    }
  }
}
```

- Max **10 rows** total (todas as secoes)
- `button`: texto do botao que abre a lista (max 20 chars)
- `rows[].title`: max 24 chars
- `rows[].description`: max 72 chars (opcional)

### 7.3 CTA URL Button

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "interactive",
  "interactive": {
    "type": "cta_url",
    "header": {
      "type": "text",
      "text": "Acompanhe seu pedido"
    },
    "body": {
      "text": "Clique no botao abaixo para ver o status do seu pedido #12345"
    },
    "footer": {
      "text": "Entrega em 3 dias uteis"
    },
    "action": {
      "name": "cta_url",
      "parameters": {
        "display_text": "Rastrear Pedido",
        "url": "https://seusite.com/rastrear/12345"
      }
    }
  }
}
```

### 7.4 Location Request (Solicitar Localizacao)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "interactive",
  "interactive": {
    "type": "location_request_message",
    "body": {
      "text": "Compartilhe sua localizacao para encontrar a loja mais proxima."
    },
    "action": {
      "name": "send_location"
    }
  }
}
```

### 7.5 Media Carousel (Carrossel de Cards)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "interactive",
  "interactive": {
    "type": "carousel",
    "body": {
      "text": "Confira nossos produtos em destaque:"
    },
    "action": {
      "cards": [
        {
          "header": {
            "type": "image",
            "image": {
              "id": "<MEDIA_ID_1>"
            }
          },
          "body": {
            "text": "Produto A - R$ 99,90"
          },
          "action": {
            "buttons": [
              {
                "type": "reply",
                "reply": {
                  "id": "buy_product_a",
                  "title": "Comprar"
                }
              }
            ]
          }
        },
        {
          "header": {
            "type": "image",
            "image": {
              "id": "<MEDIA_ID_2>"
            }
          },
          "body": {
            "text": "Produto B - R$ 149,90"
          },
          "action": {
            "buttons": [
              {
                "type": "reply",
                "reply": {
                  "id": "buy_product_b",
                  "title": "Comprar"
                }
              }
            ]
          }
        }
      ]
    }
  }
}
```

---

## 8. Envio de Midia

### Formas de enviar midia

| Metodo | Descricao | Recomendado |
|---|---|---|
| `id` | Upload previo, usa o media ID retornado | **Sim** (mais confiavel) |
| `link` | URL publica do seu servidor | Nao (requer servidor acessivel) |

### Referencia rapida - Tipos de midia

| Tipo | Campo | Formatos | Tamanho Max |
|---|---|---|---|
| `image` | `image.id` ou `image.link` | JPEG, PNG | 5 MB |
| `audio` | `audio.id` ou `audio.link` | AAC, MP4, AMR, MP3, OGG | 16 MB |
| `document` | `document.id` ou `document.link` | PDF, DOC(X), XLS(X), PPT(X), TXT | 100 MB |
| `video` | `video.id` ou `video.link` | MP4, 3GP | 16 MB |
| `sticker` | `sticker.id` | WEBP | 100 KB / 500 KB |

### Midia com Link (exemplo completo - Audio)

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "to": "+5511999999999",
    "type": "audio",
    "audio": {
      "link": "https://seuservidor.com/audio/resposta-atendente.mp3"
    }
  }'
```

### Midia com ID (exemplo completo - Documento)

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "to": "+5511999999999",
    "type": "document",
    "document": {
      "id": "<MEDIA_ID_DO_UPLOAD>",
      "caption": "Proposta comercial - Cliente XYZ",
      "filename": "proposta-xyz.pdf"
    }
  }'
```

### Cache de Midia

- Midia enviada via `link` e cacheada por **10 minutos**
- Requisicoes subsequentes com o mesmo link reusam o cache
- Para forcar novo fetch: adicionar query string aleatoria (`?v=123`)

---

## 9. Upload e Gerenciamento de Midia

### Upload de Midia

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/media' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -F 'file=@/caminho/para/arquivo.pdf' \
  -F 'type=application/pdf' \
  -F 'messaging_product=whatsapp'
```

**Resposta:**

```json
{
  "id": "<MEDIA_ID>"
}
```

### Tipos MIME Aceitos

| Tipo | MIME |
|---|---|
| Imagem | `image/jpeg`, `image/png` |
| Audio | `audio/aac`, `audio/mp4`, `audio/mpeg`, `audio/amr`, `audio/ogg` |
| Video | `video/mp4`, `video/3gp` |
| Documento | `application/pdf`, `application/msword`, etc. |
| Sticker | `image/webp` |

### Recuperar URL de Download

```bash
curl -X GET 'https://graph.facebook.com/v23.0/<MEDIA_ID>' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>'
```

**Resposta:**

```json
{
  "url": "https://lookaside.fbsbx.com/whatsapp_cloud/...",
  "messaging_product": "whatsapp",
  "mime_type": "image/jpeg",
  "file_size": 123456,
  "id": "<MEDIA_ID>"
}
```

> A URL retornada expira rapidamente. Baixe o arquivo imediatamente.

### Deletar Midia

```
DELETE https://graph.facebook.com/v23.0/<MEDIA_ID>
```

---

## 10. Webhooks - Recebendo Mensagens

### Configurar Webhook

1. Criar endpoint HTTPS no seu servidor (ex: `https://seuservidor.com/webhook`)
2. No App Dashboard > WhatsApp > Configuration > Webhook
3. Inserir Callback URL e Verify Token
4. Assinar os campos necessarios

### Verificacao do Webhook (GET)

Quando a Meta registra o webhook, envia um GET:

```
GET https://seuservidor.com/webhook?hub.mode=subscribe&hub.challenge=CHALLENGE&hub.verify_token=YOUR_VERIFY_TOKEN
```

Responda com o valor de `hub.challenge` (HTTP 200).

### Recebendo Mensagens (POST)

A Meta envia POST para seu webhook a cada evento.

#### Exemplo: Mensagem de Texto Recebida

```json
{
  "object": "whatsapp_business_account",
  "entry": [
    {
      "id": "WABA_ID",
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "5511999990000",
              "phone_number_id": "PHONE_NUMBER_ID"
            },
            "contacts": [
              {
                "profile": {
                  "name": "Maria Silva"
                },
                "wa_id": "5511999999999"
              }
            ],
            "messages": [
              {
                "from": "5511999999999",
                "id": "wamid.HBgMNTUxMTk5OTk5OTk5OSAAFhCM...",
                "timestamp": "1716300000",
                "text": {
                  "body": "Ola, preciso de ajuda com meu pedido"
                },
                "type": "text"
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

#### Exemplo: Imagem Recebida

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "image",
      "image": {
        "caption": "Foto do defeito",
        "id": "<MEDIA_ID>",
        "mime_type": "image/jpeg",
        "sha256": "abc123..."
      }
    }
  ]
}
```

#### Exemplo: Audio Recebido

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "audio",
      "audio": {
        "id": "<MEDIA_ID>",
        "mime_type": "audio/ogg; codecs=opus",
        "sha256": "abc123..."
      }
    }
  ]
}
```

#### Exemplo: Documento Recebido

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "document",
      "document": {
        "caption": "Comprovante de pagamento",
        "filename": "comprovante.pdf",
        "id": "<MEDIA_ID>",
        "mime_type": "application/pdf",
        "sha256": "abc123..."
      }
    }
  ]
}
```

#### Exemplo: Botao Clicado (Reply Button)

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "button",
      "button": {
        "payload": "btn_financeiro",
        "text": "Financeiro"
      },
      "context": {
        "id": "wamid.yyy",
        "forwarded": false
      }
    }
  ]
}
```

#### Exemplo: List Button Clicado

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "interactive",
      "interactive": {
        "list_reply": {
          "id": "row_suporte",
          "title": "Suporte",
          "description": "Problemas tecnicos e duvidas"
        },
        "type": "list_reply"
      }
    }
  ]
}
```

### Status de Mensagem Enviada (Webhook)

```json
{
  "entry": [
    {
      "changes": [
        {
          "value": {
            "messaging_product": "whatsapp",
            "metadata": {
              "display_phone_number": "5511999990000",
              "phone_number_id": "PHONE_NUMBER_ID"
            },
            "statuses": [
              {
                "id": "wamid.HBgMNTUx...",
                "status": "delivered",
                "timestamp": "1716300001",
                "recipient_id": "5511999999999"
              }
            ]
          },
          "field": "messages"
        }
      ]
    }
  ]
}
```

**Status possiveis:**

| Status | Significado |
|---|---|
| `sent` | Mensagem enviada ao servidor WhatsApp |
| `delivered` | Mensagem entregue no dispositivo |
| `read` | Mensagem lida pelo usuario |
| `failed` | Falha no envio |

### Campos de Webhook para Assinar

| Campo | Evento |
|---|---|
| `messages` | Mensagens recebidas + status (sent, delivered, read, failed) |
| `message_template_status_update` | Status de aprovacao de templates |
| `message_template_quality_update` | Qualidade de templates |
| `phone_number_name_update` | Atualizacao do nome do numero |
| `phone_number_quality_update` | Qualidade do numero |

---

## 11. Janela de Servico ao Cliente (24h)

### Regra Fundamental

| Janela aberta? | O que pode enviar |
|---|---|
| **Sim** (cliente enviou msg nas ultimas 24h) | Qualquer tipo de mensagem (texto, midia, interativa) |
| **Nao** | Apenas **templates aprovados** |

### Como a janela funciona

1. Cliente envia mensagem → janela de 24h se abre
2. Cada nova mensagem do cliente renova a janela
3. Apos 24h sem interacao, janela fecha
4. Para reabrir, e necessario enviar um template aprovado

### Implicacao para o ERP

- O ERP deve **detectar** quando a janela esta aberta/fechada
- Para iniciar atendimento proativo: usar template
- Mensagens livres (fora de template) so dentro da janela

---

## 12. Marcando Mensagens como Lidas

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "status": "read",
    "message_id": "wamid.HBgMNTUxMTk5OTk5OTk5OSAAFhCM..."
  }'
```

### Indicador de Digitacao

```bash
curl -X POST 'https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/messages' \
  -H 'Authorization: Bearer <ACCESS_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "messaging_product": "whatsapp",
    "recipient_type": "individual",
    "to": "+5511999999999",
    "type": "reaction",
    "reaction": {
      "message_id": "wamid.HBgMNTUx...",
      "emoji": ""
    }
  }'
```

> **Nota**: Typing indicator e implementado via SDK/Meta Business Platform features. Verifique a documentacao mais recente.

---

## 13. Respostas Contextuais (Quote/Reply)

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "text",
  "context": {
    "message_id": "wamid.HBgMNTUx..."
  },
  "text": {
    "body": "Sim, seu pedido ja saiu para entrega!"
  }
}
```

Isso exibe a mensagem original citada acima da resposta, como no WhatsApp normal.

---

## 14. Reacoes

### Adicionar Reacao

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "reaction",
  "reaction": {
    "message_id": "wamid.HBgMNTUx...",
    "emoji": "👍"
  }
}
```

### Remover Reacao

```json
{
  "messaging_product": "whatsapp",
  "to": "+5511999999999",
  "type": "reaction",
  "reaction": {
    "message_id": "wamid.HBgMNTUx...",
    "emoji": ""
  }
}
```

---

## 15. Localizacao

### Enviar Localizacao (ver secao 5.7)

### Receber Localizacao (via Webhook)

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "timestamp": "1716300000",
      "type": "location",
      "location": {
        "latitude": "-23.5505",
        "longitude": "-46.6333",
        "name": "Minha Localizacao",
        "address": "Rua Augusta, 1000"
      }
    }
  ]
}
```

---

## 16. Contatos

### Enviar Contatos (ver secao 5.8)

### Receber Contatos (via Webhook)

```json
{
  "messages": [
    {
      "from": "5511999999999",
      "id": "wamid.xxx",
      "type": "contacts",
      "contacts": [
        {
          "name": { "formatted_name": "Joao Silva" },
          "phones": [{ "phone": "+5511999998888" }]
        }
      ]
    }
  ]
}
```

---

## 17. Gerenciamento de Numeros

### Registrar Numero via API

```
POST https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>/register
```

```json
{
  "messaging_product": "whatsapp",
  "pin": "123456"
}
```

### Verificacao em Duas Etapas

```
POST https://graph.facebook.com/v23.0/<PHONE_NUMBER_ID>
```

```json
{
  "messaging_product": "whatsapp",
  "two_step_verification": {
    "pin": "123456"
  }
}
```

### Listar Numeros da WABA

```
GET https://graph.facebook.com/v23.0/<WABA_ID>/phone_numbers
```

---

## 18. Permissoes e App Review

### Para Producao

Antes de ir para producao, o App precisa passar pelo **App Review**:

1. Submeter para revisao com:
   - Casos de uso detalhados
   - Video demonstrativo
   - Capturas de tela
2. Permissoes a solicitar:
   - `whatsapp_business_messaging`
   - `whatsapp_business_management`
3. Apos aprovacao, gerar **System User Token** de longa duracao

### Limites do Ambiente de Teste

- Max 5 numeros de destino
- Apenas template `hello_world`
- Tokens de curta duracao

---

## 19. Precos e Limites

### Modelo de Precificacao (Por Conversa)

| Categoria | Custo | Janela |
|---|---|---|
| **Marketing** | Mais caro | 24h por conversa |
| **Utility** | Medio | 24h por conversa |
| **Authentication** | Mais barato | 24h por conversa |
| **Service** (resposta ao cliente) | Medio | 24h por conversa |

> Precos variam por pais. Verificar tabela atual em:
> https://developers.facebook.com/docs/whatsapp/pricing

### Limites de Mensagens

| Metrica | Limite |
|---|---|
| Mensagens por segundo | Configuravel (ate 80 por padrao) |
| Template parameters | Max 1024 chars cada |
| Texto de mensagem | Max 4096 chars |
| Botoes Quick Reply | Max 3 |
| Rows em List | Max 10 total |

### Limites de Envio por Nivel de Qualidade

| Nivel | Limite de clientes unicos/dia |
|---|---|
| Tier 1 (novo) | 1.000 |
| Tier 2 | 10.000 |
| Tier 3 | 100.000 |
| Tier 4 | Ilimitado |

---

## 20. Codigos de Erro Comuns

| Codigo | Descricao | Acao |
|---|---|---|
| `100` | Parametro invalido | Verificar payload |
| `131000` | Numero de telefone invalido | Verificar formato |
| `131008` | Template nao existe | Verificar nome e idioma |
| `131009` | Template rejeitado | Verificar conteudo |
| `131026` | Janela de 24h expirada | Usar template aprovado |
| `131042` | Limite de mensagens atingido | Aguardar ou subir tier |
| `131049` | Limite de marketing por usuario | Aguardar 24h |
| `131050` | Usuario optou por nao receber marketing | Respeitar preferencia |
| `131053` | Media upload falhou | Verificar formato/tamanho |
| `131054` | Token invalido | Regenerar token |

### Formato de Erro na Resposta

```json
{
  "error": {
    "message": "Invalid parameter",
    "type": "OAuthException",
    "code": 100,
    "error_subcode": 131008,
    "fbtrace_id": "Abc123..."
  }
}
```

---

## 21. Fluxo Recomendado para ERP de Atendimento

### Arquitetura de Alto Nivel

```
[Cliente WhatsApp] <--> [Meta Cloud API] <--> [Seu Servidor/Webhook]
                                                    |
                                              [ERP SMCC]
                                            /     |      \
                                      [Banco] [Fila] [Agentes]
```

### Fluxo Principal

```
1. Cliente envia mensagem
   └─> Webhook POST recebido no servidor
       └─> Parse do tipo de mensagem (text/image/audio/document/location)
       └─> Salvar no banco (contato, mensagem, midia)
       └─> Verificar se tem atendente disponivel
           ├─> Sim: Roteirizar para atendente
           └─> Nao: Enviar menu interativo (bot auto-reply)
               └─> Atendente fica disponivel → repassar conversa

2. Atendente responde
   └─> ERP envia via Cloud API
       ├─> Texto, imagem, audio, documento, video
       ├─> Botoes interativos (menu de opcoes)
       └─> Template (se janela de 24h fechou)

3. ERP envia notificacao proativa
   └─> Verificar se janela de 24h esta aberta
       ├─> Sim: Enviar mensagem livre
       └─> Nao: Enviar template aprovado
```

### Templates Recomendados para o ERP

| Template | Categoria | Uso |
|---|---|---|
| `welcome_message` | UTILITY | Primeira mensagem/boas-vindas |
| `order_update` | UTILITY | Atualizacao de pedido |
| `payment_reminder` | UTILITY | Lembrete de pagamento |
| `appointment_reminder` | UTILITY | Lembrete de agendamento |
| `promotional_offer` | MARKETING | Ofertas e promocoes |
| `satisfaction_survey` | MARKETING | Pesquisa de satisfacao |
| `auth_code` | AUTHENTICATION | Verificacao de identidade |

### Variaveis de Ambiente Necessarias

```env
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_WABA_ID=your_waba_id
WHATSAPP_WEBHOOK_VERIFY_TOKEN=your_verify_token
WHATSAPP_WEBHOOK_URL=https://seuservidor.com/webhook/whatsapp
WHATSAPP_API_VERSION=v23.0
```

### Checklist de Implementacao

- [ ] Criar App no Meta Developer
- [ ] Configurar WhatsApp Business
- [ ] Gerar System User Token (producao)
- [ ] Configurar Webhook (callback URL + verify token)
- [ ] Assinar campo `messages` no webhook
- [ ] Criar templates (UTILITY, MARKETING, AUTH)
- [ ] Aprovar templates via App Review
- [ ] Implementar endpoint de recebimento (POST /webhook)
- [ ] Implementar endpoint de verificacao (GET /webhook)
- [ ] Implementar envio de texto
- [ ] Implementar envio de imagem/audio/documento/video
- [ ] Implementar botoes interativos (menu de atendimento)
- [ ] Implementar upload de midia
- [ ] Implementar download de midia recebida
- [ ] Implementar marcacao de mensagens lidas
- [ ] Implementar respostas contextuais (quote)
- [ ] Implementar gestao da janela de 24h
- [ ] Implementar roteirizacao de atendentes
- [ ] Testar fluxo end-to-end
- [ ] Submeter App Review para producao

---

## Referencias Oficiais

| Recurso | URL |
|---|---|
| Overview | https://developers.facebook.com/documentation/business-messaging/whatsapp/overview |
| Get Started | https://developers.facebook.com/docs/whatsapp/cloud-api/get-started |
| Send Messages | https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages |
| Template Messages | https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates |
| Webhooks | https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks |
| Media API | https://developers.facebook.com/docs/whatsapp/cloud-api/reference/media |
| Pricing | https://developers.facebook.com/docs/whatsapp/pricing |
| Error Codes | https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes |
| API Reference | https://developers.facebook.com/docs/whatsapp/cloud-api/reference |
| Message Templates API | https://developers.facebook.com/docs/whatsapp/cloud-api/reference/message-templates |

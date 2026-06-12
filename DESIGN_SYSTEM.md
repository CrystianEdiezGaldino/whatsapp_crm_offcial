# 🎨 SisZap Design System

Extraído do design de exemplo: `desinger/SisZap Atendimento Standalone.html`

---

## 📋 Paleta de Cores

### Cores Primárias
| Nome | Hex | RGB | Uso |
|------|-----|-----|-----|
| **Verde Primário** | `#1DA85A` | rgb(29, 168, 90) | Botões, destaques, ação |
| **Azul Primário** | `#4353E8` | rgb(67, 83, 232) | Links, destaque secundário |
| **Laranja** | `#B45309` | rgb(180, 83, 9) | Avisos, status |
| **Vermelho** | `#D1383E` | rgb(209, 56, 62) | Erros, crítico |
| **Verde Claro** | `#22C55E` | rgb(34, 197, 94) | Sucesso, aprovado |
| **Amarelo** | `#F59E0B` | rgb(245, 158, 11) | Atenção, warning |

### Neutros (Escalas de Cinza)
| Nome | Hex | RGB | Uso |
|------|-----|-----|-----|
| **Branco** | `#FFFFFF` | rgb(255, 255, 255) | Fundo principal, cards |
| **Cinza Muito Claro** | `#F7F8FB` | rgb(247, 248, 251) | Fundo secundário, hover |
| **Cinza Claro** | `#F0F2F7` | rgb(240, 242, 247) | Botões secundários, campos |
| **Cinza Médio Claro** | `#E8EAF0` | rgb(232, 234, 240) | Borders, dividers |
| **Cinza Médio** | `#9CA3AF` | rgb(156, 163, 175) | Texto secundário |
| **Cinza Escuro** | `#6B7280` | rgb(107, 114, 128) | Texto padrão |
| **Cinza Muito Escuro** | `#3A4154` | rgb(58, 65, 84) | Texto forte |
| **Preto** | `#14171F` | rgb(20, 23, 31) | Fundo dark, texto máximo contraste |

### Variações de Fundo (Tinted)
- **Fundo Verde Claro**: `#E8F8EF` (sucesso, confirmação)
- **Fundo Amarelo Claro**: `#FFF7DE` (aviso, atenção)
- **Fundo Vermelho Claro**: `#F8D2D4` (erro, falha)
- **Fundo Azul Claro**: `#EEF0FE` (info, informação)

---

## 📏 Espaçamento

### Border Radius (Cantos Arredondados)
```css
border-radius: 99px;   /* Totalmente redondo (buttons, badges) */
border-radius: 16px;   /* Cards, containers grandes */
border-radius: 14px;   /* Campos, botões médios */
border-radius: 12px;   /* Elementos médios */
border-radius: 11px;   /* Elementos pequenos */
border-radius: 10px;   /* Muito pequeno */
border-radius: 9px;    /* Minimal */
```

### Gaps (Espaçamento interno entre elementos)
```css
gap: 14px;   /* Grande */
gap: 12px;   /* Médio-Grande */
gap: 10px;   /* Médio */
gap: 9px;    /* Médio-Pequeno */
gap: 8px;    /* Pequeno */
gap: 6px;    /* Muito pequeno */
gap: 4px;    /* Minimal */
```

### Paddings (Preenchimento interno)
```css
padding: 24px;           /* Containers grandes */
padding: 22px;           /* Cards principais */
padding: 18px 16px 12px; /* Inputs, campos */
padding: 16px 18px;      /* Buttons, elementos médios */
padding: 14px 16px;      /* Buttons pequenos */
padding: 14px 18px;      /* Menu items */
padding: 12px 18px;      /* Compact buttons */
padding: 9px 14px;       /* Small buttons */
padding: 9px 13px;       /* Badge buttons */
padding: 4px 10px;       /* Tiny badges */
padding: 0 12px;         /* Text spacing */
padding: 0 14px;         /* Text spacing */
padding: 0 16px;         /* Text spacing */
```

---

## 🔤 Tipografia

### Font Weights
- **400**: Regular (corpo de texto)
- **500**: Medium (textos secundários)
- **600**: Semibold (labels, subtítulos)
- **700**: Bold (títulos, destaque)
- **800**: Extra Bold (títulos muito grandes)

### Font Sizes
```
10.5px   → Muito pequeno (captions, helper text)
11px     → Extra pequeno (labels)
11.5px   → Pequeno (tags, badges)
12px     → Body small
12.5px   → Body text
13px     → Body padrão
13.5px   → Body large
14px     → Lead text
14.5px   → Subtitle
15px     → Large subtitle
16px     → Subheading
17px     → Subheading grande
18px     → Title
20px     → Title grande
26px     → Heading principal
```

### Line Height
```
line-height: 1.45   → Normal
line-height: 1.5    → Generous
```

---

## 🎯 Componentes Padrão

### Buttons
#### Primário (Verde)
```css
background: #1DA85A;
color: #FFFFFF;
padding: 12px 18px;
border-radius: 14px;
font-weight: 700;
font-size: 14px;
```

#### Secundário (Cinza)
```css
background: #F0F2F7;
color: #3A4154;
padding: 12px 18px;
border-radius: 14px;
font-weight: 700;
font-size: 14px;
```

#### Estados
- **Hover**: Elevação (box-shadow aumentada), cor ligeiramente mais escura
- **Active**: Pressionado (translateY 0), sombra interna
- **Disabled**: Opacidade 60%, cursor not-allowed

### Inputs & Textareas
```css
background: #FFFFFF;
border: 1px solid #E8EAF0;
border-radius: 12px;
padding: 12px 14px;
font-size: 13px;
color: #3A4154;
```

**Focus:**
```css
border-color: #4353E8;
box-shadow: 0 0 0 3px rgba(67, 83, 232, 0.1);
```

### Cards
```css
background: #FFFFFF;
border-radius: 12px;
padding: 18px;
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
```

**Hover:**
```css
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
transform: translateY(-1px);
```

### Badges & Tags
```css
background: #EEF0FE;
color: #4353E8;
padding: 4px 10px;
border-radius: 99px;
font-weight: 700;
font-size: 10.5px;
```

---

## 📦 Implementação em Tailwind + Custom CSS

### Configuração Recomendada
```js
// tailwind.config.js
module.exports = {
  theme: {
    colors: {
      'primary': '#1DA85A',
      'secondary': '#4353E8',
      'success': '#22C55E',
      'warning': '#F59E0B',
      'error': '#D1383E',
      'white': '#FFFFFF',
      'gray': {
        50: '#F7F8FB',
        100: '#F0F2F7',
        200: '#E8EAF0',
        400: '#9CA3AF',
        600: '#6B7280',
        700: '#3A4154',
        900: '#14171F',
      }
    },
    spacing: {
      '2': '4px',
      '3': '6px',
      '4': '8px',
      '5': '10px',
      '6': '12px',
      '8': '14px',
      '9': '16px',
      '10': '18px',
      '12': '22px',
      '16': '24px',
    },
    borderRadius: {
      'none': '0px',
      'xs': '9px',
      'sm': '10px',
      'md': '12px',
      'lg': '14px',
      'xl': '16px',
      'full': '99px',
    }
  }
}
```

---

## 🔗 Exemplo de Uso

### Button Primário
```blade
<button class="btn-primary">Enviar</button>
```

### Input
```blade
<input type="text" class="input-primary" placeholder="Digite aqui...">
```

### Card
```blade
<div class="card-primary">
    <h3 class="text-lg font-bold text-gray-900">Título</h3>
    <p class="text-sm text-gray-600">Conteúdo</p>
</div>
```

### Badge
```blade
<span class="badge badge-success">Aprovado</span>
```

---

## ✨ Padrões Visuais

### Sombras Recomendadas
```css
/* Subtle */
box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

/* Normal */
box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);

/* Elevated */
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);

/* Deep */
box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
```

### Transições
```css
transition: all 0.2s ease-in-out;  /* Rápido */
transition: all 0.3s ease-in-out;  /* Normal */
```

---

**Versão:** 1.0  
**Extraído de:** SisZap Atendimento Standalone Design  
**Data:** 2026-06-12

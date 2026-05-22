---
name: Efficient Communication Design System
colors:
  surface: '#f7f9fb'
  surface-dim: '#d8dadc'
  surface-bright: '#f7f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f6'
  surface-container: '#eceef0'
  surface-container-high: '#e6e8ea'
  surface-container-highest: '#e0e3e5'
  on-surface: '#191c1e'
  on-surface-variant: '#45464d'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f3'
  outline: '#76777d'
  outline-variant: '#c6c6cd'
  surface-tint: '#565e74'
  primary: '#000000'
  on-primary: '#ffffff'
  primary-container: '#131b2e'
  on-primary-container: '#7c839b'
  inverse-primary: '#bec6e0'
  secondary: '#006d2f'
  on-secondary: '#ffffff'
  secondary-container: '#5dfd8a'
  on-secondary-container: '#007232'
  tertiary: '#000000'
  on-tertiary: '#ffffff'
  tertiary-container: '#0b1c30'
  on-tertiary-container: '#75859d'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dae2fd'
  primary-fixed-dim: '#bec6e0'
  on-primary-fixed: '#131b2e'
  on-primary-fixed-variant: '#3f465c'
  secondary-fixed: '#66ff8e'
  secondary-fixed-dim: '#3de273'
  on-secondary-fixed: '#002109'
  on-secondary-fixed-variant: '#005322'
  tertiary-fixed: '#d3e4fe'
  tertiary-fixed-dim: '#b7c8e1'
  on-tertiary-fixed: '#0b1c30'
  on-tertiary-fixed-variant: '#38485d'
  background: '#f7f9fb'
  on-background: '#191c1e'
  surface-variant: '#e0e3e5'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 14px
  chat-bubble:
    fontFamily: Inter
    fontSize: 14.5px
    fontWeight: '400'
    lineHeight: 21px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  sidebar-width: 260px
  gutter: 16px
---

## Brand & Style

This design system is engineered for a high-performance ERP environment focused on WhatsApp customer service. The brand personality is rooted in **reliability** and **efficiency**, ensuring that agents can handle high volumes of data without cognitive overload. 

The aesthetic follows a **Corporate/Modern** style, blending the structured nature of enterprise software with the fluidity of modern messaging platforms. We utilize a "Work-Focused Minimalist" approach: high density where data matters, and generous whitespace where focus is required. The visual language is punctuated by tech-forward elements like subtle backdrop blurs on floating panels and precise, thin-stroke iconography to reinforce a sense of precision and professional-grade tooling.

## Colors

The palette is strategically split between brand identity and operational utility. 

- **Deep Navy (#0F172A):** Used primarily for structural navigation and primary actions to establish an anchor of authority and stability.
- **WhatsApp Green (#25D366):** Reserved strictly for platform-specific cues, positive status indicators, and primary "Send" actions. It is an accent, not a background color, ensuring it remains high-contrast and meaningful.
- **Slate Grays:** A range of grays (from #64748B to #F8FAFC) forms the backbone of the interface, separating content zones, borders, and secondary text without creating visual noise.
- **Status Colors:** Use standard semantic reds for errors/overdue chats and ambers for pending/warning states, but keep them desaturated to match the professional slate environment.

## Typography

**Inter** is the sole typeface for this design system, chosen for its exceptional legibility in data-dense environments and its neutral, "invisible" quality. 

- **Hierarchy:** Use bold weights (600-700) sparingly for page titles and navigation headers. 
- **Data Density:** Body-md (14px) is the workhorse for the ERP. Use it for table rows and message previews.
- **Micro-copy:** Label-sm is used for timestamps and metadata. The uppercase transformation helps distinguish technical data from conversational text.
- **Chat Specifics:** The `chat-bubble` role uses a slightly larger line height (21px) than standard body text to improve readability during rapid scrolling.

## Layout & Spacing

The layout utilizes a **Fixed-Fluid Hybrid** model. A fixed-width sidebar (260px) in Deep Navy houses the primary ERP navigation, while the main content area is fluid to accommodate wide data tables and multi-pane chat views.

- **Grid:** We use an 8px baseline grid. All margins and paddings must be multiples of 8px (or 4px for tight component internals).
- **Table Layout:** Tables should use a condensed vertical padding (8px) to maximize information density on laptop screens.
- **Chat View:** A three-pane layout is standard: Sidebar (Nav) > Inbox (List) > Workspace (Chat + CRM Details).
- **Breakpoints:** 
  - *Desktop (1440px+):* Full three-pane view.
  - *Tablet (1024px):* Collapse CRM details into a toggleable drawer.
  - *Mobile (768px):* Single-pane view with bottom navigation bar.

## Elevation & Depth

This design system uses **Tonal Layers** and **Low-Contrast Outlines** rather than heavy shadows to maintain a clean, "pro-tool" feel.

- **Level 0 (Background):** Slate-50 (#F8FAFC). The canvas for the entire application.
- **Level 1 (Cards/Panels):** White (#FFFFFF) with a 1px solid border in Slate-200 (#E2E8F0). No shadow.
- **Level 2 (Dropdowns/Modals):** White (#FFFFFF) with a soft ambient shadow (0px 4px 20px rgba(15, 23, 42, 0.08)) to indicate interactivity and temporary state.
- **Active State:** Use a 2px left-border accent in WhatsApp Green for active chat threads or selected navigation items.

## Shapes

The shape language is **Soft (0.25rem)** to maintain a professional, architectural feel. 

- **Components:** Buttons, input fields, and cards all use a 4px (0.25rem) radius.
- **Chat Bubbles:** Inbound bubbles use 4px on all corners except the bottom-left (0px). Outbound bubbles use 4px except the bottom-right (0px).
- **Status Badges:** Use a higher roundedness (rounded-lg: 8px) to differentiate them from interactive buttons.
- **Avatars:** Strictly circular to provide a soft counterpoint to the otherwise rectangular grid.

## Components

### Buttons
- **Primary:** Deep Navy background with white text. High-contrast.
- **Success/WhatsApp:** WhatsApp Green background with white text. Reserved for "Start Chat" or "Resolve."
- **Ghost:** Transparent background with Slate-600 text. Used for secondary actions in tables.

### Rich Data Tables
- Header background: Slate-50.
- Border-bottom only: 1px Slate-200.
- Hover state: Slate-50 background transition.
- Use truncated text for long messages with a tooltip on hover.

### Chat Bubbles
- **Agent (Outbound):** Deep Navy background, white text. Aligned right.
- **Customer (Inbound):** Slate-100 background, Slate-900 text. Aligned left.
- **System Messages:** Centered, 11px uppercase, Slate-500.

### Status Indicators
- Use small 8px dots for real-time presence (Online: Green, Away: Amber, Offline: Slate-300).
- Status chips (e.g., "In Progress") should use a light tinted background with a dark text color of the same hue.

### Input Fields
- 1px Slate-200 border. Focus state: 1px Deep Navy border with a 2px WhatsApp Green soft outer glow.
- Chat input should be a multi-line auto-expanding field with a persistent attachment icon.
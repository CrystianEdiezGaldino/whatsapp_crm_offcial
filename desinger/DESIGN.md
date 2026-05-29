---
name: Marine Corporate ERP
colors:
  surface: '#f8f9fa'
  surface-dim: '#d9dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f5'
  surface-container: '#edeeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#454652'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f2'
  outline: '#757684'
  outline-variant: '#c5c5d4'
  surface-tint: '#4256b7'
  primary: '#001769'
  on-primary: '#ffffff'
  primary-container: '#142c8e'
  on-primary-container: '#879aff'
  inverse-primary: '#b9c3ff'
  secondary: '#4d5e83'
  on-secondary: '#ffffff'
  secondary-container: '#c3d4ff'
  on-secondary-container: '#4a5b80'
  tertiary: '#002913'
  on-tertiary: '#ffffff'
  tertiary-container: '#004122'
  on-tertiary-container: '#46b575'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dee1ff'
  primary-fixed-dim: '#b9c3ff'
  on-primary-fixed: '#001159'
  on-primary-fixed-variant: '#283d9d'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#b5c6f0'
  on-secondary-fixed: '#061b3c'
  on-secondary-fixed-variant: '#354769'
  tertiary-fixed: '#8bf9b2'
  tertiary-fixed-dim: '#6edc98'
  on-tertiary-fixed: '#00210f'
  on-tertiary-fixed-variant: '#00522d'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Hanken Grotesk
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
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
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.05em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  brand-text-dark:
    fontFamily: Hanken Grotesk
    fontSize: 14px
    fontWeight: '600'
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  sidebar-width: 260px
  header-height: 64px
  gutter: 24px
  margin-mobile: 16px
  margin-desktop: 32px
  stack-sm: 8px
  stack-md: 16px
---

## Brand & Style

This design system is built for a robust ERP/OmniChannel platform, drawing inspiration from the prestigious and established identity of the Santa Monica Clube de Campo. The brand personality is **authoritative, dependable, and highly efficient**, aimed at professionals who manage complex workflows.

The visual style is **Corporate Modern**, characterized by a deep, oceanic color palette that communicates stability. It utilizes a structured grid, refined typography, and subtle depth to organize large amounts of data without overwhelming the user. The aesthetic prioritizes clarity and high contrast, ensuring that critical information is always legible and actionable. It balances the legacy feel of a traditional institution with the sleek, functional requirements of a contemporary software platform.

## Colors

The palette is anchored by **Deep Blue** (#142c8e) and **Darker Navy** (#001435). The navy is used for structural grounding—specifically the sidebar and header—providing a high-contrast backdrop for navigation elements. The primary blue is reserved for interactive elements, reinforcing brand presence with every click.

A refined **Emerald Success** (#008a4f) has been developed as an accent color. It is tuned to maintain high accessibility scores against both the white background of the content area and the dark navy of the sidebar. 

- **Primary:** Actionable elements, primary buttons, and active states.
- **Surface (Dark):** Sidebars, headers, and modal overlays.
- **Surface (Light):** Main content area backgrounds and table rows.
- **Success:** Status indicators and positive confirmations, harmonized with the blue foundation.

## Typography

The system utilizes **Hanken Grotesk** for headlines to provide a sharp, contemporary edge that feels modern yet professional. **Inter** is used for all body copy and UI labels to ensure maximum legibility in data-dense environments.

To enhance legibility on the dark brand backgrounds (Sidebar/Header), a subtle **white text-shadow** is applied to navigation labels. This "glow" prevents the text from being "eaten" by the deep navy background, particularly on lower-quality displays. Headlines use tighter letter-spacing to appear more impactful, while labels use slightly wider spacing for quick scanning.

## Layout & Spacing

The layout follows a **Fixed Sidebar / Fluid Content** model. The sidebar remains locked at 260px to provide a consistent navigation anchor, while the main dashboard area expands to fill the viewport.

A 12-column grid is utilized within the content area. Data tables and cards are designed to span the full width or 50% width depending on information density. For ERP screens, a "Comfortable" density is the default, using 24px gutters to allow the data-heavy interface to breathe. On mobile devices, the sidebar collapses into a hamburger menu, and horizontal padding reduces to 16px to maximize screen real estate for data tables.

## Elevation & Depth

Visual hierarchy is achieved through **Tonal Layering** and **Soft Ambient Shadows**. 

1. **Level 0 (Floor):** The main background uses a very light grey (#F8F9FA) to separate the content from the dark structural navigation.
2. **Level 1 (Cards/Tables):** Content containers are white (#FFFFFF) with a thin, low-contrast border (1px solid #E9ECEF) and a soft, 4px blur shadow to lift them slightly off the floor.
3. **Level 2 (Interactive/Dropdowns):** Popovers, tooltips, and active dropdown menus use a more pronounced shadow (12px blur, 0.08 opacity) to indicate they are temporary layers above the main workspace.

The sidebar uses a flat, dark treatment to appear as the "foundation" of the application, emphasizing the depth of the content floating on top of it.

## Shapes

The design system employs a **Soft** shape language. This 0.25rem (4px) base radius ensures that the interface feels professional and precise, avoiding the "bubbly" look of consumer apps while still feeling more approachable than a strictly sharp-edged system. Large containers like primary data cards use `rounded-lg` (8px) to frame the content clearly.

## Components

### Buttons
- **Primary:** Solid #142c8e with white text. Hover state shifts to a slightly lighter blue with a 2px bottom glow.
- **Success:** Solid #008a4f for "New" or "Confirm" actions.
- **Ghost:** Transparent background with #142c8e text and border, used for secondary actions like "Edit".

### Navigation Sidebar
- **Inactive Items:** Mid-grey text with high contrast against the Dark Navy.
- **Active State:** A subtle left-side accent bar in Emerald Green and a background highlight using a semi-transparent white (10% opacity) to create a "glass" effect on the dark blue.

### Data Tables
- Header rows use a light tint of the primary navy with bold, uppercase labels.
- Rows feature a subtle hover state (#F1F3F5) to help the eye track data across the screen.

### Chips & Status Indicators
- Statuses (Online, Active, Pending) use a "Light Fill" style: a pale background version of the status color with high-saturation text and a small 6px circular dot indicator for quick visual recognition.

### Input Fields
- White background, 1px grey border. On focus, the border transitions to Primary Blue with a subtle 2px outer glow (halo) to indicate activity.
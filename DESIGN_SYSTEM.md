# IAD Website Design System

This document outlines the design system for the Internal Audit Department (IAD) website. It is intended to be used as a reference for implementing the design using **Vanilla CSS**, ensuring consistency when the project is cloned or ported without Tailwind CSS.

---

## 1. Color Palette

The project uses a sophisticated color scheme based on HSL (Hue, Saturation, Lightness) values to support dynamic theme switching (Light/Dark mode).

### Core Colors (Light Mode)
| Role | HSL Value | Hex (Approx) | Description |
| :--- | :--- | :--- | :--- |
| **Primary** | `164 89% 18%` | `#055744` | Dark Teal - Used for headers, primary actions, and deep backgrounds. |
| **Secondary** | `50 74% 78%` | `#F1E39C` | Muted Gold - Used for highlights, buttons, and secondary elements. |
| **Background** | `51 29% 97%` | `#FAF9F2` | Light Cream - The main page background. |
| **Foreground** | `164 89% 18%` | `#055744` | Dark Teal - Main text color. |
| **Accent** | `50 74% 78%` | `#F1E39C` | Same as secondary - Used for decorative elements. |
| **Destructive** | `0 84.2% 60.2%` | `#EF4444` | Red - Used for error states or destructive actions. |

### Core Colors (Dark Mode)
| Role | HSL Value | Hex (Approx) | Description |
| :--- | :--- | :--- | :--- |
| **Primary** | `51 29% 97%` | `#FAF9F2` | Light Cream - Used for text and icons on dark backgrounds. |
| **Secondary** | `50 74% 78%` | `#F1E39C` | Muted Gold - Remains consistent for highlights. |
| **Background** | `164 89% 18%` | `#055744` | Dark Teal - The main background in dark mode. |
| **Foreground** | `51 29% 97%` | `#FAF9F2` | Light Cream - Main text color in dark mode. |

---

## 2. Typography

The design system relies on two Google Fonts for a modern, professional look.

### Fonts
- **Primary Font**: `Roboto` (Sans-serif) - Used for body text and general UI.
- **Heading Font**: `Montserrat` (Sans-serif) - Used for titles and headers to add character.

### Type Scale
- **Display 1**: `4.5rem` (72px) - Hero sections.
- **H1**: `2.25rem` (36px)
- **H2**: `1.875rem` (30px)
- **H3**: `1.5rem` (24px)
- **Body**: `1rem` (16px)
- **Small**: `0.875rem` (14px)

---

## 3. Layout & Spacing

### Container
- **Max Width**: `1400px`
- **Padding**: `2rem` (32px) on sides.
- **Center**: Auto-margin left/right.

### Spacing System (Based on 4px increments)
- `1` (4px), `2` (8px), `4` (16px), `6` (24px), `8` (32px), `12` (48px), `16` (64px).

---

## 4. UI Components (Vanilla CSS implementation)

### Cards
Cards are used extensively for content grouping.
```css
.card {
    background-color: var(--card-bg, white);
    color: var(--foreground);
    padding: 32px;
    border-radius: 20px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.25);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.1);
}
```

### Buttons
Primary and secondary buttons with consistent rounding.
```css
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem; /* 8px */
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: background-color 0.2s;
    cursor: pointer;
}

.btn-primary {
    background-color: hsl(164, 89%, 18%);
    color: hsl(51, 29%, 97%);
}

.btn-secondary {
    background-color: hsl(50, 74%, 78%);
    color: hsl(164, 89%, 18%);
}
```

---

## 5. Theme Management

The project implements dark mode using a `.dark` class on the `<html>` element.

### CSS Variable Pattern
```css
:root {
    --background: 51 29% 97%;
    --foreground: 164 89% 18%;
    --primary: 164 89% 18%;
    /* ... other variables ... */
}

.dark {
    --background: 164 89% 18%;
    --foreground: 51 29% 97%;
    --primary: 51 29% 97%;
}

body {
    background-color: hsl(var(--background));
    color: hsl(var(--foreground));
}
```

### JS Implementation
```javascript
function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
```

---

## 7. Animations & Effects

### Scroll-driven Animation
Used for revealing elements as the user scrolls.
```css
@keyframes appear {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-on-scroll {
    animation: appear linear;
    animation-timeline: view();
    animation-range: entry 0% cover 40%;
}
```

### View Transitions
Native browser transitions for smooth page navigation.
```css
@view-transition {
    navigation: auto;
}
```

---

## 8. Visual Effects
- **Radius**: `0.5rem` (8px) for inputs/buttons, `20px` for cards.
- **Shadow**: Subtle `0px 2px 4px rgba(0, 0, 0, 0.25)` for depth.
- **Scrollbar**: 8px wide, rounded thumb, grey/dark-grey shades.

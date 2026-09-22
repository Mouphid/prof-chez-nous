---
name: accessibility
description: Accessibilité web (normes WCAG). Use for auditing and fixing accessibility issues: ARIA labels, keyboard navigation, screen reader support, color contrast, focus indicators.
---

# Accessibility

WCAG compliance for the project.

## Checklist
- **Images**: Always include `alt` attribute on `<img>` tags
- **Icon-only links/buttons**: Add `aria-label="Description"` or visually hidden text
- **Form inputs**: Always have associated `<label>` elements
- **Color contrast**: Text on colored backgrounds must have 4.5:1 ratio (use `text-indigo-100` on `from-primary` bg for hero text)
- **Focus indicators**: Never remove `outline: none` without a visible focus replacement
- **Keyboard navigation**: All interactive elements must be reachable and activatable via keyboard
- **Skip link**: Add `href="#main"` skip navigation link at top of page
- **Semantic HTML**: Use `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>` landmarks
- **Headings hierarchy**: One `<h1>` per page, don't skip levels
- **ARIA**: `role="alert"` on error messages, `aria-expanded` on dropdowns, `aria-label` on icon buttons
- **Reduced motion**: `@media (prefers-reduced-motion: reduce)` for animations
- **Touch targets**: Minimum 44x44px for interactive elements on touch devices
- **Error identification**: Errors should be clearly associated with their form field

## Existing project patterns
- Mobile menu toggle: `aria-label="Menu"` on hamburger button
- Header nav uses semantic `<nav>` element
- Flash messages could use `role="alert"`

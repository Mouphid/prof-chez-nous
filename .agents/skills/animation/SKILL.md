---
name: animation
description: Ajout de micro-interactions et animations CSS/JS pour améliorer l'engagement utilisateur. Use for adding hover effects, transitions, loading spinners, page transitions, and micro-interactions.
---

# Animation

Micro-interactions and animations for the project.

## CSS transitions (Tailwind)
Use `transition` for all interactive elements:
- `hover:scale-[1.02]` or `hover:-translate-y-0.5` for card hover lift
- `hover:shadow-lg` or `hover:shadow-xl` with `transition-shadow`
- `hover:bg-indigo-700` for button darkening
- `active:scale-[0.97]` for button press effect

## Loading states
- Spinner: `<i class="ph ph-circle-notch animate-spin"></i>`
- Pulse: `<div class="animate-pulse bg-gray-200 rounded-lg h-4 w-48"></div>`

## Recommended effects for this project
- Cards: subtle lift on hover (`hover:-translate-y-1 hover:shadow-xl`)
- Buttons: scale down on active (`active:scale-95`)
- Images: zoom on hover (`hover:scale-105` inside `overflow-hidden` container)
- Page load: fade-in sections (`animate-fadeIn` via custom CSS)
- Like button: heartbeat animation on click

## Custom CSS animations (add in `<style>` block)
```css
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fadeIn { animation: fadeIn 0.5s ease-out; }
```

Keep animations subtle (< 300ms), respect `prefers-reduced-motion`.

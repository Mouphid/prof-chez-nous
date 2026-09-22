---
name: ui-designer
description: Améliore les composants UI existants (boutons, cartes, formulaires, modales, tableaux). Use for polishing and refining UI components with consistent styling, states (hover, active, focus, disabled), and accessibility.
---

# UI Designer

Refines and improves UI components across the project.

## Component patterns

### Buttons
- Primary: `bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2`
- Secondary: `bg-white text-gray-600 border border-gray-200 px-5 py-2.5 rounded-lg font-medium hover:border-primary hover:text-primary transition`
- Ghost: `text-sm text-gray-500 hover:text-primary transition`
- Danger: `bg-red-500 text-white hover:bg-red-600`

### Cards
- Default: `bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden`
- Feature: `bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-10`
- Stats: `bg-gray-50 rounded-xl p-4 text-center`

### Form inputs
- Text/textarea: `w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition`
- Select: same as text inputs
- Checkbox/radio: `w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary`

### Navigation
- Header: `bg-white shadow-sm sticky top-0 z-50`
- Nav link: `px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition`
- Active nav: `bg-indigo-50 text-primary`

### Badges
- Category: `bg-indigo-100 text-primary text-xs font-semibold px-3 py-1 rounded-full`
- Status (success): `bg-emerald-50 text-emerald-600 text-xs px-2 py-1 rounded-full`

## Interaction states
Always define hover, focus, active, and disabled states. Use `transition` for smooth animations.

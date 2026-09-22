---
name: design-system
description: Création d'un design system cohérent avec tokens de couleurs, espacements, typographie et ombres. Use for standardizing visual language and ensuring brand consistency.
---

# Design System

Centralized design tokens and patterns for the project.

## Colors
- **Primary**: `#4F46E5` (indigo-600)
- **Primary hover**: `#4338CA` (indigo-700)
- **Primary light**: `#EEF2FF` (indigo-50)
- **Success**: `#059669` (emerald-600)
- **Danger**: `#EF4444` (red-500)
- **Warning**: `#F59E0B` (amber-500)
- **Gray scale**: 50 (`#F9FAFB`), 100 (`#F3F4F6`), 200 (`#E5E7EB`), 400 (`#9CA3AF`), 500 (`#6B7280`), 600 (`#4B5563`), 700 (`#374151`), 800 (`#1F2937`), 900 (`#111827`)
- **Bg**: `bg-gray-50`
- **White**: `bg-white`

## Typography
- **Font**: `font-sans` (Tailwind default system font stack)
- **Headings**: `font-bold text-gray-900`
- **Body**: `text-sm sm:text-base text-gray-700`
- **Small**: `text-xs text-gray-400` (dates, meta)
- **Links**: `text-primary hover:underline` or `text-primary hover:text-indigo-700`
- **Line height**: `leading-relaxed`

## Spacing (8px grid)
- Section padding: `py-8`, `py-12`
- Card padding: `p-6 sm:p-10`
- Between cards: `gap-4`, `gap-6`
- Between sections: `mt-8`, `mb-8`
- Between elements: `mb-4`, `mb-6`, `space-y-4`, `space-y-6`

## Shadows
- `shadow-sm`: cards, sections
- `shadow-lg`: modals, dropdowns, elevated cards
- `shadow-xl`: hero sections, important cards
- `shadow-2xl`: modals

## Border radius
- `rounded-lg`: buttons, inputs, cards
- `rounded-xl`: large cards, sections
- `rounded-2xl`: hero sections, modals
- `rounded-full`: badges, avatars, pills

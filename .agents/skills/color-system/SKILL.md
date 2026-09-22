---
name: color-system
description: Harmonisation des palettes de couleurs du site. Use for defining, applying, and adjusting color palettes, ensuring consistency and accessibility across all pages.
---

# Color System

Color palette harmonization for the project.

## Brand colors
| Token | Hex | Tailwind | Usage |
|-------|-----|----------|-------|
| `--primary` | `#4F46E5` | `indigo-600` | Buttons, links, accents, active states |
| `--primary-hover` | `#4338CA` | `indigo-700` | Button hover state |
| `--primary-light` | `#EEF2FF` | `indigo-50` | Light backgrounds, badges, active nav |
| `--primary-dark` | `#3730A3` | `indigo-800` | Darker accents |
| `--accent` | `#FBBF24` | `amber-400` | CTA buttons, highlights, special badges |
| `--accent-hover` | `#F59E0B` | `amber-500` | CTA hover |
| `--success` | `#059669` | `emerald-600` | Success messages, active status |
| `--danger` | `#EF4444` | `red-500` | Delete buttons, error messages |
| `--warning` | `#F59E0B` | `amber-500` | Warning alerts |

## Semantic colors
- **Links**: `text-primary` (brand), `hover:text-indigo-700`
- **Success**: `bg-green-50 text-green-700` (flash messages)
- **Error**: `bg-red-50 text-red-700` (flash messages)
- **Info**: `bg-blue-50 text-blue-700` (info notices)
- **Gray backgrounds**: `bg-gray-50` (page), `bg-white` (cards)
- **Borders**: `border-gray-100` (subtle), `border-gray-200` (stronger)

## Gradient
- Hero: `bg-gradient-to-br from-primary to-indigo-400`
- Profile banner: `bg-gradient-to-r from-primary to-indigo-400`

## Applying colors
Always use Tailwind utility classes with the configured `primary` color:
```html
<script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
```
This allows using `bg-primary`, `text-primary`, `border-primary` directly.

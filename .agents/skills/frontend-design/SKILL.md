---
name: frontend-design
description: Design moderne de sites web avec HTML/CSS/Tailwind/Phosphor Icons. Use for creating beautiful, modern, responsive front-end interfaces with clean layouts, spacing, typography, and visual hierarchy.
---

# Frontend Design

Modern front-end design skill using the project stack:
- **CSS Framework**: Tailwind CSS (CDN)
- **Icons**: Phosphor Icons (CDN `@phosphor-icons/web@2.1.1`)
- **Colors**: Primary `#4F46E5` (indigo/violet), configured in `tailwind.config`
- **Stack**: PHP with vanilla HTML/CSS/JS (no React/Vue)

## Principles
- Mobile-first responsive design
- Clean visual hierarchy with proper spacing (8px grid)
- Consistent use of the primary color for CTAs and accents
- Soft shadows (`shadow-sm`, `shadow-lg`), rounded corners (`rounded-lg`, `rounded-xl`, `rounded-2xl`)
- Gradient backgrounds for heroes (`bg-gradient-to-r from-primary to-indigo-400`)
- Smooth transitions (`transition`, `hover:scale-[1.02]`, `hover:-translate-y-0.5`)

## Layout patterns
- Container: `max-w-7xl mx-auto px-4`
- Card: `bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-10`
- Section spacing: `py-8`, `py-12`, `mt-8`, `mb-6`
- Flex center: `flex items-center justify-center`
- Grid: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6`

## Writing new pages
Always extend the existing header/footer pattern. Match the existing style of `public/index.php`.

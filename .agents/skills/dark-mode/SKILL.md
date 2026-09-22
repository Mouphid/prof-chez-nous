---
name: dark-mode
description: Gestion complète du thème sombre pour le site. Use for implementing dark mode with toggle, persisting preference, and ensuring all components have dark variants.
---

# Dark Mode

Dark mode implementation for the project.

## Approach
Use Tailwind's `dark:` variant with a class-based strategy:
```html
<script>
if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}
</script>
```

## Toggle button
```html
<button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'))">
  <i class="ph ph-moon"></i>
</button>
```

## Dark mode color mappings
| Light | Dark | Usage |
|-------|------|-------|
| `bg-gray-50` | `dark:bg-gray-900` | Page background |
| `bg-white` | `dark:bg-gray-800` | Card/section background |
| `text-gray-900` | `dark:text-gray-100` | Headings |
| `text-gray-700` | `dark:text-gray-300` | Body text |
| `text-gray-500` | `dark:text-gray-400` | Secondary text |
| `text-gray-400` | `dark:text-gray-500` | Disabled/meta text |
| `border-gray-100` | `dark:border-gray-700` | Borders |
| `border-gray-200` | `dark:border-gray-600` | Strong borders |
| `bg-gray-100` | `dark:bg-gray-700` | Gray bg elements |
| `shadow-sm` | `dark:shadow-none` | Shadows (use border instead) |

## Key pages to cover
- `public/index.php` - Home page
- `public/post.php` - Article detail
- `public/profile.php` - User profile
- `admin/dashboard.php` - Admin panel
- `public/login.php`, `public/register.php` - Auth pages

---
name: responsive-design
description: Optimisation mobile, tablette et desktop. Use for making pages responsive, fixing mobile layout issues, ensuring proper breakpoints, and testing across screen sizes.
---

# Responsive Design

Mobile-first responsive design for the project.

## Breakpoints (Tailwind)
- `sm`: 640px (large phones)
- `md`: 768px (tablets)
- `lg`: 1024px (small desktops)
- `xl`: 1280px (large desktops)

## Patterns used in this project
- **Mobile menu**: Hidden `md:flex` nav, hamburger button `md:hidden` toggling `#mobileNav`
- **Cards grid**: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4`
- **Padding**: `p-6 sm:p-10` (more padding on larger screens)
- **Text**: `text-xl sm:text-2xl lg:text-3xl` (scales with viewport)
- **Stack on mobile**: `flex flex-col sm:flex-row`
- **Hidden scrollbar**: `overflow-x-auto scrollbar-hide`
- **Images**: Always `w-full h-auto max-w-full`
- **Tables**: `overflow-x-auto` wrapper on mobile

## Always test
- Home page hero on 360px screen
- Article detail page on tablet
- Comment section on mobile
- Admin dashboard data tables on mobile
- Header dropdown menus on all sizes

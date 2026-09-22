---
name: saas-ui
description: Conception d'interfaces SaaS modernes et professionnelles. Use for building subscription interfaces, account settings, onboarding flows, pricing tables, and SaaS-specific UI patterns.
---

# SaaS UI

Modern SaaS interface design for educational platform features.

## SaaS patterns for this project
- **User dashboard**: Profile, downloads, reading history, comments management
- **Content catalog**: Course/resource listing with search, filter, and pagination
- **Subscription tiers**: If implementing premium/paid features
- **Onboarding**: First-time user flow after registration
- **Account settings**: Profile editing, password change, notification preferences

## UI patterns
- **Pricing cards**: `grid grid-cols-1 md:grid-cols-3 gap-6`, featured plan with `ring-2 ring-primary` and `scale-105`
- **Settings sections**: White cards with form groups, section dividers with `border-t border-gray-100`
- **Activity feed**: Timeline-like layout with `flex items-start gap-3`, icons, dates
- **Feature comparison**: Table with checkmarks/X icons
- **Empty states**: `text-center py-8 text-gray-400` with icon + message + CTA

## Layout
- Sidebar navigation for settings: `flex flex-col md:flex-row gap-6`
- Main content area: `flex-1 max-w-3xl`
- Detail pages: `max-w-4xl mx-auto`

## Existing pages that follow SaaS patterns
- `public/profile.php` - Account settings with tabs/sections
- `public/my_downloads.php` - Content library
- `public/my_comments.php` - Activity history
- `admin/dashboard.php` - Admin analytics

---
name: ux-review
description: Analyse et améliore l'expérience utilisateur du site. Use for reviewing flows, navigation, user journeys, form UX, feedback mechanisms, and identifying usability issues.
---

# UX Review

Analyze and improve user experience across the project.

## Review checklist
- **Navigation clarity**: Can users always tell where they are? Active states on nav links.
- **Form feedback**: Success/error messages visible after every action. Use flash messages.
- **Loading states**: Show feedback during AJAX operations (spinner, disabled button text).
- **Empty states**: Every list view should show a friendly "Aucun élément" message with an icon.
- **Error states**: Never show raw PHP errors. Always catch exceptions, log them, show friendly messages.
- **Confirmation**: Destructive actions (delete) should confirm before proceeding.
- **Consistency**: Same patterns used throughout (same button styles, same spacing, same color for same meaning).
- **Touch targets**: Buttons/links minimum 44x44px on mobile.
- **Content hierarchy**: Clear H1 → H2 → H3 structure, adequate spacing between sections.

## UX patterns specific to this project
- Flash messages for success/error feedback (stored in `$_SESSION['flash_success']`)
- Form CSRF protection via `csrf_field()` / `verify_csrf()`
- Rate limiting on login forms (`check_rate_limit()`, `increment_rate_limit()`)
- Session timeout after 30 minutes of inactivity

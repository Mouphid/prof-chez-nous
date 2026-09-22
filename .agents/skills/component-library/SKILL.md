---
name: component-library
description: Génération de composants réutilisables pour le projet. Use for creating standardized PHP includes/modules for common UI elements like cards, buttons, modals, and forms.
---

# Component Library

Standard reusable components for the project.

## Existing components
- `includes/header.php` - Site header with nav
- `includes/footer.php` - Site footer

## Component patterns to follow
Each component should be a PHP include file in `includes/` with:
- Consistent class naming (Tailwind utilities)
- Proper escaping (`htmlspecialchars()`)
- Conditional display logic
- Clear docblock at top

## Recommended new components
- `includes/post-card.php` - Article card (title, excerpt, image, category, date, author)
- `includes/pagination.php` - Pagination component (takes `$page`, `$total_pages`, `$base_url`)
- `includes/flash-messages.php` - Flash/alert messages
- `includes/sidebar.php` - Sidebar (recent posts, categories, tags)
- `includes/comment-list.php` - Comment list with edit/delete controls
- `includes/share-buttons.php` - Social share buttons
- `includes/stats-card.php` - Dashboard stats card

## How to create
```php
// includes/post-card.php
<?php
/**
 * @param array $post Post data from DB
 * @param bool $featured Show featured/large variant
 */
$featured = $featured ?? false;
?>
<article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
  <!-- card content -->
</article>
```

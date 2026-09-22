---
name: typography
description: Amélioration de la typographie du site avec bonnes pratiques de lisibilité, hiérarchie et espacement. Use for setting type scales, line heights, font pairings, and text styling.
---

# Typography

Typography system for the project.

## Current setup
- **Font family**: `font-sans` (Tailwind default system font stack)
- **No custom web fonts** (for performance)

## Type scale
| Element | Tailwind classes | Size | Weight | Color |
|---------|-----------------|------|--------|-------|
| Hero H1 | `text-3xl sm:text-4xl sm:text-5xl font-extrabold` | 30-48px | 800 | `text-white` (on gradient) |
| Page H1 | `text-3xl sm:text-4xl font-bold` | 30-36px | 700 | `text-gray-900` |
| Section H2 | `text-xl sm:text-2xl font-bold` | 20-24px | 700 | `text-gray-900` |
| Card title | `text-base sm:text-lg font-semibold` | 16-18px | 600 | `text-gray-900` |
| Body | `text-sm sm:text-base` | 14-16px | 400 | `text-gray-700` |
| Small | `text-xs` | 12px | 400 | `text-gray-400`/`text-gray-500` |
| Meta | `text-xs` | 12px | 400 | `text-gray-400` |
| Button | `text-sm font-medium` | 14px | 500 | per context |

## Line height
- Headings: `leading-tight` (1.25)
- Body: `leading-relaxed` (1.625)
- Small text: `leading-normal` (1.5)

## Best practices
- Maximum line length: 65-75 characters per line (use `max-w-prose` or `max-w-3xl` for article content)
- Contrast ratio: minimum 4.5:1 for body text, 3:1 for large text
- Use CSS `word-break: break-word` for long URLs in comments
- Article content: wrap in `<div class="prose prose-gray max-w-none">` for rich text

## Example content structure
```html
<h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Page Title</h1>
<p class="text-sm text-gray-500 mb-8">Meta information</p>
<div class="text-gray-700 leading-relaxed space-y-4">
  <p>Article content paragraphs...</p>
</div>
```

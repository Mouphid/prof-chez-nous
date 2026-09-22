---
name: performance-ui
description: Optimisation des performances de l'interface. Use for reducing page load time, optimizing CSS/JS, lazy loading images, minimizing reflows, and improving Core Web Vitals.
---

# Performance UI

Front-end performance optimization for the project.

## Current setup
- Tailwind CSS via CDN (change to pre-built CSS for production)
- Phosphor Icons via CDN (tree-shakes unused icons)
- No bundler or build step

## Optimizations
- **Lazy load images**: `loading="lazy"` on all `<img>` below the fold
- **Debounce search**: Wait 300ms after last keystroke before sending search AJAX
- **Debounce like button**: Prevent double-clicks with a 1s debounce
- **CSS**: Extract critical CSS inline in `<head>`, defer non-critical
- **JS**: Move `<script>` tags to end of `<body>` (already done)
- **Reduce DOM**: Avoid deeply nested containers, prefer flat structure
- **Cache**: Add `Cache-Control` headers for static assets
- **Font**: Use `font-display: swap` for web fonts
- **Image optimization**: Serve WebP format, use `srcset` for responsive images
- **Minimize reflows**: Batch DOM reads/writes, use `requestAnimationFrame` for animations

## Critical rendering path
1. `<head>`: Only critical CSS inline, `<script>` tags with `defer`
2. `<body>`: Progressive rendering, lazy load below-fold content
3. End of `<body>`: Non-critical JS

## Performance monitoring
- Lighthouse score targets: 90+ Performance, 95+ Accessibility, 85+ SEO
- Largest Contentful Paint (LCP): < 2.5s
- First Input Delay (FID): < 100ms
- Cumulative Layout Shift (CLS): < 0.1

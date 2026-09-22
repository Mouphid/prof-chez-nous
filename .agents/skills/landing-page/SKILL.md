---
name: landing-page
description: Création de landing pages et pages d'accueil premium. Use for designing hero sections, feature highlights, CTAs, testimonials, and conversion-optimized layouts.
---

# Landing Page

Design premium landing pages for the project.

## Hero section pattern (from `public/index.php`)
```html
<section class="bg-gradient-to-br from-primary to-indigo-400 text-white rounded-3xl p-8 sm:p-16 relative overflow-hidden">
  <div class="relative z-10 max-w-3xl">
    <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight mb-6">Title</h1>
    <p class="text-lg sm:text-xl text-indigo-100 mb-8">Subtitle</p>
    <div class="flex flex-wrap gap-4">
      <a href="..." class="bg-amber-400 text-amber-900 font-bold px-8 py-3.5 rounded-xl hover:bg-amber-300 transition-all shadow-lg">CTA</a>
      <a href="..." class="bg-white/10 text-white border-2 border-white/30 px-8 py-3.5 rounded-xl hover:bg-white/20 transition">Secondary</a>
    </div>
  </div>
</section>
```

## Feature section
- 3-column grid for features
- Each feature: icon (Phosphor), title, description
- Icons should be in `w-12 h-12 bg-indigo-100 text-primary rounded-xl flex items-center justify-center` container

## Conversion elements
- CTA button: `bg-amber-400` (gold/amber) to stand out from primary
- Social proof: stats counters, member counts
- Trust signals: "Rejoignez X enseignants", "X ressources disponibles"

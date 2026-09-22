---
name: dashboard
description: Création de dashboards professionnels avec graphiques, statistiques et tableaux de bord. Use for admin dashboards, analytics panels, and data visualization.
---

# Dashboard

Professional dashboard design for the admin panel.

## Stats cards pattern
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <div class="flex items-center gap-4">
    <div class="w-12 h-12 bg-indigo-100 text-primary rounded-xl flex items-center justify-center">
      <i class="ph ph-users text-2xl"></i>
    </div>
    <div>
      <p class="text-sm text-gray-500">Total Users</p>
      <p class="text-2xl font-bold text-gray-900">1,234</p>
    </div>
  </div>
  <div class="mt-4 text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full inline-flex items-center gap-1">
    <i class="ph ph-trend-up"></i> +12% ce mois
  </div>
</div>
```

## Layout
- Stats row: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6`
- Charts: `bg-white rounded-xl shadow-sm border border-gray-100 p-6`
- Tables: `overflow-x-auto` wrapper, striped rows `even:bg-gray-50`
- Sidebar/filter bar: `w-64` or `w-72` hidden on mobile

## Admin table pattern
```html
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-50">
        <tr><th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">...</th></tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <tr class="hover:bg-gray-50"><td class="px-6 py-4">...</td></tr>
      </tbody>
    </table>
  </div>
</div>
```

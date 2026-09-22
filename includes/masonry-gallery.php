<?php
/**
 * Masonry-style gallery with CSS columns.
 * Usage: <?php $masonry_images = ['url1.jpg', 'url2.jpg']; include "includes/masonry-gallery.php"; ?>
 *
 * Variables (all optional):
 *   @param string $masonry_title      Section heading
 *   @param array  $masonry_images     Array of [url, height_class, caption] or url strings
 *   @param int    $masonry_cols       Number of columns (default: 3)
 */
$masonry_title  = $masonry_title ?? 'Galerie mosaïque';
$masonry_images = $masonry_images ?? [];
$masonry_cols   = $masonry_cols ?? 3;
$masonry_heights = ['h-48', 'h-56', 'h-64', 'h-72', 'h-80'];
?>
<?php if (!empty($masonry_images)): ?>
<section class="max-w-7xl mx-auto px-4 py-16 sm:py-20">
    <div class="text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= $masonry_title ?></h2>
    </div>
    <div class="columns-1 sm:columns-2 lg:columns-<?= min($masonry_cols, 4) ?> gap-4 space-y-4">
        <?php foreach ($masonry_images as $i => $img): $url = is_array($img) ? $img[0] : $img; $h = is_array($img) ? ($img[1] ?? $masonry_heights[$i % count($masonry_heights)]) : $masonry_heights[$i % count($masonry_heights)]; $caption = is_array($img) ? ($img[2] ?? '') : ''; ?>
        <div class="group relative overflow-hidden rounded-xl shadow-sm break-inside-avoid mb-4">
            <img src="<?= $url ?>" alt="<?= htmlspecialchars($caption) ?>" loading="lazy" class="w-full <?= $h ?> object-cover group-hover:scale-105 transition-transform duration-500">
            <?php if ($caption): ?>
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <p class="text-white text-sm font-medium"><?= htmlspecialchars($caption) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

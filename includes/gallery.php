<?php
/**
 * Simple image gallery grid.
 * Usage: <?php $gallery_images = ['url1.jpg', 'url2.jpg']; include "includes/gallery.php"; ?>
 *
 * Variables (all optional):
 *   @param string $gallery_title    Section heading
 *   @param array  $gallery_images   Array of image URLs or [url, caption] arrays
 *   @param int    $gallery_cols     Number of columns (2, 3, or 4, default: 3)
 *   @param string $gallery_style    'grid' (default) or 'carousel'
 */
$gallery_title  = $gallery_title ?? 'Galerie';
$gallery_images = $gallery_images ?? [];
$gallery_cols   = $gallery_cols ?? 3;
$cols_map       = [2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-2 lg:grid-cols-3', 4 => 'sm:grid-cols-2 lg:grid-cols-4'];
?>
<?php if (!empty($gallery_images)): ?>
<section class="max-w-7xl mx-auto px-4 py-16 sm:py-20">
    <div class="text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= $gallery_title ?></h2>
    </div>
    <div class="grid grid-cols-1 <?= $cols_map[$gallery_cols] ?? $cols_map[3] ?> gap-4">
        <?php foreach ($gallery_images as $img): $url = is_array($img) ? $img[0] : $img; $caption = is_array($img) ? ($img[1] ?? '') : ''; ?>
        <div class="group relative overflow-hidden rounded-xl shadow-sm">
            <img src="<?= $url ?>" alt="<?= htmlspecialchars($caption) ?>" loading="lazy" class="w-full h-56 object-cover group-hover:scale-105 transition-transform duration-500">
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

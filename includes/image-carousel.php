<?php
/**
 * Image carousel with previous/next navigation and dots.
 * Usage: <?php $carousel_images = ['url1.jpg', 'url2.jpg']; include "includes/image-carousel.php"; ?>
 *
 * Variables (all optional):
 *   @param string $carousel_id        Unique ID for multiple carousels (default: 'carousel-1')
 *   @param array  $carousel_images    Array of [url, caption] or url strings
 *   @param int    $carousel_interval  Auto-slide interval in ms (default: 5000)
 */
$carousel_id       = $carousel_id ?? 'carousel-1';
$carousel_images   = $carousel_images ?? [];
$carousel_interval = $carousel_interval ?? 5000;
$carousel_title    = $carousel_title ?? '';
?>
<?php if (!empty($carousel_images)): ?>
<section class="max-w-5xl mx-auto px-4 py-16 sm:py-20">
    <?php if ($carousel_title): ?>
    <div class="text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900"><?= $carousel_title ?></h2>
    </div>
    <?php endif; ?>
    <div id="<?= $carousel_id ?>" class="relative overflow-hidden rounded-2xl shadow-lg" role="region" aria-label="Carousel d'images">
        <div class="carousel-wrapper relative">
            <?php foreach ($carousel_images as $i => $img): $url = is_array($img) ? $img[0] : $img; $caption = is_array($img) ? ($img[1] ?? '') : ''; ?>
            <div class="carousel-slide <?= $i === 0 ? 'block' : 'hidden' ?> relative" data-index="<?= $i ?>">
                <img src="<?= $url ?>" alt="<?= htmlspecialchars($caption) ?>" loading="lazy" class="w-full h-64 sm:h-80 lg:h-96 object-cover">
                <?php if ($caption): ?>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                    <p class="text-white text-lg font-medium"><?= htmlspecialchars($caption) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($carousel_images) > 1): ?>
        <button class="carousel-prev absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center text-gray-700 shadow-md transition z-10" aria-label="Image précédente"><i class="ph ph-caret-left text-lg"></i></button>
        <button class="carousel-next absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center text-gray-700 shadow-md transition z-10" aria-label="Image suivante"><i class="ph ph-caret-right text-lg"></i></button>
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
            <?php foreach ($carousel_images as $i => $img): ?>
            <button class="carousel-dot w-2.5 h-2.5 rounded-full transition-all <?= $i === 0 ? 'bg-white scale-110' : 'bg-white/50 hover:bg-white/70' ?>" data-slide="<?= $i ?>" aria-label="Image <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<script>
(function(){
    const container = document.getElementById('<?= $carousel_id ?>');
    if (!container) return;
    const slides = container.querySelectorAll('.carousel-slide');
    const dots = container.querySelectorAll('.carousel-dot');
    const prevBtn = container.querySelector('.carousel-prev');
    const nextBtn = container.querySelector('.carousel-next');
    if (!slides.length) return;
    let current = 0;
    const interval = <?= $carousel_interval ?>;
    function goTo(index) {
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;
        slides.forEach(s => s.classList.add('hidden'));
        slides[index].classList.remove('hidden');
        dots.forEach((d, i) => {
            d.classList.toggle('bg-white', i === index);
            d.classList.toggle('scale-110', i === index);
            d.classList.toggle('bg-white/50', i !== index);
        });
        current = index;
    }
    if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(current - 1); clearInterval(timer); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(current + 1); clearInterval(timer); });
    dots.forEach(d => d.addEventListener('click', function(){ goTo(parseInt(this.dataset.slide)); clearInterval(timer); }));
    let timer = setInterval(function(){ goTo(current + 1); }, interval);
})();
</script>
<?php endif; ?>

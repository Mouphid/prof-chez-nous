<?php
/**
 * Hero section with immersive design.
 * Usage: <?php include "includes/hero-section.php"; ?>
 */
$hero_title   = $hero_title ?? "Bienvenue sur<br><span class='bg-gradient-to-r from-accent-400 to-amber-300 bg-clip-text text-transparent'>" . SITE_NAME . "</span>";
$hero_tagline = $hero_tagline ?? SITE_TAGLINE;
$slides       = isset($hero_slides_alt) ? $hero_slides_alt : ($hero_slides ?? []);
$ctas         = $hero_ctas ?? [
    ['Découvrir les articles', '#articles', 'bg-white text-primary-700 hover:bg-gray-50', '<i class="ph ph-book-open text-xl"></i>'],
    ['Rejoindre la communauté', 'register.php', 'bg-accent-500 text-white hover:bg-accent-600', '<i class="ph ph-user-plus text-xl"></i>'],
];
$show_slider  = $hero_show_slider ?? true;
?>
<section class="relative min-h-[85vh] flex items-center overflow-hidden" <?= $show_slider ? 'id="hero-slider"' : '' ?>>
    <?php if ($show_slider && !empty($slides)): ?>
        <?php foreach ($slides as $i => $src): ?>
        <div class="hero-slide absolute inset-0 bg-cover bg-center transition-opacity duration-[1500ms] <?= $i === 0 ? 'opacity-100 hero-slide-active' : 'opacity-0' ?>" style="background-image: url('<?= $src ?>');" data-index="<?= $i ?>"></div>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="absolute inset-0 bg-hero-pattern"></div>
    <?php endif; ?>
    <div class="absolute inset-0 bg-gradient-to-br from-dark/80 via-primary-900/60 to-purple-900/70"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-dark/40 via-transparent to-transparent"></div>

    <!-- Floating elements -->
    <div class="absolute top-20 left-10 w-20 h-20 bg-white/5 rounded-2xl rotate-12 animate-float hidden lg:block"></div>
    <div class="absolute bottom-32 right-16 w-16 h-16 bg-accent-500/10 rounded-full animate-float hidden lg:block" style="animation-delay: 2s;"></div>
    <div class="absolute top-40 right-20 w-12 h-12 bg-primary-400/10 rounded-xl -rotate-12 animate-float hidden lg:block" style="animation-delay: 4s;"></div>

    <?php if ($show_slider && !empty($slides)): ?>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-3">
        <?php foreach ($slides as $i => $src): ?>
        <button class="hero-dot w-3 h-3 rounded-full transition-all duration-500 <?= $i === 0 ? 'bg-white scale-125 w-8' : 'bg-white/40 hover:bg-white/60' ?>" data-slide="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-28 relative z-10 w-full">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md text-white text-sm font-medium px-5 py-2 rounded-full mb-8 border border-white/10">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse-slow"></span> Plateforme éducative dédiée aux enseignants
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-display font-extrabold leading-[1.1] mb-6 text-white"><?= $hero_title ?></h1>
            <p class="text-lg sm:text-xl text-gray-200 leading-relaxed mb-10 max-w-xl"><?= $hero_tagline ?></p>
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <?php foreach ($ctas as $cta): ?>
                <a href="<?= $cta[1] ?>" class="<?= $cta[2] ?> font-bold px-8 py-4 rounded-2xl transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-[0.97] transform hover:-translate-y-1 inline-flex items-center gap-2.5 text-base">
                    <?= $cta[3] ?? '' ?> <?= $cta[0] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php if ($show_slider && !empty($slides)): ?>
<script>
(function(){
    const slides = document.querySelectorAll('#hero-slider .hero-slide');
    const dots = document.querySelectorAll('#hero-slider .hero-dot');
    if (!slides.length) return;
    let current = 0;
    const interval = <?= HERO_SLIDE_INTERVAL ?>;
    function goTo(index) {
        if (index === current) return;
        slides[current].classList.remove('opacity-100', 'hero-slide-active');
        slides[current].classList.add('opacity-0');
        dots[current].classList.remove('bg-white', 'scale-125', 'w-8');
        dots[current].classList.add('bg-white/40');
        current = index;
        slides[current].classList.remove('opacity-0');
        slides[current].classList.add('opacity-100', 'hero-slide-active');
        dots[current].classList.remove('bg-white/40');
        dots[current].classList.add('bg-white', 'scale-125', 'w-8');
    }
    dots.forEach(d => d.addEventListener('click', function(){ goTo(parseInt(this.dataset.slide)); }));
    setInterval(function(){ goTo((current + 1) % slides.length); }, interval);
})();
</script>
<?php endif; ?>

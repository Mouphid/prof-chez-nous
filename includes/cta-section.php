<?php
/**
 * Call-to-action banner with gradient background.
 * Usage: <?php include "includes/cta-section.php"; ?>
 */
$cta_title   = $cta_title ?? 'Prêt à rejoindre la communauté ?';
$cta_text    = $cta_text ?? 'Inscrivez-vous gratuitement et accédez à toutes les ressources pédagogiques.';
$cta_label   = $cta_label ?? 'Créer un compte gratuit';
$cta_url     = $cta_url ?? 'register.php';
$cta_icon    = $cta_icon ?? 'ph-user-plus';
$cta_visible = $cta_visible ?? !isset($_SESSION['user_id']);
?>
<?php if ($cta_visible): ?>
<section class="relative py-20 sm:py-28 overflow-hidden <?= ANIM_FADE_IN ?>">
    <div class="absolute inset-0 bg-cta-pattern"></div>
    <div class="absolute inset-0 bg-dark/20"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative z-10">
        <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-white mb-5"><?= $cta_title ?></h2>
        <p class="text-white/70 leading-relaxed mb-10 max-w-lg mx-auto text-lg"><?= $cta_text ?></p>
        <a href="<?= $cta_url ?>" class="inline-flex items-center gap-2.5 bg-accent-500 text-white font-bold px-10 py-4 rounded-2xl hover:bg-accent-600 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-accent-500/25 active:scale-[0.97] transform hover:-translate-y-1 text-base">
            <i class="<?= $cta_icon ?> text-xl"></i> <?= $cta_label ?>
        </a>
    </div>
</section>
<?php endif; ?>

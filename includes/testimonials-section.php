<?php
/**
 * Testimonials grid with star ratings and avatars.
 * Usage: <?php include "includes/testimonials-section.php"; ?>
 */
$testimonials_title = $testimonials_title ?? 'Ce que disent nos membres';
$testimonials       = $testimonials ?? [
    ['Sophie Martin',  'Enseignante en primaire', '"Une ressource incroyable pour les enseignants. Les cours sont bien structurés et les téléchargements sont très utiles."',  'S', 'bg-primary-100 dark:bg-primary-900/30', 'text-primary-600 dark:text-primary-400'],
    ['Thomas Dubois',  'Professeur de français',  '"Je recommande vivement Joie Enseignante à tous mes collègues. La communauté est bienveillante et les ressources de qualité."', 'T', 'bg-emerald-100 dark:bg-emerald-900/30', 'text-emerald-600 dark:text-emerald-400'],
    ['Claire Fontaine','Enseignante en collège',  '"Grâce à cette plateforme, j\'ai découvert des méthodes pédagogiques innovantes que j\'utilise quotidiennement en classe."','C', 'bg-accent-100 dark:bg-accent-900/30', 'text-accent-600 dark:text-accent-400'],
];
?>
<section class="bg-gray-50 dark:bg-dark py-16 sm:py-24 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center">
        <span class="text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase tracking-widest mb-3 block" <?= REVEAL_UP ?>>Témoignages</span>
        <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-4" <?= REVEAL_UP ?>><?= $testimonials_title ?></h2>
        <p class="text-gray-500 dark:text-dark-300 mb-12 max-w-lg mx-auto" <?= REVEAL_UP ?>>Des enseignants et étudiants partagent leur expérience.</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($testimonials as $i => $t): ?>
            <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-7 text-left shadow-sm card-hover <?= REVEAL ?> stagger-<?= ($i % 3) + 1 ?>">
                <div class="flex items-center gap-1 text-accent-400 mb-4">
                    <?php for ($s = 0; $s < 5; $s++): ?>
                    <i class="ph ph-star-fill text-sm"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-sm text-gray-600 dark:text-dark-400 leading-relaxed mb-5"><?= $t[2] ?></p>
                <div class="flex items-center gap-3 pt-4 border-t border-gray-50 dark:border-dark-100">
                    <div class="w-10 h-10 <?= $t[4] ?> <?= $t[5] ?> rounded-full flex items-center justify-center text-sm font-bold"><?= $t[3] ?></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white"><?= $t[0] ?></p>
                        <p class="text-xs text-gray-400 dark:text-dark-300"><?= $t[1] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

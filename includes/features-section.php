<?php
/**
 * Features grid with glassmorphism cards.
 * Usage: <?php include "includes/features-section.php"; ?>
 */
$features_title    = $features_title ?? 'Tout ce dont vous avez besoin';
$features_subtitle = $features_subtitle ?? 'Une plateforme complète pour enrichir vos cours et partager vos ressources.';
$features          = $features ?? [
    ['ph-graduation-cap', 'bg-primary-100 dark:bg-primary-900/30', 'text-primary-600 dark:text-primary-400', 'Cours structurés', 'Accédez à des cours complets organisés par catégories pour faciliter votre enseignement au quotidien.'],
    ['ph-download-simple','bg-emerald-100 dark:bg-emerald-900/30', 'text-emerald-600 dark:text-emerald-400', 'Ressources téléchargeables', 'Téléchargez des ressources pour vous accompagner dans vos travaux de recherche.'],
    ['ph-flask',          'bg-purple-100 dark:bg-purple-900/30', 'text-purple-600 dark:text-purple-400', 'Recherche scientifique', 'Accédez à des travaux de recherche scientifique pour enrichir votre pédagogie.'],
    ['ph-magnifying-glass','bg-sky-100 dark:bg-sky-900/30', 'text-sky-600 dark:text-sky-400', 'Recherche intelligente', 'Trouvez rapidement les ressources qui vous intéressent grâce à notre moteur de recherche.'],
    ['ph-star',           'bg-amber-100 dark:bg-amber-900/30', 'text-accent-600 dark:text-accent-400', 'Contenu exclusif', 'Profitez de publications régulières et de contenus exclusifs pour enrichir votre pédagogie.'],
    ['ph-users-three',    'bg-rose-100 dark:bg-rose-900/30', 'text-rose-600 dark:text-rose-400', 'Communauté active', 'Rejoignez une communauté d\'enseignants et d\'étudiants passionnés par l\'éducation.'],
];
?>
<section class="bg-white dark:bg-dark-50 py-16 sm:py-24 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14" <?= REVEAL_UP ?>>
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase tracking-widest mb-3 block">Nos fonctionnalités</span>
            <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-4"><?= $features_title ?></h2>
            <p class="text-gray-500 dark:text-dark-300 max-w-xl mx-auto"><?= $features_subtitle ?></p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($features as $i => $feat): ?>
            <div class="group bg-gray-50 dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-7 card-hover <?= REVEAL ?> stagger-<?= ($i % 6) + 1 ?>">
                <div class="w-14 h-14 <?= $feat[1] ?> <?= $feat[2] ?> rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                    <i class="<?= $feat[0] ?> text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?= $feat[3] ?></h3>
                <p class="text-sm text-gray-500 dark:text-dark-300 leading-relaxed"><?= $feat[4] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

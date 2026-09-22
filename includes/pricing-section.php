<?php
/**
 * Pricing cards with feature lists and CTA buttons.
 * Usage: <?php include "includes/pricing-section.php"; ?>
 *
 * Variables (all optional):
 *   @param string $pricing_title    Section heading
 *   @param array  $pricing_plans    Array of [name, price, period, description, features[], cta_label, cta_url, popular]
 */
$pricing_title = $pricing_title ?? 'Nos offres';
$pricing_subtitle = $pricing_subtitle ?? 'Choisissez la formule qui correspond à vos besoins.';
$pricing_plans = $pricing_plans ?? [
    [
        'name'        => 'Gratuit',
        'price'       => '0',
        'period'      => '/mois',
        'description' => 'Accédez aux ressources de base et explorez la plateforme.',
        'features'    => ['Cours gratuits', '5 téléchargements/mois', 'Commenter les articles', 'Accès à la communauté'],
        'cta_label'   => 'Créer un compte',
        'cta_url'     => 'register.php',
        'popular'     => false,
    ],
    [
        'name'        => 'Premium',
        'price'       => '4 900',
        'period'      => '/mois',
        'description' => 'Téléchargements illimités et fonctionnalités avancées.',
        'features'    => ['Tout du plan Gratuit', 'Téléchargements illimités', 'Contenus exclusifs', 'Support prioritaire', 'Statistiques avancées'],
        'cta_label'   => 'Choisir Premium',
        'cta_url'     => 'register.php?plan=premium',
        'popular'     => true,
    ],
    [
        'name'        => 'Annuel',
        'price'       => '49 000',
        'period'      => '/an',
        'description' => 'Économisez 2 mois avec un engagement annuel.',
        'features'    => ['Tout du plan Premium', '2 mois offerts', 'Badge contributeur', 'Accès anticipé aux nouveautés', 'Export PDF avancé'],
        'cta_label'   => 'Choisir Annuel',
        'cta_url'     => 'register.php?plan=annuel',
        'popular'     => false,
    ],
];
?>
<section class="bg-gray-50 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <span class="text-xs font-semibold text-primary uppercase tracking-widest">Tarifs</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2 mb-2"><?= $pricing_title ?></h2>
        <p class="text-gray-500 mb-10 max-w-xl mx-auto"><?= $pricing_subtitle ?></p>
        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <?php foreach ($pricing_plans as $plan): ?>
            <div class="bg-white rounded-2xl border <?= $plan['popular'] ? 'border-primary ring-2 ring-primary/20 scale-[1.02]' : 'border-gray-100' ?> p-6 sm:p-8 text-left flex flex-col shadow-sm hover:shadow-lg transition-shadow">
                <?php if ($plan['popular']): ?>
                <span class="self-start bg-primary text-white text-xs font-bold px-3 py-1 rounded-full mb-3">Populaire</span>
                <?php endif; ?>
                <h3 class="text-xl font-bold text-gray-900"><?= $plan['name'] ?></h3>
                <p class="text-sm text-gray-500 mt-1 mb-4"><?= $plan['description'] ?></p>
                <div class="mb-6">
                    <span class="text-3xl font-extrabold text-gray-900"><?= $plan['price'] ?> F</span>
                    <span class="text-gray-400 text-sm"><?= $plan['period'] ?></span>
                </div>
                <ul class="space-y-3 mb-8 flex-1">
                    <?php foreach ($plan['features'] as $feat): ?>
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <i class="ph ph-check-circle text-emerald-500 mt-0.5"></i> <?= $feat ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= $plan['cta_url'] ?>" class="block text-center py-3 rounded-xl text-sm font-bold transition-all <?= $plan['popular'] ? 'bg-primary text-white hover:bg-indigo-700 shadow-md' : 'bg-gray-100 text-gray-900 hover:bg-gray-200' ?>">
                    <?= $plan['cta_label'] ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

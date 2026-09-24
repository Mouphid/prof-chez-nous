<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Cours - Joie Enseignante";

$courses = [
    [
        'code'        => 'LIT301',
        'icon'        => 'ph-book-open',
        'title'       => 'Introduction à la Littérature',
        'description' => 'Ce cours présente les bases de l\'analyse littéraire : genres, courants littéraires, figures de style et méthodes d\'interprétation des textes.',
        'objectives'  => ['Maîtriser les concepts fondamentaux de la littérature', 'Analyser un texte littéraire avec rigueur', 'Identifier les genres et courants littéraires', 'Rédaction de commentaires composés'],
        'chapters'    => [
            ['Qu\'est-ce que la littérature ?', 'Définitions, enjeux et fonctions de la littérature'],
            ['Les genres littéraires', 'Poésie, roman, théâtre, essayistique'],
            ['Figures de style', 'Métaphores, métonymies, allitérations...'],
            ['Courants littéraires', 'Classicisme, romantisme, naturalisme...'],
        ],
        'meta' => ['45 heures', 'Licence 3', 'Pair', '6 crédits'],
    ],
    [
        'code'        => 'LIT401',
        'icon'        => 'ph-feather',
        'title'       => 'Littérature Orale Africaine',
        'description' => 'Étude approfondie des genres de la littérature orale africaine : contes, légendes, mythes, proverbes, devinettes et chants traditionnels.',
        'objectives'  => ['Identifier et classer les genres oraux', 'Comprendre les fonctions sociales de l\'oraliture', 'Analyser les techniques de narration traditionnelle', 'Étudier la transmission orale et ses enjeux'],
        'chapters'    => [
            ['Genres de l\'oralité', 'Contes, mythes, légendes et épopées'],
            ['L\'art du conteur', 'Techniques de narration et performance'],
            ['Proverbes et devinettes', 'Fonction didactique et sociale'],
            ['Chants traditionnels', 'Lien entre musique, parole et mémoire collective'],
        ],
        'meta' => ['60 heures', 'Master 1', 'Impair', '8 crédits'],
    ],
    [
        'code'        => 'LIT302',
        'icon'        => 'ph-pen-nib',
        'title'       => 'Critique Littéraire',
        'description' => 'Initiation aux différentes méthodes de critique littéraire : narratologie, analyse du discours, sociocritique et analyse psychocritique.',
        'objectives'  => ['Maîtriser les outils d\'analyse textuelle', 'Appliquer les méthodes de critique', 'Rédiger une analyse critique argumentée', 'Évaluer les travaux scientifiques en littérature'],
        'chapters'    => [
            ['Narratologie', 'Le récit, ses instances et ses techniques'],
            ['Analyse du discours', 'Énonciation et pragmatique du texte'],
            ['Sociocritique', 'Le texte littéraire et son contexte social'],
            ['Méthodes d\'analyse', 'Panorama des approches critiques contemporaines'],
        ],
        'meta' => ['45 heures', 'Licence 3', 'Impair', '6 crédits'],
    ],
];
?>
<?php include "../includes/header.php"; ?>

    <div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/hero-1.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3">Formation universitaire</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Cours du professeur</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">Ressources pédagogiques pour mes étudiants en littérature</p>
    </div>
</div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 mt-4" id="main-content">
        <div class="space-y-8">
            <?php foreach ($courses as $i => $course): ?>
            <article class="bg-white dark:bg-dark-50 rounded-2xl shadow-sm dark:shadow-none border border-gray-100 dark:border-dark-100 overflow-hidden <?= REVEAL ?>">
                <div class="p-6 sm:p-8">
                    <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="ph <?= $course['icon'] ?> text-2xl"></i>
                            </div>
                            <div>
                                <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-1"><?= $course['code'] ?></span>
                                <h2 class="text-xl sm:text-2xl font-bold"><?= $course['title'] ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-3 gap-8 mt-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><i class="ph ph-info text-primary-500"></i> Description</h3>
                                <p class="text-gray-600 dark:text-dark-400 leading-relaxed"><?= $course['description'] ?></p>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><i class="ph ph-target text-primary-500"></i> Objectifs</h3>
                                <ul class="space-y-2">
                                    <?php foreach ($course['objectives'] as $obj): ?>
                                    <li class="flex items-start gap-2.5 text-gray-600 dark:text-dark-400">
                                        <i class="ph ph-check-circle text-emerald-500 mt-1 flex-shrink-0"></i>
                                        <span><?= $obj ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><i class="ph ph-list-bullets text-primary-500"></i> Plan du cours</h3>
                                <div class="space-y-3">
                                    <?php foreach ($course['chapters'] as $ci => $chapter): ?>
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-dark-100">
                                        <span class="w-8 h-8 flex-shrink-0 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 text-white text-xs font-bold flex items-center justify-center"><?= str_pad((string)($ci + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white"><?= $chapter[0] ?></h4>
                                            <p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5"><?= $chapter[1] ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <aside>
                            <div class="bg-gray-50 dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-5">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2"><i class="ph ph-chart-line text-primary-500"></i> Détails</h3>
                                <ul class="space-y-3 text-sm">
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-clock text-primary-500"></i> <strong>Volume :</strong> <?= $course['meta'][0] ?></li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-users text-primary-500"></i> <strong>Niveau :</strong> <?= $course['meta'][1] ?></li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-calendar text-primary-500"></i> <strong>Semestre :</strong> <?= $course['meta'][2] ?></li>
                                    <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-file-text text-primary-500"></i> <strong>Crédits :</strong> <?= $course['meta'][3] ?></li>
                                </ul>
                            </div>
                        </aside>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>
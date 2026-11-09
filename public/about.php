<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "À propos - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/banner-about.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3"><i class="ph ph-info"></i> Notre histoire</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">À propos</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">Découvrez notre mission et ceux qui font vivre cette plateforme.</p>
    </div>
</div>

<div id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-20">

    <!-- ═══ UN SEUL BLOC : INTRODUCTION, IDENTITÉ, MISSION ET APPEL À L'ACTION ═══ -->
    <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 shadow-sm overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-primary-500 to-primary-700"></div>

        <!-- Introduction -->
        <div class="p-6 sm:p-10 text-center">
            <span class="text-xs font-semibold text-accent-600 uppercase tracking-widest">Introduction</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white mt-2 mb-6">Introduction à Joie Enseignante</h2>
            <p class="text-gray-600 dark:text-dark-400 leading-relaxed max-w-4xl mx-auto sbr-justify">
                <strong class="text-primary">Joie Enseignante</strong> est une plateforme dédiée à l'enrichissement de l'éducation,
                créée sous l'égide du Professeur Sylvestre Djouamon. L'objectif de cette initiative est de partager son expertise
                en littérature orale, en politiques publiques, ainsi qu'en communication. Ce site vise à promouvoir les savoirs
                et les valeurs liées à la transmission de la culture et des connaissances à travers des ressources pédagogiques
                et des formations spécialisées.
            </p>
            <div class="mt-6 w-16 h-1 bg-accent-500 rounded-full mx-auto"></div>
        </div>

        <!-- Identité + mission, côte à côte sur grand écran -->
        <div class="grid grid-cols-1 lg:grid-cols-2 border-t border-gray-100 dark:border-dark-100">
            <div class="p-6 sm:p-10">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-handshake text-xl"></i></span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Qui sommes-nous ?</h2>
                        <p class="text-xs text-gray-400 mt-1">Une plateforme au service du savoir</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-dark-400 leading-relaxed mb-4 text-sm sbr-justify">
                    <strong class="text-primary">Joie Enseignante</strong> est une plateforme pédagogique dédiée aux enseignants et aux étudiants.
                    Notre mission est de faciliter le partage de connaissances et de ressources éducatives de qualité.
                </p>
                <p class="text-gray-600 dark:text-dark-400 leading-relaxed mb-4 text-sm sbr-justify">
                    Nous croyons que l'éducation est la clé du développement et que chaque apprenant mérite
                    d'avoir accès à des contenus pédagogiques riches et variés.
                </p>
                <p class="text-gray-600 dark:text-dark-400 leading-relaxed text-sm sbr-justify">
                    Que vous soyez enseignant souhaitant partager vos cours ou étudiant à la recherche de
                    ressources pour approfondir vos connaissances, notre plateforme est conçue pour vous.
                </p>
            </div>

            <div class="p-6 sm:p-10 border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-dark-100">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-target text-xl"></i></span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notre mission</h2>
                        <p class="text-xs text-gray-400 mt-1">Quatre engagements au quotidien</p>
                    </div>
                </div>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                        <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-0.5"><i class="ph ph-check-circle"></i></span>
                        <div><strong class="text-gray-900 dark:text-white">Accessibilité</strong><p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5">Rendre l'éducation accessible à tous, partout et à tout moment.</p></div>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                        <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-0.5"><i class="ph ph-check-circle"></i></span>
                        <div><strong class="text-gray-900 dark:text-white">Qualité</strong><p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5">Proposer des ressources pédagogiques fiables et bien structurées.</p></div>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                        <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-0.5"><i class="ph ph-check-circle"></i></span>
                        <div><strong class="text-gray-900 dark:text-white">Partage</strong><p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5">Créer une communauté d'apprentissage où savoir rime avec partage.</p></div>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                        <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-0.5"><i class="ph ph-check-circle"></i></span>
                        <div><strong class="text-gray-900 dark:text-white">Innovation</strong><p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5">Utiliser les technologies modernes pour améliorer l'expérience d'apprentissage.</p></div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Appel à l'action, intégré au même bloc -->
        <div class="border-t border-gray-100 dark:border-dark-100 bg-gray-50 dark:bg-dark p-6 sm:p-10 text-center">
            <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">Envie d'apprendre ou de partager ?</h2>
            <p class="text-gray-500 dark:text-dark-400 mt-3 text-sm max-w-xl mx-auto">Parcourez les cours et les publications, ou écrivez-nous pour proposer vos ressources.</p>
            <div class="flex flex-wrap gap-3 justify-center mt-6">
                <a href="<?= BASE_URL ?>public/cours.php" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white font-semibold px-6 py-3 rounded-2xl text-sm transition-colors">
                    <i class="ph ph-graduation-cap"></i> Voir les cours
                </a>
                <a href="<?= BASE_URL ?>public/publications.php" class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-6 py-3 rounded-2xl text-sm transition-colors">
                    <i class="ph ph-book-open"></i> Publications
                </a>
                <a href="<?= BASE_URL ?>public/contact.php" class="inline-flex items-center gap-2 border border-gray-200 dark:border-dark-100 text-gray-700 dark:text-dark-400 hover:bg-white dark:hover:bg-dark-50 font-semibold px-6 py-3 rounded-2xl text-sm transition-colors">
                    <i class="ph ph-envelope"></i> Nous contacter
                </a>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

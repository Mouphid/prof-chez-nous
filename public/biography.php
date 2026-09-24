<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Biographie - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/banner-biography.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-5xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3">À propos de l'auteur</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Biographie</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">Découvrez le parcours et l'engagement de Sylvestre Djouamon, enseignant passionné.</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 py-12 sm:py-20">

    <!-- ═══ PRÉSENTATION ═══ -->
    <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 shadow-sm overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-primary-500 to-primary-700"></div>
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row gap-8 items-center">
                <div class="w-full sm:w-52 h-48 sm:h-64 rounded-3xl overflow-hidden flex-shrink-0 shadow-lg bg-gray-100">
                    <img src="../img/P.jpg" alt="Sylvestre Djouamon" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0 text-center sm:text-left">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">Sylvestre Djouamon</h2>
                    <p class="text-primary text-sm font-medium mt-1">Maître-Assistant au département des Lettres Modernes — FLLAC</p>
                    <div class="mt-4 w-16 h-1 bg-accent-500 rounded-full mx-auto sm:mx-0"></div>
                    <p class="text-gray-600 dark:text-dark-400 leading-relaxed mt-6 text-sm">
                        Sa thèse, intitulée <em>« Le fonctionnement du pessimisme dans les chansons traditionnelles
                        modernes fons et maxis du Bénin »</em>, explore les dynamiques du discours littéraire dans la
                        musique du Sud-Bénin.
                    </p>
                    <div class="flex flex-wrap gap-4 mt-6 justify-center sm:justify-start">
                        <a href="../public/cours.php" class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white font-semibold px-6 py-3 rounded-2xl text-sm transition-colors duration-300">
                            <i class="ph ph-graduation-cap"></i> Voir les cours
                        </a>
                        <a href="../public/publications.php" class="inline-flex items-center gap-2 border border-gray-200 dark:border-dark-100 text-gray-700 dark:text-dark-400 hover:bg-gray-50 font-semibold px-6 py-3 rounded-2xl text-sm transition-colors">
                            <i class="ph ph-book-open"></i> Publications
                        </a>
                        <a href="../public/contact.php" class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-6 py-3 rounded-2xl text-sm transition-colors duration-300">
                            <i class="ph ph-envelope"></i> Me contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ PARCOURS ACADÉMIQUE ═══ -->
    <div class="mt-8 bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-6">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-book-open text-xl"></i></span>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Parcours Académique</h3>
                <p class="text-xs text-gray-400 mt-1">Une formation pluridisciplinaire complète</p>
            </div>
        </div>
        <ul class="space-y-3">
            <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-dark-400">
                <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-graduation-cap"></i></span>
                Doctorat en Lettres Modernes
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-dark-400">
                <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-graduation-cap"></i></span>
                Maîtrise en Droit
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-dark-400">
                <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-graduation-cap"></i></span>
                Expert-Évaluateur en Politiques Publiques
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-dark-400">
                <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-graduation-cap"></i></span>
                Master en Communication Commerciale et Stratégies
            </li>
            <li class="flex items-center gap-3 text-sm text-gray-700 dark:text-dark-400">
                <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-graduation-cap"></i></span>
                Certificat en Journalisme et Communication
            </li>
        </ul>
    </div>

    <!-- ═══ CARRIÈRE + EXPERTISE ═══ -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-buildings text-xl"></i></span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Carrière Universitaire</h3>
                    <p class="text-xs text-gray-400 mt-1">Chef adjoint, département des Lettres Modernes — UAC</p>
                </div>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-chalkboard-teacher"></i></span>
                    Phénoménologie de la littérature orale
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-chalkboard-teacher"></i></span>
                    La chanson traditionnelle béninoise et ses innovations
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-chalkboard-teacher"></i></span>
                    Le conte africain
                </li>
            </ul>
        </div>

        <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-briefcase text-xl"></i></span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Consultations & Expertise</h3>
                    <p class="text-xs text-gray-400 mt-1">Interventions pour diverses institutions</p>
                </div>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-globe"></i></span>
                    Consultant AUF au Haut Conseil de l'Éducation en République du Congo
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-megaphone"></i></span>
                    Rapporteur général de la Conférence régionale sur l'évaluation d'impact en Afrique francophone
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent-600 flex-shrink-0 mt-1"><i class="ph ph-bank"></i></span>
                    Consultant juridique pour l'évaluation du Plan Foncier Rural (Banque mondiale)
                </li>
            </ul>
        </div>
    </div>

    <!-- ═══ ENGAGEMENT + RECHERCHE ═══ -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-newspaper text-xl"></i></span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Engagement Journalistique</h3>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-dark-400 leading-relaxed">
                Journaliste et directeur de publication du magazine <strong class="text-gray-900 dark:text-white">Afrique Identité</strong>,
                membre actif de l'Association des journalistes scientifiques du Bénin et coordonnateur du projet
                <strong class="text-gray-900 dark:text-white">École Avenir</strong>.
            </p>
            <div class="flex flex-wrap gap-2 mt-5">
                <span class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-100 dark:bg-dark dark:border-dark-100 text-xs font-medium text-gray-600 dark:text-dark-400 px-3 py-1.5 rounded-full"><i class="ph ph-megaphone text-accent-600"></i> Afrique Identité</span>
                <span class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-100 dark:bg-dark dark:border-dark-100 text-xs font-medium text-gray-600 dark:text-dark-400 px-3 py-1.5 rounded-full"><i class="ph ph-users-three text-primary"></i> AJSB</span>
                <span class="inline-flex items-center gap-1.5 bg-gray-50 border border-gray-100 dark:bg-dark dark:border-dark-100 text-xs font-medium text-gray-600 dark:text-dark-400 px-3 py-1.5 rounded-full"><i class="ph ph-graduation-cap text-accent-600"></i> Projet École Avenir</span>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white shadow-md"><i class="ph ph-magnifying-glass text-xl"></i></span>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Axes de Recherche</h3>
                </div>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0 mt-1"><i class="ph ph-feather"></i></span>
                    Littérature orale et transmission des savoirs en Afrique
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0 mt-1"><i class="ph ph-music-notes"></i></span>
                    Analyse du discours et représentations sociales dans les chansons béninoises
                </li>
                <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-dark-400">
                    <span class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-primary flex-shrink-0 mt-1"><i class="ph ph-scales"></i></span>
                    Politiques publiques et impact sur les mentalités collectives
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>
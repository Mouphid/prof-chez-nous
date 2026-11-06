<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

http_response_code(404);
$page_title = "Page introuvable - " . SITE_NAME;
include "../includes/header.php";
?>

<section class="max-w-2xl mx-auto px-4 py-24 text-center" id="main-content">
    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-primary-50 text-primary flex items-center justify-center">
        <i class="ph ph-magnifying-glass text-4xl"></i>
    </div>
    <p class="text-sm font-semibold text-primary uppercase tracking-widest mb-2">Erreur 404</p>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white mb-4">Cette page n'existe pas</h1>
    <p class="text-gray-500 dark:text-dark-300 mb-8">Le lien est peut-être ancien, ou l'adresse contient une erreur. Vous pouvez revenir à l'accueil ou consulter les publications.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="index.php" class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            <i class="ph ph-house"></i> Accueil
        </a>
        <a href="publications.php" class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition">
            <i class="ph ph-book"></i> Publications
        </a>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

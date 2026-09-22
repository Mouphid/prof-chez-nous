<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Mentions légales - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center py-16" style="background-image: url('<?= BASE_URL ?>img/bg/banner-mentions.jpg');">
    <div class="absolute inset-0 bg-white/75"></div>
    <div class="relative z-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-file-text text-primary"></i> Mentions légales</h1>
        <p class="text-gray-500 mt-2">Informations légales et crédits</p>
    </div>
</div>

<div id="main-content" class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 sm:p-12 prose prose-gray max-w-none">
        <h2 class="text-xl font-bold text-gray-900">1. Éditeur du site</h2>
        <p class="text-gray-600 leading-relaxed">
            <strong class="text-primary">Joie Enseignante</strong><br>
            Plateforme pédagogique dédiée aux enseignants et étudiants.<br>
            Email : contact@joieenseignante.com
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Directeur de la publication</h2>
        <p class="text-gray-600 leading-relaxed">
            Le directeur de la publication est le fondateur de Joie Enseignante.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Hébergement</h2>
        <p class="text-gray-600 leading-relaxed">
            Le site est hébergé par un serveur local dans le cadre d'un environnement de développement et de production.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Propriété intellectuelle</h2>
        <p class="text-gray-600 leading-relaxed">
            L'ensemble des contenus présents sur le site (textes, images, vidéos, cours, etc.) est protégé par le droit d'auteur. 
            Toute reproduction, représentation, modification ou exploitation, totale ou partielle, sans autorisation préalable est interdite.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Responsabilité</h2>
        <p class="text-gray-600 leading-relaxed">
            Joie Enseignante s'efforce d'assurer l'exactitude des informations publiées. 
            Toutefois, la plateforme ne saurait être tenue responsable des erreurs, omissions ou indisponibilités temporaires des ressources.
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

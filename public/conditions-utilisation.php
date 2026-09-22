<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Conditions d'utilisation - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center py-16" style="background-image: url('<?= BASE_URL ?>img/bg/banner-conditions.jpg');">
    <div class="absolute inset-0 bg-white/75"></div>
    <div class="relative z-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-scroll text-primary"></i> Conditions d'utilisation</h1>
        <p class="text-gray-500 mt-2">Règles et obligations pour l'utilisation de la plateforme</p>
    </div>
</div>

<div id="main-content" class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 sm:p-12 prose prose-gray max-w-none">
        <h2 class="text-xl font-bold text-gray-900">1. Acceptation des conditions</h2>
        <p class="text-gray-600 leading-relaxed">
            En accédant et en utilisant la plateforme <strong class="text-primary">Joie Enseignante</strong>, 
            vous acceptez pleinement et sans réserve les présentes conditions d'utilisation.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Accès aux services</h2>
        <p class="text-gray-600 leading-relaxed">
            La plateforme est accessible gratuitement à tout utilisateur disposant d'un accès à Internet. 
            Certaines fonctionnalités peuvent nécessiter la création d'un compte utilisateur.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Compte utilisateur</h2>
        <p class="text-gray-600 leading-relaxed">
            L'utilisateur s'engage à fournir des informations exactes lors de son inscription et à maintenir la confidentialité de ses identifiants. 
            Tout compte suspecté d'activité frauduleuse peut être suspendu sans préavis.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Contenu publié</h2>
        <p class="text-gray-600 leading-relaxed">
            Les enseignants et auteurs conservent les droits sur leurs contenus publiés. 
            En publiant sur la plateforme, ils accordent à Joie Enseignante le droit de diffuser ces contenus à des fins pédagogiques.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Utilisation responsable</h2>
        <p class="text-gray-600 leading-relaxed">
            L'utilisateur s'engage à ne pas utiliser la plateforme à des fins illicites, 
            à ne pas perturber le fonctionnement du site, et à respecter les autres membres de la communauté.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Modification des conditions</h2>
        <p class="text-gray-600 leading-relaxed">
            Joie Enseignante se réserve le droit de modifier les présentes conditions à tout moment. 
            Les utilisateurs seront informés des modifications importantes par email ou via la plateforme.
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

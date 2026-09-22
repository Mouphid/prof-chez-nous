<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Politique de confidentialité - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center py-16" style="background-image: url('<?= BASE_URL ?>img/bg/banner-confidentialite.jpg');">
    <div class="absolute inset-0 bg-white/75"></div>
    <div class="relative z-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-shield-check text-primary"></i> Politique de confidentialité</h1>
        <p class="text-gray-500 mt-2">Protection de vos données personnelles</p>
    </div>
</div>

<div id="main-content" class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 sm:p-12 prose prose-gray max-w-none">
        <h2 class="text-xl font-bold text-gray-900">1. Collecte des données</h2>
        <p class="text-gray-600 leading-relaxed">
            Nous collectons les informations que vous nous fournissez directement lors de la création de votre compte 
            (nom, email) ainsi que les données générées par votre utilisation de la plateforme (commentaires, téléchargements, likes).
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Utilisation des données</h2>
        <p class="text-gray-600 leading-relaxed">
            Vos données sont utilisées pour :<br>
            - Gérer votre compte et vous fournir les services demandés<br>
            - Améliorer et personnaliser votre expérience sur la plateforme<br>
            - Vous contacter concernant votre compte ou les évolutions du service<br>
            - Assurer la sécurité et l'intégrité de la plateforme
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Partage des données</h2>
        <p class="text-gray-600 leading-relaxed">
            Vos données personnelles ne sont jamais vendues à des tiers. Elles peuvent être partagées uniquement 
            dans le cadre légal (obligation légale, protection de nos droits).
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Sécurité</h2>
        <p class="text-gray-600 leading-relaxed">
            Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données 
            contre tout accès non autorisé, modification, divulgation ou destruction.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Vos droits</h2>
        <p class="text-gray-600 leading-relaxed">
            Conformément à la réglementation applicable, vous disposez d'un droit d'accès, de rectification, 
            d'effacement et de portabilité de vos données. Pour exercer ces droits, contactez-nous à l'adresse email 
            de la plateforme.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Cookies</h2>
        <p class="text-gray-600 leading-relaxed">
            La plateforme utilise des cookies de session nécessaires à son fonctionnement. 
            Aucun cookie publicitaire ou de traçage tiers n'est utilisé sans votre consentement explicite.
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

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
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-5 py-4 mb-8 text-sm" role="note">
            <strong><i class="ph ph-warning-circle"></i> À compléter avant la mise en ligne :</strong>
            les zones surlignées en jaune doivent être remplacées par les informations réelles
            de l'éditeur. Elles se trouvent aussi dans <code>config/theme.php</code>
            (téléphone et adresse).
        </div>

        <h2 class="text-xl font-bold text-gray-900">1. Éditeur du site</h2>
        <p class="text-gray-600 leading-relaxed">
            <strong class="text-primary"><?= htmlspecialchars(SITE_NAME) ?></strong><br>
            Plateforme pédagogique dédiée aux enseignants et étudiants.<br>
            Nom et prénom de l'éditeur : <span class="legal-todo">[À COMPLÉTER]</span><br>
            Adresse : <?= htmlspecialchars(SITE_ADDRESS) ?><br>
            Téléphone : <?= htmlspecialchars(SITE_PHONE) ?><br>
            E-mail : <?= htmlspecialchars(SITE_EMAIL) ?><br>
            RCCM : <span class="legal-todo">[À COMPLÉTER, si applicable]</span><br>
            IFU : <span class="legal-todo">[À COMPLÉTER, si applicable]</span>
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Directeur de la publication</h2>
        <p class="text-gray-600 leading-relaxed">
            Directeur de la publication : <span class="legal-todo">[À COMPLÉTER : nom et prénom]</span>.<br>
            Contact : <?= htmlspecialchars(SITE_EMAIL) ?>
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Hébergement</h2>
        <p class="text-gray-600 leading-relaxed">
            Le site est hébergé par <strong>OVHcloud</strong><br>
            OVH SAS — 2 rue Kellermann, 59100 Roubaix, France<br>
            Site : <a href="https://www.ovhcloud.com" target="_blank" rel="noopener" class="text-primary hover:underline">www.ovhcloud.com</a>
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Prestataire de paiement</h2>
        <p class="text-gray-600 leading-relaxed">
            Les paiements en ligne sont traités par un prestataire de paiement sécurisé
            (<strong>Kkiapay</strong> ou <strong>FedaPay</strong>).<br>
            Les données de carte ou de compte mobile money ne sont jamais stockées sur le site.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Propriété intellectuelle</h2>
        <p class="text-gray-600 leading-relaxed">
            L'ensemble des contenus présents sur le site (textes, images, vidéos, cours, documents, etc.)
            est protégé par le droit d'auteur.
            Toute reproduction, représentation, modification, revente ou exploitation, totale ou partielle,
            sans autorisation préalable écrite est interdite.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Responsabilité</h2>
        <p class="text-gray-600 leading-relaxed">
            <?= htmlspecialchars(SITE_NAME) ?> s'efforce d'assurer l'exactitude des informations publiées.
            Toutefois, la plateforme ne saurait être tenue responsable des erreurs, omissions
            ou indisponibilités temporaires des ressources.
        </p>

        <p class="text-sm text-gray-500 mt-10">
            Voir aussi :
            <a href="conditions-utilisation.php" class="text-primary hover:underline">Conditions d'utilisation</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline">Conditions de vente</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline">Confidentialité</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

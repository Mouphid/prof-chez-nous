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
        <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : 7 novembre 2026</p>

        <h2 class="text-xl font-bold text-gray-900">1. Acceptation des conditions</h2>
        <p class="text-gray-600 leading-relaxed">
            En accédant et en utilisant la plateforme <strong class="text-primary"><?= htmlspecialchars(SITE_NAME) ?></strong>,
            vous acceptez pleinement et sans réserve les présentes conditions d'utilisation.
            Si vous n'acceptez pas ces conditions, nous vous invitons à ne pas utiliser le site.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Accès aux services</h2>
        <p class="text-gray-600 leading-relaxed">
            La plateforme est accessible gratuitement à tout utilisateur disposant d'une connexion à Internet.
            Elle est gérée par le professeur, qui publie les articles, les cours et les documents.
        </p>
        <p class="text-gray-600 leading-relaxed">
            <strong>Les visiteurs n'ont pas besoin de créer un compte.</strong>
            Pour un contenu payant, l'acheteur reçoit un lien privé par e-mail, qui sert de clé d'accès.
            Ce lien est personnel et ne doit pas être partagé.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Comptes contributeurs</h2>
        <p class="text-gray-600 leading-relaxed">
            Des comptes réservés aux <strong>professeurs contributeurs</strong> pourront être ouverts ultérieurement,
            sur validation de l'administrateur. Ils permettent de proposer des contenus, qui sont relus avant publication.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Contenu publié</h2>
        <p class="text-gray-600 leading-relaxed">
            Les auteurs conservent les droits sur leurs contenus publiés.
            En publiant sur la plateforme, ils accordent à <?= htmlspecialchars(SITE_NAME) ?>
            le droit de diffuser ces contenus à des fins pédagogiques.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Utilisation responsable</h2>
        <p class="text-gray-600 leading-relaxed">
            L'utilisateur s'engage à ne pas utiliser la plateforme à des fins illicites,
            à ne pas perturber son fonctionnement et à respecter les autres membres de la communauté.
            Il s'interdit notamment de copier, revendre ou rediffuser les contenus payants.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Achats et paiements</h2>
        <p class="text-gray-600 leading-relaxed">
            Les documents et les cours peuvent être gratuits ou payants.
            Les prix sont affichés en francs CFA (FCFA).
            Les modalités de commande, de paiement, d'accès et de remboursement sont décrites dans les
            <a href="conditions-vente.php" class="text-primary hover:underline">conditions de vente</a>.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">7. Modification des conditions</h2>
        <p class="text-gray-600 leading-relaxed">
            <?= htmlspecialchars(SITE_NAME) ?> se réserve le droit de modifier les présentes conditions à tout moment.
            La version en ligne fait foi. Les utilisateurs sont invités à la consulter régulièrement.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">8. Droit applicable</h2>
        <p class="text-gray-600 leading-relaxed">
            Les présentes conditions sont soumises au droit béninois.
            En cas de litige, une solution amiable sera recherchée en priorité
            (contact : <?= htmlspecialchars(SITE_EMAIL) ?>).
            À défaut, le litige sera porté devant les tribunaux compétents de
            <span class="legal-todo">[À COMPLÉTER : ville]</span>.
        </p>

        <p class="text-sm text-gray-500 mt-10">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline">Mentions légales</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline">Conditions de vente</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline">Confidentialité</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

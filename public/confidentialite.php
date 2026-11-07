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
        <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : 7 novembre 2026</p>

        <h2 class="text-xl font-bold text-gray-900">1. Principe</h2>
        <p class="text-gray-600 leading-relaxed">
            <strong class="text-primary"><?= htmlspecialchars(SITE_NAME) ?></strong> respecte votre vie privée.
            Les visiteurs peuvent consulter le site sans créer de compte.
            Nous ne collectons que les informations nécessaires au fonctionnement du service.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Données collectées</h2>
        <p class="text-gray-600 leading-relaxed">
            Selon votre utilisation du site, nous pouvons collecter :
        </p>
        <ul class="text-gray-600 leading-relaxed list-disc pl-6 space-y-1">
            <li><strong>Achat d'un contenu :</strong> nom, adresse e-mail, contenu acheté, montant et date ;</li>
            <li><strong>Newsletter :</strong> adresse e-mail, si vous vous inscrivez volontairement ;</li>
            <li><strong>Contact :</strong> nom, adresse e-mail et message que vous nous envoyez ;</li>
            <li><strong>Commentaires :</strong> nom affiché et contenu du commentaire ;</li>
            <li><strong>Données techniques :</strong> pages consultées, adresse IP et type de navigateur,
                pour la sécurité et les statistiques de lecture.</li>
        </ul>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Paiement</h2>
        <p class="text-gray-600 leading-relaxed">
            Les paiements sont traités par un prestataire sécurisé (<strong>Kkiapay</strong> ou <strong>FedaPay</strong>).
            Les informations de carte bancaire ou de compte mobile money sont transmises directement
            au prestataire et ne sont <strong>jamais stockées</strong> sur nos serveurs.
            Nous recevons uniquement la confirmation du paiement et un identifiant de transaction.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Utilisation des données</h2>
        <p class="text-gray-600 leading-relaxed">
            Vos données servent à :
        </p>
        <ul class="text-gray-600 leading-relaxed list-disc pl-6 space-y-1">
            <li>traiter votre commande et vous envoyer le lien d'accès ;</li>
            <li>répondre à vos messages et demandes d'assistance ;</li>
            <li>envoyer la newsletter si vous y êtes inscrit ;</li>
            <li>assurer la sécurité du site et prévenir les abus ;</li>
            <li>établir des statistiques anonymes de consultation.</li>
        </ul>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Partage des données</h2>
        <p class="text-gray-600 leading-relaxed">
            Vos données personnelles ne sont jamais vendues.
            Elles peuvent être transmises uniquement :
        </p>
        <ul class="text-gray-600 leading-relaxed list-disc pl-6 space-y-1">
            <li>au prestataire de paiement, pour encaisser la commande ;</li>
            <li>à l'hébergeur, pour le stockage technique du site ;</li>
            <li>aux autorités, en cas d'obligation légale.</li>
        </ul>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Durée de conservation</h2>
        <p class="text-gray-600 leading-relaxed">
            Les informations de commande sont conservées le temps nécessaire à la gestion des achats
            et aux obligations comptables. Les abonnés à la newsletter restent enregistrés
            jusqu'à leur désinscription. Les messages de contact sont conservés le temps du traitement.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">7. Vos droits</h2>
        <p class="text-gray-600 leading-relaxed">
            Conformément à la réglementation applicable, vous disposez d'un droit d'accès,
            de rectification, d'effacement et de portabilité de vos données.
            Pour exercer ces droits, écrivez à
            <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>.
            Une réponse vous sera apportée dans les meilleurs délais.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">8. Cookies</h2>
        <p class="text-gray-600 leading-relaxed">
            Le site utilise uniquement des <strong>cookies de session</strong> nécessaires à son fonctionnement
            (connexion à l'administration, protection des formulaires).
            Aucun cookie publicitaire ni de traçage tiers n'est utilisé.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">9. Sécurité</h2>
        <p class="text-gray-600 leading-relaxed">
            Nous mettons en œuvre des mesures techniques et organisationnelles pour protéger vos données
            contre tout accès non autorisé, modification, divulgation ou destruction
            (connexion sécurisée HTTPS, mots de passe chiffrés, accès limité à l'administration).
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">10. Contact</h2>
        <p class="text-gray-600 leading-relaxed">
            Pour toute question sur vos données personnelles :
            <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a><br>
            Adresse : <?= htmlspecialchars(SITE_ADDRESS) ?>
        </p>

        <p class="text-sm text-gray-500 mt-10">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline">Mentions légales</a> ·
            <a href="conditions-utilisation.php" class="text-primary hover:underline">Conditions d'utilisation</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline">Conditions de vente</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

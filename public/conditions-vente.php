<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Conditions de vente - Joie Enseignante";

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center py-16" style="background-image: url('<?= BASE_URL ?>img/bg/banner-conditions.jpg');">
    <div class="absolute inset-0 bg-white/75"></div>
    <div class="relative z-10 text-center">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-shopping-cart text-primary"></i> Conditions de vente</h1>
        <p class="text-gray-500 mt-2">Achat des documents et des cours en ligne</p>
    </div>
</div>

<div id="main-content" class="max-w-3xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 sm:p-12 prose prose-gray max-w-none">
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl px-5 py-4 mb-8 text-sm" role="note">
            <strong><i class="ph ph-warning-circle"></i> À faire valider avant la mise en ligne :</strong>
            les zones surlignées en jaune (délai de rétractation, ville du tribunal) doivent être
            confirmées par le professeur, si possible avec un conseil juridique.
        </div>

        <p class="text-sm text-gray-500 mb-8">Dernière mise à jour : 7 novembre 2026</p>

        <h2 class="text-xl font-bold text-gray-900">1. Objet</h2>
        <p class="text-gray-600 leading-relaxed">
            Les présentes conditions régissent la vente des documents numériques et des cours en ligne
            proposés sur <strong class="text-primary"><?= htmlspecialchars(SITE_NAME) ?></strong>.
            Elles s'appliquent à tout acheteur, qu'il achète un document, un cours complet ou un chapitre.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">2. Produits proposés</h2>
        <p class="text-gray-600 leading-relaxed">
            La plateforme propose :
        </p>
        <ul class="text-gray-600 leading-relaxed list-disc pl-6 space-y-1">
            <li>des <strong>documents PDF</strong>, gratuits ou payants ;</li>
            <li>des <strong>cours en ligne</strong> composés de chapitres (texte, PDF et/ou vidéo) ;</li>
            <li>des <strong>chapitres vendus séparément</strong>, lorsque le professeur l'a prévu.</li>
        </ul>
        <p class="text-gray-600 leading-relaxed">
            Les contenus gratuits restent accessibles librement.
            Les contenus payants sont décrits sur leur page avec leur prix et leur contenu.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">3. Prix</h2>
        <p class="text-gray-600 leading-relaxed">
            Les prix sont indiqués en <strong>francs CFA (FCFA)</strong>, toutes taxes comprises.
            <?= htmlspecialchars(SITE_NAME) ?> peut modifier ses prix à tout moment,
            mais le prix appliqué est celui affiché au moment de la commande.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">4. Commande sans compte</h2>
        <p class="text-gray-600 leading-relaxed">
            L'acheteur n'a pas besoin de créer un compte.
            Il indique son nom et son adresse e-mail au moment du paiement.
            Ces informations servent uniquement à traiter la commande et à envoyer le lien d'accès.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">5. Paiement</h2>
        <p class="text-gray-600 leading-relaxed">
            Le paiement est traité par un prestataire sécurisé (<strong>Kkiapay</strong> ou <strong>FedaPay</strong>),
            par mobile money ou carte bancaire selon les moyens proposés.
            Les données bancaires ne sont jamais stockées sur le site.
            La commande est confirmée après vérification du paiement par le prestataire.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">6. Accès au contenu</h2>
        <p class="text-gray-600 leading-relaxed">
            Après confirmation du paiement, un <strong>e-mail contenant un lien privé</strong> est envoyé
            à l'adresse indiquée. Ce lien permet d'ouvrir le document ou le chapitre acheté,
            et éventuellement de lire la vidéo directement sur la plateforme.
            L'accès est immédiat après validation du paiement.
        </p>
        <p class="text-gray-600 leading-relaxed">
            Le lien est personnel : il ne doit pas être partagé ni revendu.
            En cas de perte de l'e-mail, l'acheteur peut utiliser la page
            <a href="retrouver-mon-achat.php" class="text-primary hover:underline">Retrouver mon achat</a>
            avec l'adresse utilisée lors du paiement.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">7. Rétractation et remboursement</h2>
        <p class="text-gray-600 leading-relaxed">
            S'agissant de contenus numériques fournis immédiatement, l'acheteur demande l'accès
            dès la confirmation du paiement. Conformément à la réglementation applicable aux contenus numériques,
            le droit de rétractation peut ne pas s'appliquer une fois le contenu ouvert.
            Délai et conditions exacts à confirmer :
            <span class="legal-todo">[À COMPLÉTER : délai de rétractation éventuel]</span>.
        </p>
        <p class="text-gray-600 leading-relaxed">
            Un remboursement ou un nouvel envoi du lien est prévu en cas de :
        </p>
        <ul class="text-gray-600 leading-relaxed list-disc pl-6 space-y-1">
            <li>double paiement pour la même commande ;</li>
            <li>paiement encaissé mais lien non reçu ou contenu inaccessible ;</li>
            <li>fichier corrompu ou illisible.</li>
        </ul>
        <p class="text-gray-600 leading-relaxed">
            Toute demande doit être adressée à
            <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>
            en précisant le nom, l'adresse e-mail utilisée, la date et le contenu acheté.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">8. Usage personnel</h2>
        <p class="text-gray-600 leading-relaxed">
            Les documents et cours achetés sont réservés à un usage <strong>personnel et pédagogique</strong>.
            Toute revente, redistribution, publication ou mise en ligne, totale ou partielle, est interdite.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">9. Disponibilité et support</h2>
        <p class="text-gray-600 leading-relaxed">
            <?= htmlspecialchars(SITE_NAME) ?> met tout en œuvre pour maintenir le site accessible.
            Une interruption temporaire pour maintenance ou pour une raison technique ne donne pas lieu à remboursement.
            Pour toute difficulté : <?= htmlspecialchars(SITE_EMAIL) ?>.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">10. Données personnelles</h2>
        <p class="text-gray-600 leading-relaxed">
            Les données transmises lors de l'achat sont utilisées uniquement pour traiter la commande,
            envoyer le lien d'accès et répondre aux demandes.
            Pour en savoir plus, consultez la
            <a href="confidentialite.php" class="text-primary hover:underline">politique de confidentialité</a>.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8">11. Litiges et droit applicable</h2>
        <p class="text-gray-600 leading-relaxed">
            Les présentes conditions sont soumises au droit béninois.
            En cas de désaccord, une solution amiable sera recherchée en priorité.
            À défaut, le litige sera porté devant les tribunaux compétents de
            <span class="legal-todo">[À COMPLÉTER : ville]</span>.
        </p>

        <p class="text-sm text-gray-500 mt-10">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline">Mentions légales</a> ·
            <a href="conditions-utilisation.php" class="text-primary hover:underline">Conditions d'utilisation</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline">Confidentialité</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

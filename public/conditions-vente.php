<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Conditions de vente - Joie Enseignante";

include "../includes/header.php";
?>

<section id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-6 sm:p-10 lg:p-12">
        <header class="border-b border-gray-100 dark:border-dark-100 pb-6 mb-8">
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest block mb-2">Documents &amp; cours en ligne</span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Conditions de vente</h1>
            <p class="text-sm text-gray-500 dark:text-dark-300 mt-2">Modalités d'achat, de paiement et d'accès aux ressources numériques sur <?= htmlspecialchars(SITE_NAME) ?>.</p>
        </header>

        <div class="space-y-8 text-gray-600 dark:text-dark-300 leading-relaxed sbr-justify">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">1. Objet</h2>
                <p>
                    Les présentes conditions régissent la vente des documents numériques et des cours en ligne proposés sur <strong class="text-gray-900 dark:text-white"><?= htmlspecialchars(SITE_NAME) ?></strong>. Elles s'appliquent à tout acheteur, qu'il achète un document, un cours complet ou un chapitre.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">2. Produits proposés</h2>
                <p class="mb-2">La plateforme propose :</p>
                <ul class="list-disc pl-6 space-y-1.5 mb-3">
                    <li>des <strong class="text-gray-900 dark:text-white">documents PDF</strong>, gratuits ou payants ;</li>
                    <li>des <strong class="text-gray-900 dark:text-white">cours en ligne</strong> composés de chapitres (texte, PDF et/ou vidéo) ;</li>
                    <li>des <strong class="text-gray-900 dark:text-white">chapitres vendus séparément</strong>, lorsque le professeur l'a prévu.</li>
                </ul>
                <p>
                    Les contenus gratuits restent accessibles librement. Les contenus payants sont décrits sur leur page avec leur prix et leur contenu détaillé.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">3. Prix</h2>
                <p>
                    Les prix sont indiqués en <strong class="text-gray-900 dark:text-white">francs CFA (FCFA)</strong>, toutes taxes comprises. <?= htmlspecialchars(SITE_NAME) ?> peut modifier ses prix à tout moment, mais le tarif appliqué est celui affiché au moment de la commande.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">4. Commande sans compte</h2>
                <p>
                    L'acheteur n'a pas besoin de créer un compte. Il indique son nom et son adresse e-mail au moment du paiement. Ces informations servent uniquement à traiter la commande et à envoyer le lien d'accès.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">5. Paiement</h2>
                <p>
                    Le paiement est traité par un prestataire sécurisé (<strong class="text-gray-900 dark:text-white">Kkiapay</strong> ou <strong class="text-gray-900 dark:text-white">FedaPay</strong>), par mobile money ou carte bancaire selon les moyens proposés. Les données bancaires ne sont jamais stockées sur le site. La commande est confirmée après vérification du paiement par le prestataire.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">6. Accès au contenu</h2>
                <p class="mb-3">
                    Après confirmation du paiement, un <strong class="text-gray-900 dark:text-white">e-mail contenant un lien privé</strong> est envoyé à l'adresse indiquée. Ce lien permet d'ouvrir le document ou le chapitre acheté, et éventuellement de lire la vidéo directement sur la plateforme. L'accès est immédiat après validation du paiement.
                </p>
                <p>
                    Le lien est personnel : il ne doit pas être partagé ni revendu. En cas de perte de l'e-mail, l'acheteur peut utiliser la page <a href="retrouver-mon-achat.php" class="text-primary hover:underline font-medium">Retrouver mon achat</a> avec l'adresse utilisée lors du paiement.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">7. Rétractation et remboursement</h2>
                <p class="mb-2">
                    S'agissant de contenus numériques fournis immédiatement, l'acheteur accède au contenu dès la confirmation du paiement. Conformément à la réglementation applicable aux contenus numériques, le droit de rétractation ne s'applique plus une fois le contenu numérique ouvert ou téléchargé.
                </p>
                <p class="mb-2">Un remboursement ou un nouvel envoi du lien est assuré en cas de :</p>
                <ul class="list-disc pl-6 space-y-1.5 mb-3">
                    <li>double paiement pour la même commande ;</li>
                    <li>paiement encaissé mais lien non reçu ou contenu inaccessible ;</li>
                    <li>fichier corrompu ou illisible.</li>
                </ul>
                <p>
                    Toute demande doit être adressée à <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a> en précisant le nom, l'adresse e-mail utilisée, la date et le contenu acheté.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">8. Usage personnel</h2>
                <p>
                    Les documents et cours achetés sont réservés à un usage <strong class="text-gray-900 dark:text-white">personnel et pédagogique</strong>. Toute revente, redistribution, publication ou mise en ligne, totale ou partielle, est interdite.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">9. Disponibilité et support</h2>
                <p>
                    <?= htmlspecialchars(SITE_NAME) ?> met tout en œuvre pour maintenir le site accessible. Une interruption temporaire pour maintenance ou pour une raison technique ne donne pas lieu à remboursement. Pour toute assistance : <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">10. Données personnelles</h2>
                <p>
                    Les données transmises lors de l'achat sont utilisées uniquement pour traiter la commande, envoyer le lien d'accès et répondre aux demandes. Pour en savoir plus, consultez notre <a href="confidentialite.php" class="text-primary hover:underline font-medium">politique de confidentialité</a>.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">11. Litiges et droit applicable</h2>
                <p>
                    Les présentes conditions sont soumises au droit béninois. En cas de désaccord, une solution amiable sera recherchée en priorité. À défaut, le litige sera porté devant les juridictions compétents de Cotonou.
                </p>
            </div>
        </div>

        <footer class="border-t border-gray-100 dark:border-dark-100 pt-6 mt-10 text-sm text-gray-500 dark:text-dark-300">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline font-medium">Mentions légales</a> ·
            <a href="conditions-utilisation.php" class="text-primary hover:underline font-medium">Conditions d'utilisation</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline font-medium">Confidentialité</a>
        </footer>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

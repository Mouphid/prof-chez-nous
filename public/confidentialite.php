<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Politique de confidentialité - Joie Enseignante";

include "../includes/header.php";
?>

<section id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-6 sm:p-10 lg:p-12">
        <header class="border-b border-gray-100 dark:border-dark-100 pb-6 mb-8">
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest block mb-2">Protection des données</span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Politique de confidentialité</h1>
            <p class="text-sm text-gray-500 dark:text-dark-300 mt-2">Traitement des données personnelles et utilisation des cookies sur <?= htmlspecialchars(SITE_NAME) ?>.</p>
        </header>

        <div class="space-y-8 text-gray-600 dark:text-dark-300 leading-relaxed sbr-justify">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">1. Principe</h2>
                <p>
                    <strong class="text-gray-900 dark:text-white"><?= htmlspecialchars(SITE_NAME) ?></strong> respecte votre vie privée. Les visiteurs peuvent consulter le site sans créer de compte. Nous ne collectons que les informations strictement nécessaires au fonctionnement du service.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">2. Données collectées</h2>
                <p class="mb-2">Selon votre utilisation du site, nous pouvons collecter :</p>
                <ul class="list-disc pl-6 space-y-1.5">
                    <li><strong class="text-gray-900 dark:text-white">Achat d'un contenu :</strong> nom, adresse e-mail, contenu acheté, montant et date ;</li>
                    <li><strong class="text-gray-900 dark:text-white">Newsletter :</strong> adresse e-mail et date d'inscription, lorsque vous vous abonnez volontairement ;</li>
                    <li><strong class="text-gray-900 dark:text-white">Contact :</strong> nom, adresse e-mail et message que vous nous envoyez ;</li>
                    <li><strong class="text-gray-900 dark:text-white">Commentaires :</strong> nom affiché et contenu du commentaire ;</li>
                    <li><strong class="text-gray-900 dark:text-white">Données techniques :</strong> pages consultées, adresse IP et type de navigateur, pour la sécurité et les statistiques de lecture.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">3. Paiement</h2>
                <p>
                    Les paiements sont traités par un prestataire sécurisé (<strong class="text-gray-900 dark:text-white">Kkiapay</strong> ou <strong class="text-gray-900 dark:text-white">FedaPay</strong>). Les informations de carte bancaire ou de compte mobile money sont transmises directement au prestataire et ne sont <strong class="text-gray-900 dark:text-white">jamais stockées</strong> sur nos serveurs. Nous recevons uniquement la confirmation du paiement et un identifiant de transaction.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">4. Utilisation des données</h2>
                <p class="mb-2">Vos données servent uniquement à :</p>
                <ul class="list-disc pl-6 space-y-1.5">
                    <li>traiter votre commande et vous envoyer votre lien d'accès ;</li>
                    <li>répondre à vos messages et demandes d'assistance ;</li>
                    <li>vous envoyer la lettre pédagogique si vous y êtes abonné ;</li>
                    <li>assurer la sécurité du site et prévenir les abus ;</li>
                    <li>établir des statistiques anonymes de consultation.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">5. Partage des données</h2>
                <p class="mb-2">Vos données personnelles ne sont jamais vendues. Elles peuvent être transmises uniquement :</p>
                <ul class="list-disc pl-6 space-y-1.5">
                    <li>au prestataire de paiement, pour encaisser la commande ;</li>
                    <li>à l'hébergeur technique du site (OVHcloud) ;</li>
                    <li>aux autorités compétentes, en cas d'obligation légale.</li>
                </ul>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">6. Durée de conservation</h2>
                <p>
                    Les informations de commande sont conservées le temps nécessaire à la gestion des achats et aux obligations comptables. Les abonnés à la newsletter restent enregistrés jusqu'à leur désinscription (accessible en un clic dans chaque e-mail). Les messages de contact sont conservés le temps de leur traitement.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">7. Vos droits</h2>
                <p>
                    Conformément à la réglementation applicable, vous disposez d'un droit d'accès, de rectification, d'effacement et de portabilité de vos données. Pour exercer ces droits, écrivez à <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>. Une réponse vous sera apportée dans les meilleurs délais.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">8. Cookies</h2>
                <p>
                    Le site utilise des <strong class="text-gray-900 dark:text-white">cookies techniques et de préférence</strong> nécessaires à son bon fonctionnement (session de navigation, choix du thème clair/sombre, mémorisation de votre choix de consentement et sécurité des formulaires). Aucun cookie publicitaire tiers n'est utilisé.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">9. Sécurité</h2>
                <p>
                    Nous mettons en œuvre des mesures techniques et organisationnelles pour protéger vos données contre tout accès non autorisé, modification, divulgation ou destruction (connexion sécurisée HTTPS, mots de passe chiffrés, accès restreint à l'administration).
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">10. Contact</h2>
                <p>
                    Pour toute question sur vos données personnelles : <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a><br>
                    Adresse : <?= htmlspecialchars(SITE_ADDRESS) ?>
                </p>
            </div>
        </div>

        <footer class="border-t border-gray-100 dark:border-dark-100 pt-6 mt-10 text-sm text-gray-500 dark:text-dark-300">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline font-medium">Mentions légales</a> ·
            <a href="conditions-utilisation.php" class="text-primary hover:underline font-medium">Conditions d'utilisation</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline font-medium">Conditions de vente</a>
        </footer>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Mentions légales - Joie Enseignante";

include "../includes/header.php";
?>

<section id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-6 sm:p-10 lg:p-12">
        <header class="border-b border-gray-100 dark:border-dark-100 pb-6 mb-8">
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest block mb-2">Informations légales</span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Mentions légales</h1>
            <p class="text-sm text-gray-500 dark:text-dark-300 mt-2">Informations légales, éditeur et hébergement de la plateforme <?= htmlspecialchars(SITE_NAME) ?>.</p>
        </header>

        <div class="space-y-8 text-gray-600 dark:text-dark-300 leading-relaxed sbr-justify">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">1. Éditeur du site</h2>
                <p>
                    <strong class="text-gray-900 dark:text-white"><?= htmlspecialchars(SITE_NAME) ?></strong> — <?= htmlspecialchars(SITE_TAGLINE) ?>.<br>
                    Responsable : Professeur Sylvestre Djouamon<br>
                    Adresse : <?= htmlspecialchars(SITE_ADDRESS) ?><br>
                    Téléphone : <?= htmlspecialchars(SITE_PHONE) ?><br>
                    E-mail : <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">2. Directeur de la publication</h2>
                <p>
                    Directeur de la publication : <strong class="text-gray-900 dark:text-white">Sylvestre Djouamon</strong>.<br>
                    Contact : <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">3. Hébergement</h2>
                <p>
                    Le site est hébergé par <strong class="text-gray-900 dark:text-white">OVHcloud</strong><br>
                    OVH SAS — 2 rue Kellermann, 59100 Roubaix, France<br>
                    Site web : <a href="https://www.ovhcloud.com" target="_blank" rel="noopener" class="text-primary hover:underline">www.ovhcloud.com</a>
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">4. Prestataire de paiement</h2>
                <p>
                    Les paiements en ligne sont traités par un prestataire de paiement sécurisé (<strong class="text-gray-900 dark:text-white">Kkiapay</strong> ou <strong class="text-gray-900 dark:text-white">FedaPay</strong>). Les données de carte bancaire ou de compte mobile money ne sont jamais stockées sur le site.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">5. Propriété intellectuelle</h2>
                <p>
                    L'ensemble des contenus présents sur le site (textes, images, vidéos, cours, documents, etc.) est protégé par le droit d'auteur. Toute reproduction, représentation, modification, revente ou exploitation, totale ou partielle, sans autorisation préalable écrite est interdite.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">6. Responsabilité</h2>
                <p>
                    <?= htmlspecialchars(SITE_NAME) ?> s'efforce d'assurer l'exactitude des informations publiées. Toutefois, la plateforme ne saurait être tenue responsable des erreurs, omissions ou indisponibilités temporaires des ressources.
                </p>
            </div>
        </div>

        <footer class="border-t border-gray-100 dark:border-dark-100 pt-6 mt-10 text-sm text-gray-500 dark:text-dark-300">
            Voir aussi :
            <a href="conditions-utilisation.php" class="text-primary hover:underline font-medium">Conditions d'utilisation</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline font-medium">Conditions de vente</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline font-medium">Confidentialité</a>
        </footer>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

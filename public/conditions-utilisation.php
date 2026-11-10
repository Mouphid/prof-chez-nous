<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Conditions d'utilisation - Joie Enseignante";

include "../includes/header.php";
?>

<section id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-6 sm:p-10 lg:p-12">
        <header class="border-b border-gray-100 dark:border-dark-100 pb-6 mb-8">
            <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest block mb-2">Règles de la plateforme</span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Conditions d'utilisation</h1>
            <p class="text-sm text-gray-500 dark:text-dark-300 mt-2">Règles et obligations applicables à l'utilisation de la plateforme <?= htmlspecialchars(SITE_NAME) ?>.</p>
        </header>

        <div class="space-y-8 text-gray-600 dark:text-dark-300 leading-relaxed sbr-justify">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">1. Acceptation des conditions</h2>
                <p>
                    En accédant et en utilisant la plateforme <strong class="text-gray-900 dark:text-white"><?= htmlspecialchars(SITE_NAME) ?></strong>, vous acceptez pleinement et sans réserve les présentes conditions d'utilisation. Si vous n'acceptez pas ces conditions, nous vous invitons à ne pas utiliser le site.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">2. Accès aux services</h2>
                <p class="mb-3">
                    La plateforme est accessible gratuitement à tout utilisateur disposant d'une connexion à Internet. Elle est gérée par le professeur, qui publie les articles, les cours et les documents.
                </p>
                <p>
                    <strong class="text-gray-900 dark:text-white">Les visiteurs n'ont pas besoin de créer un compte.</strong> Pour un contenu payant, l'acheteur reçoit un lien privé par e-mail, qui sert de clé d'accès. Ce lien est personnel et ne doit pas être partagé.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">3. Comptes contributeurs</h2>
                <p>
                    Des comptes réservés aux <strong class="text-gray-900 dark:text-white">professeurs contributeurs</strong> peuvent être ouverts sur validation de l'administrateur. Ils permettent de proposer des contenus pédagogiques, qui sont relus avant publication.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">4. Contenu publié</h2>
                <p>
                    Les auteurs conservent les droits sur leurs contenus publiés. En publiant sur la plateforme, ils accordent à <?= htmlspecialchars(SITE_NAME) ?> le droit de diffuser ces contenus à des fins pédagogiques.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">5. Utilisation responsable</h2>
                <p>
                    L'utilisateur s'engage à ne pas utiliser la plateforme à des fins illicites, à ne pas perturber son fonctionnement et à respecter les autres membres de la communauté. Il s'interdit notamment de copier, revendre ou rediffuser les contenus payants.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">6. Achats et paiements</h2>
                <p>
                    Les documents et les cours peuvent être gratuits ou payants. Les prix sont affichés en francs CFA (FCFA). Les modalités de commande, de paiement, d'accès et de remboursement sont décrites dans les <a href="conditions-vente.php" class="text-primary hover:underline font-medium">conditions de vente</a>.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">7. Modification des conditions</h2>
                <p>
                    <?= htmlspecialchars(SITE_NAME) ?> se réserve le droit de modifier les présentes conditions à tout moment. La version en ligne fait foi. Les utilisateurs sont invités à la consulter régulièrement.
                </p>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">8. Droit applicable</h2>
                <p>
                    Les présentes conditions sont soumises au droit béninois. En cas de litige, une solution amiable sera recherchée en priorité (contact : <a href="mailto:<?= htmlspecialchars(SITE_EMAIL) ?>" class="text-primary hover:underline"><?= htmlspecialchars(SITE_EMAIL) ?></a>). À défaut d'accord amiable, le litige sera porté devant les juridictions compétentes de Cotonou.
                </p>
            </div>
        </div>

        <footer class="border-t border-gray-100 dark:border-dark-100 pt-6 mt-10 text-sm text-gray-500 dark:text-dark-300">
            Voir aussi :
            <a href="mentions-legales.php" class="text-primary hover:underline font-medium">Mentions légales</a> ·
            <a href="conditions-vente.php" class="text-primary hover:underline font-medium">Conditions de vente</a> ·
            <a href="confidentialite.php" class="text-primary hover:underline font-medium">Confidentialité</a>
        </footer>
    </div>
</section>

<?php include "../includes/footer.php"; ?>

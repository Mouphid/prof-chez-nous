<?php
/**
 * Premium footer with social links and newsletter.
 * Usage: include "includes/footer-section.php";
 */
?>
<footer class="bg-dark dark:bg-dark-50 text-gray-400 dark:text-dark-300 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div class="sm:col-span-2 lg:col-span-1">
            <a href="index.php" class="flex items-center gap-2.5 mb-4 group">
                <img src="../img/logo.jpg" alt="<?= SITE_NAME ?>" class="h-10 w-auto rounded-lg group-hover:scale-105 transition-transform">
            </a>
            <p class="leading-relaxed text-gray-500 dark:text-dark-300 text-sm mb-6"><?= SITE_TAGLINE ?></p>
            <div class="flex items-center gap-2">
                <?php social_icons() ?>
            </div>
        </div>
        <div>
            <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Navigation</h4>
            <ul class="space-y-3">
                <li><a href="index.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-house text-gray-600 dark:text-dark-200"></i> Accueil</a></li>
                <li><a href="about.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-info text-gray-600 dark:text-dark-200"></i> À propos</a></li>
                <li><a href="biography.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-user-circle text-gray-600 dark:text-dark-200"></i> Biographie</a></li>
                <li><a href="contact.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-envelope text-gray-600 dark:text-dark-200"></i> Contact</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Compte</h4>
            <ul class="space-y-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Mon profil</a></li>
                <li><a href="logout.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Déconnexion</a></li>
                <?php else: ?>
                <li><a href="login.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Connexion</a></li>
                <li><a href="register.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Inscription</a></li>
                <?php endif; ?>
                <li><a href="../admin/login.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Administration</a></li>
            </ul>
        </div>
        <div>
            <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Légal</h4>
            <ul class="space-y-3">
                <li><a href="mentions-legales.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-file-text text-gray-600 dark:text-dark-200"></i> Mentions légales</a></li>
                <li><a href="conditions-utilisation.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-scroll text-gray-600 dark:text-dark-200"></i> Conditions d'utilisation</a></li>
                <li><a href="confidentialite.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-shield-check text-gray-600 dark:text-dark-200"></i> Confidentialité</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/5 py-6 text-center text-xs text-gray-600 dark:text-dark-200">
        &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.
    </div>
</footer>

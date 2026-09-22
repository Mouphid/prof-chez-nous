<?php
/**
 * Navigation bar with dark mode toggle.
 * Usage: include "includes/navbar.php";
 */
$nav_active = $nav_active ?? ($_GET['page'] ?? 'accueil');
$nav_links  = $nav_links ?? [
    ['accueil', 'index.php', 'ph-house', 'Accueil'],
    ['about', 'about.php', 'ph-info', 'À propos'],
    ['biography', 'biography.php', 'ph-user-circle', 'Biographie'],
    ['contact', 'contact.php', 'ph-envelope', 'Contact'],
];
?>
<header class="bg-white/80 dark:bg-dark/80 backdrop-blur-xl border-b border-gray-100 dark:border-dark-100 sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 sm:h-18">
        <a href="index.php" class="flex items-center gap-2.5 transition group">
            <img src="../img/logo.jpg" alt="<?= SITE_NAME ?>" class="h-10 sm:h-12 w-auto rounded-lg group-hover:scale-105 transition-transform duration-300">
        </a>
        <nav class="hidden md:flex items-center gap-1" aria-label="Navigation principale">
            <?php foreach ($nav_links as $link): ?>
            <a href="<?= $link[1] ?>" class="px-3.5 py-2 rounded-xl text-sm font-medium <?= $nav_active === $link[0] ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 ring-1 ring-primary-200 dark:ring-primary-800' : 'text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white' ?> transition-all duration-300">
                <i class="<?= $link[2] ?>"></i> <?= $link[3] ?>
            </a>
            <?php endforeach; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="relative group">
                <button class="flex items-center gap-2 bg-primary-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-primary-600 transition-all duration-300 shadow-sm hover:shadow-md" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="ph ph-caret-down text-xs"></i>
                </button>
                <div class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-dark-50 rounded-2xl shadow-xl dark:shadow-dark-lg border border-gray-100 dark:border-dark-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50" role="menu">
                    <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 rounded-t-2xl transition-colors" role="menuitem"><i class="ph ph-user-cog text-base"></i> Mon profil</a>
                    <a href="my_downloads.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" role="menuitem"><i class="ph ph-download text-base"></i> Mes téléchargements</a>
                    <a href="my_comments.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" role="menuitem"><i class="ph ph-chats text-base"></i> Mes commentaires</a>
                    <hr class="border-gray-100 dark:border-dark-100">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-b-2xl transition-colors" role="menuitem"><i class="ph ph-sign-out text-base"></i> Déconnexion</a>
                </div>
            </div>
            <?php else: ?>
            <a href="register.php" class="px-3.5 py-2 rounded-xl text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-300"><i class="ph ph-user-plus"></i> Inscription</a>
            <a href="login.php" class="bg-primary-500 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-primary-600 transition-all duration-300 shadow-sm hover:shadow-md"><i class="ph ph-sign-in"></i> Connexion</a>
            <?php endif; ?>
        </nav>
        <div class="flex items-center gap-2 md:hidden">
            <button id="darkModeToggle" class="p-2 rounded-xl text-gray-500 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 transition-all" aria-label="Basculer le thème">
                <i class="ph ph-moon text-lg dark:hidden"></i>
                <i class="ph ph-sun text-lg hidden dark:block"></i>
            </button>
            <button class="text-gray-600 dark:text-dark-400 p-2 hover:bg-gray-100 dark:hover:bg-dark-50 rounded-xl transition-all" onclick="toggleMobileMenu(this)" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
        </div>
    </div>
    <div class="hidden md:hidden bg-white dark:bg-dark border-t border-gray-100 dark:border-dark-100 px-4 py-3 space-y-1" id="mobileNav" role="navigation">
        <?php foreach ($nav_links as $link): ?>
        <a href="<?= $link[1] ?>" class="block px-3 py-2.5 rounded-xl text-sm font-medium <?= $nav_active === $link[0] ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50' ?>"><i class="<?= $link[2] ?>"></i> <?= $link[3] ?></a>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-user-cog"></i> Mon profil</a>
        <a href="my_downloads.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-download"></i> Mes téléchargements</a>
        <a href="my_comments.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-chats"></i> Mes commentaires</a>
        <a href="logout.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 dark:text-red-400"><i class="ph ph-sign-out"></i> Déconnexion</a>
        <?php else: ?>
        <a href="login.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium bg-primary-500 text-white text-center"><i class="ph ph-sign-in"></i> Connexion</a>
        <a href="register.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-primary-600 dark:text-primary-400 text-center border border-primary-200 dark:border-primary-800"><i class="ph ph-user-plus"></i> Inscription</a>
        <?php endif; ?>
    </div>
</header>

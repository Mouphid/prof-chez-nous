<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>
    <meta name="description" content="<?= SITE_TAGLINE ?>">
    <?php cdn_head(); animation_styles(); ?>
</head>
<body class="bg-gray-50 font-sans text-gray-800 leading-relaxed">
    <?php skip_link() ?>
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="../public/index.php" class="flex items-center transition">
                <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-12 w-auto">
            </a>

            <nav class="hidden md:flex items-center gap-1" aria-label="Navigation principale">
                <a href="../public/index.php" class="px-3 py-2 rounded-lg text-sm font-medium <?= (htmlspecialchars($_GET['page'] ?? '') === '') ? 'bg-indigo-50 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?> transition">
                    <i class="ph ph-house"></i> Accueil
                </a>
                <a href="../public/about.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-info"></i> À propos</a>
                <a href="../public/biography.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-user-circle"></i> Biographie</a>
                <a href="../public/contact.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-envelope"></i> Contact</a>
            </nav>

            <div class="flex items-center gap-3">
                <form action="../public/search.php" method="get" class="hidden md:flex items-center bg-gray-100 rounded-lg px-3">
                    <button type="submit" class="text-gray-500" aria-label="Rechercher"><i class="ph ph-magnifying-glass"></i></button>
                    <input type="text" name="q" placeholder="Rechercher..." class="bg-transparent border-none px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 rounded w-28">
                </form>

                <?php if (is_admin()): ?>
                <a href="../admin/dashboard.php" class="hidden md:flex items-center gap-1 bg-primary text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    <i class="ph ph-gear"></i> Admin
                </a>
                <?php endif; ?>

                <button class="md:hidden text-gray-600 p-2" onclick="toggleMobileMenu(this)" aria-label="Menu">
                    <i class="ph ph-list text-xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile nav -->
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <a href="../public/index.php" class="block px-3 py-2 rounded-lg text-sm font-medium <?= (htmlspecialchars($_GET['page'] ?? '') === '') ? 'bg-indigo-50 text-primary' : 'text-gray-600 hover:bg-gray-100' ?>"><i class="ph ph-house"></i> Accueil</a>
            <a href="../public/about.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-info"></i> À propos</a>
            <a href="../public/biography.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-circle"></i> Biographie</a>
            <a href="../public/contact.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-envelope"></i> Contact</a>
            <?php if (is_admin()): ?>
            <a href="../admin/dashboard.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white"><i class="ph ph-gear"></i> Admin</a>
            <?php endif; ?>
            <form action="../public/search.php" method="get" class="flex bg-gray-100 rounded-lg px-3 mt-2">
                <button type="submit" class="text-gray-500" aria-label="Rechercher"><i class="ph ph-magnifying-glass"></i></button>
                <input type="text" name="q" placeholder="Rechercher..." class="bg-transparent border-none px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 rounded w-full">
            </form>
        </div>
    </header>

    <?php if (hasFlash('success')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2" role="alert">
            <i class="ph ph-check-circle"></i> <?= flash('success') ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasFlash('error')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2" role="alert">
            <i class="ph ph-warning-circle"></i> <?= flash('error') ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="min-h-screen">

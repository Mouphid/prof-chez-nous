<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Joie Enseignante' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="Plateforme pédagogique pour enseignants et étudiants">
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="../public/index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary hover:text-indigo-700 transition">
                <i class="fas fa-graduation-cap"></i>
                <span>Joie Enseignante</span>
            </a>

            <nav class="hidden md:flex items-center gap-1">
                <a href="../public/index.php" class="px-3 py-2 rounded-lg text-sm font-medium <?= (htmlspecialchars($_GET['page'] ?? '') === '') ? 'bg-indigo-50 text-primary' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' ?> transition">
                    <i class="fas fa-home"></i> Accueil
                </a>
                <a href="../public/category.php?slug=cours" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="fas fa-book"></i> Cours</a>
                <a href="../public/category.php?slug=exercices" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="fas fa-pencil-alt"></i> Exercices</a>
                <a href="../public/category.php?slug=examens" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="fas fa-file-alt"></i> Examens</a>
                <a href="../public/resources.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="fas fa-folder-open"></i> Ressources</a>
                <a href="../public/search.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="fas fa-search"></i> Recherche</a>
            </nav>

            <div class="flex items-center gap-3">
                <form action="../public/search.php" method="get" class="hidden md:flex items-center bg-gray-100 rounded-lg px-3">
                    <button type="submit" class="text-gray-400"><i class="fas fa-search"></i></button>
                    <input type="text" name="q" placeholder="Rechercher..." class="bg-transparent border-none px-2 py-2 text-sm focus:outline-none w-28">
                </form>

                <?php if (is_admin()): ?>
                <a href="../admin/dashboard.php" class="hidden md:flex items-center gap-1 bg-primary text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                    <i class="fas fa-cog"></i> Admin
                </a>
                <?php endif; ?>

                <button class="md:hidden text-gray-600 p-2" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile nav -->
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav">
            <a href="../public/index.php" class="block px-3 py-2 rounded-lg text-sm font-medium <?= (htmlspecialchars($_GET['page'] ?? '') === '') ? 'bg-indigo-50 text-primary' : 'text-gray-600 hover:bg-gray-100' ?>"><i class="fas fa-home"></i> Accueil</a>
            <a href="../public/category.php?slug=cours" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-book"></i> Cours</a>
            <a href="../public/category.php?slug=exercices" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-pencil-alt"></i> Exercices</a>
            <a href="../public/category.php?slug=examens" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-file-alt"></i> Examens</a>
            <a href="../public/resources.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-folder-open"></i> Ressources</a>
            <a href="../public/search.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-search"></i> Recherche</a>
            <?php if (is_admin()): ?>
            <a href="../admin/dashboard.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white"><i class="fas fa-cog"></i> Admin</a>
            <?php endif; ?>
            <form action="../public/search.php" method="get" class="flex bg-gray-100 rounded-lg px-3 mt-2">
                <button type="submit" class="text-gray-400"><i class="fas fa-search"></i></button>
                <input type="text" name="q" placeholder="Rechercher..." class="bg-transparent border-none px-2 py-2 text-sm focus:outline-none w-full">
            </form>
        </div>
    </header>

    <?php if (hasFlash('success')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-check-circle"></i> <?= flash('success') ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasFlash('error')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= flash('error') ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="min-h-screen">

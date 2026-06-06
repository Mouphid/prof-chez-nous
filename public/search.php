<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Recherche - Joie Enseignante";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if (!empty($q)) {
    $search = "%$q%";
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON p.id_category = c.id_category
        WHERE p.status = 'published' AND (p.title LIKE ? OR p.content LIKE ?)
        ORDER BY p.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary"><i class="fas fa-graduation-cap"></i> Joie Enseignante</a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="fas fa-home"></i> Accueil</a>
                <a href="resources.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="fas fa-folder-open"></i> Ressources</a>
                <a href="search.php" class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="fas fa-search"></i> Recherche</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <a href="profile.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary rounded-t-lg"><i class="fas fa-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="fas fa-download w-5"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="fas fa-comments w-5"></i> Mes commentaires</a>
                        <hr class="border-gray-100">
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"><i class="fas fa-sign-out-alt w-5"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-primary hover:bg-indigo-50 transition"><i class="fas fa-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu"><i class="fas fa-bars text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-home"></i> Accueil</a>
            <a href="resources.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-folder-open"></i> Ressources</a>
            <a href="search.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="fas fa-search"></i> Recherche</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-comments"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white text-center"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 mb-6"><i class="fas fa-search text-primary"></i> Recherche</h1>

                <form method="get" class="mb-8">
                    <div class="flex gap-2">
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un article..." required class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </form>

                <?php if (!empty($q)): ?>
                    <p class="text-gray-500 mb-6"><?= count($results) ?> résultat(s) pour "<strong class="text-gray-900"><?= htmlspecialchars($q) ?></strong>"</p>

                    <?php if (count($results) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach ($results as $post): ?>
                        <article class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                            <h2 class="text-lg font-bold text-gray-900 mb-2">
                                <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary transition"><?= htmlspecialchars($post['title']) ?></a>
                            </h2>
                            <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-3">
                                <?php if ($post['category_name']): ?>
                                <span><i class="fas fa-folder text-primary"></i> <?= htmlspecialchars($post['category_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="fas fa-calendar text-primary"></i> <?= format_date($post['created_at']) ?></span>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4"><?= truncate(strip_tags($post['content']), 200) ?></p>
                            <a href="post.php?id=<?= $post['id_post'] ?>" class="inline-flex items-center gap-1 text-primary font-medium text-sm hover:underline">
                                <i class="fas fa-book-open"></i> Lire plus
                            </a>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                        <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Aucun résultat trouvé.</p>
                        <p class="text-gray-400">Essayez avec d'autres mots-clés.</p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="fas fa-folder text-primary mr-2"></i> Catégories</h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                                <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0"></span>
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="border-t border-gray-800 pt-6 text-center text-sm">
                &copy; <?= date('Y') ?> Joie Enseignante. Tous droits réservés.
            </div>
        </div>
    </footer>
</body>
</html>

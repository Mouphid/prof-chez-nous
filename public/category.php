<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$id_category = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id_category = ?");
    $stmt->execute([$id_category]);
}
$category = $stmt->fetch();

if (!$category) {
    header("Location: index.php");
    exit;
}

$page_title = htmlspecialchars($category['name']);

$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name,
           (SELECT COUNT(*) FROM likes WHERE id_post = p.id_post) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post AND status = 'visible') as comments_count
    FROM posts p
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.id_category = ? AND p.status = 'published'
    ORDER BY p.created_at DESC
");
$stmt->execute([$category['id_category']]);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Joie Enseignante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary"><i class="ph ph-graduation-cap"></i> Joie Enseignante</a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-house"></i> Accueil</a>
                <a href="category.php" class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="ph ph-folder"></i> Catégories</a>
                <a href="about.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-info"></i> À propos</a>
                <a href="biography.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-user-tie"></i> Biographie</a>
                <a href="contact.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-envelope"></i> Contact</a>
                <a href="search.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-magnifying-glass"></i> Recherche</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition">
                        <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="ph ph-caret-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <a href="profile.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary rounded-t-lg"><i class="ph ph-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="ph ph-download w-5"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="ph ph-chats w-5"></i> Mes commentaires</a>
                        <hr class="border-gray-100">
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-primary hover:bg-indigo-50 transition"><i class="ph ph-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"><i class="ph ph-sign-in"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-house"></i> Accueil</a>
            <a href="category.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="ph ph-folder"></i> Catégories</a>
            <a href="about.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-info"></i> À propos</a>
            <a href="biography.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-tie"></i> Biographie</a>
            <a href="contact.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-envelope"></i> Contact</a>
            <a href="search.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-magnifying-glass"></i> Recherche</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-chats"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="ph ph-sign-out"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white text-center"><i class="ph ph-sign-in"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-6">
                    <i class="ph ph-folder text-3xl text-primary"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= $page_title ?></h1>
                        <?php if ($category['description']): ?>
                        <p class="text-gray-500 mt-1"><?= nl2br(htmlspecialchars($category['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($posts) > 0): ?>
                <div class="space-y-6">
                    <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                        <div class="flex flex-col sm:flex-row">
                            <?php if (!empty($post['main_image'])): ?>
                            <div class="sm:w-48 h-48 sm:h-auto flex-shrink-0">
                                <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <?php endif; ?>
                            <div class="p-6 flex-1">
                                <h2 class="text-lg font-bold text-gray-900 mb-2">
                                    <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary transition"><?= htmlspecialchars($post['title']) ?></a>
                                </h2>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-3">
                                    <span><i class="ph ph-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                                    <span><i class="ph ph-calendar"></i> <?= format_date($post['created_at']) ?></span>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4"><?= truncate(strip_tags($post['content']), 250) ?></p>
                                <div class="flex items-center gap-4 text-sm">
                                    <a href="post.php?id=<?= $post['id_post'] ?>" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition inline-flex items-center gap-1">
                                        <i class="ph ph-book-open"></i> Lire plus
                                    </a>
                                    <span class="text-gray-400"><i class="ph ph-heart"></i> <?= $post['likes_count'] ?? 0 ?></span>
                                    <span class="text-gray-400"><i class="ph ph-chats"></i> <?= $post['comments_count'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                    <i class="ph ph-folder-open text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Aucun article dans cette catégorie.</p>
                    <a href="index.php" class="text-primary hover:underline mt-2 inline-block"><i class="ph ph-arrow-left"></i> Retour à l'accueil</a>
                </div>
                <?php endif; ?>
            </div>

            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="ph ph-folder text-primary mr-2"></i> Catégories</h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?= $cat['id_category'] == $category['id_category'] ? 'bg-indigo-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50 transition' ?>">
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

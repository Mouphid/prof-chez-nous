<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Accueil - Joie Enseignante";

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, u.name as author_name,
           (SELECT COUNT(*) FROM likes WHERE id_post = p.id_post) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post AND status = 'visible') as comments_count
    FROM posts p
    LEFT JOIN categories c ON p.id_category = c.id_category
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$posts = $stmt->fetchAll();

$total = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$total_pages = ceil($total / $per_page);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5',amber:'#F59E0B'}}}}</script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="bg-[#F8FAFC] font-sans text-gray-800 antialiased">
    <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary hover:text-indigo-700 transition"><i class="ph ph-graduation-cap"></i> Joie Enseignante</a>
            <nav class="hidden md:flex items-center gap-1" aria-label="Navigation principale">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary ring-1 ring-indigo-200"><i class="ph ph-house"></i> Accueil</a>
                <a href="about.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-info"></i> À propos</a>
                <a href="biography.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-user-tie"></i> Biographie</a>
                <a href="contact.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition"><i class="ph ph-envelope"></i> Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition shadow-sm" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="ph ph-caret-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50" role="menu">
                        <a href="profile.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary rounded-t-xl" role="menuitem"><i class="ph ph-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary" role="menuitem"><i class="ph ph-download w-5"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary" role="menuitem"><i class="ph ph-chats w-5"></i> Mes commentaires</a>
                        <hr class="border-gray-100">
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-xl" role="menuitem"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-primary hover:bg-indigo-50 transition"><i class="ph ph-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm"><i class="ph ph-sign-in"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2 hover:bg-gray-100 rounded-lg transition" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu" aria-expanded="false"><i class="ph ph-list text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="ph ph-house"></i> Accueil</a>
            <a href="about.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-info"></i> À propos</a>
            <a href="biography.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-tie"></i> Biographie</a>
            <a href="contact.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-envelope"></i> Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-chats"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="ph ph-sign-out"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white text-center"><i class="ph ph-sign-in"></i> Connexion</a>
            <a href="register.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-primary text-center"><i class="ph ph-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($page === 1): ?>
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-primary to-purple-600 text-white">
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 60px 60px;"></div>
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-purple-300/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-indigo-300/20 rounded-full blur-3xl"></div>
        <div class="max-w-7xl mx-auto px-4 py-20 sm:py-28 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm text-white text-sm font-medium px-4 py-1.5 rounded-full mb-6 border border-white/10">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span> Plateforme éducative dédiée aux enseignants
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">Bienvenue sur <span class="text-amber-300">Joie Enseignante</span></h1>
                <p class="text-lg sm:text-xl text-indigo-100 leading-relaxed mb-8 max-w-2xl mx-auto">Une plateforme collaborative pour partager, découvrir et échanger des ressources pédagogiques de qualité.</p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#articles" class="bg-white text-primary font-bold px-8 py-3.5 rounded-xl hover:bg-indigo-50 transition-all shadow-lg hover:shadow-xl hover:shadow-white/25 active:scale-[0.97] transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                        <i class="ph ph-book-open"></i> Découvrir les articles
                    </a>
                    <a href="register.php" class="bg-amber-400 text-amber-900 font-bold px-8 py-3.5 rounded-xl hover:bg-amber-300 transition-all shadow-lg hover:shadow-xl hover:shadow-amber-400/25 active:scale-[0.97] transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                        <i class="ph ph-user-plus"></i> Rejoindre la communauté
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-[#F8FAFC] to-transparent"></div>
    </section>
    <?php endif; ?>

    <main class="max-w-7xl mx-auto px-4 py-12" id="articles">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Derniers articles</h2>
                    <span class="text-xs text-gray-400"><?= $total ?> article<?= $total > 1 ? 's' : '' ?></span>
                </div>

                <?php if (count($posts) > 0): ?>
                    <div class="space-y-5">
                        <?php foreach ($posts as $i => $post): ?>
                        <article class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all">
                            <div class="flex flex-col sm:flex-row">
                                <?php if (!empty($post['main_image'])): ?>
                                <div class="sm:w-52 h-44 sm:h-auto overflow-hidden flex-shrink-0">
                                    <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
                                </div>
                                <?php endif; ?>
                                <div class="p-5 flex-1 flex flex-col">
                                    <div class="flex items-center gap-2.5 mb-2">
                                        <span class="text-xs font-medium text-primary"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                                        <span class="text-gray-300">·</span>
                                        <span class="text-xs text-gray-400"><?= format_date($post['created_at']) ?></span>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900 mb-1.5">
                                        <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary transition"><?= htmlspecialchars($post['title']) ?></a>
                                    </h3>
                                    <p class="text-sm text-gray-500 leading-relaxed mb-3 flex-1"><?= truncate(strip_tags($post['content']), 180) ?></p>
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-50">
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            <span><i class="ph ph-user mr-1"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                                            <span><i class="ph ph-heart mr-1"></i> <?= $post['likes_count'] ?? 0 ?></span>
                                            <span><i class="ph ph-chat mr-1"></i> <?= $post['comments_count'] ?? 0 ?></span>
                                        </div>
                                        <a href="post.php?id=<?= $post['id_post'] ?>" class="text-xs font-medium text-primary hover:text-indigo-700 transition inline-flex items-center gap-1">
                                            Lire <i class="ph ph-arrow-right text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav class="flex justify-center items-center gap-1.5 mt-8" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition"><i class="ph ph-caret-left mr-1"></i> Précédent</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm transition <?= $i === $page ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' ?>" aria-current="<?= $i === $page ? 'page' : 'false' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">Suivant <i class="ph ph-caret-right ml-1"></i></a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-24 bg-white rounded-2xl border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ph ph-book-open text-3xl text-gray-400"></i></div>
                    <p class="text-lg font-medium text-gray-600">Aucun article disponible pour le moment.</p>
                    <p class="text-sm text-gray-400 mt-1">Revenez bientôt pour découvrir les nouveautés !</p>
                </div>
                <?php endif; ?>
            </div>

            <aside class="w-full lg:w-72 space-y-5 flex-shrink-0" aria-label="Barre latérale">
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Catégories</h3>
                    <ul class="space-y-0.5">
                        <?php foreach ($categories as $cat):
                            $count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_category = ? AND status = 'published'");
                            $count->execute([$cat['id_category']]);
                            $total_cat = $count->fetchColumn();
                        ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center justify-between px-2 py-2 rounded-lg text-sm text-gray-600 hover:text-primary transition">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="text-xs text-gray-400"><?= $total_cat ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Récents</h3>
                    <ul class="space-y-1">
                        <?php
                        $recent = $pdo->query("SELECT id_post, title FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
                        while ($r = $recent->fetch()):
                        ?>
                        <li>
                            <a href="post.php?id=<?= $r['id_post'] ?>" class="text-sm text-gray-500 hover:text-primary transition block py-1.5"><?= htmlspecialchars(truncate($r['title'], 40)) ?></a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 mt-16 text-sm">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <h4 class="text-white font-semibold mb-3">Joie Enseignante</h4>
                <p class="leading-relaxed">Plateforme pédagogique collaborative pour enseignants et étudiants.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Navigation</h4>
                <ul class="space-y-2">
                    <li><a href="index.php" class="hover:text-white transition">Accueil</a></li>
                    <li><a href="about.php" class="hover:text-white transition">À propos</a></li>
                    <li><a href="biography.php" class="hover:text-white transition">Biographie</a></li>
                    <li><a href="contact.php" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Compte</h4>
                <ul class="space-y-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php" class="hover:text-white transition">Mon profil</a></li>
                    <li><a href="logout.php" class="hover:text-white transition">Déconnexion</a></li>
                    <?php else: ?>
                    <li><a href="login.php" class="hover:text-white transition">Connexion</a></li>
                    <li><a href="register.php" class="hover:text-white transition">Inscription</a></li>
                    <?php endif; ?>
                    <li><a href="../admin/login.php" class="hover:text-white transition">Administration</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Contact</h4>
                <ul class="space-y-2">
                    <li>contact@joieenseignante.com</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 py-6 text-center text-xs">
            &copy; <?= date('Y') ?> Joie Enseignante
        </div>
    </footer>
</body>
</html>

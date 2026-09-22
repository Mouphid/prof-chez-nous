<?php
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config_admin.php";

$admin = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

$can_view_users = has_permission('manage_users');
$can_view_categories = has_permission('manage_categories');
$can_view_posts = has_permission('manage_posts');
$can_view_comments = has_permission('manage_comments');
$can_publish = has_permission('publish_articles');
$admin_role_label = $admin_role === 'admin' ? 'Super Admin' : ($admin_role === 'auteur' ? 'Auteur' : 'Étudiant');

if ($_SESSION['admin_role'] === 'auteur') {
    $owner_cond = " WHERE id_user = " . (int)$_SESSION['admin_id'];

    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts$owner_cond"); $stats['posts'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts$owner_cond AND status='published'"); $stats['posts_published'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts$owner_cond AND status='draft'"); $stats['posts_draft'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts$owner_cond AND status='scheduled'"); $stats['posts_scheduled'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE id_post IN (SELECT id_post FROM posts$owner_cond)"); $stats['comments'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts$owner_cond"); $stats['views'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM likes WHERE id_post IN (SELECT id_post FROM posts$owner_cond)"); $stats['likes'] = $stmt->fetchColumn();
    $stats['users'] = 0; $stats['categories'] = 0; $stats['files'] = 0; $stats['comments_pending'] = 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM posts$owner_cond AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"); $stats['posts_month'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE id_post IN (SELECT id_post FROM posts$owner_cond) AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"); $stats['comments_month'] = $stmt->fetchColumn();
} else {
    $stats = [];
    $stats['posts'] = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $stats['posts_published'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
    $stats['posts_draft'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
    $stats['posts_scheduled'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='scheduled'")->fetchColumn();
    $stats['comments'] = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $stats['comments_pending'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending' OR status IS NULL")->fetchColumn();
    $stats['likes'] = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
    $stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['files'] = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
    $stats['views'] = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();

    $stats['posts_month'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats['users_month'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats['comments_month'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
}

$draft_posts = $pdo->query("SELECT p.*, u.name as author_name FROM posts p LEFT JOIN users u ON p.id_user=u.id_user WHERE p.status='draft' ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
$recent_comments = $pdo->query("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.id_post=p.id_post ORDER BY c.created_at DESC LIMIT 10")->fetchAll();
$popular_posts = $pdo->query("SELECT id_post, title, views, created_at FROM posts WHERE status='published' ORDER BY views DESC LIMIT 5")->fetchAll();
$latest_posts = $pdo->query("SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.id_category=c.id_category LEFT JOIN users u ON p.id_user=u.id_user ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
$now = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto z-40">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3">
                    <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-8 w-auto bg-white p-1 rounded">
                </a>
            </div>
            <nav class="p-4" aria-label="Menu admin">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium"><i class="ph ph-gauge w-5"></i> Dashboard</a></li>
                    <?php if ($can_view_posts): ?>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-newspaper w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-plus-circle w-5"></i> Nouveau post</a></li>
                    <?php endif; ?>
                    <?php if ($can_view_comments): ?>
                    <li><a href="manage_comments.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-chats w-5"></i> Commentaires</a></li>
                    <?php endif; ?>
                    <?php if ($can_view_categories): ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-folder w-5"></i> Catégories</a></li>
                    <?php endif; ?>
                    <?php if ($can_view_users): ?>
                    <li><a href="manage_users.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-users w-5"></i> Utilisateurs</a></li>
                    <?php endif; ?>
                    <li class="border-t border-gray-700 pt-3 mt-3 space-y-1">
                        <a href="../public/index.php" target="_blank" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-arrow-square-out w-5"></i> Voir le site</a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition text-sm"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1 ml-64" id="main-content">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
                <h1 class="text-xl font-bold text-gray-900"><i class="ph ph-gauge text-primary mr-2"></i> Tableau de bord</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></span>
                    <span class="bg-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?= $admin_role_label ?></span>
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                        <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?>
                    </div>
                </div>
            </header>

            <div class="p-8">
                <!-- Welcome -->
                <div class="bg-gradient-to-r from-primary to-indigo-400 rounded-2xl p-6 sm:p-8 text-white mb-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Bonjour, <?= htmlspecialchars($admin['name'] ?? 'Admin') ?> 👋</h2>
                            <p class="text-indigo-100 mt-1">Voici un aperçu de votre plateforme au <?= date('d/m/Y') ?></p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2 bg-white/10 rounded-xl px-4 py-2">
                            <i class="ph ph-clock"></i>
                            <span class="text-sm"><?= date('H:i') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                    <?php if ($can_view_posts): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 bg-indigo-100 text-primary rounded-xl flex items-center justify-center"><i class="ph ph-newspaper text-xl"></i></div>
                            <?php if (isset($stats['posts_month']) && $stats['posts_month'] > 0): ?>
                            <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full font-medium flex items-center gap-1"><i class="ph ph-trend-up"></i> +<?= $stats['posts_month'] ?> ce mois</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500">Total articles</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['posts'] ?></p>
                        <div class="mt-2 flex gap-3 text-xs text-gray-400">
                            <span class="text-emerald-600"><?= $stats['posts_published'] ?> publiés</span>
                            <span class="text-amber-600"><?= $stats['posts_draft'] ?> brouillons</span>
                            <?php if (isset($stats['posts_scheduled']) && $stats['posts_scheduled'] > 0): ?>
                            <span class="text-blue-600"><?= $stats['posts_scheduled'] ?> programmés</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($can_view_comments): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center"><i class="ph ph-chats text-xl"></i></div>
                            <?php if (isset($stats['comments_month']) && $stats['comments_month'] > 0): ?>
                            <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full font-medium flex items-center gap-1"><i class="ph ph-trend-up"></i> +<?= $stats['comments_month'] ?> ce mois</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500">Commentaires</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['comments'] ?></p>
                        <?php if (isset($stats['comments_pending']) && $stats['comments_pending'] > 0): ?>
                        <div class="mt-2 text-xs text-amber-600"><?= $stats['comments_pending'] ?> en attente</div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($admin_role === 'admin'): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center"><i class="ph ph-users text-xl"></i></div>
                            <?php if (isset($stats['users_month']) && $stats['users_month'] > 0): ?>
                            <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full font-medium flex items-center gap-1"><i class="ph ph-trend-up"></i> +<?= $stats['users_month'] ?> ce mois</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500">Utilisateurs</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['users'] ?></p>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center"><i class="ph ph-eye text-xl"></i></div>
                        </div>
                        <p class="text-sm text-gray-500">Vues totales</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['views']) ?></p>
                        <div class="mt-2 flex gap-3 text-xs text-gray-400">
                            <span class="text-red-500"><i class="ph ph-heart"></i> <?= $stats['likes'] ?> likes</span>
                            <span><i class="ph ph-folder"></i> <?= $stats['categories'] ?> catégories</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-11 h-11 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center"><i class="ph ph-file text-xl"></i></div>
                        </div>
                        <p class="text-sm text-gray-500">Fichiers</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $stats['files'] ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Quick actions -->
                <div class="flex gap-3 mb-8 flex-wrap">
                    <?php if ($can_publish): ?>
                    <a href="add_post.php" class="flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition shadow-sm"><i class="ph ph-plus-circle"></i> Nouvel article</a>
                    <?php endif; ?>
                    <?php if ($can_view_comments): ?>
                    <a href="manage_comments.php" class="flex items-center gap-2 bg-white border border-gray-200 px-5 py-2.5 rounded-lg font-medium text-gray-700 hover:border-primary hover:text-primary transition shadow-sm"><i class="ph ph-chats"></i> Commentaires</a>
                    <?php endif; ?>
                    <?php if ($can_view_users): ?>
                    <a href="manage_users.php" class="flex items-center gap-2 bg-white border border-gray-200 px-5 py-2.5 rounded-lg font-medium text-gray-700 hover:border-primary hover:text-primary transition shadow-sm"><i class="ph ph-user-plus"></i> Utilisateurs</a>
                    <?php endif; ?>
                    <a href="../public/index.php" target="_blank" class="flex items-center gap-2 bg-white border border-gray-200 px-5 py-2.5 rounded-lg font-medium text-gray-700 hover:border-primary hover:text-primary transition shadow-sm"><i class="ph ph-arrow-square-out"></i> Voir le site</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Latest posts -->
                    <?php if (count($latest_posts) > 0 && $can_view_posts): ?>
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-base font-bold text-gray-900"><i class="ph ph-clock text-primary mr-2"></i> Derniers articles</h2>
                            <a href="manage_posts.php" class="text-xs text-primary hover:underline font-medium">Voir tout</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-6 py-3">Titre</th>
                                        <th class="px-6 py-3">Statut</th>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php foreach ($latest_posts as $post): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3.5">
                                            <a href="../public/post.php?id=<?= $post['id_post'] ?>" target="_blank" class="text-sm font-medium text-gray-900 hover:text-primary"><?= htmlspecialchars(truncate($post['title'], 45)) ?></a>
                                            <p class="text-xs text-gray-400 mt-0.5"><?= $post['author_name'] ?? 'Admin' ?> <?= $post['category_name'] ? '· ' . $post['category_name'] : '' ?></p>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $post['status']==='published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>"><?= $post['status'] ?></span>
                                        </td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500"><?= format_date($post['created_at']) ?></td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="edit_post.php?id=<?= $post['id_post'] ?>" class="text-gray-400 hover:text-primary transition mx-1"><i class="ph ph-pencil"></i></a>
                                            <?php if (has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id']): ?>
                                            <a href="delete_post.php?id=<?= $post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="text-gray-400 hover:text-red-500 transition mx-1" onclick="return confirm('Supprimer cet article ?')"><i class="ph ph-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Right sidebar -->
                    <div class="space-y-6">
                        <!-- Popular posts -->
                        <?php if (count($popular_posts) > 0 && $can_view_posts): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-bold text-gray-900"><i class="ph ph-trend-up text-primary mr-2"></i> Articles populaires</h2>
                            </div>
                            <div class="p-4 space-y-2">
                                <?php foreach ($popular_posts as $i => $pp): ?>
                                <div class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                                    <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-xs font-bold flex items-center justify-center flex-shrink-0"><?= $i + 1 ?></span>
                                    <div class="min-w-0 flex-1">
                                        <a href="../public/post.php?id=<?= $pp['id_post'] ?>" target="_blank" class="text-sm font-medium text-gray-800 hover:text-primary block truncate"><?= htmlspecialchars(truncate($pp['title'], 35)) ?></a>
                                        <span class="text-xs text-gray-400"><i class="ph ph-eye"></i> <?= number_format($pp['views']) ?> vues</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        $scheduled_posts = $pdo->query("SELECT p.*, u.name as author_name FROM posts p LEFT JOIN users u ON p.id_user=u.id_user WHERE p.status='scheduled' ORDER BY p.published_at ASC LIMIT 10")->fetchAll();
                        ?>
                        <?php if (count($scheduled_posts) > 0 && $can_view_posts): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-bold text-gray-900"><i class="ph ph-clock text-blue-500 mr-2"></i> Programmés</h2>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <?php foreach ($scheduled_posts as $sp): ?>
                                <a href="edit_post.php?id=<?= $sp['id_post'] ?>" class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition">
                                    <span class="text-sm text-gray-700 truncate max-w-[200px]"><?= htmlspecialchars(truncate($sp['title'], 30)) ?></span>
                                    <span class="text-xs text-blue-500"><?= date('d/m H:i', strtotime($sp['published_at'])) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Drafts -->
                        <?php if (count($draft_posts) > 0 && $can_view_posts): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-base font-bold text-gray-900"><i class="ph ph-pencil text-amber-500 mr-2"></i> Brouillons</h2>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <?php foreach ($draft_posts as $dp): ?>
                                <a href="edit_post.php?id=<?= $dp['id_post'] ?>" class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition">
                                    <span class="text-sm text-gray-700 truncate max-w-[200px]"><?= htmlspecialchars(truncate($dp['title'], 30)) ?></span>
                                    <span class="text-xs text-gray-400"><?= format_date($dp['created_at']) ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent comments -->
                <?php if (count($recent_comments) > 0 && $can_view_comments): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900"><i class="ph ph-chats text-primary mr-2"></i> Commentaires récents</h2>
                        <a href="manage_comments.php" class="text-xs text-primary hover:underline font-medium">Voir tout</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-3">Article</th>
                                    <th class="px-6 py-3">Auteur</th>
                                    <th class="px-6 py-3">Commentaire</th>
                                    <th class="px-6 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($recent_comments as $c): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-3.5 text-sm text-gray-700"><?= htmlspecialchars(truncate($c['post_title'] ?? '-', 30)) ?></td>
                                    <td class="px-6 py-3.5 text-sm text-gray-700"><?= htmlspecialchars($c['author_name'] ?? 'Visiteur') ?></td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500 max-w-xs truncate"><?= htmlspecialchars(truncate($c['content'], 60)) ?></td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500 whitespace-nowrap"><?= format_date($c['created_at']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

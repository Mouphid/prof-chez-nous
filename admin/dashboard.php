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

if($_SESSION['admin_role'] === 'auteur'){
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_user = ?");
    $stmt->execute([$_SESSION['admin_id']]); $stats['posts'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE id_post IN (SELECT id_post FROM posts WHERE id_user = ?)");
    $stmt->execute([$_SESSION['admin_id']]); $stats['comments'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(views),0) FROM posts WHERE id_user = ?");
    $stmt->execute([$_SESSION['admin_id']]); $stats['views'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_user = ? AND status = 'published'");
    $stmt->execute([$_SESSION['admin_id']]); $stats['posts_published'] = $stmt->fetchColumn();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_user = ? AND status = 'draft'");
    $stmt->execute([$_SESSION['admin_id']]); $stats['posts_draft'] = $stmt->fetchColumn();
} else {
    $stats['posts'] = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $stats['posts_published'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
    $stats['posts_draft'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
    $stats['comments'] = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $stats['comments_pending'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending' OR status IS NULL")->fetchColumn();
    $stats['likes'] = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
    $stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['files'] = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
    $stats['views'] = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();
}

$draft_posts = $pdo->query("SELECT p.*, u.name as author_name FROM posts p LEFT JOIN users u ON p.id_user = u.id_user WHERE p.status = 'draft' ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
$recent_comments = $pdo->query("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.id_post = p.id_post ORDER BY c.created_at DESC LIMIT 10")->fetchAll();
$popular_posts = $pdo->query("SELECT id_post, title, views, created_at FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 5")->fetchAll();
$latest_posts = $pdo->query("SELECT p.*, c.name as category_name, u.name as author_name FROM posts p LEFT JOIN categories c ON p.id_category = c.id_category LEFT JOIN users u ON p.id_user = u.id_user ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3 text-xl font-extrabold">
                    <i class="fas fa-graduation-cap text-indigo-400"></i> JoieEnseignante
                </a>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-700 text-white"><i class="fas fa-home w-5"></i> Dashboard</a></li>
                    <?php if($can_view_posts): ?>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-file-alt w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-plus w-5"></i> Nouveau post</a></li>
                    <?php endif; ?>
                    <?php if($can_view_comments): ?>
                    <li><a href="manage_comments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-comments w-5"></i> Commentaires</a></li>
                    <?php endif; ?>
                    <?php if($can_view_categories): ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-folder w-5"></i> Catégories</a></li>
                    <?php endif; ?>
                    <?php if($can_view_users): ?>
                    <li><a href="manage_users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-users w-5"></i> Utilisateurs</a></li>
                    <?php endif; ?>
                    <li class="border-t border-gray-700 pt-3 mt-3">
                        <a href="../public/index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-external-link-alt w-5"></i> Voir le site</a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-sign-out-alt w-5"></i> Déconnexion</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-1 ml-64 p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-tachometer-alt text-primary mr-2"></i> Tableau de bord</h1>
                <div class="flex items-center gap-3">
                    <span class="text-gray-600"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></span>
                    <span class="bg-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?= $admin_role_label ?></span>
                </div>
            </div>

            <?php if($can_view_users): ?>
            <!-- Permission table -->
            <div class="bg-indigo-50 rounded-xl p-5 mb-6">
                <h3 class="font-semibold text-indigo-900 mb-3"><i class="fas fa-shield-alt"></i> Vos permissions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th class="p-2 text-left">Permission</th>
                                <th class="p-2 text-center">Admin</th>
                                <th class="p-2 text-center">Auteur</th>
                                <th class="p-2 text-center">Étudiant</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php
                            $rows = [
                                ['Accéder au panel admin', '✅', '✅', '❌'],
                                ['Publier des articles', '✅', '✅', '❌'],
                                ['Gérer les utilisateurs', '✅', '❌', '❌'],
                                ['Attribuer des rôles', '✅', '❌', '❌'],
                                ['Gérer les catégories', '✅', '❌', '❌'],
                                ['Modérer les commentaires', '✅', '✅', '❌'],
                                ['Télécharger les ressources', '✅', '✅', '✅'],
                                ['Commenter les articles', '✅', '✅', '✅'],
                            ];
                            foreach($rows as $r): ?>
                            <tr class="border-b border-gray-100">
                                <td class="p-2 font-medium"><?= $r[0] ?></td>
                                <td class="p-2 text-center"><?= $r[1] ?></td>
                                <td class="p-2 text-center"><?= $r[2] ?></td>
                                <td class="p-2 text-center"><?= $r[3] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick actions -->
            <div class="flex gap-3 mb-8 flex-wrap">
                <?php if($can_publish): ?>
                <a href="add_post.php" class="flex items-center gap-2 bg-white px-5 py-3 rounded-lg shadow-sm hover:shadow-md transition font-medium text-gray-800"><i class="fas fa-plus text-primary"></i> Nouveau post</a>
                <?php endif; ?>
                <?php if($can_view_comments): ?>
                <a href="manage_comments.php" class="flex items-center gap-2 bg-white px-5 py-3 rounded-lg shadow-sm hover:shadow-md transition font-medium text-gray-800"><i class="fas fa-comments text-primary"></i> Commentaires</a>
                <?php endif; ?>
                <?php if($can_view_users): ?>
                <a href="manage_users.php" class="flex items-center gap-2 bg-white px-5 py-3 rounded-lg shadow-sm hover:shadow-md transition font-medium text-gray-800"><i class="fas fa-user-plus text-primary"></i> Utilisateurs</a>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <?php if($can_view_posts): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-file-alt text-indigo-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['posts'] ?></div><div class="text-sm text-gray-500">Articles</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-check-circle text-green-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['posts_published'] ?></div><div class="text-sm text-gray-500">Publiés</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-edit text-yellow-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['posts_draft'] ?></div><div class="text-sm text-gray-500">Brouillons</div></div>
                <?php endif; ?>
                <?php if($can_view_comments): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-comments text-blue-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['comments'] ?></div><div class="text-sm text-gray-500">Commentaires</div></div>
                <?php endif; ?>
                <?php if($admin_role === 'admin'): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-heart text-red-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['likes'] ?></div><div class="text-sm text-gray-500">Likes</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-eye text-gray-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= number_format($stats['views']) ?></div><div class="text-sm text-gray-500">Vues</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm"><i class="fas fa-users text-purple-400 text-xl mb-2 block"></i><div class="text-3xl font-extrabold text-gray-800"><?= $stats['users'] ?></div><div class="text-sm text-gray-500">Utilisateurs</div></div>
                <?php endif; ?>
            </div>

            <!-- Latest posts -->
            <?php if(count($latest_posts) > 0 && $can_view_posts): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
                <h2 class="text-lg font-semibold p-5 border-b border-gray-100"><i class="fas fa-clock text-primary mr-2"></i> Derniers articles</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="p-3">Titre</th><th class="p-3">Catégorie</th><th class="p-3">Statut</th><th class="p-3">Date</th><th class="p-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($latest_posts as $post): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-3"><a href="../public/post.php?id=<?= $post['id_post'] ?>" target="_blank" class="text-primary hover:underline"><?= htmlspecialchars(truncate($post['title'], 40)) ?></a></td>
                                <td class="p-3 text-gray-600"><?= $post['category_name'] ?? '-' ?></td>
                                <td class="p-3"><span class="text-xs font-semibold px-2 py-1 rounded-full <?= $post['status']==='published'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' ?>"><?= $post['status'] ?></span></td>
                                <td class="p-3 text-gray-500 text-sm"><?= format_date($post['created_at']) ?></td>
                                <td class="p-3">
                                    <a href="edit_post.php?id=<?= $post['id_post'] ?>" class="text-indigo-600 hover:text-indigo-800 mr-2"><i class="fas fa-edit"></i></a>
                                    <?php if(has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id']): ?>
                                    <a href="delete_post.php?id=<?= $post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Supprimer?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent comments -->
            <?php if(count($recent_comments) > 0 && $can_view_comments): ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <h2 class="text-lg font-semibold p-5 border-b border-gray-100"><i class="fas fa-comments text-primary mr-2"></i> Commentaires récents</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="p-3">Article</th><th class="p-3">Commentaire</th><th class="p-3">Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_comments as $c): ?>
                            <tr class="border-b border-gray-50">
                                <td class="p-3 text-gray-600"><?= $c['post_title'] ?? '-' ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars(truncate($c['content'], 60)) ?></td>
                                <td class="p-3 text-gray-500 text-sm"><?= format_date($c['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

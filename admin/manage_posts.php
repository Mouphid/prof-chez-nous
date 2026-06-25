<?php
require_once "config_admin.php";

if(!has_permission('manage_posts')){
    header("Location: dashboard.php");
    exit;
}

if($_SESSION['admin_role'] === 'auteur'){
    $stmt = $pdo->prepare("SELECT p.*, u.name AS author_name, c.name AS category_name
                           FROM posts p
                           LEFT JOIN users u ON p.id_user = u.id_user
                           LEFT JOIN categories c ON p.id_category = c.id_category
                           WHERE p.id_user = ?
                           ORDER BY p.created_at DESC");
    $stmt->execute([$_SESSION['admin_id']]);
    $posts = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT p.*, u.name AS author_name, c.name AS category_name
                         FROM posts p
                         LEFT JOIN users u ON p.id_user = u.id_user
                         LEFT JOIN categories c ON p.id_category = c.id_category
                         ORDER BY p.created_at DESC");
    $posts = $stmt->fetchAll();
}

$can_view_comments = has_permission('manage_comments');
$can_view_categories = has_permission('manage_categories');
$can_view_users = has_permission('manage_users');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Articles - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3 text-xl font-extrabold">
                    <i class="ph ph-graduation-cap text-indigo-400"></i> JoieEnseignante
                </a>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-house w-5"></i> Dashboard</a></li>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-700 text-white"><i class="ph ph-file-alt w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-plus w-5"></i> Nouveau post</a></li>
                    <?php if($can_view_comments): ?>
                    <li><a href="manage_comments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-chats w-5"></i> Commentaires</a></li>
                    <?php endif; ?>
                    <?php if($can_view_categories): ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-folder w-5"></i> Catégories</a></li>
                    <?php endif; ?>
                    <?php if($can_view_users): ?>
                    <li><a href="manage_users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-users w-5"></i> Utilisateurs</a></li>
                    <?php endif; ?>
                    <li class="border-t border-gray-700 pt-3 mt-3">
                        <a href="../public/index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-arrow-square-out w-5"></i> Voir le site</a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 ml-64 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800"><i class="ph ph-newspaper text-primary mr-2"></i> Gestion des Articles</h1>
                <?php if(has_permission('publish_articles')): ?>
                <a href="add_post.php" class="bg-primary hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg transition font-semibold flex items-center gap-2"><i class="ph ph-plus"></i> Ajouter un article</a>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="p-3">Titre</th><th class="p-3">Auteur</th><th class="p-3">Catégorie</th><th class="p-3">Statut</th><th class="p-3">Date</th><th class="p-3">Vues</th><th class="p-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($posts as $post): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($post['title']) ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></td>
                                <td class="p-3">
                                    <?php
                                    $badge = match($post['status']){
                                        'published' => 'bg-green-100 text-green-700',
                                        'draft' => 'bg-yellow-100 text-yellow-700',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                    $label = match($post['status']){
                                        'published' => 'Publié',
                                        'draft' => 'Brouillon',
                                        default => 'Archivé'
                                    };
                                    ?>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $badge ?>"><?= $label ?></span>
                                </td>
                                <td class="p-3 text-gray-500 text-sm"><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                                <td class="p-3 text-gray-600"><?= $post['views'] ?? 0 ?></td>
                                <td class="p-3">
                                    <?php $can_edit = $_SESSION['admin_role'] === 'admin' || $post['id_user'] == $_SESSION['admin_id']; ?>
                                    <?php if($can_edit): ?>
                                    <a href="edit_post.php?id=<?= $post['id_post'] ?>" class="text-emerald-600 hover:text-emerald-800 mr-2"><i class="ph ph-pencil"></i></a>
                                    <?php if(has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id']): ?>
                                    <a href="delete_post.php?id=<?= $post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Supprimer cet article ?');"><i class="ph ph-trash"></i></a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($posts) === 0): ?>
                            <tr><td colspan="7" class="p-10 text-center text-gray-400">Aucun article trouvé.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

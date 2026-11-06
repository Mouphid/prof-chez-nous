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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Articles';
            $page_icon = 'ph-newspaper';
            $page_action = has_permission('publish_articles')
                ? '<a href="add_post.php" class="bg-primary hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition font-semibold flex items-center gap-2 text-sm"><i class="ph ph-plus"></i> Ajouter un article</a>'
                : '';
            include "_topbar.php";
            ?>
            <div class="p-8">

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="px-4 py-3">Titre</th><th class="px-4 py-3">Auteur</th><th class="px-4 py-3">Catégorie</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Vues</th><th class="px-4 py-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($posts as $post): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-3 font-medium text-gray-800"><?= htmlspecialchars($post['title']) ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                    $badge = match($post['status']){
                                        'published' => 'bg-green-100 text-green-700',
                                        'draft' => 'bg-yellow-100 text-yellow-700',
                                        'scheduled' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                    $label = match($post['status']){
                                        'published' => 'Publié',
                                        'draft' => 'Brouillon',
                                        'scheduled' => 'Programmé',
                                        default => 'Archivé'
                                    };
                                    ?>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $badge ?>"><?= $label ?></span>
                                </td>
                                <td class="p-3 text-gray-500 text-sm"><?= $post['status'] === 'scheduled' && !empty($post['published_at']) ? date('d/m/Y H:i', strtotime($post['published_at'])) : date('d/m/Y', strtotime($post['created_at'])) ?></td>
                                <td class="p-3 text-gray-600"><?= $post['views'] ?? 0 ?></td>
                                <td class="px-4 py-3">
                                    <?php $can_edit = $_SESSION['admin_role'] === 'admin' || $post['id_user'] == $_SESSION['admin_id']; ?>
                                    <?php if($can_edit): ?>
                                    <a href="edit_post.php?id=<?= $post['id_post'] ?>" class="text-emerald-600 hover:text-emerald-800 mr-2" aria-label="Modifier"><i class="ph ph-pencil"></i></a>
                                    <?php if(has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id']): ?>
                                    <a href="delete_post.php?id=<?= $post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Supprimer cet article ?');" aria-label="Supprimer"><i class="ph ph-trash"></i></a>
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
            </div>
        </main>
    </div>
</body>
</html>

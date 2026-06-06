<?php
require_once "config_admin.php";

if(!has_permission('manage_categories')){
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if(isset($_POST['add'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        if(empty($name)){
            $error = "Le nom de la catégorie est requis.";
        } else {
            $slug = generateSlug($name);
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $description]);
            $success = "Catégorie ajoutée avec succès !";
        }
    }
}

if(isset($_POST['edit'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $id = (int)$_POST['id_category'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        if(empty($name)){
            $error = "Le nom de la catégorie est requis.";
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id_category=?");
            $stmt->execute([$name, $description, $id]);
            $success = "Catégorie mise à jour avec succès !";
        }
    }
}

if(isset($_GET['delete'])){
    if(!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $id = (int)$_GET['delete'];
        $pdo->prepare("DELETE FROM categories WHERE id_category=?")->execute([$id]);
        $success = "Catégorie supprimée avec succès !";
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$can_view_posts = has_permission('manage_posts');
$can_view_comments = has_permission('manage_comments');
$can_view_users = has_permission('manage_users');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Catégories - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3 text-xl font-extrabold"><i class="fas fa-graduation-cap text-indigo-400"></i> JoieEnseignante</a>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-home w-5"></i> Dashboard</a></li>
                    <?php if($can_view_posts): ?>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-file-alt w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-plus w-5"></i> Nouveau post</a></li>
                    <?php endif; ?>
                    <?php if($can_view_comments): ?>
                    <li><a href="manage_comments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-comments w-5"></i> Commentaires</a></li>
                    <?php endif; ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-700 text-white"><i class="fas fa-folder w-5"></i> Catégories</a></li>
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

        <main class="flex-1 ml-64 p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-8"><i class="fas fa-folder text-primary mr-2"></i> Gestion des Catégories</h1>

            <?php if($error): ?><div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
            <?php if($success): ?><div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>

            <!-- Add form -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-plus-circle text-primary mr-2"></i> Ajouter une catégorie</h2>
                <form method="post" class="flex gap-4 items-end">
                    <?= csrf_field() ?>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nom</label>
                        <input type="text" name="name" placeholder="Ex: Littérature" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" placeholder="Description (facultatif)" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>
                    <button type="submit" name="add" class="bg-primary hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg transition font-semibold flex items-center gap-2"><i class="fas fa-plus"></i> Ajouter</button>
                </form>
            </div>

            <!-- Categories list -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <h2 class="text-lg font-semibold p-5 border-b border-gray-100"><i class="fas fa-list mr-2"></i> Catégories existantes</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="p-3">Nom</th><th class="p-3">Description</th><th class="p-3">Articles</th><th class="p-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat):
                                $post_count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_category = ?");
                                $post_count->execute([$cat['id_category']]);
                                $count = $post_count->fetchColumn();
                            ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-3 font-medium"><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                                <td class="p-3"><span class="bg-indigo-50 text-primary text-xs font-semibold px-2 py-1 rounded-full"><?= $count ?></span></td>
                                <td class="p-3">
                                    <form method="post" class="flex gap-2 items-center">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_category" value="<?= $cat['id_category'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-32" required>
                                        <input type="text" name="description" value="<?= htmlspecialchars($cat['description'] ?? '') ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-40">
                                        <button type="submit" name="edit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded text-sm transition"><i class="fas fa-save"></i></button>
                                    </form>
                                    <a href="?delete=<?= $cat['id_category'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800 ml-2" onclick="return confirm('Supprimer cette catégorie ?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(count($categories) === 0): ?>
                            <tr><td colspan="4" class="p-10 text-center text-gray-400">Aucune catégorie trouvée.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

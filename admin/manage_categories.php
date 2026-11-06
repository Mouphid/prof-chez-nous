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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Catégories'; $page_icon = 'ph-folder'; include "_topbar.php"; ?>
            <div class="p-8">

            <?php if($error): ?><div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if($success): ?><div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

            <!-- Add form -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4"><i class="ph ph-plus-circle text-primary mr-2"></i> Ajouter une catégorie</h2>
                <form method="post" class="flex gap-4 items-end">
                    <?= csrf_field() ?>
                    <div class="flex-1">
                        <label for="cat_name" class="block text-sm font-semibold text-gray-700 mb-1">Nom</label>
                        <input type="text" id="cat_name" name="name" placeholder="Ex: Littérature" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>
                    <div class="flex-1">
                        <label for="cat_desc" class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <input type="text" id="cat_desc" name="description" placeholder="Description (facultatif)" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>
                    <button type="submit" name="add" class="bg-primary hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg transition font-semibold flex items-center gap-2"><i class="ph ph-plus"></i> Ajouter</button>
                </form>
            </div>

            <!-- Categories list -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <h2 class="text-lg font-semibold p-5 border-b border-gray-100"><i class="ph ph-list-bullets mr-2"></i> Catégories existantes</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="px-4 py-3">Nom</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">Articles</th><th class="px-4 py-3">Actions</th></tr>
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
                                <td class="px-4 py-3"><span class="bg-indigo-50 text-primary text-xs font-semibold px-2 py-1 rounded-full"><?= $count ?></span></td>
                                <td class="px-4 py-3">
                                    <form method="post" class="flex gap-2 items-center">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_category" value="<?= $cat['id_category'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-32" required>
                                        <input type="text" name="description" value="<?= htmlspecialchars($cat['description'] ?? '') ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-40">
                                        <button type="submit" name="edit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded text-sm transition" aria-label="Enregistrer"><i class="ph ph-floppy-disk"></i></button>
                                    </form>
                                    <a href="?delete=<?= $cat['id_category'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800 ml-2" onclick="return confirm('Supprimer cette catégorie ?');" aria-label="Supprimer"><i class="ph ph-trash"></i></a>
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
            </div>
        </main>
    </div>
</body>
</html>

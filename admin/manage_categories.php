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
            admin_activity('create', 'category', (int)$pdo->lastInsertId(), $name);
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
            admin_activity('update', 'category', (int)$id, $name);
        }
    }
}

if(isset($_GET['delete'])){
    if(!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $id = (int)$_GET['delete'];
        $nom_categorie = $pdo->prepare("SELECT name FROM categories WHERE id_category = ?");
        $nom_categorie->execute([$id]);
        $nom_categorie = (string)$nom_categorie->fetchColumn();

        $pdo->prepare("DELETE FROM categories WHERE id_category=?")->execute([$id]);
        $success = "Catégorie supprimée avec succès !";
        admin_activity('delete', 'category', (int)$id, $nom_categorie);
    }
}

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id_post) AS post_count
    FROM categories c
    LEFT JOIN posts p ON p.id_category = c.id_category
    GROUP BY c.id_category
    ORDER BY c.name ASC
")->fetchAll();

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
            <?php
            $page_title = 'Catégories';
            $page_subtitle = 'Organisez les rubriques qui structurent le site';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if($error): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars($success) ?></span></div>
            <?php endif; ?>

            <!-- Ajout rapide -->
            <div class="adm-card mb-5">
                <div class="adm-card-head">
                    <div>
                        <h2 class="adm-card-title">Ajouter une catégorie</h2>
                        <p class="adm-card-sub">Le nom apparaîtra dans les filtres et sous les articles.</p>
                    </div>
                </div>
                <div class="adm-card-body">
                    <form method="post" class="flex flex-wrap items-end gap-3">
                        <?= csrf_field() ?>
                        <div class="flex-1 min-w-0" style="min-width:12rem">
                            <label for="cat_name" class="adm-label">Nom</label>
                            <input type="text" id="cat_name" name="name" placeholder="Ex : Littérature" required class="adm-input">
                        </div>
                        <div class="flex-1 min-w-0" style="min-width:14rem">
                            <label for="cat_desc" class="adm-label">Description <span class="text-gray-400 font-normal">(facultatif)</span></label>
                            <input type="text" id="cat_desc" name="description" placeholder="En quelques mots…" class="adm-input">
                        </div>
                        <button type="submit" name="add" class="adm-btn adm-btn-primary adm-btn-match">
                            <i class="ph ph-plus"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste -->
            <div class="adm-card overflow-hidden">
                <div class="adm-card-head">
                    <div>
                        <h2 class="adm-card-title">Catégories existantes</h2>
                        <p class="adm-card-sub"><?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?></p>
                    </div>
                </div>

                <?php if (!$categories): ?>
                <div class="adm-empty">
                    <i class="ph ph-folder-simple"></i>
                    <p class="adm-empty-title">Aucune catégorie pour le moment</p>
                    <p class="adm-empty-note">Créez une première rubrique avec le formulaire ci-dessus.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Articles</th>
                                <th class="adm-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td class="font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="text-gray-600" style="max-width:32rem">
                                    <span class="adm-clamp-1" title="<?= htmlspecialchars($cat['description'] ?? '') ?>"><?= htmlspecialchars($cat['description'] ?: '—') ?></span>
                                </td>
                                <td>
                                    <span class="adm-badge adm-badge-gray"><?= (int)$cat['post_count'] ?> article<?= (int)$cat['post_count'] > 1 ? 's' : '' ?></span>
                                </td>
                                <td class="adm-col-actions">
                                    <button type="button" class="adm-action adm-action-primary" title="Modifier"
                                            aria-label="Modifier <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                            onclick="document.getElementById('edit-cat-<?= (int)$cat['id_category'] ?>').showModal()">
                                        <i class="ph ph-pencil"></i>
                                    </button>
                                    <a href="?delete=<?= (int)$cat['id_category'] ?>&csrf_token=<?= csrf_token() ?>"
                                       class="adm-action adm-action-danger" title="Supprimer"
                                       aria-label="Supprimer <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                       onclick="return confirm('Supprimer cette catégorie ? Les articles concernés resteront en ligne.');">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            </div>
        </main>
    </div>

    <?php foreach($categories as $cat): ?>
    <dialog class="adm-dialog" id="edit-cat-<?= (int)$cat['id_category'] ?>" aria-labelledby="edit-cat-title-<?= (int)$cat['id_category'] ?>">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_category" value="<?= (int)$cat['id_category'] ?>">
            <div class="adm-dialog-head">
                <h2 class="adm-dialog-title" id="edit-cat-title-<?= (int)$cat['id_category'] ?>">Modifier la catégorie</h2>
                <button type="button" class="adm-dialog-close" onclick="this.closest('dialog').close()" aria-label="Fermer">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div class="adm-dialog-body">
                <div class="mb-4">
                    <label for="edit-name-<?= (int)$cat['id_category'] ?>" class="adm-label">Nom</label>
                    <input type="text" id="edit-name-<?= (int)$cat['id_category'] ?>" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required class="adm-input">
                </div>
                <div>
                    <label for="edit-desc-<?= (int)$cat['id_category'] ?>" class="adm-label">Description</label>
                    <textarea id="edit-desc-<?= (int)$cat['id_category'] ?>" name="description" rows="3" class="adm-textarea"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="adm-dialog-foot">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="this.closest('dialog').close()">Annuler</button>
                <button type="submit" name="edit" class="adm-btn adm-btn-primary"><i class="ph ph-floppy-disk"></i> Enregistrer</button>
            </div>
        </form>
    </dialog>
    <?php endforeach; ?>
</body>
</html>

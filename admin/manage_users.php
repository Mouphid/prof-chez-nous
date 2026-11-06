<?php
require_once "config_admin.php";

if(!has_permission('manage_users')){
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
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];

        if($role === 'admin' || $role === 'auteur'){
            if($_SESSION['admin_role'] !== 'admin'){
                $error = "Vous n'avez pas le droit d'attribuer ce rôle.";
            }
        }

        if(empty($name) || empty($email) || empty($password)){
            $error = "Veuillez remplir tous les champs.";
        } elseif(empty($error)){
            $check = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
            $check->execute([$email]);
            if($check->fetch()){
                $error = "Cet email est déjà utilisé.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$name, $email, $hashed_password, $role]);
                $success = "Utilisateur ajouté avec succès !";
            }
        }
    }
}

if(isset($_POST['edit'])){
    if(!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $id = (int)$_POST['id_user'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];

        if($_SESSION['admin_role'] !== 'admin'){
            $current = $pdo->prepare("SELECT role FROM users WHERE id_user = ?");
            $current->execute([$id]);
            $current_role = $current->fetchColumn();
            $role = $current_role;
        }

        if($role === 'admin' && $id !== $_SESSION['admin_id'] && $_SESSION['admin_role'] !== 'admin'){
            $error = "Vous ne pouvez pas modifier un administrateur.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id_user=?");
            $stmt->execute([$name, $email, $role, $id]);
            $success = "Utilisateur mis à jour avec succès !";
        }
    }
}

if(isset($_GET['delete'])){
    if(!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])){
        header("Location: manage_users.php?msg=invalid_token");
        exit;
    }
    $id = (int)$_GET['delete'];
    if($_SESSION['admin_role'] !== 'admin'){
        header("Location: manage_users.php?msg=no_permission");
        exit;
    }
    if($id === $_SESSION['admin_id']){
        header("Location: manage_users.php?msg=cannot_delete_self");
        exit;
    }
    $pdo->prepare("DELETE FROM users WHERE id_user=?")->execute([$id]);
    $success = "Utilisateur supprimé avec succès !";
}

$users = $pdo->query("SELECT * FROM users ORDER BY name ASC")->fetchAll();
$current_user_role = $_SESSION['admin_role'] ?? 'admin';

$role_labels = ['admin' => 'Admin', 'auteur' => 'Auteur', 'etudiant' => 'Étudiant'];
$role_badges = ['admin' => 'adm-badge-amber', 'auteur' => 'adm-badge-blue', 'etudiant' => 'adm-badge-gray'];

$initials_of = function (string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name) ?: [$name];
    $out = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $out .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    }
    return $out;
};

$permissions = [
    ['Accéder au panel admin', true, true, false],
    ['Voir le tableau de bord', true, true, false],
    ['Publier des articles', true, true, false],
    ['Modifier/supprimer ses propres articles', true, true, false],
    ['Modifier/supprimer les articles des autres', true, false, false],
    ['Gérer les utilisateurs', true, false, false],
    ['Attribuer des rôles', true, false, false],
    ['Gérer les catégories', true, false, false],
    ['Modérer les commentaires', true, true, false],
    ['Télécharger les ressources', true, true, true],
    ['Commenter les articles', true, true, true],
];

$page_action = $current_user_role === 'admin'
    ? '<button type="button" class="adm-btn adm-btn-primary" onclick="document.getElementById(\'addUserDialog\').showModal()"><i class="ph ph-user-plus"></i><span class="hidden sm:inline">Nouvel utilisateur</span></button>'
    : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Utilisateurs';
            $page_subtitle = 'Comptes, rôles et permissions du panel';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if($error): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars($success) ?></span></div>
            <?php endif; ?>
            <?php if(isset($_GET['msg'])):
                $msgs = ['no_permission'=>'Action non autorisée.', 'cannot_delete_self'=>'Vous ne pouvez pas vous supprimer.', 'invalid_token'=>'Token invalide.'];
            ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= $msgs[$_GET['msg']] ?? 'Erreur inconnue' ?></span></div>
            <?php endif; ?>

            <!-- Liste des utilisateurs -->
            <div class="adm-card overflow-hidden">
                <div class="adm-card-head">
                    <div>
                        <h2 class="adm-card-title">Comptes existants</h2>
                        <p class="adm-card-sub"><?= count($users) ?> utilisateur<?= count($users) > 1 ? 's' : '' ?> · les auteurs ne voient que leurs propres articles</p>
                    </div>
                </div>

                <?php if (!$users): ?>
                <div class="adm-empty">
                    <i class="ph ph-users"></i>
                    <p class="adm-empty-title">Aucun utilisateur</p>
                    <p class="adm-empty-note">Ajoutez un premier compte pour commencer.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>E-mail</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th class="adm-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user):
                                $is_self = ((int)$user['id_user'] === (int)$_SESSION['admin_id']);
                                $can_manage = !$is_self && ($current_user_role === 'admin' || ($current_user_role === 'auteur' && $user['role'] !== 'admin'));
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="adm-avatar-xs"><?= htmlspecialchars($initials_of((string)$user['name'])) ?></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 whitespace-nowrap">
                                                <?= htmlspecialchars($user['name']) ?>
                                                <?php if ($is_self): ?><span class="text-xs font-normal text-gray-400">(vous)</span><?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-600 break-all"><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="adm-badge <?= $role_badges[$user['role']] ?? 'adm-badge-gray' ?>">
                                        <?= $role_labels[$user['role']] ?? htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="adm-status <?= $user['is_active'] ? 'is-green' : 'is-red' ?>">
                                        <span class="adm-status-dot"></span><?= $user['is_active'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td class="adm-col-actions">
                                    <?php if ($can_manage): ?>
                                    <button type="button" class="adm-action adm-action-primary" title="Modifier"
                                            aria-label="Modifier <?= htmlspecialchars($user['name'], ENT_QUOTES) ?>"
                                            onclick="document.getElementById('edit-user-<?= (int)$user['id_user'] ?>').showModal()">
                                        <i class="ph ph-pencil"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($current_user_role === 'admin' && !$is_self): ?>
                                    <a href="?delete=<?= (int)$user['id_user'] ?>&csrf_token=<?= csrf_token() ?>"
                                       class="adm-action adm-action-danger" title="Supprimer"
                                       aria-label="Supprimer <?= htmlspecialchars($user['name'], ENT_QUOTES) ?>"
                                       onclick="return confirm('Supprimer cet utilisateur ?');">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($is_self): ?>
                                    <span class="text-xs text-gray-400">Compte connecté</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Permissions -->
            <details class="adm-accordion mt-5">
                <summary><i class="ph ph-shield-check"></i> Voir les permissions par rôle</summary>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                <th style="text-align:center">Admin</th>
                                <th style="text-align:center">Auteur</th>
                                <th style="text-align:center">Étudiant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($permissions as $row): ?>
                            <tr>
                                <td class="text-gray-700"><?= htmlspecialchars($row[0]) ?></td>
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                <td style="text-align:center">
                                    <?php if ($row[$i]): ?>
                                    <i class="ph ph-check adm-inline-icon" style="color:#12b76a" aria-label="Autorisé"></i>
                                    <?php else: ?>
                                    <i class="ph ph-minus adm-inline-icon" style="color:#d0d5dd" aria-label="Non autorisé"></i>
                                    <?php endif; ?>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>

            <p class="adm-note mt-4">
                <i class="ph ph-info"></i>
                <span>Un auteur peut publier et modérer les commentaires, mais ne gère ni les utilisateurs, ni les catégories. Les étudiants n'ont pas accès au panel.</span>
            </p>

            </div>
        </main>
    </div>

    <?php if ($current_user_role === 'admin'): ?>
    <!-- Modale : nouvel utilisateur -->
    <dialog class="adm-dialog" id="addUserDialog" aria-labelledby="addUserTitle">
        <form method="post">
            <?= csrf_field() ?>
            <div class="adm-dialog-head">
                <h2 class="adm-dialog-title" id="addUserTitle">Nouvel utilisateur</h2>
                <button type="button" class="adm-dialog-close" onclick="this.closest('dialog').close()" aria-label="Fermer">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div class="adm-dialog-body">
                <div class="adm-form-row mb-4">
                    <div>
                        <label for="user_name" class="adm-label">Nom complet</label>
                        <input type="text" id="user_name" name="name" placeholder="Ex : Marie Dupont" required class="adm-input">
                    </div>
                    <div>
                        <label for="user_email" class="adm-label">Adresse e-mail</label>
                        <input type="email" id="user_email" name="email" placeholder="marie@exemple.com" required class="adm-input">
                    </div>
                </div>
                <div class="adm-form-row">
                    <div>
                        <label for="user_password" class="adm-label">Mot de passe</label>
                        <div class="adm-field">
                            <input type="password" id="user_password" name="password" placeholder="••••••••" required class="adm-input">
                            <button type="button" class="adm-eye-btn" onclick="togglePw(this)" aria-label="Afficher le mot de passe">
                                <i class="ph ph-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="user_role" class="adm-label">Rôle</label>
                        <select id="user_role" name="role" class="adm-select">
                            <option value="etudiant">Étudiant</option>
                            <option value="auteur">Auteur</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="adm-dialog-foot">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="this.closest('dialog').close()">Annuler</button>
                <button type="submit" name="add" class="adm-btn adm-btn-primary"><i class="ph ph-plus"></i> Créer le compte</button>
            </div>
        </form>
    </dialog>
    <?php endif; ?>

    <?php foreach($users as $user):
        $is_self = ((int)$user['id_user'] === (int)$_SESSION['admin_id']);
        $can_manage = !$is_self && ($current_user_role === 'admin' || ($current_user_role === 'auteur' && $user['role'] !== 'admin'));
        if (!$can_manage) continue;
    ?>
    <dialog class="adm-dialog" id="edit-user-<?= (int)$user['id_user'] ?>" aria-labelledby="edit-user-title-<?= (int)$user['id_user'] ?>">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_user" value="<?= (int)$user['id_user'] ?>">
            <div class="adm-dialog-head">
                <h2 class="adm-dialog-title" id="edit-user-title-<?= (int)$user['id_user'] ?>">Modifier l'utilisateur</h2>
                <button type="button" class="adm-dialog-close" onclick="this.closest('dialog').close()" aria-label="Fermer">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            <div class="adm-dialog-body">
                <div class="adm-form-row mb-4">
                    <div>
                        <label for="edit-name-<?= (int)$user['id_user'] ?>" class="adm-label">Nom complet</label>
                        <input type="text" id="edit-name-<?= (int)$user['id_user'] ?>" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="adm-input">
                    </div>
                    <div>
                        <label for="edit-email-<?= (int)$user['id_user'] ?>" class="adm-label">Adresse e-mail</label>
                        <input type="email" id="edit-email-<?= (int)$user['id_user'] ?>" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="adm-input">
                    </div>
                </div>
                <div>
                    <label for="edit-role-<?= (int)$user['id_user'] ?>" class="adm-label">Rôle</label>
                    <?php if ($current_user_role === 'admin'): ?>
                    <select id="edit-role-<?= (int)$user['id_user'] ?>" name="role" class="adm-select">
                        <option value="etudiant" <?= $user['role'] === 'etudiant' ? 'selected' : '' ?>>Étudiant</option>
                        <option value="auteur" <?= $user['role'] === 'auteur' ? 'selected' : '' ?>>Auteur</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php else: ?>
                    <input type="hidden" name="role" value="<?= htmlspecialchars($user['role']) ?>">
                    <p class="adm-input" style="display:flex;align-items:center;color:#6b7280"><?= $role_labels[$user['role']] ?? htmlspecialchars($user['role']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="adm-dialog-foot">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="this.closest('dialog').close()">Annuler</button>
                <button type="submit" name="edit" class="adm-btn adm-btn-primary"><i class="ph ph-floppy-disk"></i> Enregistrer</button>
            </div>
        </form>
    </dialog>
    <?php endforeach; ?>

    <script>
    function togglePw(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ph ph-eye-slash';
            btn.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            input.type = 'password';
            icon.className = 'ph ph-eye';
            btn.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    }
    </script>
</body>
</html>

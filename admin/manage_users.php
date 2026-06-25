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

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, name ASC")->fetchAll();
$current_user_role = $_SESSION['admin_role'] ?? 'admin';

$can_view_posts = has_permission('manage_posts');
$can_view_comments = has_permission('manage_comments');
$can_view_categories = has_permission('manage_categories');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin</title>
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
                    <?php if($can_view_categories): ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-folder w-5"></i> Catégories</a></li>
                    <?php endif; ?>
                    <li><a href="manage_users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-700 text-white"><i class="fas fa-users w-5"></i> Utilisateurs</a></li>
                    <li class="border-t border-gray-700 pt-3 mt-3">
                        <a href="../public/index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-external-link-alt w-5"></i> Voir le site</a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="fas fa-sign-out-alt w-5"></i> Déconnexion</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 ml-64 p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-8"><i class="fas fa-users text-primary mr-2"></i> Gestion des utilisateurs</h1>

            <?php if($error): ?><div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if($success): ?><div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if(isset($_GET['msg'])):
                $msgs = ['no_permission'=>'Action non autorisée.', 'cannot_delete_self'=>'Vous ne pouvez pas vous supprimer.', 'invalid_token'=>'Token invalide.'];
            ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= $msgs[$_GET['msg']] ?? 'Erreur inconnue' ?></div>
            <?php endif; ?>

            <!-- Permission table -->
            <div class="bg-indigo-50 rounded-xl p-5 mb-6 overflow-x-auto">
                <h3 class="font-semibold text-indigo-900 mb-3"><i class="fas fa-shield-alt"></i> Tableau des permissions</h3>
                <table class="w-full text-sm">
                    <thead><tr class="bg-primary text-white"><th class="p-2 text-left">Permission</th><th class="p-2 text-center">Admin</th><th class="p-2 text-center">Auteur</th><th class="p-2 text-center">Étudiant</th></tr></thead>
                    <tbody class="bg-white">
                        <?php
                        $rows = [
                            ['Accéder au panel admin','✅','✅','❌'],
                            ['Voir le dashboard','✅','✅','❌'],
                            ['Publier des articles','✅','✅','❌'],
                            ['Modifier/supprimer ses propres articles','✅','✅','❌'],
                            ['Modifier/supprimer les articles des autres','✅','❌','❌'],
                            ['Gérer les utilisateurs','✅','❌','❌'],
                            ['Attribuer des rôles','✅','❌','❌'],
                            ['Gérer les catégories','✅','❌','❌'],
                            ['Modérer les commentaires','✅','✅','❌'],
                            ['Télécharger les ressources','✅','✅','✅'],
                            ['Commenter les articles','✅','✅','✅'],
                        ];
                        foreach($rows as $r): ?>
                        <tr class="border-b border-gray-100"><td class="p-2 font-medium"><?= $r[0] ?></td><td class="p-2 text-center"><?= $r[1] ?></td><td class="p-2 text-center"><?= $r[2] ?></td><td class="p-2 text-center"><?= $r[3] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add user -->
            <?php if($current_user_role === 'admin'): ?>
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4"><i class="fas fa-user-plus text-primary mr-2"></i> Ajouter un utilisateur</h2>
                <form method="post" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nom</label>
                            <input type="text" name="name" placeholder="Nom complet" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" placeholder="email@exemple.com" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Mot de passe</label>
                            <input type="password" name="password" placeholder="••••••••" required class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Rôle</label>
                            <select name="role" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                                <option value="etudiant">Étudiant</option>
                                <option value="auteur">Auteur</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="add" class="bg-primary hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg transition font-semibold flex items-center gap-2"><i class="fas fa-plus"></i> Ajouter</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Users list -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <h2 class="text-lg font-semibold p-5 border-b border-gray-100"><i class="fas fa-list mr-2"></i> Utilisateurs existants</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr><th class="p-3">Nom</th><th class="p-3">Email</th><th class="p-3">Rôle</th><th class="p-3">Statut</th><th class="p-3">Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $role_labels = ['admin' => 'Admin', 'auteur' => 'Auteur', 'etudiant' => 'Étudiant'];
                            $role_colors = ['admin' => 'bg-yellow-100 text-yellow-700', 'auteur' => 'bg-blue-100 text-blue-700', 'etudiant' => 'bg-gray-100 text-gray-600'];
                            foreach($users as $user): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="p-3 font-medium"><?= htmlspecialchars($user['name']) ?></td>
                                <td class="p-3 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="p-3"><span class="text-xs font-semibold px-2 py-1 rounded-full <?= $role_colors[$user['role']] ?? 'bg-gray-100' ?>"><?= $role_labels[$user['role']] ?? $user['role'] ?></span></td>
                                <td class="p-3"><span class="text-xs font-semibold px-2 py-1 rounded-full <?= $user['is_active'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= $user['is_active'] ? 'Actif' : 'Inactif' ?></span></td>
                                <td class="p-3">
                                    <?php if($user['id_user'] !== $_SESSION['admin_id'] && ($current_user_role === 'admin' || ($current_user_role === 'auteur' && $user['role'] !== 'admin'))): ?>
                                    <form method="post" class="flex gap-2 items-center flex-wrap">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-28" required>
                                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="px-2 py-1 border border-gray-200 rounded text-sm w-36" required>
                                        <?php if($current_user_role === 'admin'): ?>
                                        <select name="role" class="px-2 py-1 border border-gray-200 rounded text-sm">
                                            <option value="etudiant" <?= $user['role']=='etudiant'?'selected':'' ?>>Étudiant</option>
                                            <option value="auteur" <?= $user['role']=='auteur'?'selected':'' ?>>Auteur</option>
                                            <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                                        </select>
                                        <?php else: ?>
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $role_colors[$user['role']] ?? 'bg-gray-100' ?>"><?= $role_labels[$user['role']] ?? $user['role'] ?></span>
                                        <?php endif; ?>
                                        <button type="submit" name="edit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2 py-1 rounded text-sm transition"><i class="fas fa-save"></i></button>
                                    </form>
                                    <?php if($current_user_role === 'admin'): ?>
                                    <a href="?delete=<?= $user['id_user'] ?>&csrf_token=<?= csrf_token() ?>" class="text-red-600 hover:text-red-800 ml-2" onclick="return confirm('Supprimer cet utilisateur ?');"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <?php if($user['id_user'] === $_SESSION['admin_id']): ?><em class="text-gray-400 text-sm">Vous</em><?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

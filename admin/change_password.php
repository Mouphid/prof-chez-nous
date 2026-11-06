<?php
require_once "config_admin.php";

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT id_user, name, email, password FROM users WHERE id_user = ?");
$stmt->execute([$admin_id]);
$me = $stmt->fetch();

if (!$me) {
    header("Location: logout.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide. Rechargez la page.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!password_verify($current, $me['password'])) {
            $error = "Le mot de passe actuel est incorrect.";
        } elseif (strlen($new) < 10) {
            $error = "Le nouveau mot de passe doit contenir au moins 10 caractères.";
        } elseif ($new === $current) {
            $error = "Le nouveau mot de passe doit être différent de l'ancien.";
        } elseif ($new !== $confirm) {
            $error = "Les deux nouveaux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $upd->execute([$hash, $admin_id]);
            $success = "Votre mot de passe a été modifié avec succès.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changer mon mot de passe - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Changer mon mot de passe'; $page_icon = 'ph-lock-key'; include "_topbar.php"; ?>
            <div class="p-8 max-w-3xl">
                <p class="text-gray-500 mb-8">Connecté en tant que <strong><?= htmlspecialchars($me['email']) ?></strong></p>

            <?php if($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="post" class="space-y-5" autocomplete="off">
                    <?= csrf_field() ?>

                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-1">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="10" autocomplete="new-password" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Au moins 10 caractères. Évitez votre nom, votre email ou « 123456 ».</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="10" autocomplete="new-password" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <button type="submit" class="bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center gap-2">
                        <i class="ph ph-lock-key"></i> Enregistrer le nouveau mot de passe
                    </button>
                </form>
            </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php
/**
 * Choix d'un nouveau mot de passe après demande de réinitialisation.
 *
 * Le lien reçu par e-mail contient un jeton aléatoire. Seule son empreinte
 * SHA-256 est stockée en base, avec une expiration d'une heure. Le jeton est
 * effacé après usage : il ne peut donc pas servir deux fois.
 */
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

$token = trim((string)($_POST['token'] ?? ($_GET['token'] ?? '')));
$error = '';
$success = '';
$user  = null;
$valid_token = false;

if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $pdo->prepare("
        SELECT id_user, name, email
        FROM users
        WHERE reset_token = ?
          AND reset_expires > NOW()
          AND role IN ('admin', 'auteur')
          AND is_active = 1
    ");
    $stmt->execute([hash('sha256', $token)]);
    $user = $stmt->fetch();
    $valid_token = (bool)$user;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide. Rechargez la page.";
    } elseif (!check_rate_limit('admin_reset', 5, 300)) {
        $error = "Trop de tentatives. Réessayez dans 5 minutes.";
    } else {
        increment_rate_limit('admin_reset');

        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $assez_fort = mb_strlen($new) >= 10
            && preg_match('/[A-Z]/', $new)
            && preg_match('/[a-z]/', $new)
            && preg_match('/[0-9]/', $new)
            && preg_match('/[^A-Za-z0-9]/', $new);

        if ($new === '' || $confirm === '') {
            $error = "Veuillez remplir les deux champs.";
        } elseif (!$assez_fort) {
            $error = "Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } elseif ($new !== $confirm) {
            $error = "Les deux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("
                UPDATE users
                SET password = ?, reset_token = NULL, reset_expires = NULL
                WHERE id_user = ?
            ");
            $upd->execute([$hash, $user['id_user']]);

            logger("Mot de passe admin réinitialisé : " . $user['email'], 'info');

            $success = "Votre mot de passe a été modifié avec succès. Vous pouvez maintenant vous connecter.";
            $valid_token = false;
        }
    }
}

$page_title = "Nouveau mot de passe - Joie Enseignante";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); ?>
</head>
<body class="min-h-screen flex font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="hidden lg:flex w-1/2 bg-cover bg-center relative items-center justify-center" style="background-image: url('<?= BASE_URL ?>img/bg/admin-login.jpg');">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-indigo-900/90"></div>
        <div class="relative z-10 text-center px-12 max-w-md">
            <?= logo_tag('h-32', 'dark') ?>
            <p class="text-white/70 mt-6 leading-relaxed text-lg">Espace réservé aux administrateurs et auteurs</p>
        </div>
    </div>

    <main class="w-full lg:w-1/2 min-h-screen flex items-center justify-center p-4" id="main-content">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <?= logo_tag('h-16', 'light', 'mx-auto mb-2') ?>
                <h1 class="text-2xl font-bold text-gray-900">Nouveau mot de passe</h1>
                <p class="text-gray-500 mt-1">Choisissez un mot de passe solide</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 text-green-800 px-4 py-3 rounded-lg mb-6 flex items-start gap-2" role="status">
                <i class="ph ph-check-circle mt-0.5"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
            <a href="login.php" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-sign-in"></i> Se connecter
            </a>
            <?php elseif (!$valid_token): ?>
            <div class="bg-amber-50 text-amber-900 px-4 py-3 rounded-lg mb-6 flex items-start gap-2" role="note">
                <i class="ph ph-clock mt-0.5"></i>
                <span>Ce lien de réinitialisation est invalide, expiré ou a déjà été utilisé. Refaites une demande pour recevoir un nouveau lien.</span>
            </div>
            <a href="forgot_password.php" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                <i class="ph ph-paper-plane-tilt"></i> Demander un nouveau lien
            </a>
            <?php else: ?>
            <form method="post" class="space-y-5" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-lock mr-1"></i> Nouveau mot de passe
                    </label>
                    <input type="password" id="new_password" name="new_password" required minlength="10" autocomplete="new-password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                    <p class="text-xs text-gray-500 mt-2">Au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-lock-key mr-1"></i> Confirmer le mot de passe
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="10" autocomplete="new-password"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="ph ph-check-circle"></i> Enregistrer le mot de passe
                </button>
            </form>
            <?php endif; ?>

            <div class="text-center mt-8 pt-5 border-t border-gray-100">
                <a href="login.php" class="text-gray-500 text-sm hover:text-primary transition inline-flex items-center gap-1">
                    <i class="ph ph-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        </div>
    </main>
</body>
</html>

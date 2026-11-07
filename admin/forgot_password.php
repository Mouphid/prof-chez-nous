<?php
/**
 * Mot de passe oublié — espace administration.
 *
 * L'utilisateur saisit son adresse e-mail. Si elle correspond à un compte
 * administrateur ou auteur actif, un lien de réinitialisation valable
 * 1 heure est envoyé. Le message affiché reste toujours le même, pour ne
 * pas révéler si une adresse existe ou non.
 */
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide. Rechargez la page.";
    } elseif (!check_rate_limit('admin_forgot', 5, 300)) {
        $error = "Trop de demandes. Réessayez dans 5 minutes.";
    } else {
        increment_rate_limit('admin_forgot');

        $email = trim((string)($_POST['email'] ?? ''));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Cette adresse e-mail n'est pas valide.";
        } else {
            $stmt = $pdo->prepare("
                SELECT id_user, name, email
                FROM users
                WHERE email = ? AND role IN ('admin', 'auteur') AND is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $hash  = hash('sha256', $token);

                $upd = $pdo->prepare("
                    UPDATE users
                    SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR)
                    WHERE id_user = ?
                ");
                $upd->execute([$hash, $user['id_user']]);

                try {
                    require_once __DIR__ . '/../config/mail.php';
                    send_admin_reset_email($user, $token);
                    logger("Demande de réinitialisation admin : " . $email, 'info');
                } catch (Throwable $e) {
                    error_log("Réinitialisation admin — envoi à " . $email . " : " . $e->getMessage());
                    logger("Échec de l'envoi du lien de réinitialisation admin : " . $email, 'error');
                }
            } else {
                logger("Demande de réinitialisation pour une adresse sans compte admin : " . $email, 'warning');
            }

            $success = "Si cette adresse correspond à un compte administrateur, un e-mail contenant un lien de réinitialisation vient d'être envoyé. Le lien est valable 1 heure.";
        }
    }
}

$page_title = "Mot de passe oublié - Joie Enseignante";
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
            <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-32 w-auto">
            <p class="text-white/70 mt-6 leading-relaxed text-lg">Espace réservé aux administrateurs et auteurs</p>
        </div>
    </div>

    <main class="w-full lg:w-1/2 min-h-screen flex items-center justify-center p-4" id="main-content">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-10">
            <div class="text-center mb-8">
                <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-16 w-auto mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-900">Mot de passe oublié</h1>
                <p class="text-gray-500 mt-1">Recevez un lien pour choisir un nouveau mot de passe</p>
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
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="post" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-envelope mr-1"></i> Adresse e-mail du compte
                    </label>
                    <input type="email" id="email" name="email" placeholder="admin@exemple.com" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="ph ph-paper-plane-tilt"></i> Envoyer le lien
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

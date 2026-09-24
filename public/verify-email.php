<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Location: index.php");
exit;

$page_title = "Vérification email - Joie Enseignante";
$message = '';
$success = false;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = "Lien de vérification invalide.";
} else {
    $stmt = $pdo->prepare("SELECT id_user, email, token_expires FROM users WHERE verification_token = ? AND email_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = "Lien de vérification invalide ou déjà utilisé.";
    } elseif (strtotime($user['token_expires']) < time()) {
        $message = "Ce lien de vérification a expiré.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, token_expires = NULL WHERE id_user = ?");
        $stmt->execute([$user['id_user']]);
        $success = true;
        $message = "Votre adresse email a été vérifiée avec succès ! Vous pouvez maintenant vous connecter.";
    }
}

include "../includes/header.php";
?>

<div id="main-content" class="max-w-lg mx-auto px-4 py-20 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
        <?php if ($success): ?>
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-check-circle text-3xl text-green-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Email vérifié</h1>
        <p class="text-gray-600 mb-6"><?= htmlspecialchars($message) ?></p>
        <a href="login.php" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
            <i class="ph ph-sign-in"></i> Se connecter
        </a>
        <?php else: ?>
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-warning-circle text-3xl text-red-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Échec de vérification</h1>
        <p class="text-gray-600 mb-6"><?= htmlspecialchars($message) ?></p>
        <a href="index.php" class="text-primary hover:underline inline-flex items-center gap-1">
            <i class="ph ph-house"></i> Retour à l'accueil
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

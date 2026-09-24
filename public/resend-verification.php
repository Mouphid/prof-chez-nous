<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Location: index.php");
exit;

$email = trim($_GET['email'] ?? '');
$message = '';
$success = false;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $message = "Adresse email invalide.";
} else {
    $stmt = $pdo->prepare("SELECT id_user, name, email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = "Aucun compte trouvé avec cette adresse email.";
    } elseif ($user['email_verified']) {
        $message = "Cette adresse email est déjà vérifiée. <a href='login.php' class='text-primary font-semibold hover:underline'>Connectez-vous</a>";
        $success = true;
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt = $pdo->prepare("UPDATE users SET verification_token = ?, token_expires = ? WHERE id_user = ?");
        $stmt->execute([$token, $expires, $user['id_user']]);

        try {
            require_once __DIR__ . '/../config/mail.php';
            $mail = getMailer();
            $mail->addAddress($email, $user['name']);
            $mail->Subject = "Confirmez votre inscription - Joie Enseignante";
            $link = "http://{$_SERVER['HTTP_HOST']}/JoieEnseignante/public/verify-email.php?token=" . urlencode($token);
            $mail->Body = "Bonjour {$user['name']},\n\nVoici un nouveau lien pour vérifier votre adresse email :\n$link\n\nCe lien est valable 24 heures.\n\nL'équipe Joie Enseignante";
            $mail->send();
            $success = true;
            $message = "Un nouvel email de confirmation a été envoyé à <strong>" . htmlspecialchars($email) . "</strong>.";
        } catch (Exception $e) {
            error_log("Erreur renvoi email vérification: " . $e->getMessage());
            $message = "Impossible d'envoyer l'email. Réessayez plus tard.";
        }
    }
}

$page_title = "Renvoyer la vérification - Joie Enseignante";
include "../includes/header.php";
?>

<div id="main-content" class="max-w-lg mx-auto px-4 py-20 text-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
        <?php if ($success): ?>
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-check-circle text-3xl text-green-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Email envoyé</h1>
        <p class="text-gray-600 mb-6"><?= $message ?></p>
        <a href="login.php" class="text-primary hover:underline inline-flex items-center gap-1">
            <i class="ph ph-sign-in"></i> Aller à la connexion
        </a>
        <?php else: ?>
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-warning-circle text-3xl text-red-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Erreur</h1>
        <p class="text-gray-600 mb-6"><?= $message ?></p>
        <a href="login.php" class="text-primary hover:underline inline-flex items-center gap-1">
            <i class="ph ph-arrow-left"></i> Retour à la connexion
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

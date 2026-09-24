<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Location: index.php");
exit;

$page_title = "Inscription - Joie Enseignante";
$error = '';
$success = '';
$csrf_token = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $check = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active, email_verified, verification_token, token_expires) VALUES (?, ?, ?, 'etudiant', 1, 0, ?, ?)");
            $stmt->execute([$name, $email, $hashed, $token, $expires]);

            try {
                require_once __DIR__ . '/../config/mail.php';
                $mail = getMailer();
                $mail->addAddress($email, $name);
                $mail->Subject = "Confirmez votre inscription - Joie Enseignante";
                $link = "http://{$_SERVER['HTTP_HOST']}/JoieEnseignante/public/verify-email.php?token=" . urlencode($token);
                $mail->Body = "Bonjour $name,\n\nMerci de vous être inscrit sur Joie Enseignante.\n\nPour activer votre compte, cliquez sur le lien ci-dessous :\n$link\n\nCe lien est valable 24 heures.\n\nSi vous n'avez pas créé ce compte, ignorez cet email.\n\nL'équipe Joie Enseignante";
                $mail->send();
            } catch (Exception $e) {
                error_log("Erreur envoi email vérification: " . $e->getMessage());
            }

            $success = "Inscription réussie ! Un email de confirmation vous a été envoyé. Vérifiez votre boîte de réception.";
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); ?>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; } }
    </style>
</head>
<body class="min-h-screen flex font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="hidden lg:flex w-1/2 bg-cover bg-center relative items-center justify-center" style="background-image: url('<?= BASE_URL ?>img/bg/register.jpg');">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 to-indigo-900/90"></div>
        <div class="relative z-10 text-center px-12 max-w-md">
            <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-32 w-auto">
            <p class="text-2xl font-bold text-white mt-4">Joie Enseignante</p>
            <p class="text-white/70 mt-3 leading-relaxed">"L'éducation est l'arme la plus puissante pour changer le monde."</p>
            <p class="text-white/50 text-sm mt-4">— Nelson Mandela</p>
        </div>
    </div>

    <main class="w-full lg:w-1/2 min-h-screen flex items-center justify-center p-4" id="main-content">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-10 animate-fadeInUp">
            <div class="text-center mb-8">
                <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-16 w-auto mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-900">Inscription</h1>
                <p class="text-gray-500 mt-1">Créez un compte pour accéder aux ressources</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 text-center" role="alert"><?= $success ?></div>
            <?php else: ?>

            <form method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label for="reg_name" class="block text-sm font-semibold text-gray-700 mb-1"><i class="ph ph-user mr-1"></i> Nom complet</label>
                    <input type="text" id="reg_name" name="name" placeholder="Votre nom" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                </div>
                <div>
                    <label for="reg_email" class="block text-sm font-semibold text-gray-700 mb-1"><i class="ph ph-envelope mr-1"></i> Email</label>
                    <input type="email" id="reg_email" name="email" placeholder="votre@email.com" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                </div>
                <div>
                    <label for="reg_password" class="block text-sm font-semibold text-gray-700 mb-1"><i class="ph ph-lock mr-1"></i> Mot de passe</label>
                    <div class="relative">
                        <input type="password" id="reg_password" name="password" placeholder="Au moins 6 caractères" required class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-600 transition" aria-label="Afficher le mot de passe">
                            <i class="ph ph-eye text-xl"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="reg_confirm" class="block text-sm font-semibold text-gray-700 mb-1"><i class="ph ph-lock mr-1"></i> Confirmer le mot de passe</label>
                    <div class="relative">
                        <input type="password" id="reg_confirm" name="confirm_password" placeholder="Répétez le mot de passe" required class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-600 transition" aria-label="Afficher la confirmation du mot de passe">
                            <i class="ph ph-eye text-xl"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i class="ph ph-user-plus"></i> S'inscrire
                </button>
            </form>

            <p class="text-center mt-6 text-gray-500">
                Déjà un compte ? <a href="login.php" class="text-primary font-semibold hover:underline">Se connecter</a>
            </p>
            <?php endif; ?>
            <div class="text-center mt-6 pt-5 border-t border-gray-100">
                <a href="index.php" class="text-gray-500 text-sm hover:text-primary transition inline-flex items-center gap-1"><i class="ph ph-house"></i> Retour à l'accueil</a>
            </div>
        </div>
    </main>
<script>
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'ph ph-eye-slash text-xl';
    } else {
        input.type = 'password';
        icon.className = 'ph ph-eye text-xl';
    }
}
</script>
</body>
</html>

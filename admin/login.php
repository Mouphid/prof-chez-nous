<?php
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

$error = '';
$success = '';
$csrf_token = csrf_token();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } elseif (!check_rate_limit('login_admin')) {
        $error = "Trop de tentatives. Réessayez dans 5 minutes.";
    } else {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $error = "Veuillez remplir tous les champs";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'auteur') AND is_active = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if($admin && password_verify($password, $admin['password'])){
                $_SESSION['rate_login_admin'] = ['count' => 0, 'first' => 0];
                session_regenerate_id(true);
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_id'] = $admin['id_user'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                logger("Connexion admin: " . $email, 'info');
                redirect("dashboard.php");
            } else {
                $error = "Email ou mot de passe incorrect";
                logger("Échec connexion admin: $email", 'warning');
                increment_rate_limit('login_admin');
            }
    }
    }
}

$page_title = "Connexion Admin - Joie Enseignante";
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
                <h1 class="text-2xl font-bold text-gray-900">Connexion Admin</h1>
                <p class="text-gray-500 mt-1">Connectez-vous pour gérer le site</p>
            </div>

            <?php if($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-envelope mr-1"></i> Email
                    </label>
                    <input type="email" id="email" name="email" placeholder="admin@exemple.com" required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="ph ph-lock mr-1"></i> Mot de passe
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        <button type="button" onclick="togglePassword(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-600 transition" aria-label="Afficher le mot de passe">
                            <i class="ph ph-eye text-xl"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="ph ph-sign-in"></i> Se connecter
                </button>
            </form>

            <div class="text-center mt-8 pt-5 border-t border-gray-100">
                <a href="../public/index.php" class="text-gray-500 text-sm hover:text-primary transition inline-flex items-center gap-1"><i class="ph ph-house"></i> Retour à l'accueil</a>
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

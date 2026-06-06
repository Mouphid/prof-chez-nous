<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Connexion - Joie Enseignante";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-primary to-indigo-400 flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-10">
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-6xl text-primary"></i>
            <h1 class="text-2xl font-bold text-gray-900 mt-4">Connexion</h1>
            <p class="text-gray-500 mt-1">Connectez-vous pour accéder aux ressources</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-1"></i> Email
                </label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i> Mot de passe
                </label>
                <input type="password" id="password" name="password" placeholder="••••••••" required
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <p class="text-center mt-6 text-gray-500">
            Pas de compte ? <a href="register.php" class="text-primary font-semibold hover:underline">S'inscrire</a>
        </p>
        <p class="text-center mt-3">
            <a href="../admin/login.php" class="text-gray-400 text-sm hover:text-gray-600">Connexion admin</a>
        </p>
    </div>
</body>
</html>

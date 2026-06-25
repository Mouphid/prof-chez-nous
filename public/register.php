<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Inscription - Joie Enseignante";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, 'etudiant', 1)");
            $stmt->execute([$name, $email, $hashed]);
            $success = "Inscription réussie! <a href='login.php' class='text-primary font-semibold hover:underline'>Connectez-vous</a>";
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
            <h1 class="text-2xl font-bold text-gray-900 mt-4">Inscription</h1>
            <p class="text-gray-500 mt-1">Créez un compte pour accéder aux ressources</p>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6"><?= htmlspecialchars($success) ?></div>
        <?php else: ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><i class="fas fa-user mr-1"></i> Nom complet</label>
                <input type="text" name="name" placeholder="Votre nom" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><i class="fas fa-envelope mr-1"></i> Email</label>
                <input type="email" name="email" placeholder="votre@email.com" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><i class="fas fa-lock mr-1"></i> Mot de passe</label>
                <input type="password" name="password" placeholder="Au moins 6 caractères" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><i class="fas fa-lock mr-1"></i> Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" placeholder="Répétez le mot de passe" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center justify-center gap-2">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>

        <p class="text-center mt-6 text-gray-500">
            Déjà un compte ? <a href="login.php" class="text-primary font-semibold hover:underline">Se connecter</a>
        </p>
        <?php endif; ?>
    </div>
</body>
</html>

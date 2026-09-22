<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$default_password = "Prof@123DSy";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['current_password'] === $default_password) {
        $new_password = $_POST['new_password'];
        file_put_contents('password.txt', password_hash($new_password, PASSWORD_DEFAULT));
        $success = "Mot de passe modifié avec succès.";
    } else {
        $error = "Mot de passe actuel incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4">
        <h3 class="text-center">Changer le mot de passe</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Mot de passe actuel</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Modifier</button>
        </form>
    </div>
</body>
</html>
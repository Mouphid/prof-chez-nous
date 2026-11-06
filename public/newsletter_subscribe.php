<?php
/**
 * Inscription à la newsletter.
 * POST uniquement, avec protection CSRF.
 */
require_once "../config/config.php";
require_once "../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
// Sécurité : on n'accepte qu'un retour vers notre propre site
$host = parse_url($back, PHP_URL_HOST);
$self = $_SERVER['HTTP_HOST'] ?? '';
if ($host && $host !== $self) {
    $back = 'index.php';
}

if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
    flash('error', 'Token de sécurité invalide.');
    header("Location: $back");
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Adresse email invalide.');
    header("Location: $back");
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO newsletter_subscribers (email, status, created_at)
        VALUES (?, 'active', NOW())
        ON DUPLICATE KEY UPDATE status = 'active'
    ");
    $stmt->execute([$email]);
    flash('success', 'Merci ! Votre adresse est bien inscrite à la newsletter.');
} catch (PDOException $e) {
    error_log("newsletter_subscribe: " . $e->getMessage());
    flash('error', "L'inscription a échoué. Réessayez plus tard.");
}

header("Location: $back");
exit;

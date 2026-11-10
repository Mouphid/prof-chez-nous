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
    $stmt = $pdo->prepare("SELECT id_subscriber, unsubscribe_token FROM newsletter_subscribers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    $token = bin2hex(random_bytes(32));

    if ($existing) {
        // Réinscription : on réactive le compte sans changer son jeton s'il existe déjà.
        $token = (string)($existing['unsubscribe_token'] ?? '');
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
        }
        $pdo->prepare("
            UPDATE newsletter_subscribers
            SET status = 'active', unsubscribed_at = NULL, unsubscribe_token = ?, consent_at = NOW()
            WHERE id_subscriber = ?
        ")->execute([$token, (int)$existing['id_subscriber']]);
    } else {
        $pdo->prepare("
            INSERT INTO newsletter_subscribers (email, status, unsubscribe_token, consent_at, created_at)
            VALUES (?, 'active', ?, NOW(), NOW())
        ")->execute([$email, $token]);
    }

    flash('success', 'Merci ! Votre adresse est bien inscrite à la newsletter.');
} catch (PDOException $e) {
    error_log("newsletter_subscribe: " . $e->getMessage());
    flash('error', "L'inscription a échoué. Réessayez plus tard.");
}

header("Location: $back");
exit;

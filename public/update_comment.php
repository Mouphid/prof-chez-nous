<?php
require_once "../config/config.php";

$id_comment = (int)($_POST['id_comment'] ?? 0);
$content = trim($_POST['content'] ?? '');
$token = trim($_POST['token'] ?? '');

if (!$id_comment || !$content || !$token) {
    die("⚠️ Paramètres manquants.");
}

$stmt = $pdo->prepare("SELECT token_hash FROM comments WHERE id_comment = ?");
$stmt->execute([$id_comment]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment || !password_verify($token, $comment['token_hash'])) {
    die("⛔ Vous n’êtes pas autorisé à modifier ce commentaire.");
}

$stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id_comment = ?");
$stmt->execute([$content, $id_comment]);

echo "✅ Commentaire mis à jour.";
?>

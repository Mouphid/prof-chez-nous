<?php
require_once "../config/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;

if (!$id_post) {
    echo json_encode(['success' => false, 'message' => 'Post invalide']);
    exit;
}

$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $stmt = $pdo->prepare("SELECT id_like FROM likes WHERE id_post = ? AND id_user = ?");
    $stmt->execute([$id_post, $user_id]);
} else {
    $stmt = $pdo->prepare("SELECT id_like FROM likes WHERE id_post = ? AND ip_address = ? AND id_user IS NULL");
    $stmt->execute([$id_post, $user_ip]);
}
$existing = $stmt->fetch();

if ($existing) {
    if ($user_id) {
        $pdo->prepare("DELETE FROM likes WHERE id_post = ? AND id_user = ?")->execute([$id_post, $user_id]);
    } else {
        $pdo->prepare("DELETE FROM likes WHERE id_post = ? AND ip_address = ? AND id_user IS NULL")->execute([$id_post, $user_ip]);
    }
    $action = 'unliked';
} else {
    $pdo->prepare("INSERT INTO likes (id_post, id_user, ip_address, created_at) VALUES (?, ?, ?, NOW())")->execute([$id_post, $user_id, $user_ip]);
    $action = 'liked';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_post = ?");
$stmt->execute([$id_post]);
$total_likes = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'total_likes' => $total_likes,
    'action' => $action
]);

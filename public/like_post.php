<?php
require_once "../config/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;

if (!$id_post) {
    echo json_encode(['success' => false, 'message' => 'Post invalide']);
    exit;
}

// Use both IP and session for more reliable tracking
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$session_id = session_id() ?: 'no-session';

// Check if already liked (using IP + session for uniqueness)
$stmt = $pdo->prepare("SELECT id_like FROM likes WHERE id_post = ? AND (user_ip = ? OR user_ip = ?)");
$stmt->execute([$id_post, $user_ip, $session_id]);
$existing_like = $stmt->fetch();

if ($existing_like) {
    // Remove the like
    $pdo->prepare("DELETE FROM likes WHERE id_post = ? AND (user_ip = ? OR user_ip = ?)")->execute([$id_post, $user_ip, $session_id]);
    $action = 'unliked';
} else {
    // Add the like
    $pdo->prepare("INSERT INTO likes (id_post, user_ip, created_at) VALUES (?, ?, NOW())")->execute([$id_post, $user_ip]);
    $action = 'liked';
}

// Get total likes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_post = ?");
$stmt->execute([$id_post]);
$total_likes = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'total_likes' => $total_likes,
    'action' => $action,
    'debug' => ['ip' => $user_ip, 'session' => $session_id]
]);
?>
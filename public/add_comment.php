<?php
require_once "../config/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;
$content = trim($_POST['content'] ?? '');

if (!$id_post || !$content) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

if (strlen($content) < 3) {
    echo json_encode(['success' => false, 'message' => 'Commentaire trop court']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_post FROM posts WHERE id_post = ?");
    $stmt->execute([$id_post]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
        exit;
    }

    $token = bin2hex(random_bytes(16));
    $author_name = $_SESSION['user_name'] ?? 'Visiteur';
    $author_email = $_SESSION['user_email'] ?? ('visiteur_' . $token);
    $id_user = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO comments (id_post, id_user, author_name, author_email, content, token_hash, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'visible', NOW())");
    $stmt->execute([$id_post, $id_user, $author_name, $author_email, $content, $token]);

    $id_comment = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Commentaire publié avec succès !',
        'id_comment' => $id_comment,
        'token' => $token,
        'author_name' => $author_name
    ]);
} catch (PDOException $e) {
    error_log("add_comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la publication']);
}

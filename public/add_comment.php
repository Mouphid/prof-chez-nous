<?php
require_once "../config/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee']);
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
    // Verifier que le post existe
    $stmt = $pdo->prepare("SELECT id_post FROM posts WHERE id_post = ?");
    $stmt->execute([$id_post]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Article non trouve']);
        exit;
    }

    // Generate unique token for this comment
    $token = bin2hex(random_bytes(16));
    
    // If user is logged in, use their info
    if (isset($_SESSION['user_id'])) {
        $author_name = $_SESSION['user_name'] ?? 'Utilisateur';
        $author_email = $_SESSION['user_email'] ?? '';
        $id_user = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            INSERT INTO comments (id_post, id_user, author_name, author_email, content, token_hash, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'visible', NOW())
        ");
        $stmt->execute([$id_post, $id_user, $author_name, $author_email, $content, $token]);
    } else {
        $author_email = 'visiteur_' . $token;
        
        $stmt = $pdo->prepare("
            INSERT INTO comments (id_post, author_email, content, token_hash, status, created_at)
            VALUES (?, ?, ?, ?, 'visible', NOW())
        ");
        $stmt->execute([$id_post, $author_email, $content, $token]);
    }
    
    $id_comment = $pdo->lastInsertId();
    
    // Return the token so visitor can manage their comment
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire publie avec succes !',
        'id_comment' => $id_comment,
        'token' => $token,
        'author_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Visiteur'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la publication']);
}
?>
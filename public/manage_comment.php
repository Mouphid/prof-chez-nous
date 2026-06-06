<?php
require_once "../config/config.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comment = (int)($_POST['id_comment'] ?? 0);
    $token = $_POST['token'] ?? '';
    $content = trim($_POST['content'] ?? '');
    
    if (!$id_comment || !$content) {
        echo json_encode(['success' => false, 'message' => 'Parametres invalides']);
        exit;
    }
    
    $can_update = false;
    
    // Check if logged in user owns the comment
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
        $stmt = $pdo->prepare("SELECT id_comment FROM comments WHERE id_comment = ? AND (id_user = ? OR author_email = ?)");
        $stmt->execute([$id_comment, $_SESSION['user_id'], $_SESSION['user_email']]);
        if ($stmt->fetch()) {
            $can_update = true;
        }
    }
    
    // Check if visitor owns the comment (via token)
    if (!$can_update && $token) {
        $author_email = 'visiteur_' . $token;
        $stmt = $pdo->prepare("SELECT id_comment FROM comments WHERE id_comment = ? AND author_email = ?");
        $stmt->execute([$id_comment, $author_email]);
        if ($stmt->fetch()) {
            $can_update = true;
        }
    }
    
    if (!$can_update) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier ce commentaire']);
        exit;
    }
    
    // Update
    $stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id_comment = ?");
    $stmt->execute([$content, $id_comment]);
    
    echo json_encode(['success' => true, 'message' => 'Commentaire mis a jour']);
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comment = (int)($_POST['id_comment'] ?? 0);
    $token = $_POST['token'] ?? '';
    
    if (!$id_comment) {
        echo json_encode(['success' => false, 'message' => 'Parametres invalides']);
        exit;
    }
    
    $can_delete = false;
    
    // Check if logged in user owns the comment
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
        $stmt = $pdo->prepare("SELECT id_comment FROM comments WHERE id_comment = ? AND (id_user = ? OR author_email = ?)");
        $stmt->execute([$id_comment, $_SESSION['user_id'], $_SESSION['user_email']]);
        if ($stmt->fetch()) {
            $can_delete = true;
        }
    }
    
    // Check if visitor owns the comment (via token)
    if (!$can_delete && $token) {
        $author_email = 'visiteur_' . $token;
        $stmt = $pdo->prepare("SELECT id_comment FROM comments WHERE id_comment = ? AND author_email = ?");
        $stmt->execute([$id_comment, $author_email]);
        if ($stmt->fetch()) {
            $can_delete = true;
        }
    }
    
    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer ce commentaire']);
        exit;
    }
    
    // Delete
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);
    
    echo json_encode(['success' => true, 'message' => 'Commentaire supprime']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
?>
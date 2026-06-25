<?php
require_once "../config/config.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comment = (int)($_POST['id_comment'] ?? 0);
    $token = $_POST['token'] ?? '';
    $content = trim($_POST['content'] ?? '');

    if (!$id_comment || !$content) {
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
        exit;
    }

    $can_update = false;
    $stmt = $pdo->prepare("SELECT id_user, author_email, token_hash FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);
    $comment = $stmt->fetch();

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Commentaire introuvable']);
        exit;
    }

    if (isset($_SESSION['user_id']) && $comment['id_user'] == $_SESSION['user_id']) {
        $can_update = true;
    } elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $can_update = true;
    } elseif ($token && $token === $comment['token_hash']) {
        $can_update = true;
    } elseif ($token && $comment['author_email'] === 'visiteur_' . $token) {
        $can_update = true;
    }

    if (!$can_update) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier ce commentaire']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id_comment = ?");
    $stmt->execute([$content, $id_comment]);

    echo json_encode(['success' => true, 'message' => 'Commentaire mis à jour']);
    exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comment = (int)($_POST['id_comment'] ?? 0);
    $token = $_POST['token'] ?? '';

    if (!$id_comment) {
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
        exit;
    }

    $can_delete = false;
    $stmt = $pdo->prepare("SELECT id_user, author_email, token_hash FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);
    $comment = $stmt->fetch();

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Commentaire introuvable']);
        exit;
    }

    if (isset($_SESSION['user_id']) && $comment['id_user'] == $_SESSION['user_id']) {
        $can_delete = true;
    } elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $can_delete = true;
    } elseif ($token && $token === $comment['token_hash']) {
        $can_delete = true;
    } elseif ($token && $comment['author_email'] === 'visiteur_' . $token) {
        $can_delete = true;
    }

    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer ce commentaire']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);

    echo json_encode(['success' => true, 'message' => 'Commentaire supprimé']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action non reconnue']);

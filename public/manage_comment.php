<?php
require_once "../config/config.php";

$is_form = !empty($_POST['from_form']) || !empty($_GET['from_form']);
if (!$is_form) {
    header('Content-Type: application/json');
}

$action = $_GET['action'] ?? '';

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
        exit;
    }
    $id_comment = (int)($_POST['id_comment'] ?? 0);
    $token = $_POST['token'] ?? '';
    $content = trim($_POST['content'] ?? '');

    if (!$id_comment || !$content) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
        exit;
    }

    $can_update = false;
    $stmt = $pdo->prepare("SELECT id_user, author_email, token_hash FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);
    $comment = $stmt->fetch();

    if (!$comment) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Commentaire introuvable']);
        exit;
    }

    if (isset($_SESSION['user_id']) && $comment['id_user'] == $_SESSION['user_id']) {
        $can_update = true;
    } elseif (isset($_SESSION['user_email']) && !empty($comment['author_email']) && $comment['author_email'] === $_SESSION['user_email']) {
        $can_update = true;
    } elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $can_update = true;
    } elseif ($token && $token === $comment['token_hash']) {
        $can_update = true;
    } elseif ($token && $comment['author_email'] === 'visiteur_' . $token) {
        $can_update = true;
    }

    if (!$can_update) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas modifier ce commentaire']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id_comment = ?");
    $stmt->execute([$content, $id_comment]);

    if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
    echo json_encode(['success' => true, 'message' => 'Commentaire mis à jour']);
    exit;
}

if ($action === 'delete') {
    $csrf = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!verify_csrf($csrf)) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
        exit;
    }
    $id_comment = (int)(($_POST['id_comment'] ?? $_GET['id_comment'] ?? 0));
    $token = $_POST['token'] ?? $_GET['token'] ?? '';
    $post_id = (int)(($_POST['post_id'] ?? $_GET['post_id'] ?? 0));

    if (!$id_comment) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Paramètres invalides']);
        exit;
    }

    $can_delete = false;
    if (!$post_id) {
        $stmt = $pdo->prepare("SELECT id_post, id_user, author_email, token_hash FROM comments WHERE id_comment = ?");
        $stmt->execute([$id_comment]);
        $comment = $stmt->fetch();
        if ($comment) $post_id = $comment['id_post'];
    } else {
        $stmt = $pdo->prepare("SELECT id_user, author_email, token_hash FROM comments WHERE id_comment = ?");
        $stmt->execute([$id_comment]);
        $comment = $stmt->fetch();
    }

    if (!$comment) {
        if ($is_form) { header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php')); exit; }
        echo json_encode(['success' => false, 'message' => 'Commentaire introuvable']);
        exit;
    }

    if (isset($_SESSION['user_id']) && $comment['id_user'] == $_SESSION['user_id']) {
        $can_delete = true;
    } elseif (isset($_SESSION['user_email']) && !empty($comment['author_email']) && $comment['author_email'] === $_SESSION['user_email']) {
        $can_delete = true;
    } elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $can_delete = true;
    } elseif ($token && $token === $comment['token_hash']) {
        $can_delete = true;
    } elseif ($token && $comment['author_email'] === 'visiteur_' . $token) {
        $can_delete = true;
    }

    if (!$can_delete) {
        if ($is_form) { header("Location: post.php?id=" . $post_id . "&error=suppression"); exit; }
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer ce commentaire']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);

    if ($is_form) {
        header("Location: post.php?id=" . $post_id);
        exit;
    }
    echo json_encode(['success' => true, 'message' => 'Commentaire supprimé']);
    exit;
}

if ($is_form) { header("Location: index.php"); exit; }
echo json_encode(['success' => false, 'message' => 'Action non reconnue']);

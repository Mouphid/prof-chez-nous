<?php
require_once "../config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "❌ Requête invalide.";
    exit;
}

if (!isset($_POST['id_comment'])) {
    echo "❌ Paramètre manquant.";
    exit;
}

$id_comment = (int) $_POST['id_comment'];

if ($id_comment <= 0) {
    echo "❌ ID invalide.";
    exit;
}

// Vérifier que l'utilisateur possède ce commentaire
$stmt = $pdo->prepare("SELECT id_user FROM comments WHERE id_comment = ?");
$stmt->execute([$id_comment]);
$comment = $stmt->fetch();

$is_owner = false;
if (isset($_SESSION['user_id']) && $comment && $comment['id_user'] == $_SESSION['user_id']) {
    $is_owner = true;
}
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $is_owner = true;
}

if (!$is_owner) {
    echo "❌ Vous n'avez pas le droit de supprimer ce commentaire.";
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id_comment = ?");
    $stmt->execute([$id_comment]);

    if ($stmt->rowCount() > 0) {
        echo "✅ Commentaire supprimé avec succès.";
    } else {
        echo "❌ Commentaire introuvable.";
    }
} catch (Exception $e) {
    error_log("delete_comment.php: " . $e->getMessage());
    echo "❌ Erreur lors de la suppression.";
}

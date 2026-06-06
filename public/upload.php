<?php
require_once "../config/config.php";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_posts FROM posts");
    $row = $stmt->fetch();
    echo "Connexion réussie ! Nombre de posts dans la base : " . $row['total_posts'];
} catch (Exception $e) {
    echo "Erreur lors de la lecture de la table posts : " . $e->getMessage();
}
?>

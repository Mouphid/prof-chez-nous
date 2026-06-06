<?php
require_once "config_admin.php";

// CSRF check
if(!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])){
    die("Token de sécurité invalide");
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    die("Fichier non trouvé !");
}

$file_id = (int)$_GET['id'];

// Récupérer le fichier
$stmt = $pdo->prepare("SELECT * FROM files WHERE id_file = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if($file){
    $file_path = "../uploads/".$file['file_type']."/".$file['file_name'];
    if(file_exists($file_path)){
        unlink($file_path); // Supprime le fichier du serveur
    }

    // Supprimer de la base
    $stmt_del = $pdo->prepare("DELETE FROM files WHERE id_file = ?");
    $stmt_del->execute([$file_id]);

    // Retourner à la page d'édition du post
    header("Location: edit_post.php?id=".$file['id_post']);
    exit;
}else{
    die("Fichier introuvable !");
}
?>

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
    $file_type = basename($file['file_type']);
    $file_name = basename($file['file_name']);
    $allowed_types = ['pdf','word','docs','audio','video','images','image','excel'];
    if (!in_array($file_type, $allowed_types)) { die("Type de fichier invalide"); }
    $file_path = realpath(__DIR__ . '/../uploads/' . $file_type . '/' . $file_name);
    $upload_root = realpath(__DIR__ . '/../uploads/');
    if ($file_path && str_starts_with($file_path, $upload_root) && file_exists($file_path)){
        unlink($file_path);
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

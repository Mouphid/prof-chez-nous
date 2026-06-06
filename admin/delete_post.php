<?php
require_once "config_admin.php";

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_posts.php");
    exit;
}

// CSRF check
if(!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])){
    die("Token de sécurité invalide");
}

$post_id = (int)$_GET['id'];

// Check ownership
$stmt = $pdo->prepare("SELECT id_user FROM posts WHERE id_post = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post){
    header("Location: manage_posts.php");
    exit;
}

// Check permission: admin can delete any, author can delete own
if($_SESSION['admin_role'] !== 'admin' && $post['id_user'] != $_SESSION['admin_id']){
    header("Location: manage_posts.php");
    exit;
}

// Delete attached files
$stmt_files = $pdo->prepare("SELECT * FROM files WHERE id_post = ?");
$stmt_files->execute([$post_id]);
$files = $stmt_files->fetchAll();

foreach($files as $file){
    $file_path = "../uploads/".$file['file_type']."/".$file['file_name'];
    if(file_exists($file_path)){
        unlink($file_path);
    }
    $stmt_del_file = $pdo->prepare("DELETE FROM files WHERE id_file = ?");
    $stmt_del_file->execute([$file['id_file']]);
}

// Delete the post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id_post = ?");
$stmt->execute([$post_id]);

header("Location: manage_posts.php");
exit;
?>

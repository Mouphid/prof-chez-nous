<?php
session_start();
require_once "../config/config.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true){
    header("Location: login.php");
    exit;
}

// Récupérer le rôle de l'utilisateur connecté
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// Définition des permissions par rôle
define('PERMISSIONS', [
    'admin' => [
        'manage_users' => true,
        'manage_posts' => true,
        'manage_categories' => true,
        'manage_comments' => true,
        'manage_downloads' => true,
        'view_dashboard' => true,
        'publish_articles' => true,
        'edit_any_post' => true,
        'delete_any_post' => true,
        'manage_site' => true,
    ],
    'auteur' => [
        'manage_users' => false,
        'manage_posts' => true,
        'manage_categories' => false,
        'manage_comments' => true,
        'manage_downloads' => true,
        'view_dashboard' => true,
        'publish_articles' => true,
        'edit_any_post' => false,
        'delete_any_post' => false,
        'manage_site' => false,
    ],
    'etudiant' => [
        'manage_users' => false,
        'manage_posts' => false,
        'manage_categories' => false,
        'manage_comments' => false,
        'manage_downloads' => true,
        'view_dashboard' => true,
        'publish_articles' => false,
        'edit_any_post' => false,
        'delete_any_post' => false,
        'manage_site' => false,
    ],
]);

// Fonction pour vérifier une permission
function has_permission($permission){
    global $admin_role;
    $perms = PERMISSIONS;
    return $perms[$admin_role][$permission] ?? false;
}

// Optionnel : récupérer les infos de l'admin depuis la base si besoin
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
?>

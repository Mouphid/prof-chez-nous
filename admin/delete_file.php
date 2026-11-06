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
    $upload_root = realpath(__DIR__ . '/../uploads/');
    $file_name   = basename($file['file_name']);
    $type_folder = basename($file['file_type']);

    // Chemins possibles (ancien format et nouveau format)
    $candidates = [];
    if (!empty($file['file_path'])) {
        $stored = $file['file_path'];
        if (str_starts_with($stored, 'uploads/')) {
            $candidates[] = __DIR__ . '/../' . $stored;
        } else {
            $candidates[] = __DIR__ . '/../uploads/' . $type_folder . '/' . basename($stored);
        }
    }
    $candidates[] = __DIR__ . '/../uploads/' . $type_folder . '/' . $file_name;
    // Correspondances de dossiers (word/docs, image/images...)
    $folder_aliases = [
        'doc'   => ['docs', 'word'],
        'word'  => ['docs', 'word'],
        'image' => ['images', 'image'],
        'other' => ['excel', 'audio', 'images', 'docs', 'pdf'],
    ];
    $extra = $folder_aliases[$type_folder] ?? [];
    foreach ($extra as $folder) {
        $candidates[] = __DIR__ . '/../uploads/' . $folder . '/' . $file_name;
    }

    foreach ($candidates as $candidate) {
        $real = realpath($candidate);
        if ($real && $upload_root && str_starts_with($real, $upload_root) && is_file($real)) {
            unlink($real);
            break;
        }
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

<?php
require_once "../config/config.php";

if (!isset($_GET['type'], $_GET['file']) || empty($_GET['type']) || empty($_GET['file'])) {
    die("Fichier introuvable.");
}

$type = $_GET['type'];
$file = $_GET['file'];
$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$allowed_types = ['pdf','word','excel','audio','video','images','docs'];
if (!in_array($type, $allowed_types)) {
    die("Type de fichier non autorisé.");
}

$file = basename($file);
$filepath = __DIR__ . "/../uploads/$type/$file";

if (!file_exists($filepath)) {
    $filepath = null;
    $folders = ['pdf', 'docs', 'images', 'audio', 'video', 'word', 'excel'];
    foreach ($folders as $folder) {
        $test = __DIR__ . "/../uploads/$folder/$file";
        if (file_exists($test)) {
            $filepath = $test;
            break;
        }
    }
}

if (!$filepath || !file_exists($filepath)) {
    die("Fichier introuvable.");
}

if (isset($_SESSION['user_id']) && $file_id > 0) {
    $user_id = $_SESSION['user_id'];
    $insert = $pdo->prepare("INSERT IGNORE INTO user_downloads (id_user, id_file) VALUES (?, ?)");
    $insert->execute([$user_id, $file_id]);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

readfile($filepath);
exit;
?>
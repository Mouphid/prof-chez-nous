<?php
/**
 * Téléchargement sécurisé des documents.
 * - Document gratuit (price = 0) : téléchargement direct.
 * - Document payant (price > 0) : nécessite un token d'achat valide.
 */
require_once "../config/config.php";

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token   = isset($_GET['token']) ? trim($_GET['token']) : '';

if (!$file_id) {
    http_response_code(404);
    die("Fichier introuvable.");
}

$stmt = $pdo->prepare("SELECT * FROM files WHERE id_file = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    die("Fichier introuvable.");
}

$price = (float)$file['price'];

// ─── Contrôle d'accès pour les documents payants ───
if ($price > 0) {
    $authorized = false;
    if ($token !== '') {
        $p = $pdo->prepare("SELECT id_purchase FROM purchases WHERE download_token = ? AND id_file = ? AND status = 'paid' LIMIT 1");
        $p->execute([$token, $file_id]);
        $authorized = (bool)$p->fetch();
    }
    if (!$authorized) {
        header("Location: purchase.php?id=" . $file_id);
        exit;
    }
}

// ─── Localisation physique du fichier (sécurisée) ───
$candidates = [];
$file_path = $file['file_path'] ?? '';
if ($file_path !== '') {
    if (str_starts_with($file_path, 'uploads/')) {
        $candidates[] = __DIR__ . '/../' . $file_path;
    } else {
        $candidates[] = __DIR__ . '/../uploads/' . $file['file_type'] . '/' . basename($file_path);
    }
}
$candidates[] = __DIR__ . '/../uploads/' . $file['file_type'] . '/' . basename($file['file_name']);
$candidates[] = __DIR__ . '/../uploads/pdf/' . basename($file['file_name']);
$candidates[] = __DIR__ . '/../uploads/docs/' . basename($file['file_name']);

$upload_root = realpath(__DIR__ . '/../uploads');
$filepath = null;
foreach ($candidates as $candidate) {
    $real = realpath($candidate);
    if ($real && $upload_root && str_starts_with($real, $upload_root) && is_file($real)) {
        $filepath = $real;
        break;
    }
}

if (!$filepath) {
    http_response_code(404);
    die("Fichier introuvable sur le serveur.");
}

// ─── Historique (si un compte est connecté) ───
if (!empty($_SESSION['user_id'])) {
    $insert = $pdo->prepare("INSERT IGNORE INTO user_downloads (id_user, id_file) VALUES (?, ?)");
    $insert->execute([$_SESSION['user_id'], $file_id]);
}

// ─── Envoi du fichier ───
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $filepath);
finfo_close($finfo);

header('Content-Description: File Transfer');
header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('Expires: 0');

readfile($filepath);
exit;

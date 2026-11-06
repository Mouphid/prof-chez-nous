<?php
/**
 * public/video.php — Diffusion protégée des vidéos d'articles.
 *
 * Le dossier uploads/video n'est plus accessible directement (voir .htaccess).
 * Cette page sert le fichier uniquement s'il est rattaché à un article visible,
 * avec prise en charge des requêtes partielles (Range) pour permettre
 * la lecture et le déplacement dans la vidéo.
 */
require_once "../config/config.php";

$name = isset($_GET['f']) ? trim((string)$_GET['f']) : '';

// On n'accepte qu'un simple nom de fichier : pas de chemin, pas de « ../ ».
$name = basename($name);

if ($name === '' || !preg_match('/^[A-Za-z0-9._\- ]+$/', $name)) {
    http_response_code(404);
    exit('Vidéo introuvable.');
}

$video_root = realpath(__DIR__ . '/../uploads/video');
$path       = realpath(__DIR__ . '/../uploads/video/' . $name);

if (!$video_root || !$path || !str_starts_with($path, $video_root) || !is_file($path)) {
    http_response_code(404);
    exit('Vidéo introuvable.');
}

// La vidéo doit appartenir à un article visible (publié ou programmé arrivé à échéance).
$stmt = $pdo->prepare("
    SELECT 1 FROM posts
    WHERE main_video = ?
      AND (
            status = 'published'
            OR (status = 'scheduled' AND published_at IS NOT NULL AND published_at <= NOW())
          )
    LIMIT 1
");
$stmt->execute([$name]);

if (!$stmt->fetchColumn()) {
    http_response_code(403);
    exit('Accès refusé.');
}

$size = filesize($path);
$start = 0;
$end   = $size - 1;

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $path) ?: 'video/mp4';
finfo_close($finfo);

header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Requête partielle : le navigateur demande un morceau pour se déplacer dans la vidéo.
if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    $from = $m[1];
    $to   = $m[2];

    if ($from === '' && $to === '') {
        // En-tête illisible : on ignore et on sert le fichier entier.
    } elseif ($from === '') {
        // « bytes=-500 » : les 500 derniers octets.
        $start = max(0, $size - (int)$to);
    } else {
        $start = (int)$from;
        if ($to !== '') {
            $end = min((int)$to, $size - 1);
        }
    }

    if ($start >= $size || $start > $end) {
        http_response_code(416);
        header('Content-Range: bytes */' . $size);
        exit;
    }

    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}

$length = $end - $start + 1;
header('Content-Length: ' . $length);

$fp = fopen($path, 'rb');
if ($fp === false) {
    http_response_code(500);
    exit;
}

fseek($fp, $start);

$remaining = $length;
while ($remaining > 0 && !feof($fp)) {
    $chunk = fread($fp, (int)min(8192, $remaining));
    if ($chunk === false || $chunk === '') {
        break;
    }
    echo $chunk;
    $remaining -= strlen($chunk);
    flush();
}

fclose($fp);
exit;

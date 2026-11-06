<?php
/**
 * public/course_file.php — Envoi protégé des fichiers d'un cours.
 *
 * Les dossiers uploads/cours/pdf et uploads/cours/video ne sont pas
 * accessibles directement (voir leur .htaccess). Cette page vérifie
 * que le chapitre est gratuit, en aperçu, ou acheté avant d'envoyer
 * le fichier. Elle gère aussi les requêtes partielles (Range), utiles
 * pour lire une vidéo sans la télécharger entièrement.
 */
require_once "../config/config.php";
require_once "../includes/functions.php";
require_once "../includes/courses.php";

$item_id = isset($_GET['item']) ? (int)$_GET['item'] : 0;
$type    = isset($_GET['type']) ? strtolower(trim((string)$_GET['type'])) : '';
$token   = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

if (!$item_id || !in_array($type, ['pdf', 'video'], true)) {
    http_response_code(404);
    exit('Fichier introuvable.');
}

$stmt = $pdo->prepare("
    SELECT ci.*, c.title AS course_title, c.slug AS course_slug,
           c.price AS course_price, c.status AS course_status
    FROM course_items ci
    JOIN courses c ON c.id_course = ci.id_course
    WHERE ci.id_item = ?
    LIMIT 1
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item || $item['course_status'] !== 'published') {
    http_response_code(404);
    exit('Fichier introuvable.');
}

$course = [
    'id_course' => (int)$item['id_course'],
    'price'     => (float)$item['course_price'],
    'slug'      => $item['course_slug'],
    'title'     => $item['course_title'],
];

$purchase = course_purchase_by_token($pdo, $token);
if ($purchase) {
    $belongs = ((int)($purchase['id_course'] ?? 0) === (int)$course['id_course'])
        || (int)($purchase['id_item'] ?? 0) === (int)$item['id_item'];
    if (!$belongs) {
        $purchase = null;
    }
}

if (!course_item_is_unlocked($course, $item, $purchase)) {
    http_response_code(403);
    $retour = 'course.php?slug=' . rawurlencode((string)$item['course_slug']);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accès réservé - Joie Enseignante</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #f9fafb; color: #374151; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 24px; }
            .box { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; max-width: 460px; padding: 32px; text-align: center; }
            h1 { font-size: 20px; color: #111827; margin: 0 0 8px; }
            p { font-size: 15px; line-height: 1.6; margin: 0 0 20px; }
            a { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; font-weight: 600; padding: 12px 20px; border-radius: 10px; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Accès réservé</h1>
            <p>Ce chapitre fait partie d'un cours payant. Achetez le cours ou le chapitre pour pouvoir l'ouvrir.</p>
            <a href="<?= htmlspecialchars($retour) ?>">Retour au cours</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ─── Localisation du fichier (jamais de chemin fourni par l'utilisateur) ───
$dir = $type === 'pdf' ? 'uploads/cours/pdf' : 'uploads/cours/video';
$name = basename((string)($type === 'pdf' ? $item['pdf_path'] : $item['video_path']));

if ($name === '' || $name === '.' || $name === '..') {
    http_response_code(404);
    exit('Fichier introuvable.');
}

$root = realpath(__DIR__ . '/../' . $dir);
$path = realpath(__DIR__ . '/../' . $dir . '/' . $name);

if (!$root || !$path || !str_starts_with($path, $root) || !is_file($path)) {
    http_response_code(404);
    exit('Fichier introuvable sur le serveur.');
}

$size = filesize($path);
if ($size === false || $size === 0) {
    http_response_code(404);
    exit('Fichier vide.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $path) ?: ($type === 'pdf' ? 'application/pdf' : 'video/mp4');
finfo_close($finfo);

// Nom proposé au téléchargement / à l'ouverture, sans caractères gênants.
$pretty = preg_replace('/[^A-Za-z0-9._-]+/', '-', course_slug((string)$item['title']));
$pretty = trim($pretty, '-') ?: 'chapitre';
$filename = $pretty . ($type === 'pdf' ? '.pdf' : '.' . pathinfo($name, PATHINFO_EXTENSION));

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=0, must-revalidate');
header('X-Content-Type-Options: nosniff');

$start = 0;
$end   = $size - 1;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    $from = $m[1];
    $to   = $m[2];

    if ($from === '' && $to === '') {
        // En-tête illisible : on sert le fichier entier.
    } elseif ($from === '') {
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

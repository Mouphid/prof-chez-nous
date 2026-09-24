<?php
/**
 * Import des données legacy (branche legacy) vers la base actuelle.
 * À exécuter une seule fois : php sql/import_legacy.php
 *
 * Sources : _debug_backup/legacy/publications.json + comments.json
 * Effets :
 *  - Crée/récupère les catégories "Publications" (Articles) et "Actualités" (Infos)
 *  - Crée l'utilisateur "Prof. Sylvestre Djouamon" (rôle auteur)
 *  - Importe les 22 posts + fichiers (PDF -> uploads/pdf, images -> uploads/images)
 *  - Importe les commentaires
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/_legacy_config.php';

$legacyDir = dirname(__DIR__) . '/_debug_backup/legacy';
$uploadsDir = dirname(__DIR__) . '/uploads';

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = str_replace(
        ['à','â','ä','á','ã','å','è','é','ê','ë','ì','í','î','ï','ò','ó','ô','ö','õ','ù','ú','û','ü','ý','ÿ','ç','ñ','œ','æ','Ɖ','ɖ','Ɛ','ɛ','Ɔ','ɔ','Ǹ','ǹ','Ń','ń','Ó','ó','Ô','ô','̈'],
        ['a','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','y','y','c','n','oe','ae','d','d','e','e','o','o','n','n','n','n','o','o','o','o','']
        , $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text === '' ? 'article' : substr($text, 0, 190);
}

$pdo->beginTransaction();

// --- Catégories ---
function getCategory(PDO $pdo, string $name, string $slug, string $color): int {
    $stmt = $pdo->prepare("SELECT id_category FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    $id = $stmt->fetchColumn();
    if ($id) return (int)$id;
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, color) VALUES (?, ?, '', ?)");
    $stmt->execute([$name, $slug, $color]);
    return (int)$pdo->lastInsertId();
}

$catPublications = getCategory($pdo, 'Publications', 'publications', '#007BFF');
$catActualites   = getCategory($pdo, 'Actualités', 'actualites', '#6f42c1');

// Brouillon de test du legacy : exclu de l'import (n'est pas du contenu réel)
$excludeTitles = ['Article teste'];

// --- Utilisateur professeur (administrateur du site) ---
// NB: le nom n'inclut PAS le préfixe "Prof." (les templates l'ajoutent déjà)
$profName = 'Sylvestre Djouamon';
$profPass = getenv('PROF_PASS') ?: 'Djouamon2026!';
$stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
$stmt->execute(['prof@joieenseignante.com']);
$profId = $stmt->fetchColumn();
if (!$profId) {
    // Supprime le compte admin générique par défaut (insécurisé)
    $pdo->exec("DELETE FROM users WHERE role = 'admin' AND email = 'admin@joieenseignante.com'");
    $hash = password_hash($profPass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, bio, is_active) VALUES (?, ?, ?, 'admin', ?, 1)");
    $stmt->execute([$profName, 'prof@joieenseignante.com', $hash, 'Professeur de littérature, Département de Littérature, Université de Cotonou.']);
    $profId = (int)$pdo->lastInsertId();
} else {
    $pdo->prepare("UPDATE users SET name = ? WHERE id_user = ?")->execute([$profName, $profId]);
}

// --- Publications & commentaires legacy ---
$publications = json_decode(file_get_contents("$legacyDir/publications.json"), true);
$comments = json_decode(file_get_contents("$legacyDir/comments.json"), true);

// --- Images référencées : copie AVANT l'insertion des posts ---
foreach ($publications as $p) {
    $img = trim((string)($p['image'] ?? ''));
    if ($img === '') continue;
    $imgName = basename(str_replace('\\', '/', $img));
    $src = "$legacyDir/uploads/$imgName";
    if (file_exists($src) && !file_exists("$uploadsDir/images/$imgName")) {
        copy($src, "$uploadsDir/images/$imgName");
    }
}

// --- Posts ---

$legacyToNew = [];
$slugIndex = [];

foreach ($publications as $p) {
    $title = html_entity_decode($p['titre'] ?? '', ENT_QUOTES, 'UTF-8');
    $content = html_entity_decode($p['contenu'] ?? '', ENT_QUOTES, 'UTF-8');

    if (in_array($title, $excludeTitles, true)) {
        echo "SKIP (brouillon) : $title\n";
        continue;
    }

    $categorie = ($p['categorie'] ?? '') === 'Infos' ? $catActualites : $catPublications;
    $dateOrig = ($p['date'] ?? '');
    $createdAt = null;
    if (preg_match('/^(\d{4}-\d{2}-\d{2}):(\d{2}:\d{2})$/', $dateOrig, $m)) {
        $createdAt = "$m[1] " . $m[2] . ":00";
    }

    $slug = slugify($title);
    $baseSlug = $slug;
    $n = 2;
    while (isset($slugIndex[$slug])) {
        $slug = $baseSlug . '-' . $n++;
    }
    $slugIndex[$slug] = true;

    $stmt = $pdo->prepare(
        "INSERT INTO posts (id_user, id_category, title, slug, content, excerpt, main_image, status, views, created_at, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'published', 0, ?, ?)"
    );
    $excerpt = $content !== '' ? mb_substr(strip_tags($content), 0, 200) : '';
    $mainImage = null;
    $img = trim((string)($p['image'] ?? ''));
    if ($img !== '') {
        // Le code affiche ../uploads/images/<main_image> => on stocke le simple nom
        $mainImage = basename(str_replace('\\', '/', $img));
    }
    $stmt->execute([$profId, $categorie, $title, $slug, $content, $excerpt, $mainImage, $createdAt, $createdAt]);
    $newPostId = (int)$pdo->lastInsertId();
    $legacyToNew[$p['id']] = $newPostId;

    echo "OK  : #$newPostId [$p[categorie]] $title\n";

    // --- Fichier attaché (pdf / doc / image selon l'extension réelle) ---
    $pdf = trim((string)($p['pdf'] ?? ''));
    if ($pdf !== '') {
        $fileName = basename(str_replace('\\', '/', $pdf));
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $folder = 'images';
            $type = 'image';
        } elseif (in_array($ext, ['doc', 'docx', 'odt', 'txt'])) {
            $folder = 'docs';
            $type = 'doc';
        } else {
            $folder = 'pdf';
            $type = 'pdf';
        }
        $src = "$legacyDir/uploads/$fileName";
        $destPath = "uploads/$folder/$fileName";
        if (file_exists($src) && !file_exists("$uploadsDir/$folder/$fileName")) {
            copy($src, "$uploadsDir/$folder/$fileName");
        }
        $size = file_exists("$uploadsDir/$folder/$fileName") ? filesize("$uploadsDir/$folder/$fileName") : null;
        $stmt = $pdo->prepare("INSERT INTO files (id_post, file_name, file_type, file_path, file_size) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$newPostId, $fileName, $type, $destPath, $size]);
    }

    // --- Image principale -> fichier (si pdf+image distincts) ---
    if ($mainImage && file_exists("$uploadsDir/images/" . basename($mainImage))) {
        $imgName = basename($mainImage);
        $size = filesize("$uploadsDir/images/$imgName");
        $stmt = $pdo->prepare("INSERT INTO files (id_post, file_name, file_type, file_path, file_size) VALUES (?, ?, 'image', ?, ?)");
        $stmt->execute([$newPostId, $imgName, $mainImage, $size]);
    }
}

// --- Commentaires ---
foreach ($comments as $c) {
    if (!isset($legacyToNew[$c['publication_id']])) continue;
    $name = html_entity_decode($c['auteur'] ?? 'Inconnu', ENT_QUOTES, 'UTF-8');
    $content = html_entity_decode($c['contenu'] ?? '', ENT_QUOTES, 'UTF-8');
    $stmt = $pdo->prepare(
        "INSERT INTO comments (id_post, author_email, author_name, content, status)
         VALUES (?, 'guest@localhost', ?, ?, 'visible')"
    );
    $stmt->execute([$legacyToNew[$c['publication_id']], $name, $content]);
    echo "OK  : commentaire de $name sur #{$legacyToNew[$c['publication_id']]}\n";
}

$pdo->commit();

echo "\n=== IMPORT TERMINÉ ===\n";
echo "Posts importés : " . count($legacyToNew) . "\n";
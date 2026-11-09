<?php
/**
 * Migration des titres d'articles écrits ENTIÈREMENT EN MAJUSCULES.
 * Les remet en casse normale (première lettre majuscule, reste en minuscules).
 *
 * Utilisation en ligne de commande :
 *   php sql/migration_titres_casse.php
 *
 * Le script est idempotent : on peut le relancer sans risque.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$posts = $pdo->query("SELECT id_post, title FROM posts")->fetchAll();
$updated = 0;

$update = $pdo->prepare("UPDATE posts SET title = ?, updated_at = NOW() WHERE id_post = ?");

foreach ($posts as $post) {
    $new_title = normalize_title_case($post['title']);
    if ($new_title !== $post['title']) {
        $update->execute([$new_title, (int)$post['id_post']]);
        $updated++;
    }
}

echo "Titres mis à jour : $updated / " . count($posts) . PHP_EOL;

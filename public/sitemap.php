<?php
/**
 * Plan du site (sitemap XML) généré dynamiquement.
 * Accessible via https://joie-enseignante.com/sitemap.xml (voir .htaccess).
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    ['loc' => SITE_URL . '/',                 'priority' => '1.0'],
    ['loc' => SITE_URL . '/publications.php', 'priority' => '0.9'],
    ['loc' => SITE_URL . '/cours.php',        'priority' => '0.8'],
    ['loc' => SITE_URL . '/biography.php',    'priority' => '0.7'],
    ['loc' => SITE_URL . '/about.php',        'priority' => '0.7'],
    ['loc' => SITE_URL . '/contact.php',      'priority' => '0.6'],
];

$posts = $pdo->query("
    SELECT id_post, updated_at, created_at
    FROM posts
    WHERE status = 'published' OR (status = 'scheduled' AND published_at <= NOW())
    ORDER BY created_at DESC
")->fetchAll();

foreach ($posts as $p) {
    $urls[] = [
        'loc'      => SITE_URL . '/post.php?id=' . (int)$p['id_post'],
        'lastmod'  => date('Y-m-d', strtotime($p['updated_at'] ?: $p['created_at'])),
        'priority' => '0.8',
    ];
}

try {
    $courses = $pdo->query("
        SELECT id_course, updated_at, created_at
        FROM courses
        WHERE status = 'published'
        ORDER BY id_course DESC
    ")->fetchAll();
    foreach ($courses as $cr) {
        $urls[] = [
            'loc'      => SITE_URL . '/course.php?id=' . (int)$cr['id_course'],
            'lastmod'  => date('Y-m-d', strtotime($cr['updated_at'] ?: $cr['created_at'])),
            'priority' => '0.8',
        ];
    }
} catch (Throwable $e) {
    // Table absente si non initialisée
}

$cats = $pdo->query("SELECT id_category FROM categories ORDER BY name")->fetchAll();
foreach ($cats as $c) {
    $urls[] = [
        'loc'      => SITE_URL . '/category.php?id=' . (int)$c['id_category'],
        'priority' => '0.6',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?= htmlspecialchars($u['loc']) ?></loc>
<?php if (!empty($u['lastmod'])): ?>
        <lastmod><?= $u['lastmod'] ?></lastmod>
<?php endif; ?>
        <priority><?= $u['priority'] ?></priority>
    </url>
<?php endforeach; ?>
</urlset>

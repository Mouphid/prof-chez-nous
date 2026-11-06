<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$courses = $pdo->query("
    SELECT c.*,
           COUNT(ci.id_item) AS items_count,
           SUM(CASE WHEN ci.pdf_path IS NOT NULL AND ci.pdf_path <> '' THEN 1 ELSE 0 END) AS pdf_count,
           SUM(CASE WHEN ci.video_path IS NOT NULL AND ci.video_path <> '' THEN 1 ELSE 0 END) AS video_count
    FROM courses c
    LEFT JOIN course_items ci ON ci.id_course = c.id_course
    GROUP BY c.id_course
    ORDER BY c.created_at DESC
")->fetchAll();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts,
        SUM(CASE WHEN price > 0 THEN 1 ELSE 0 END) AS paid
    FROM courses
")->fetch();

$items_total = (int)$pdo->query("SELECT COUNT(*) FROM course_items")->fetchColumn();

$montant = fn(float $n): string => $n > 0
    ? number_format($n, 0, ',', ' ') . ' FCFA'
    : 'Gratuit';

// L'image de couverture peut être une URL absolue ou un chemin relatif au site.
$cover_src = function (string $cover): string {
    $cover = trim($cover);
    if ($cover === '') return '';
    if (preg_match('#^https?://#i', $cover)) return $cover;
    return '../' . ltrim($cover, '/');
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cours - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Cours';
            $page_subtitle = 'Catalogue, chapitres et tarifs des cours';
            $page_action = '<a href="add_course.php" class="adm-btn adm-btn-primary"><i class="ph ph-plus"></i><span class="hidden sm:inline">Nouveau cours</span></a>';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if (hasFlash('success')): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars(flash('success')) ?></span></div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars(flash('error')) ?></span></div>
            <?php endif; ?>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Cours</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-graduation-cap"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['total'] ?></p>
                    <p class="adm-kpi-foot">dans le catalogue</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Publiés</span>
                        <span class="adm-kpi-icon adm-kpi-icon-green"><i class="ph ph-check-circle"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['published'] ?></p>
                    <p class="adm-kpi-foot"><?= (int)$stats['drafts'] ?> brouillon<?= (int)$stats['drafts'] > 1 ? 's' : '' ?> en attente</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Chapitres</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-stack"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= $items_total ?></p>
                    <p class="adm-kpi-foot">contenus pédagogiques</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Cours payants</span>
                        <span class="adm-kpi-icon adm-kpi-icon-amber"><i class="ph ph-tag"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['paid'] ?></p>
                    <p class="adm-kpi-foot">les autres sont gratuits</p>
                </div>
            </div>

            <div class="adm-card overflow-hidden">
                <?php if (!$courses): ?>
                <div class="adm-empty">
                    <i class="ph ph-graduation-cap"></i>
                    <p class="adm-empty-title">Aucun cours pour le moment</p>
                    <p class="adm-empty-note">Créez votre premier cours, puis ajoutez ses chapitres et ses fichiers.</p>
                    <a href="add_course.php" class="adm-btn adm-btn-primary"><i class="ph ph-plus"></i> Créer un cours</a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Cours</th>
                                <th>Chapitres</th>
                                <th>Contenus</th>
                                <th>Tarif</th>
                                <th>Statut</th>
                                <th class="adm-col-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $c):
                                $src = $cover_src((string)($c['cover_image'] ?? ''));
                            ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="adm-cover">
                                            <i class="ph <?= htmlspecialchars($c['icon'] ?: 'ph-book-open') ?>"></i>
                                            <?php if ($src !== ''): ?>
                                            <img src="<?= htmlspecialchars($src) ?>" alt="" loading="lazy" onerror="this.remove()">
                                            <?php endif; ?>
                                        </span>
                                        <div class="min-w-0">
                                            <a href="edit_course.php?id=<?= (int)$c['id_course'] ?>" class="adm-table-title" style="max-width:24rem" title="<?= htmlspecialchars($c['title']) ?>">
                                                <?= htmlspecialchars($c['title']) ?>
                                            </a>
                                            <p class="adm-table-sub">
                                                <?= htmlspecialchars($c['code'] ?: 'Sans code') ?>
                                                <?= $c['level'] ? ' · ' . htmlspecialchars($c['level']) : '' ?>
                                                <?= $c['duration'] ? ' · ' . htmlspecialchars($c['duration']) : '' ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-600 whitespace-nowrap"><?= (int)$c['items_count'] ?></td>
                                <td class="text-xs text-gray-500 whitespace-nowrap">
                                    <?= (int)$c['pdf_count'] ?> PDF · <?= (int)$c['video_count'] ?> vidéo<?= (int)$c['video_count'] > 1 ? 's' : '' ?>
                                </td>
                                <td class="whitespace-nowrap">
                                    <?php if ((float)$c['price'] > 0): ?>
                                    <span class="text-sm font-semibold text-gray-900"><?= $montant((float)$c['price']) ?></span>
                                    <?php else: ?>
                                    <span class="adm-badge adm-badge-green"><i class="ph ph-gift"></i> Gratuit</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="adm-badge <?= $c['status'] === 'published' ? 'adm-badge-green' : 'adm-badge-amber' ?>">
                                        <?= $c['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                                    </span>
                                </td>
                                <td class="adm-col-actions">
                                    <a href="../public/course.php?id=<?= (int)$c['id_course'] ?>" target="_blank" rel="noopener" class="adm-action" aria-label="Voir le cours" title="Voir sur le site">
                                        <i class="ph ph-arrow-square-out"></i>
                                    </a>
                                    <a href="edit_course.php?id=<?= (int)$c['id_course'] ?>" class="adm-action adm-action-primary" aria-label="Modifier le cours" title="Modifier">
                                        <i class="ph ph-pencil"></i>
                                    </a>
                                    <form method="post" action="delete_course.php" class="inline" onsubmit="return confirm('Supprimer ce cours et tous ses chapitres ? Cette action est définitive.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_course" value="<?= (int)$c['id_course'] ?>">
                                        <button type="submit" class="adm-action adm-action-danger" aria-label="Supprimer le cours" title="Supprimer">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="adm-note mt-4">
                <i class="ph ph-info"></i>
                <span>
                    Un cours peut être gratuit. Le prix de chaque chapitre se règle sur la page de modification du cours.
                    Un chapitre marqué « aperçu gratuit » reste visible avant l'achat.
                </span>
            </div>

            </div>
        </main>
    </div>
</body>
</html>

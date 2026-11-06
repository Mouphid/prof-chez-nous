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
            $page_icon = 'ph-graduation-cap';
            $page_action = '<a href="add_course.php" class="bg-primary hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition font-semibold flex items-center gap-2 text-sm"><i class="ph ph-plus"></i> Nouveau cours</a>';
            include "_topbar.php";
            ?>
            <div class="p-8">

            <?php if (hasFlash('success')): ?>
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl px-4 py-3 mb-4 text-sm" role="status">
                <i class="ph ph-check-circle"></i> <?= htmlspecialchars(flash('success')) ?>
            </div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars(flash('error')) ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-gray-800"><?= (int)$stats['total'] ?></div>
                    <div class="text-sm text-gray-500">Cours</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-emerald-600"><?= (int)$stats['published'] ?></div>
                    <div class="text-sm text-gray-500">Publiés</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-primary"><?= $items_total ?></div>
                    <div class="text-sm text-gray-500">Chapitres</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-amber-600"><?= (int)$stats['paid'] ?></div>
                    <div class="text-sm text-gray-500">Cours payants</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <tr>
                                <th class="px-4 py-3">Cours</th>
                                <th class="px-4 py-3">Chapitres</th>
                                <th class="px-4 py-3">Contenus</th>
                                <th class="px-4 py-3">Prix du cours</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!$courses): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                    <i class="ph ph-graduation-cap text-3xl text-gray-300 block mb-2"></i>
                                    Aucun cours pour l'instant.
                                    <a href="add_course.php" class="text-primary font-semibold hover:underline">Créer le premier cours</a>.
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($courses as $c): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center flex-shrink-0">
                                            <i class="ph <?= htmlspecialchars($c['icon'] ?: 'ph-book-open') ?>"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <a href="edit_course.php?id=<?= (int)$c['id_course'] ?>" class="font-semibold text-gray-900 hover:text-primary"><?= htmlspecialchars($c['title']) ?></a>
                                            <div class="text-xs text-gray-400">
                                                <?= htmlspecialchars($c['code'] ?: '—') ?>
                                                <?= $c['level'] ? ' · ' . htmlspecialchars($c['level']) : '' ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap"><?= (int)$c['items_count'] ?></td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                    <?= (int)$c['pdf_count'] ?> PDF · <?= (int)$c['video_count'] ?> vidéo<?= (int)$c['video_count'] > 1 ? 's' : '' ?>
                                </td>
                                <td class="px-4 py-3 font-semibold whitespace-nowrap <?= (float)$c['price'] > 0 ? 'text-gray-900' : 'text-emerald-600' ?>">
                                    <?= $montant((float)$c['price']) ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $c['status'] === 'published' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?>">
                                        <?= $c['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="../public/course.php?id=<?= (int)$c['id_course'] ?>" target="_blank" rel="noopener" class="text-gray-400 hover:text-primary transition mx-1" aria-label="Voir le cours" title="Voir sur le site"><i class="ph ph-arrow-square-out"></i></a>
                                    <a href="edit_course.php?id=<?= (int)$c['id_course'] ?>" class="text-gray-400 hover:text-primary transition mx-1" aria-label="Modifier le cours" title="Modifier"><i class="ph ph-pencil"></i></a>
                                    <form method="post" action="delete_course.php" class="inline" onsubmit="return confirm('Supprimer ce cours et tous ses chapitres ? Cette action est définitive.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_course" value="<?= (int)$c['id_course'] ?>">
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition mx-1" aria-label="Supprimer le cours" title="Supprimer"><i class="ph ph-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-4">
                <i class="ph ph-info"></i>
                Un cours peut être gratuit. Le prix de chaque chapitre se règle sur la page de modification du cours.
                Un chapitre marqué « aperçu gratuit » reste visible avant l'achat.
            </p>

            </div>
        </main>
    </div>
</body>
</html>

<?php
session_start();
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config_admin.php";

$admin = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$admin->execute([$_SESSION['admin_id']]);
$admin = $admin->fetch();

$can_view_users      = has_permission('manage_users');
$can_view_categories = has_permission('manage_categories');
$can_view_posts      = has_permission('manage_posts');
$can_view_comments   = has_permission('manage_comments');
$can_publish         = has_permission('publish_articles');

$is_full_admin       = ($admin_role === 'admin');
$admin_role_label    = $is_full_admin ? 'Super Admin' : ($admin_role === 'auteur' ? 'Auteur' : 'Étudiant');

// Un auteur ne voit que ses propres contenus.
$owner_sql = $admin_role === 'auteur' ? ' AND p.id_user = ' . (int)$_SESSION['admin_id'] : '';

$status_labels = [
    'published' => 'Publié',
    'draft'     => 'Brouillon',
    'scheduled' => 'Programmé',
];
$status_styles = [
    'published' => 'bg-emerald-50 text-emerald-700',
    'draft'     => 'bg-amber-50 text-amber-700',
    'scheduled' => 'bg-blue-50 text-blue-700',
];

// ─── Indicateurs ───
if ($admin_role === 'auteur') {
    $stats = [];
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id']);
    $stats['posts'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . " AND status='published'");
    $stats['posts_published'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . " AND status='draft'");
    $stats['posts_draft'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . " AND status='scheduled'");
    $stats['posts_scheduled'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE id_post IN (SELECT id_post FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . ")");
    $stats['comments'] = $stmt->fetchColumn();
    $stats['comments_pending'] = 0;
    $stmt = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id']);
    $stats['views'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM likes WHERE id_post IN (SELECT id_post FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . ")");
    $stats['likes'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['posts_month'] = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM comments WHERE id_post IN (SELECT id_post FROM posts WHERE id_user = " . (int)$_SESSION['admin_id'] . ") AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['comments_month'] = $stmt->fetchColumn();
} else {
    $stats = [];
    $stats['posts']            = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    $stats['posts_published']  = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
    $stats['posts_draft']      = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='draft'")->fetchColumn();
    $stats['posts_scheduled']  = $pdo->query("SELECT COUNT(*) FROM posts WHERE status='scheduled'")->fetchColumn();
    $stats['comments']         = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $stats['comments_pending'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending' OR status IS NULL")->fetchColumn();
    $stats['likes']            = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
    $stats['categories']       = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['users']            = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['files']            = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
    $stats['views']            = $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn();

    $stats['posts_month'] = $pdo->query("SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats['users_month'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    $stats['comments_month'] = $pdo->query("SELECT COUNT(*) FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
}

// ─── Ventes et newsletter (réservé à l'admin principal) ───
// Les tables peuvent ne pas exister si les migrations n'ont pas encore été passées :
// on protège le tableau de bord avec des valeurs par défaut.
$sales = [
    'month'         => 0,
    'month_count'   => 0,
    'prev_month'    => 0,
    'total'         => 0,
    'total_count'   => 0,
    'paid_files'    => 0,
    'subscribers'   => 0,
    'recent'        => [],
];
$sales_delta = null;

if ($is_full_admin) {
    try {
        $sales['month'] = (float)$pdo->query("
            SELECT COALESCE(SUM(amount),0) FROM purchases
            WHERE status='paid' AND paid_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ")->fetchColumn();

        $sales['month_count'] = (int)$pdo->query("
            SELECT COUNT(*) FROM purchases
            WHERE status='paid' AND paid_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ")->fetchColumn();

        $sales['prev_month'] = (float)$pdo->query("
            SELECT COALESCE(SUM(amount),0) FROM purchases
            WHERE status='paid'
              AND paid_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
              AND paid_at <  DATE_FORMAT(NOW(), '%Y-%m-01')
        ")->fetchColumn();

        $sales['total'] = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM purchases WHERE status='paid'")->fetchColumn();
        $sales['total_count'] = (int)$pdo->query("SELECT COUNT(*) FROM purchases WHERE status='paid'")->fetchColumn();
        $sales['paid_files'] = (int)$pdo->query("SELECT COUNT(*) FROM files WHERE price > 0")->fetchColumn();

        $sales['recent'] = $pdo->query("
            SELECT pu.buyer_name, pu.buyer_email, pu.amount, pu.paid_at, f.file_name
            FROM purchases pu
            LEFT JOIN files f ON f.id_file = pu.id_file
            WHERE pu.status='paid'
            ORDER BY pu.paid_at DESC
            LIMIT 5
        ")->fetchAll();

        if ($sales['prev_month'] > 0) {
            $sales_delta = (($sales['month'] - $sales['prev_month']) / $sales['prev_month']) * 100;
        }
    } catch (PDOException $e) {
        error_log('Dashboard ventes: ' . $e->getMessage());
    }

    try {
        $sales['subscribers'] = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='active'")->fetchColumn();
    } catch (PDOException $e) {
        error_log('Dashboard newsletter: ' . $e->getMessage());
    }
}

// ─── Listes ───
$draft_posts = $pdo->query("
    SELECT p.*, u.name as author_name
    FROM posts p LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.status='draft'$owner_sql
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();

$scheduled_posts = $pdo->query("
    SELECT p.*, u.name as author_name
    FROM posts p LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.status='scheduled'$owner_sql
    ORDER BY p.published_at ASC LIMIT 5
")->fetchAll();

$recent_comments = $pdo->query("
    SELECT c.*, p.title as post_title
    FROM comments c
    LEFT JOIN posts p ON c.id_post = p.id_post
    WHERE 1=1" . ($admin_role === 'auteur' ? " AND p.id_user = " . (int)$_SESSION['admin_id'] : '') . "
    ORDER BY c.created_at DESC LIMIT 8
")->fetchAll();

$popular_posts = $pdo->query("
    SELECT p.id_post, p.title, p.views
    FROM posts p
    WHERE p.status='published'$owner_sql
    ORDER BY p.views DESC LIMIT 5
")->fetchAll();

$latest_posts = $pdo->query("
    SELECT p.*, c.name as category_name, u.name as author_name
    FROM posts p
    LEFT JOIN categories c ON p.id_category = c.id_category
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE 1=1$owner_sql
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// ─── Résumé et date en français ───
$tasks = [];
if ($stats['comments_pending'] > 0) {
    $tasks[] = [
        'label' => $stats['comments_pending'] . ' commentaire' . ($stats['comments_pending'] > 1 ? 's' : '') . ' à valider',
        'href'  => 'manage_comments.php',
        'tone'  => 'amber',
        'icon'  => 'ph-chats',
    ];
}
if ($stats['posts_draft'] > 0) {
    $tasks[] = [
        'label' => $stats['posts_draft'] . ' brouillon' . ($stats['posts_draft'] > 1 ? 's' : '') . ' en attente',
        'href'  => 'manage_posts.php',
        'tone'  => 'gray',
        'icon'  => 'ph-pencil-simple',
    ];
}
if ($stats['posts_scheduled'] > 0) {
    $tasks[] = [
        'label' => $stats['posts_scheduled'] . ' article' . ($stats['posts_scheduled'] > 1 ? 's' : '') . ' programmé' . ($stats['posts_scheduled'] > 1 ? 's' : ''),
        'href'  => 'manage_posts.php',
        'tone'  => 'blue',
        'icon'  => 'ph-calendar',
    ];
}

$jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$mois  = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$date_fr = ucfirst($jours[(int)date('w')]) . ' ' . (int)date('j') . ' ' . $mois[(int)date('n')] . ' ' . date('Y');

$montant = fn(float $n): string => number_format($n, 0, ',', ' ') . ' FCFA';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Tableau de bord'; $page_icon = 'ph-gauge'; include "_topbar.php"; ?>

            <div class="p-8">

                <!-- En-tête -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500"><?= $date_fr ?> · <?= date('H:i') ?></p>
                            <h2 class="text-xl font-bold text-gray-900 mt-1">Bonjour, <?= htmlspecialchars($admin['name'] ?? 'Admin') ?></h2>
                            <p class="text-sm text-gray-500 mt-1">
                                <?= (int)$stats['posts_published'] ?> article<?= (int)$stats['posts_published'] > 1 ? 's' : '' ?> publié<?= (int)$stats['posts_published'] > 1 ? 's' : '' ?>
                                · <?= number_format((int)$stats['views']) ?> vue<?= (int)$stats['views'] > 1 ? 's' : '' ?>
                                <?php if ($is_full_admin): ?> · <?= (int)$sales['total_count'] ?> vente<?= (int)$sales['total_count'] > 1 ? 's' : '' ?><?php endif; ?>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($can_publish): ?>
                            <a href="add_post.php" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                                <i class="ph ph-plus-circle"></i> Nouvel article
                            </a>
                            <?php endif; ?>
                            <a href="../public/index.php" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:border-primary hover:text-primary transition">
                                <i class="ph ph-arrow-square-out"></i> Voir le site
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Indicateurs -->
                <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Vue d'ensemble</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 <?= $is_full_admin ? 'lg:grid-cols-3' : 'lg:grid-cols-4' ?> gap-4 mb-8">

                    <?php if ($is_full_admin): ?>
                    <!-- Revenus -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-wallet text-lg"></i></div>
                            <?php if ($sales_delta !== null): ?>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $sales_delta >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' ?>">
                                <?= $sales_delta >= 0 ? '+' : '' ?><?= number_format($sales_delta, 0, ',', ' ') ?> %
                            </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Revenus du mois</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= $montant($sales['month']) ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= (int)$sales['month_count'] ?> vente<?= (int)$sales['month_count'] > 1 ? 's' : '' ?> · total <?= $montant($sales['total']) ?></p>
                    </div>

                    <!-- Ventes -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-receipt text-lg"></i></div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Ventes payées</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$sales['total_count'] ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= (int)$sales['paid_files'] ?> document<?= (int)$sales['paid_files'] > 1 ? 's' : '' ?> payant<?= (int)$sales['paid_files'] > 1 ? 's' : '' ?></p>
                    </div>

                    <!-- Abonnés newsletter -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-envelope-simple text-lg"></i></div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Abonnés newsletter</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$sales['subscribers'] ?></p>
                        <p class="text-xs text-gray-500 mt-2">adresses actives</p>
                    </div>
                    <?php endif; ?>

                    <!-- Articles -->
                    <?php if ($can_view_posts): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-newspaper text-lg"></i></div>
                            <?php if ((int)$stats['posts_month'] > 0): ?>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">+<?= (int)$stats['posts_month'] ?> ce mois</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Articles publiés</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$stats['posts_published'] ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= (int)$stats['posts'] ?> au total · <?= (int)$stats['posts_draft'] ?> brouillon<?= (int)$stats['posts_draft'] > 1 ? 's' : '' ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Vues -->
                    <?php if ($can_view_posts): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-eye text-lg"></i></div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Vues totales</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format((int)$stats['views']) ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= number_format((int)$stats['likes']) ?> j'aime</p>
                    </div>
                    <?php endif; ?>

                    <!-- Commentaires -->
                    <?php if ($can_view_comments): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-chats text-lg"></i></div>
                            <?php if ((int)$stats['comments_pending'] > 0): ?>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-50 text-amber-700"><?= (int)$stats['comments_pending'] ?> en attente</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Commentaires</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= number_format((int)$stats['comments']) ?></p>
                        <p class="text-xs text-gray-500 mt-2">+<?= (int)$stats['comments_month'] ?> ce mois</p>
                    </div>
                    <?php endif; ?>

                    <!-- Fichiers -->
                    <?php if ($is_full_admin): ?>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-700 flex items-center justify-center"><i class="ph ph-file-text text-lg"></i></div>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Documents</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int)$stats['files'] ?></p>
                        <p class="text-xs text-gray-500 mt-2"><?= (int)$sales['paid_files'] ?> payant<?= (int)$sales['paid_files'] > 1 ? 's' : '' ?> · <?= (int)$stats['files'] - (int)$sales['paid_files'] ?> gratuit<?= ((int)$stats['files'] - (int)$sales['paid_files']) > 1 ? 's' : '' ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Derniers articles + colonne droite -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                    <!-- Derniers articles -->
                    <?php if ($can_view_posts): ?>
                    <section class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Derniers articles</h2>
                            <a href="manage_posts.php" class="text-xs text-primary hover:underline font-semibold">Voir tout</a>
                        </div>

                        <?php if (!$latest_posts): ?>
                        <div class="px-6 py-12 text-center text-sm text-gray-500">
                            <i class="ph ph-newspaper text-3xl text-gray-300 block mb-3"></i>
                            Aucun article pour l'instant.
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-6 py-3">Titre</th>
                                        <th class="px-6 py-3">Statut</th>
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($latest_posts as $post): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3.5">
                                            <a href="../public/post.php?id=<?= (int)$post['id_post'] ?>" target="_blank" rel="noopener" class="text-sm font-medium text-gray-900 hover:text-primary"><?= htmlspecialchars(truncate($post['title'], 48)) ?></a>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?><?= $post['category_name'] ? ' · ' . htmlspecialchars($post['category_name']) : '' ?></p>
                                        </td>
                                        <td class="px-6 py-3.5">
                                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $status_styles[$post['status']] ?? 'bg-gray-100 text-gray-600' ?>"><?= $status_labels[$post['status']] ?? htmlspecialchars($post['status']) ?></span>
                                        </td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500 whitespace-nowrap"><?= format_date($post['created_at']) ?></td>
                                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                            <a href="edit_post.php?id=<?= (int)$post['id_post'] ?>" class="text-gray-400 hover:text-primary transition mx-1" aria-label="Modifier l'article"><i class="ph ph-pencil"></i></a>
                                            <?php if (has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id']): ?>
                                            <a href="delete_post.php?id=<?= (int)$post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="text-gray-400 hover:text-red-500 transition mx-1" aria-label="Supprimer l'article" onclick="return confirm('Supprimer cet article ?')"><i class="ph ph-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>

                    <!-- Colonne droite -->
                    <div class="space-y-6">
                        <!-- À traiter -->
                        <section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="text-sm font-bold text-gray-900">À traiter</h2>
                            </div>
                            <?php if (!$tasks): ?>
                            <div class="px-6 py-8 text-center">
                                <i class="ph ph-check-circle text-2xl text-emerald-500 block mb-2"></i>
                                <p class="text-sm text-gray-500">Rien en attente. Tout est à jour.</p>
                            </div>
                            <?php else: ?>
                            <div class="divide-y divide-gray-100">
                                <?php foreach ($tasks as $task):
                                    $tones = [
                                        'amber' => 'bg-amber-50 text-amber-700',
                                        'gray'  => 'bg-gray-100 text-gray-700',
                                        'blue'  => 'bg-blue-50 text-blue-700',
                                    ];
                                ?>
                                <a href="<?= htmlspecialchars($task['href']) ?>" class="flex items-center gap-3 px-6 py-3.5 hover:bg-gray-50 transition">
                                    <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $tones[$task['tone']] ?? $tones['gray'] ?>"><i class="ph <?= htmlspecialchars($task['icon']) ?>"></i></span>
                                    <span class="text-sm text-gray-700 flex-1"><?= htmlspecialchars($task['label']) ?></span>
                                    <i class="ph ph-caret-right text-gray-400"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>

                        <!-- Dernières ventes (admin) -->
                        <?php if ($is_full_admin): ?>
                        <section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h2 class="text-sm font-bold text-gray-900">Dernières ventes</h2>
                                <a href="manage_purchases.php" class="text-xs text-primary hover:underline font-semibold">Voir tout</a>
                            </div>
                            <?php if (!$sales['recent']): ?>
                            <div class="px-6 py-8 text-center text-sm text-gray-500">
                                <i class="ph ph-receipt text-2xl text-gray-300 block mb-2"></i>
                                Aucune vente pour l'instant.
                            </div>
                            <?php else: ?>
                            <div class="divide-y divide-gray-100">
                                <?php foreach ($sales['recent'] as $sale): ?>
                                <div class="px-6 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($sale['buyer_name'] ?: 'Acheteur') ?></p>
                                        <p class="text-sm font-semibold text-gray-900 whitespace-nowrap"><?= $montant((float)$sale['amount']) ?></p>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($sale['file_name'] ?? 'Document') ?> · <?= $sale['paid_at'] ? format_date($sale['paid_at']) : '' ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Commentaires + articles populaires -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <?php if (count($recent_comments) > 0 && $can_view_comments): ?>
                    <section class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-900">Commentaires récents</h2>
                            <a href="manage_comments.php" class="text-xs text-primary hover:underline font-semibold">Modérer</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-6 py-3">Auteur</th>
                                        <th class="px-6 py-3">Commentaire</th>
                                        <th class="px-6 py-3">Article</th>
                                        <th class="px-6 py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($recent_comments as $c): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-3.5 text-sm font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($c['author_name'] ?? 'Visiteur') ?></td>
                                        <td class="px-6 py-3.5 text-sm text-gray-600 max-w-xs truncate"><?= htmlspecialchars(truncate($c['content'], 55)) ?></td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500"><?= htmlspecialchars(truncate($c['post_title'] ?? '—', 28)) ?></td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500 whitespace-nowrap"><?= format_date($c['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (count($popular_posts) > 0 && $can_view_posts): ?>
                    <section class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-sm font-bold text-gray-900">Les plus lus</h2>
                        </div>
                        <div class="p-3 space-y-1">
                            <?php foreach ($popular_posts as $i => $pp): ?>
                            <a href="../public/post.php?id=<?= (int)$pp['id_post'] ?>" target="_blank" rel="noopener" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition">
                                <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold flex items-center justify-center flex-shrink-0"><?= $i + 1 ?></span>
                                <span class="min-w-0 flex-1">
                                    <span class="text-sm font-medium text-gray-800 block truncate"><?= htmlspecialchars(truncate($pp['title'], 34)) ?></span>
                                    <span class="text-xs text-gray-500"><?= number_format((int)$pp['views']) ?> vues</span>
                                </span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</body>
</html>

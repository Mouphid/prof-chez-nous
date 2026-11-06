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

$can_view_posts    = has_permission('manage_posts');
$can_view_comments = has_permission('manage_comments');
$can_publish       = has_permission('publish_articles');

$is_full_admin = ($admin_role === 'admin');

// Un auteur ne voit que ses propres contenus.
$owner_sql = $admin_role === 'auteur' ? ' AND p.id_user = ' . (int)$_SESSION['admin_id'] : '';

$status_labels = [
    'published' => 'Publié',
    'draft'     => 'Brouillon',
    'scheduled' => 'Programmé',
];
$status_styles = [
    'published' => 'adm-badge adm-badge-green',
    'draft'     => 'adm-badge adm-badge-amber',
    'scheduled' => 'adm-badge adm-badge-blue',
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
    'month'              => 0,
    'month_count'        => 0,
    'prev_month'         => 0,
    'total'              => 0,
    'total_count'        => 0,
    'paid_files'         => 0,
    'subscribers'        => 0,
    'subscribers_month'  => 0,
    'recent'             => [],
    'recent_subscribers' => [],
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
            SELECT pu.buyer_name, pu.buyer_email, pu.amount, pu.paid_at,
                   f.file_name,
                   c.title AS course_title,
                   ci.title AS item_title
            FROM purchases pu
            LEFT JOIN files f ON f.id_file = pu.id_file
            LEFT JOIN courses c ON c.id_course = pu.id_course
            LEFT JOIN course_items ci ON ci.id_item = pu.id_item
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
        $sales['subscribers_month'] = (int)$pdo->query("
            SELECT COUNT(*) FROM newsletter_subscribers
            WHERE status='active' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->fetchColumn();
        $sales['recent_subscribers'] = $pdo->query("
            SELECT email, created_at FROM newsletter_subscribers
            WHERE status='active' ORDER BY created_at DESC LIMIT 3
        ")->fetchAll();
    } catch (PDOException $e) {
        error_log('Dashboard newsletter: ' . $e->getMessage());
    }
}

// ─── Listes ───
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

// ─── Série du graphique : ventes (admin) ou articles créés (auteur) ───
$chart_is_money = $is_full_admin;
$chart_title    = $is_full_admin ? 'Évolution des ventes' : 'Articles créés';
$chart_series   = ['7' => [], '30' => [], '12m' => []];

$jours_court = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
$mois_court  = [1 => 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

$chart_where = $admin_role === 'auteur' ? " AND id_user = " . (int)$_SESSION['admin_id'] : '';

if ($is_full_admin) {
    try {
        // 7 derniers jours
        $map7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $map7[date('Y-m-d', strtotime("-$i days"))] = 0.0;
        }
        $rows = $pdo->query("
            SELECT DATE(paid_at) d, SUM(amount) s FROM purchases
            WHERE status='paid' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(paid_at)
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map7[$r['d']])) $map7[$r['d']] = (float)$r['s'];
        }
        foreach ($map7 as $d => $v) {
            $chart_series['7'][] = [
                'label' => $jours_court[(int)date('w', strtotime($d))],
                'full'  => date('d/m/Y', strtotime($d)),
                'value' => $v,
            ];
        }

        // 30 derniers jours
        $map30 = [];
        for ($i = 29; $i >= 0; $i--) {
            $map30[date('Y-m-d', strtotime("-$i days"))] = 0.0;
        }
        $rows = $pdo->query("
            SELECT DATE(paid_at) d, SUM(amount) s FROM purchases
            WHERE status='paid' AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY DATE(paid_at)
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map30[$r['d']])) $map30[$r['d']] = (float)$r['s'];
        }
        foreach ($map30 as $d => $v) {
            $chart_series['30'][] = [
                'label' => date('d/m', strtotime($d)),
                'full'  => date('d/m/Y', strtotime($d)),
                'value' => $v,
            ];
        }

        // 12 derniers mois
        $map12 = [];
        for ($i = 11; $i >= 0; $i--) {
            $map12[date('Y-m', strtotime(date('Y-m-01') . " -$i months"))] = 0.0;
        }
        $rows = $pdo->query("
            SELECT DATE_FORMAT(paid_at, '%Y-%m') m, SUM(amount) s FROM purchases
            WHERE status='paid' AND paid_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
            GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map12[$r['m']])) $map12[$r['m']] = (float)$r['s'];
        }
        foreach ($map12 as $m => $v) {
            $chart_series['12m'][] = [
                'label' => $mois_court[(int)date('n', strtotime($m . '-01'))],
                'full'  => $m,
                'value' => $v,
            ];
        }
    } catch (PDOException $e) {
        error_log('Dashboard graphique ventes: ' . $e->getMessage());
    }
} else {
    try {
        // 7 derniers jours
        $map7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $map7[date('Y-m-d', strtotime("-$i days"))] = 0;
        }
        $rows = $pdo->query("
            SELECT DATE(created_at) d, COUNT(*) n FROM posts
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)$chart_where
            GROUP BY DATE(created_at)
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map7[$r['d']])) $map7[$r['d']] = (int)$r['n'];
        }
        foreach ($map7 as $d => $v) {
            $chart_series['7'][] = [
                'label' => $jours_court[(int)date('w', strtotime($d))],
                'full'  => date('d/m/Y', strtotime($d)),
                'value' => $v,
            ];
        }

        // 30 derniers jours
        $map30 = [];
        for ($i = 29; $i >= 0; $i--) {
            $map30[date('Y-m-d', strtotime("-$i days"))] = 0;
        }
        $rows = $pdo->query("
            SELECT DATE(created_at) d, COUNT(*) n FROM posts
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)$chart_where
            GROUP BY DATE(created_at)
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map30[$r['d']])) $map30[$r['d']] = (int)$r['n'];
        }
        foreach ($map30 as $d => $v) {
            $chart_series['30'][] = [
                'label' => date('d/m', strtotime($d)),
                'full'  => date('d/m/Y', strtotime($d)),
                'value' => $v,
            ];
        }

        // 12 derniers mois
        $map12 = [];
        for ($i = 11; $i >= 0; $i--) {
            $map12[date('Y-m', strtotime(date('Y-m-01') . " -$i months"))] = 0;
        }
        $rows = $pdo->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') m, COUNT(*) n FROM posts
            WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')$chart_where
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ")->fetchAll();
        foreach ($rows as $r) {
            if (isset($map12[$r['m']])) $map12[$r['m']] = (int)$r['n'];
        }
        foreach ($map12 as $m => $v) {
            $chart_series['12m'][] = [
                'label' => $mois_court[(int)date('n', strtotime($m . '-01'))],
                'full'  => $m,
                'value' => $v,
            ];
        }
    } catch (PDOException $e) {
        error_log('Dashboard graphique articles: ' . $e->getMessage());
    }
}

// ─── Résumé et date en français ───
$tasks = [];
if ($stats['comments_pending'] > 0) {
    $tasks[] = [
        'label' => $stats['comments_pending'] . ' commentaire' . ($stats['comments_pending'] > 1 ? 's' : '') . ' à valider',
        'href'  => 'manage_comments.php',
        'tone'  => 'adm-badge-amber',
        'icon'  => 'ph-chats-circle',
    ];
}
if ($stats['posts_draft'] > 0) {
    $tasks[] = [
        'label' => $stats['posts_draft'] . ' brouillon' . ($stats['posts_draft'] > 1 ? 's' : '') . ' en attente',
        'href'  => 'manage_posts.php',
        'tone'  => 'adm-badge-gray',
        'icon'  => 'ph-pencil-simple',
    ];
}
if ($stats['posts_scheduled'] > 0) {
    $tasks[] = [
        'label' => $stats['posts_scheduled'] . ' article' . ($stats['posts_scheduled'] > 1 ? 's' : '') . ' programmé' . ($stats['posts_scheduled'] > 1 ? 's' : ''),
        'href'  => 'manage_posts.php',
        'tone'  => 'adm-badge-blue',
        'icon'  => 'ph-calendar-check',
    ];
}

$jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$mois  = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$date_fr = ucfirst($jours[(int)date('w')]) . ' ' . (int)date('j') . ' ' . $mois[(int)date('n')] . ' ' . date('Y');

$montant      = fn(float $n): string => number_format($n, 0, ',', ' ') . ' FCFA';
$montant_court = fn(float $n): string => number_format($n, 0, ',', ' ');

// Initiales pour les avatars du flux d'activité
$initials_of = function (string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    if (str_contains($name, '@')) return mb_strtoupper(mb_substr($name, 0, 2));
    $parts = preg_split('/\s+/', $name) ?: [$name];
    $out = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (count($parts) > 1) $out .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    return $out;
};

// ─── Flux d'activité mélangé (commentaires, ventes, abonnés) ───
$activity = [];
foreach ($recent_comments as $c) {
    $is_pending = empty($c['status']) || $c['status'] === 'pending';
    $activity[] = [
        'name'  => $c['author_name'] ?: 'Visiteur',
        'text'  => ($is_pending ? 'a laissé un commentaire à valider' : 'a commenté') . ' « ' . truncate($c['post_title'] ?? 'un article', 36) . ' »',
        'date'  => $c['created_at'],
        'tone'  => $is_pending ? 'amber' : 'blue',
    ];
}
if ($is_full_admin) {
    foreach ($sales['recent'] as $s) {
        $label = $s['file_name']
            ?: ($s['course_title'] ? 'le cours « ' . $s['course_title'] . ' »'
                : ($s['item_title'] ? 'le chapitre « ' . $s['item_title'] . ' »' : 'un contenu'));
        $activity[] = [
            'name' => $s['buyer_name'] ?: 'Acheteur',
            'text' => 'a acheté ' . truncate($label, 40),
            'date' => $s['paid_at'],
            'tone' => 'green',
        ];
    }
    foreach ($sales['recent_subscribers'] as $s) {
        $activity[] = [
            'name' => $s['email'],
            'text' => "s'est abonné à la newsletter",
            'date' => $s['created_at'],
            'tone' => 'purple',
        ];
    }
}
usort($activity, fn($a, $b) => strtotime($b['date'] ?? '1970-01-01') <=> strtotime($a['date'] ?? '1970-01-01'));
$activity = array_slice($activity, 0, 6);

// Première série : évite un flash « vide » avant l'exécution du script
$chart_initial_has_data = false;
foreach ($chart_series['7'] as $point) {
    if ($point['value'] > 0) {
        $chart_initial_has_data = true;
        break;
    }
}

// ─── Top articles et répartition ───
$max_views = 0;
foreach ($popular_posts as $pp) {
    $max_views = max($max_views, (int)$pp['views']);
}

$total_posts   = max(1, (int)$stats['posts']);
$published_pct = (int)round(((int)$stats['posts_published'] / $total_posts) * 100);
$published_pct = max(0, min(100, $published_pct));
$draft_total   = (int)$stats['posts_draft'] + (int)$stats['posts_scheduled'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Tableau de bord'; $page_icon = 'ph-squares-four'; include "_topbar.php"; ?>

            <div class="p-6 sm:p-8">

                <!-- Bienvenue -->
                <section class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-7">
                    <div class="min-w-0">
                        <h2 class="adm-welcome-title">Bonjour, <?= htmlspecialchars($admin['name'] ?? 'Admin') ?></h2>
                        <p class="adm-welcome-date"><?= $date_fr ?> · <?= date('H:i') ?></p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="adm-tabs mr-1" role="tablist" aria-label="Période du graphique">
                            <button type="button" class="adm-tab is-active" data-chart-period="7" role="tab" aria-selected="true">7 jours</button>
                            <button type="button" class="adm-tab" data-chart-period="30" role="tab" aria-selected="false">30 jours</button>
                            <button type="button" class="adm-tab" data-chart-period="12m" role="tab" aria-selected="false">12 mois</button>
                        </div>
                        <a href="../public/index.php" target="_blank" rel="noopener" class="adm-btn adm-btn-ghost">
                            <i class="ph ph-arrow-square-out"></i> Voir le site
                        </a>
                        <?php if ($can_publish): ?>
                        <a href="add_post.php" class="adm-btn adm-btn-primary">
                            <i class="ph ph-plus"></i> Nouvel article
                        </a>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Indicateurs -->
                <section class="mb-7" aria-label="Indicateurs clés">
                    <div class="adm-kpi-grid <?= $is_full_admin ? '' : 'is-4' ?>">

                        <?php if ($is_full_admin): ?>
                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Revenus du mois</span>
                                <?php if ($sales_delta !== null): ?>
                                <span class="adm-badge <?= $sales_delta >= 0 ? 'adm-badge-green' : 'adm-badge-red' ?>">
                                    <i class="ph <?= $sales_delta >= 0 ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
                                    <?= ($sales_delta >= 0 ? '+' : '') . number_format($sales_delta, 0, ',', ' ') ?> %
                                </span>
                                <?php elseif ($sales['month'] > 0): ?>
                                <span class="adm-badge adm-badge-green">Nouveau</span>
                                <?php endif; ?>
                            </div>
                            <p class="adm-kpi-value"><?= $montant_court($sales['month']) ?> <small>FCFA</small></p>
                            <p class="adm-kpi-foot"><?= (int)$sales['month_count'] ?> vente<?= (int)$sales['month_count'] > 1 ? 's' : '' ?> · total <?= $montant($sales['total']) ?></p>
                        </div>

                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Ventes payées</span>
                                <?php if ((int)$sales['month_count'] > 0): ?>
                                <span class="adm-badge adm-badge-blue"><i class="ph ph-trend-up"></i> +<?= (int)$sales['month_count'] ?> ce mois</span>
                                <?php endif; ?>
                            </div>
                            <p class="adm-kpi-value"><?= number_format((int)$sales['total_count'], 0, ',', ' ') ?></p>
                            <p class="adm-kpi-foot"><?= (int)$sales['paid_files'] ?> document<?= (int)$sales['paid_files'] > 1 ? 's' : '' ?> payant<?= (int)$sales['paid_files'] > 1 ? 's' : '' ?></p>
                        </div>

                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Abonnés newsletter</span>
                                <?php if ((int)$sales['subscribers_month'] > 0): ?>
                                <span class="adm-badge adm-badge-green"><i class="ph ph-trend-up"></i> +<?= (int)$sales['subscribers_month'] ?> / 30 j</span>
                                <?php endif; ?>
                            </div>
                            <p class="adm-kpi-value"><?= number_format((int)$sales['subscribers'], 0, ',', ' ') ?></p>
                            <p class="adm-kpi-foot">adresse<?= (int)$sales['subscribers'] > 1 ? 's' : '' ?> active<?= (int)$sales['subscribers'] > 1 ? 's' : '' ?> · <a href="manage_newsletter.php" class="adm-link">Gérer</a></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($can_view_posts): ?>
                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Articles publiés</span>
                                <?php if ((int)$stats['posts_month'] > 0): ?>
                                <span class="adm-badge adm-badge-green"><i class="ph ph-trend-up"></i> +<?= (int)$stats['posts_month'] ?> nouveaux</span>
                                <?php endif; ?>
                            </div>
                            <p class="adm-kpi-value"><?= number_format((int)$stats['posts_published'], 0, ',', ' ') ?></p>
                            <p class="adm-kpi-foot"><?= (int)$stats['posts'] ?> au total · <?= (int)$stats['posts_draft'] ?> brouillon<?= (int)$stats['posts_draft'] > 1 ? 's' : '' ?></p>
                        </div>

                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Vues totales</span>
                                <span class="adm-badge adm-badge-gray"><i class="ph ph-heart"></i> <?= number_format((int)$stats['likes'], 0, ',', ' ') ?></span>
                            </div>
                            <p class="adm-kpi-value"><?= number_format((int)$stats['views'], 0, ',', ' ') ?></p>
                            <p class="adm-kpi-foot">depuis le lancement</p>
                        </div>
                        <?php endif; ?>

                        <?php if (!$is_full_admin && $can_view_comments): ?>
                        <div class="adm-card adm-kpi">
                            <div class="adm-kpi-top">
                                <span class="adm-kpi-label">Commentaires</span>
                                <?php if ((int)$stats['comments_month'] > 0): ?>
                                <span class="adm-badge adm-badge-blue">+<?= (int)$stats['comments_month'] ?> / 30 j</span>
                                <?php endif; ?>
                            </div>
                            <p class="adm-kpi-value"><?= number_format((int)$stats['comments'], 0, ',', ' ') ?></p>
                            <p class="adm-kpi-foot">sur vos articles</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Graphique + À traiter / Activité -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

                    <section class="adm-card lg:col-span-2 overflow-hidden">
                        <div class="adm-card-head flex-wrap">
                            <div class="min-w-0">
                                <h2 class="adm-card-title"><?= htmlspecialchars($chart_title) ?></h2>
                                <p class="adm-chart-total mt-1" id="salesTotal">—</p>
                                <p class="adm-chart-note" id="salesNote">sur les 7 derniers jours</p>
                            </div>
                            <span class="adm-badge adm-badge-gray" id="salesPeriodLabel">7 jours</span>
                        </div>
                        <div class="adm-card-body">
                            <div id="salesChartWrap" style="<?= $chart_initial_has_data ? '' : 'display:none' ?>">
                                <div class="adm-chart" id="salesChart" role="img" aria-label="Graphique de l'activité"></div>
                                <div class="adm-chart-x" id="salesX" aria-hidden="true"></div>
                            </div>
                            <div id="salesEmpty" class="adm-empty" style="<?= $chart_initial_has_data ? 'display:none' : '' ?>">
                                <i class="ph ph-chart-bar"></i>
                                <p>Aucune donnée sur cette période.<br>Les premiers chiffres apparaîtront ici.</p>
                            </div>
                        </div>
                    </section>

                    <div class="space-y-5">
                        <!-- À traiter -->
                        <section class="adm-card overflow-hidden">
                            <div class="adm-card-head">
                                <div>
                                    <h2 class="adm-card-title">À traiter</h2>
                                    <p class="adm-card-sub">Ce qui demande votre attention</p>
                                </div>
                                <?php if ($tasks): ?>
                                <span class="adm-badge adm-badge-amber"><?= count($tasks) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$tasks): ?>
                            <div class="adm-empty">
                                <i class="ph ph-check-circle" style="color:#12b76a"></i>
                                <p>Rien en attente.<br>Tout est à jour, bravo !</p>
                            </div>
                            <?php else: ?>
                            <div class="divide-y" style="border-color:var(--adm-border-soft)">
                                <?php foreach ($tasks as $task): ?>
                                <a href="<?= htmlspecialchars($task['href']) ?>" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                                    <span class="adm-avatar-sm <?= $task['tone'] === 'adm-badge-amber' ? 'adm-avatar-amber' : ($task['tone'] === 'adm-badge-blue' ? 'adm-avatar-blue' : 'adm-avatar-purple') ?>">
                                        <i class="ph <?= htmlspecialchars($task['icon']) ?>"></i>
                                    </span>
                                    <span class="text-sm text-gray-700 flex-1"><?= htmlspecialchars($task['label']) ?></span>
                                    <i class="ph ph-caret-right text-gray-300"></i>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>

                        <!-- Activité récente -->
                        <section class="adm-card overflow-hidden">
                            <div class="adm-card-head">
                                <div>
                                    <h2 class="adm-card-title">Activité récente</h2>
                                    <p class="adm-card-sub">Commentaires, ventes et abonnés</p>
                                </div>
                            </div>
                            <div class="px-5 py-4">
                                <?php if (!$activity): ?>
                                <div class="adm-empty">
                                    <i class="ph ph-pulse"></i>
                                    <p>L'activité apparaîtra ici dès la première visite.</p>
                                </div>
                                <?php else: ?>
                                <div class="adm-activity">
                                    <?php foreach ($activity as $item): ?>
                                    <div class="adm-activity-item">
                                        <span class="adm-avatar-sm adm-avatar-<?= htmlspecialchars($item['tone']) ?>"><?= htmlspecialchars($initials_of($item['name'])) ?></span>
                                        <div class="adm-activity-body">
                                            <p class="adm-activity-text"><strong><?= htmlspecialchars($item['name']) ?></strong> <?= htmlspecialchars($item['text']) ?></p>
                                            <p class="adm-activity-time"><?= format_date($item['date'] ?? '', 'd/m/Y') ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Derniers articles + top / répartition -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

                    <?php if ($can_view_posts): ?>
                    <section class="adm-card lg:col-span-2 overflow-hidden">
                        <div class="adm-card-head">
                            <div>
                                <h2 class="adm-card-title">Derniers articles</h2>
                                <p class="adm-card-sub">Les contenus les plus récents</p>
                            </div>
                            <a href="manage_posts.php" class="adm-link">Voir tout <i class="ph ph-arrow-right"></i></a>
                        </div>

                        <?php if (!$latest_posts): ?>
                        <div class="adm-empty">
                            <i class="ph ph-newspaper"></i>
                            <p>Aucun article pour l'instant.<br><a href="add_post.php">Écrire le premier article</a></p>
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="adm-table">
                                <thead>
                                    <tr>
                                        <th>Titre</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th style="text-align:right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_posts as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="../public/post.php?id=<?= (int)$post['id_post'] ?>" target="_blank" rel="noopener" class="text-sm font-medium text-gray-900 hover:text-primary"><?= htmlspecialchars(truncate($post['title'], 48)) ?></a>
                                            <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($post['author_name'] ?? 'Admin') ?><?= $post['category_name'] ? ' · ' . htmlspecialchars($post['category_name']) : '' ?></p>
                                        </td>
                                        <td>
                                            <span class="<?= $status_styles[$post['status']] ?? 'adm-badge adm-badge-gray' ?>"><?= $status_labels[$post['status']] ?? htmlspecialchars($post['status']) ?></span>
                                        </td>
                                        <td class="text-sm text-gray-500 whitespace-nowrap"><?= format_date($post['created_at']) ?></td>
                                        <td style="text-align:right;white-space:nowrap">
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

                    <div class="space-y-5">
                        <!-- Top articles -->
                        <section class="adm-card overflow-hidden">
                            <div class="adm-card-head">
                                <div>
                                    <h2 class="adm-card-title">Top articles</h2>
                                    <p class="adm-card-sub">Les plus consultés</p>
                                </div>
                            </div>
                            <div class="px-5 py-4">
                                <?php if (!$popular_posts): ?>
                                <div class="adm-empty">
                                    <i class="ph ph-eye"></i>
                                    <p>Pas encore de vues.</p>
                                </div>
                                <?php else: ?>
                                <div class="divide-y" style="border-color:var(--adm-border-soft)">
                                    <?php foreach ($popular_posts as $pp):
                                        $pct = $max_views > 0 ? max(4, (int)round(((int)$pp['views'] / $max_views) * 100)) : 0;
                                    ?>
                                    <a href="../public/post.php?id=<?= (int)$pp['id_post'] ?>" target="_blank" rel="noopener" class="block py-3 first:pt-0 last:pb-0 group">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-medium text-gray-800 truncate group-hover:text-primary transition"><?= htmlspecialchars(truncate($pp['title'], 36)) ?></span>
                                            <span class="text-xs text-gray-500 whitespace-nowrap"><?= number_format((int)$pp['views'], 0, ',', ' ') ?> vues</span>
                                        </div>
                                        <div class="adm-meter mt-2"><span style="width:<?= $pct ?>%"></span></div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Répartition -->
                        <section class="adm-card overflow-hidden">
                            <div class="adm-card-head">
                                <div>
                                    <h2 class="adm-card-title">Répartition</h2>
                                    <p class="adm-card-sub">Publiés et brouillons</p>
                                </div>
                            </div>
                            <div class="adm-card-body flex items-center gap-5">
                                <div class="adm-donut" style="background: conic-gradient(#2563eb 0 <?= $published_pct ?>%, #ece7e2 <?= $published_pct ?>% 100%);">
                                    <div class="adm-donut-center">
                                        <strong><?= $published_pct ?>%</strong>
                                        <span>publiés</span>
                                    </div>
                                </div>
                                <div class="adm-legend">
                                    <div class="adm-legend-row"><span class="adm-legend-dot" style="background:#2563eb"></span> Publiés <strong><?= (int)$stats['posts_published'] ?></strong></div>
                                    <div class="adm-legend-row"><span class="adm-legend-dot" style="background:#ece7e2"></span> Brouillons <strong><?= (int)$stats['posts_draft'] ?></strong></div>
                                    <div class="adm-legend-row"><span class="adm-legend-dot" style="background:#f59e0b"></span> Programmés <strong><?= (int)$stats['posts_scheduled'] ?></strong></div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <?php if ($is_full_admin): ?>
                <!-- Dernières ventes -->
                <section class="adm-card overflow-hidden">
                    <div class="adm-card-head">
                        <div>
                            <h2 class="adm-card-title">Dernières ventes</h2>
                            <p class="adm-card-sub">Les paiements les plus récents</p>
                        </div>
                        <a href="manage_purchases.php" class="adm-link">Voir tout <i class="ph ph-arrow-right"></i></a>
                    </div>
                    <?php if (!$sales['recent']): ?>
                    <div class="adm-empty">
                        <i class="ph ph-receipt"></i>
                        <p>Aucune vente pour l'instant.<br>Les achats de documents et de cours apparaîtront ici.</p>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th>Acheteur</th>
                                    <th>Contenu</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales['recent'] as $sale):
                                    $sale_label = $sale['file_name']
                                        ?: ($sale['course_title']
                                            ? 'Cours · ' . $sale['course_title']
                                            : ($sale['item_title'] ? 'Chapitre · ' . $sale['item_title'] : 'Contenu'));
                                ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="adm-avatar-sm adm-avatar-green"><?= htmlspecialchars($initials_of($sale['buyer_name'] ?: 'Acheteur')) ?></span>
                                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($sale['buyer_name'] ?: 'Acheteur') ?></span>
                                        </div>
                                    </td>
                                    <td class="text-sm text-gray-600"><?= htmlspecialchars(truncate($sale_label, 44)) ?></td>
                                    <td class="text-sm font-semibold text-gray-900 whitespace-nowrap"><?= $montant((float)$sale['amount']) ?></td>
                                    <td class="text-sm text-gray-500 whitespace-nowrap"><?= $sale['paid_at'] ? format_date($sale['paid_at']) : '' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
    (function () {
        const series = <?= json_encode($chart_series, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const isMoney = <?= $chart_is_money ? 'true' : 'false' ?>;
        const chart = document.getElementById('salesChart');
        const xaxis = document.getElementById('salesX');
        const wrap = document.getElementById('salesChartWrap');
        const empty = document.getElementById('salesEmpty');
        const totalEl = document.getElementById('salesTotal');
        const noteEl = document.getElementById('salesNote');
        const periodEl = document.getElementById('salesPeriodLabel');

        const fmt = (n) => new Intl.NumberFormat('fr-FR').format(Math.round(n));

        function render(key) {
            const data = series[key] || [];
            const max = data.reduce((m, d) => Math.max(m, d.value), 0);
            const total = data.reduce((s, d) => s + d.value, 0);

            totalEl.textContent = isMoney ? fmt(total) + ' FCFA' : fmt(total) + (total > 1 ? ' articles' : ' article');
            noteEl.textContent = key === '7' ? 'sur les 7 derniers jours'
                : key === '30' ? 'sur les 30 derniers jours'
                : 'sur les 12 derniers mois';
            periodEl.textContent = key === '7' ? '7 jours' : key === '30' ? '30 jours' : '12 mois';

            chart.innerHTML = '';
            xaxis.innerHTML = '';

            if (max <= 0) {
                wrap.style.display = 'none';
                empty.style.display = 'block';
                return;
            }
            wrap.style.display = 'block';
            empty.style.display = 'none';

            data.forEach(function (d, i) {
                const col = document.createElement('div');
                col.className = 'adm-chart-col';

                const bar = document.createElement('div');
                bar.className = 'adm-chart-bar';
                bar.style.height = Math.max(3, (d.value / max) * 100) + '%';
                bar.title = d.full + ' : ' + (isMoney ? fmt(d.value) + ' FCFA' : fmt(d.value));
                col.appendChild(bar);
                chart.appendChild(col);

                const show = key === '7' || key === '12m' || i % 5 === 0 || i === data.length - 1;
                const label = document.createElement('span');
                label.textContent = show ? d.label : '';
                xaxis.appendChild(label);
            });
        }

        document.querySelectorAll('[data-chart-period]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('[data-chart-period]').forEach(function (b) {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-selected', 'false');
                });
                btn.classList.add('is-active');
                btn.setAttribute('aria-selected', 'true');
                render(btn.dataset.chartPeriod);
            });
        });

        render('7');
    })();
    </script>
</body>
</html>

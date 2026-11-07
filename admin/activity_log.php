<?php
/**
 * Journal des actions de l'administration.
 *
 * Affiche qui a créé, modifié ou supprimé un contenu, envoyé une newsletter,
 * changé un réglage, etc. Les entrées sont ajoutées par admin_activity().
 */
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$action_labels = [
    'create'   => ['Création',    'adm-badge-green'],
    'update'   => ['Modification','adm-badge-blue'],
    'delete'   => ['Suppression', 'adm-badge-red'],
    'login'    => ['Connexion',   'adm-badge-gray'],
    'logout'   => ['Déconnexion', 'adm-badge-gray'],
    'send'     => ['Envoi',       'adm-badge-amber'],
    'resend'   => ['Renvoi',      'adm-badge-amber'],
    'moderate' => ['Modération',  'adm-badge-blue'],
    'password' => ['Mot de passe','adm-badge-amber'],
];

$target_labels = [
    'post'       => 'Article',
    'course'     => 'Cours',
    'item'       => 'Chapitre',
    'file'       => 'Document',
    'comment'    => 'Commentaire',
    'user'       => 'Utilisateur',
    'newsletter' => 'Newsletter',
    'purchase'   => 'Vente',
    'settings'   => 'Réglages',
    'category'   => 'Catégorie',
];

$action_filter = (string)($_GET['action'] ?? 'all');
if ($action_filter !== 'all' && !isset($action_labels[$action_filter])) {
    $action_filter = 'all';
}

$target_filter = (string)($_GET['target'] ?? 'all');
if ($target_filter !== 'all' && !isset($target_labels[$target_filter])) {
    $target_filter = 'all';
}

$user_filter = trim((string)($_GET['user'] ?? ''));
if (mb_strlen($user_filter) > 100) {
    $user_filter = mb_substr($user_filter, 0, 100);
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}

$conds = [];
$params = [];
if ($action_filter !== 'all') {
    $conds[] = 'action = ?';
    $params[] = $action_filter;
}
if ($target_filter !== 'all') {
    $conds[] = 'target_type = ?';
    $params[] = $target_filter;
}
if ($user_filter !== '') {
    $conds[] = 'user_name = ?';
    $params[] = $user_filter;
}
if ($q !== '') {
    $conds[] = '(details LIKE ? OR user_name LIKE ? OR ip LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(created_at >= CURDATE()) AS today,
        SUM(created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS week
    FROM admin_activity_log
")->fetch();

$per_page = 50;
$page = max(1, (int)($_GET['page'] ?? 1));

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_activity_log $where");
$count_stmt->execute($params);
$total_filtered = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total_filtered / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("
    SELECT *
    FROM admin_activity_log
    $where
    ORDER BY created_at DESC, id_log DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$entries = $stmt->fetchAll();

$users = $pdo->query("
    SELECT DISTINCT user_name FROM admin_activity_log
    WHERE user_name <> ''
    ORDER BY user_name
")->fetchAll(PDO::FETCH_COLUMN);

$is_filtered = $action_filter !== 'all' || $target_filter !== 'all' || $user_filter !== '' || $q !== '';

$lien_page = function (int $n) use ($action_filter, $target_filter, $user_filter, $q) {
    return 'activity_log.php?' . http_build_query(array_filter([
        'action' => $action_filter,
        'target' => $target_filter,
        'user'   => $user_filter,
        'q'      => $q,
        'page'   => $n,
    ], fn($v) => $v !== '' && $v !== 'all' && $v !== 1));
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal des actions - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Journal des actions';
            $page_subtitle = 'Qui a fait quoi dans l\'administration';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Actions enregistrées</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-list-checks"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['total'] ?></p>
                    <p class="adm-kpi-foot">depuis l'ouverture du journal</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Aujourd'hui</span>
                        <span class="adm-kpi-icon adm-kpi-icon-green"><i class="ph ph-calendar-check"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['today'] ?></p>
                    <p class="adm-kpi-foot">actions effectuées ce jour</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">7 derniers jours</span>
                        <span class="adm-kpi-icon adm-kpi-icon-amber"><i class="ph ph-clock-counter-clockwise"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['week'] ?></p>
                    <p class="adm-kpi-foot">activité de la semaine</p>
                </div>
            </div>

            <div class="adm-toolbar">
                <form method="get" class="flex flex-wrap items-center gap-2" role="search">
                    <label class="sr-only" for="log-action">Type d'action</label>
                    <select id="log-action" name="action" class="adm-input" style="width:auto" onchange="this.form.submit()">
                        <option value="all">Toutes les actions</option>
                        <?php foreach ($action_labels as $k => $meta): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $action_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($meta[0]) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="sr-only" for="log-target">Type de contenu</label>
                    <select id="log-target" name="target" class="adm-input" style="width:auto" onchange="this.form.submit()">
                        <option value="all">Tous les contenus</option>
                        <?php foreach ($target_labels as $k => $label): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $target_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="sr-only" for="log-user">Utilisateur</label>
                    <select id="log-user" name="user" class="adm-input" style="width:auto" onchange="this.form.submit()">
                        <option value="">Tous les utilisateurs</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u) ?>" <?= $user_filter === $u ? 'selected' : '' ?>><?= htmlspecialchars($u) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="adm-toolbar-spacer"></div>

                    <div class="adm-search">
                        <i class="ph ph-magnifying-glass"></i>
                        <label class="sr-only" for="log-search">Rechercher dans le journal</label>
                        <input type="search" id="log-search" name="q" value="<?= htmlspecialchars($q) ?>"
                               placeholder="Détail, utilisateur ou IP…" autocomplete="off"
                               oninput="debounceLogSearch(this.form)">
                    </div>
                </form>
            </div>

            <?php if ($is_filtered): ?>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <p class="text-sm text-gray-600">
                    <?= $total_filtered ?> résultat<?= $total_filtered > 1 ? 's' : '' ?>
                    <?php if ($q !== ''): ?>pour « <strong><?= htmlspecialchars($q) ?></strong> »<?php endif; ?>
                </p>
                <a href="activity_log.php" class="adm-link"><i class="ph ph-x"></i> Effacer les filtres</a>
            </div>
            <?php endif; ?>

            <div class="adm-card overflow-hidden">
                <?php if (!$entries): ?>
                <div class="adm-empty">
                    <i class="ph ph-clock-counter-clockwise"></i>
                    <p class="adm-empty-title"><?= $is_filtered ? 'Aucune action ne correspond' : 'Le journal est vide' ?></p>
                    <p class="adm-empty-note">
                        <?= $is_filtered
                            ? 'Essayez un autre filtre ou revenez à tout le journal.'
                            : 'Les prochaines actions effectuées dans l\'administration apparaîtront ici.' ?>
                    </p>
                    <?php if ($is_filtered): ?>
                    <a href="activity_log.php" class="adm-btn adm-btn-secondary"><i class="ph ph-arrow-left"></i> Tout le journal</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Contenu</th>
                                <th>Détail</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $e):
                                $meta = $action_labels[$e['action']] ?? [ucfirst((string)$e['action']), 'adm-badge-gray'];
                                $cible = $target_labels[$e['target_type']] ?? ($e['target_type'] ? ucfirst((string)$e['target_type']) : '—');
                            ?>
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($e['created_at'])) ?></td>
                                <td class="whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($e['user_name']) ?></span>
                                </td>
                                <td><span class="adm-badge <?= $meta[1] ?>"><?= htmlspecialchars($meta[0]) ?></span></td>
                                <td class="whitespace-nowrap">
                                    <?= htmlspecialchars($cible) ?>
                                    <?php if (!empty($e['target_id'])): ?>
                                    <span class="text-xs text-gray-400">#<?= (int)$e['target_id'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:28rem">
                                    <span class="text-sm text-gray-700"><?= htmlspecialchars((string)($e['details'] ?? '')) ?></span>
                                </td>
                                <td class="text-xs text-gray-400 whitespace-nowrap" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace">
                                    <?= htmlspecialchars((string)($e['ip'] ?? '')) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav class="flex items-center justify-between gap-3 px-4 py-3 border-t border-gray-100" aria-label="Pagination du journal">
                    <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($lien_page($page - 1)) ?>" class="adm-btn adm-btn-secondary"><i class="ph ph-caret-left"></i> Précédent</a>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>
                    <span class="text-sm text-gray-500">Page <?= $page ?> sur <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                    <a href="<?= htmlspecialchars($lien_page($page + 1)) ?>" class="adm-btn adm-btn-secondary">Suivant <i class="ph ph-caret-right"></i></a>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            </div>
        </main>
    </div>

    <script>
    let logSearchTimer = null;
    function debounceLogSearch(form) {
        clearTimeout(logSearchTimer);
        logSearchTimer = setTimeout(function () { form.submit(); }, 450);
    }
    </script>
</body>
</html>

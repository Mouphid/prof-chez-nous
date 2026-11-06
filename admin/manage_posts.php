<?php
require_once "config_admin.php";

if(!has_permission('manage_posts')){
    header("Location: dashboard.php");
    exit;
}

// ─── Actions groupées (publier, brouillon, supprimer) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = (string) $_POST['bulk_action'];
    $ids = array_values(array_unique(array_map('intval', (array) ($_POST['ids'] ?? []))));
    $ids = array_values(array_filter($ids, fn($id) => $id > 0));

    $retour_qs = array_filter([
        'q'        => trim((string) ($_POST['q'] ?? '')),
        'status'   => (string) ($_POST['status_filter'] ?? 'all'),
        'category' => (string) ($_POST['category_filter'] ?? ''),
        'sort'     => (string) ($_POST['sort_filter'] ?? 'recent'),
        'page'     => (string) ($_POST['page_filter'] ?? '1'),
    ], fn($v) => $v !== '' && $v !== 'all');

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token de sécurité invalide. Rechargez la page.');
    } elseif (!$ids) {
        flash('error', 'Sélectionnez au moins un article.');
    } elseif (!in_array($action, ['publish', 'draft', 'delete'], true)) {
        flash('error', 'Action groupée inconnue.');
    } else {
        // Un auteur ne peut agir que sur ses propres articles.
        $scope = '';
        $scope_params = $ids;
        if ($_SESSION['admin_role'] !== 'admin') {
            $scope = " AND id_user = ?";
            $scope_params[] = $_SESSION['admin_id'];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id_post FROM posts WHERE id_post IN ($placeholders)$scope");
        $stmt->execute($scope_params);
        $allowed = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

        if (!$allowed) {
            flash('error', "Vous n'avez pas la permission de modifier ces articles.");
        } else {
            $in = implode(',', array_fill(0, count($allowed), '?'));
            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM posts WHERE id_post IN ($in)")->execute($allowed);
                $n = count($allowed);
                flash('success', $n . ' article' . ($n > 1 ? 's supprimés' : ' supprimé') . '.');
            } elseif ($action === 'publish') {
                if (!has_permission('publish_articles')) {
                    flash('error', "Vous n'avez pas la permission de publier des articles.");
                } else {
                    $pdo->prepare("UPDATE posts SET status = 'published', published_at = COALESCE(published_at, NOW()) WHERE id_post IN ($in)")->execute($allowed);
                    $n = count($allowed);
                    flash('success', $n . ' article' . ($n > 1 ? 's publiés' : ' publié') . '.');
                }
            } else {
                $pdo->prepare("UPDATE posts SET status = 'draft' WHERE id_post IN ($in)")->execute($allowed);
                $n = count($allowed);
                flash('success', $n . ' article' . ($n > 1 ? 's passés' : ' passé') . ' en brouillon.');
            }
        }
    }

    $back = 'manage_posts.php' . ($retour_qs ? '?' . http_build_query($retour_qs) : '');
    header('Location: ' . $back);
    exit;
}

// ─── Filtres ───
$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}

$status_filter = (string)($_GET['status'] ?? 'all');
if (!in_array($status_filter, ['all', 'published', 'draft', 'scheduled'], true)) {
    $status_filter = 'all';
}

$cat_filter = (int)($_GET['category'] ?? 0);
$sort_filter = (string)($_GET['sort'] ?? 'recent');
$sorts = [
    'recent' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    'title'  => 'p.title ASC',
    'views'  => 'p.views DESC, p.created_at DESC',
];
if (!isset($sorts[$sort_filter])) {
    $sort_filter = 'recent';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

// ─── Compteurs par statut (périmètre de l'utilisateur) ───
$count_sql = "SELECT status, COUNT(*) AS n FROM posts p WHERE 1=1";
$count_params = [];
if ($_SESSION['admin_role'] === 'auteur') {
    $count_sql .= " AND p.id_user = ?";
    $count_params[] = $_SESSION['admin_id'];
}
$count_sql .= " GROUP BY status";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($count_params);
$status_counts = ['all' => 0, 'published' => 0, 'draft' => 0, 'scheduled' => 0];
foreach ($stmt->fetchAll() as $row) {
    $key = (string) $row['status'];
    if (!isset($status_counts[$key])) {
        $status_counts[$key] = 0;
    }
    $status_counts[$key] += (int) $row['n'];
    $status_counts['all'] += (int) $row['n'];
}

// ─── Liste filtrée ───
$where = ['1=1'];
$params = [];

if ($_SESSION['admin_role'] === 'auteur') {
    $where[] = 'p.id_user = ?';
    $params[] = $_SESSION['admin_id'];
}
if ($status_filter !== 'all') {
    $where[] = 'p.status = ?';
    $params[] = $status_filter;
}
if ($cat_filter > 0) {
    $where[] = 'p.id_category = ?';
    $params[] = $cat_filter;
}
if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.content LIKE ? OR u.name LIKE ? OR c.name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$where_sql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM posts p
    LEFT JOIN users u ON p.id_user = u.id_user
    LEFT JOIN categories c ON p.id_category = c.id_category
    WHERE $where_sql
");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$total_pages = max(1, (int) ceil($total / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("
    SELECT p.*, u.name AS author_name, c.name AS category_name
    FROM posts p
    LEFT JOIN users u ON p.id_user = u.id_user
    LEFT JOIN categories c ON p.id_category = c.id_category
    WHERE $where_sql
    ORDER BY {$sorts[$sort_filter]}
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT id_category, name FROM categories ORDER BY name ASC")->fetchAll();

// ─── Liens qui conservent les filtres ───
$base_qs = array_filter([
    'q'        => $q,
    'status'   => $status_filter !== 'all' ? $status_filter : '',
    'category' => $cat_filter > 0 ? (string) $cat_filter : '',
    'sort'     => $sort_filter !== 'recent' ? $sort_filter : '',
], fn($v) => $v !== '');

$lien = fn(array $extra = []) => 'manage_posts.php?' . http_build_query(array_merge($base_qs, $extra));

$initials_of = function (string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name) ?: [$name];
    $out = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $out .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    }
    return $out;
};

$status_meta = [
    'published' => ['label' => 'Publié',    'dot' => 'is-green', 'badge' => 'adm-badge-green'],
    'draft'     => ['label' => 'Brouillon', 'dot' => 'is-amber', 'badge' => 'adm-badge-amber'],
    'scheduled' => ['label' => 'Programmé', 'dot' => 'is-blue',  'badge' => 'adm-badge-blue'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Articles';
            $page_subtitle = 'Publications, brouillons et articles programmés';
            $page_search = true;
            $page_action = has_permission('publish_articles')
                ? '<a href="add_post.php" class="adm-btn adm-btn-primary"><i class="ph ph-plus"></i><span class="hidden sm:inline">Nouvel article</span></a>'
                : '';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if (hasFlash('success')): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars(flash('success')) ?></span></div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars(flash('error')) ?></span></div>
            <?php endif; ?>

            <form class="adm-search lg:hidden mb-4" action="manage_posts.php" method="get" role="search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un article…" aria-label="Rechercher dans les articles">
            </form>

            <div class="adm-toolbar">
                <nav class="adm-segmented" aria-label="Filtrer par statut">
                    <?php
                    $tabs = [
                        'all'       => 'Tous',
                        'published' => 'Publiés',
                        'draft'     => 'Brouillons',
                        'scheduled' => 'Programmés',
                    ];
                    foreach ($tabs as $key => $label): ?>
                    <a href="<?= htmlspecialchars($lien(['status' => $key === 'all' ? '' : $key, 'page' => ''])) ?>"
                       class="<?= $status_filter === $key ? 'is-active' : '' ?>">
                        <?= $label ?> <span class="text-gray-400"><?= (int)($status_counts[$key] ?? 0) ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>

                <div class="adm-toolbar-spacer"></div>

                <form method="get" class="flex flex-wrap items-center gap-2" aria-label="Filtrer et trier les articles">
                    <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <label class="sr-only" for="filter-category">Catégorie</label>
                    <select id="filter-category" name="category" class="adm-select adm-btn-match" style="width:auto" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id_category'] ?>" <?= $cat_filter === (int)$cat['id_category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="sr-only" for="filter-sort">Trier</label>
                    <select id="filter-sort" name="sort" class="adm-select adm-btn-match" style="width:auto" onchange="this.form.submit()">
                        <option value="recent" <?= $sort_filter === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                        <option value="oldest" <?= $sort_filter === 'oldest' ? 'selected' : '' ?>>Plus anciens</option>
                        <option value="title" <?= $sort_filter === 'title' ? 'selected' : '' ?>>Titre A → Z</option>
                        <option value="views" <?= $sort_filter === 'views' ? 'selected' : '' ?>>Plus vus</option>
                    </select>
                </form>
            </div>

            <?php if ($q !== '' || $cat_filter > 0 || $status_filter !== 'all'): ?>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <p class="text-sm text-gray-600">
                    <?= $total ?> résultat<?= $total > 1 ? 's' : '' ?>
                    <?php if ($q !== ''): ?>pour « <strong><?= htmlspecialchars($q) ?></strong> »<?php endif; ?>
                </p>
                <a href="manage_posts.php" class="adm-link"><i class="ph ph-x"></i> Effacer les filtres</a>
            </div>
            <?php endif; ?>

            <form method="post" id="bulkForm">
                <?= csrf_field() ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
                <input type="hidden" name="category_filter" value="<?= $cat_filter > 0 ? $cat_filter : '' ?>">
                <input type="hidden" name="sort_filter" value="<?= htmlspecialchars($sort_filter) ?>">
                <input type="hidden" name="page_filter" value="<?= $page ?>">

                <div class="adm-bulkbar" id="bulkBar">
                    <i class="ph ph-check-square-offset"></i>
                    <span><strong id="bulkCount">0</strong> article(s) sélectionné(s)</span>
                    <div class="flex flex-wrap items-center gap-2 ml-auto">
                        <?php if (has_permission('publish_articles')): ?>
                        <button type="submit" name="bulk_action" value="publish" class="adm-btn adm-btn-secondary adm-btn-sm">
                            <i class="ph ph-eye"></i> Publier
                        </button>
                        <button type="submit" name="bulk_action" value="draft" class="adm-btn adm-btn-secondary adm-btn-sm">
                            <i class="ph ph-pencil-simple"></i> Brouillon
                        </button>
                        <?php endif; ?>
                        <button type="submit" name="bulk_action" value="delete" class="adm-btn adm-btn-danger adm-btn-sm"
                                onclick="return confirm('Supprimer les articles sélectionnés ? Cette action est définitive.');">
                            <i class="ph ph-trash"></i> Supprimer
                        </button>
                    </div>
                </div>

                <div class="adm-card overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="adm-table">
                            <thead>
                                <tr>
                                    <th class="adm-col-check">
                                        <input type="checkbox" class="adm-checkbox" id="checkAll" aria-label="Tout sélectionner">
                                    </th>
                                    <th>Article</th>
                                    <th>Auteur</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Vues</th>
                                    <th class="adm-col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($posts as $post):
                                    $meta = $status_meta[$post['status']] ?? ['label' => ucfirst((string)$post['status']), 'dot' => 'is-gray', 'badge' => 'adm-badge-gray'];
                                    $can_edit = $_SESSION['admin_role'] === 'admin' || $post['id_user'] == $_SESSION['admin_id'];
                                    $can_delete = has_permission('delete_any_post') || $post['id_user'] == $_SESSION['admin_id'];
                                    $date = $post['status'] === 'scheduled' && !empty($post['published_at'])
                                        ? date('d/m/Y H:i', strtotime($post['published_at']))
                                        : date('d/m/Y', strtotime($post['created_at']));
                                ?>
                                <tr data-row>
                                    <td class="adm-col-check">
                                        <input type="checkbox" class="adm-checkbox row-check" name="ids[]" value="<?= (int)$post['id_post'] ?>" aria-label="Sélectionner « <?= htmlspecialchars($post['title'], ENT_QUOTES) ?> »">
                                    </td>
                                    <td style="max-width:22rem">
                                        <a href="../public/post.php?id=<?= (int)$post['id_post'] ?>" target="_blank" rel="noopener" class="adm-table-title" title="<?= htmlspecialchars($post['title']) ?>">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="adm-avatar-xs"><?= htmlspecialchars($initials_of((string)($post['author_name'] ?? 'Anonyme'))) ?></span>
                                            <span class="text-sm text-gray-700 whitespace-nowrap"><?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="adm-badge adm-badge-gray"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                                    </td>
                                    <td>
                                        <span class="adm-status <?= $meta['dot'] ?>"><span class="adm-status-dot"></span><?= $meta['label'] ?></span>
                                    </td>
                                    <td class="text-sm text-gray-500 whitespace-nowrap"><?= $date ?></td>
                                    <td class="text-sm text-gray-600"><?= (int)($post['views'] ?? 0) ?></td>
                                    <td class="adm-col-actions">
                                        <a href="../public/post.php?id=<?= (int)$post['id_post'] ?>" target="_blank" rel="noopener" class="adm-action" aria-label="Voir l'article" title="Voir sur le site">
                                            <i class="ph ph-arrow-square-out"></i>
                                        </a>
                                        <?php if ($can_edit): ?>
                                        <a href="edit_post.php?id=<?= (int)$post['id_post'] ?>" class="adm-action adm-action-primary" aria-label="Modifier l'article" title="Modifier">
                                            <i class="ph ph-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($can_delete): ?>
                                        <a href="delete_post.php?id=<?= (int)$post['id_post'] ?>&csrf_token=<?= csrf_token() ?>" class="adm-action adm-action-danger" aria-label="Supprimer l'article" title="Supprimer" onclick="return confirm('Supprimer cet article ?');">
                                            <i class="ph ph-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (count($posts) === 0): ?>
                    <div class="adm-empty">
                        <i class="ph <?= ($q !== '' || $cat_filter > 0 || $status_filter !== 'all') ? 'ph-magnifying-glass' : 'ph-newspaper' ?>"></i>
                        <p class="adm-empty-title"><?= ($q !== '' || $cat_filter > 0 || $status_filter !== 'all') ? 'Aucun article ne correspond' : 'Aucun article pour le moment' ?></p>
                        <p class="adm-empty-note">
                            <?= ($q !== '' || $cat_filter > 0 || $status_filter !== 'all')
                                ? 'Essayez un autre mot-clé ou retirez les filtres.'
                                : 'Écrivez votre premier article pour alimenter le site.' ?>
                        </p>
                        <?php if (has_permission('publish_articles') && $q === '' && $cat_filter === 0 && $status_filter === 'all'): ?>
                        <a href="add_post.php" class="adm-btn adm-btn-primary"><i class="ph ph-plus"></i> Nouvel article</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($total_pages > 1): ?>
            <div class="adm-pager">
                <p class="adm-pager-info">
                    <?= $offset + 1 ?>–<?= min($offset + $per_page, $total) ?> sur <?= $total ?> article<?= $total > 1 ? 's' : '' ?>
                </p>
                <div class="adm-pager-links">
                    <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page - 1])) ?>" aria-label="Page précédente">←</a>
                    <?php else: ?>
                    <span class="is-disabled">←</span>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $start + 4);
                    $start = max(1, $end - 4);
                    for ($p = $start; $p <= $end; $p++): ?>
                        <?php if ($p === $page): ?>
                        <span class="is-current" aria-current="page"><?= $p ?></span>
                        <?php else: ?>
                        <a href="<?= htmlspecialchars($lien(['page' => $p])) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page + 1])) ?>" aria-label="Page suivante">→</a>
                    <?php else: ?>
                    <span class="is-disabled">→</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
    (function () {
        const form = document.getElementById('bulkForm');
        const bar = document.getElementById('bulkBar');
        const countEl = document.getElementById('bulkCount');
        const checkAll = document.getElementById('checkAll');
        if (!form || !bar) return;

        const boxes = Array.from(form.querySelectorAll('.row-check'));

        function sync() {
            const checked = boxes.filter(function (b) { return b.checked; });
            countEl.textContent = checked.length;
            bar.style.display = checked.length ? 'flex' : 'none';
            boxes.forEach(function (b) {
                const row = b.closest('tr');
                if (row) row.classList.toggle('is-selected', b.checked);
            });
            if (checkAll) {
                checkAll.checked = checked.length > 0 && checked.length === boxes.length;
                checkAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function () {
                boxes.forEach(function (b) { b.checked = checkAll.checked; });
                sync();
            });
        }
        boxes.forEach(function (b) { b.addEventListener('change', sync); });

        form.addEventListener('submit', function (e) {
            const submitter = e.submitter;
            if (submitter && submitter.name === 'bulk_action' && boxes.filter(function (b) { return b.checked; }).length === 0) {
                e.preventDefault();
                alert('Sélectionnez au moins un article.');
            }
        });

        sync();
    })();
    </script>
</body>
</html>

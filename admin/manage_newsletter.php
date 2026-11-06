<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// ─── Changer le statut d'un abonné (désinscrire ou réactiver) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_subscriber') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token de sécurité invalide. Rechargez la page.');
    } else {
        $id = (int)($_POST['id_subscriber'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE id_subscriber = ? LIMIT 1");
        $stmt->execute([$id]);
        $abonne = $stmt->fetch();

        if (!$abonne) {
            flash('error', 'Abonné introuvable.');
        } elseif ($abonne['status'] === 'active') {
            $pdo->prepare("
                UPDATE newsletter_subscribers
                SET status = 'unsubscribed', unsubscribed_at = COALESCE(unsubscribed_at, NOW())
                WHERE id_subscriber = ?
            ")->execute([$id]);
            flash('success', 'Abonné désinscrit.');
        } else {
            $token = (string)($abonne['unsubscribe_token'] ?? '');
            if ($token === '') {
                $token = bin2hex(random_bytes(32));
            }
            $pdo->prepare("
                UPDATE newsletter_subscribers
                SET status = 'active', unsubscribed_at = NULL, unsubscribe_token = ?
                WHERE id_subscriber = ?
            ")->execute([$token, $id]);
            flash('success', 'Abonné réactivé.');
        }
    }

    $retour = 'manage_newsletter.php';
    $qs = array_filter([
        'status' => $_POST['status_filter'] ?? '',
        'q'      => $_POST['q'] ?? '',
        'page'   => $_POST['page'] ?? '',
    ], fn($v) => $v !== '' && $v !== 'all');
    if ($qs) {
        $retour .= '?' . http_build_query($qs);
    }
    header('Location: ' . $retour);
    exit;
}

// ─── Filtres ───
$status_filter = $_GET['status'] ?? 'all';
if (!in_array($status_filter, ['all', 'active', 'unsubscribed'], true)) {
    $status_filter = 'all';
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}

$page = max(1, (int)($_GET['page'] ?? 1));
$par_page = 50;

$conds = [];
$params = [];
if ($status_filter !== 'all') {
    $conds[] = 'status = ?';
    $params[] = $status_filter;
}
if ($q !== '') {
    $conds[] = 'email LIKE ?';
    $params[] = '%' . $q . '%';
}
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_subscribers $where");
$stmt->execute($params);
$total_filtre = (int)$stmt->fetchColumn();

$total_pages = max(1, (int)ceil($total_filtre / $par_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $par_page;

$stmt = $pdo->prepare("
    SELECT * FROM newsletter_subscribers
    $where
    ORDER BY created_at DESC, id_subscriber DESC
    LIMIT $par_page OFFSET $offset
");
$stmt->execute($params);
$abonnes = $stmt->fetchAll();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS actifs,
        SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) AS desinscrits
    FROM newsletter_subscribers
")->fetch();

$campagnes = [];
try {
    $campagnes = $pdo->query("
        SELECT * FROM newsletter_campaigns
        ORDER BY sent_at DESC, id_campaign DESC
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    // Migration pas encore appliquée : l'historique reste simplement vide.
}

$filtres = array_filter([
    'status' => $status_filter !== 'all' ? $status_filter : '',
    'q'      => $q,
], fn($v) => $v !== '');

$lien = fn(array $extra = []) => 'manage_newsletter.php?' . http_build_query(array_merge($filtres, $extra));

$lien_statut = fn(string $k) => 'manage_newsletter.php?' . http_build_query(array_filter([
    'status' => $k,
    'q' => $q,
], fn($v) => $v !== '' && $v !== 'all'));

$is_filtered = $q !== '' || $status_filter !== 'all';

$page_action = '<a href="send_newsletter.php" class="adm-btn adm-btn-primary"><i class="ph ph-paper-plane-tilt"></i><span class="hidden sm:inline">Écrire un message</span></a>';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Newsletter';
            $page_subtitle = 'Abonnés, campagnes et envois';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if (hasFlash('success')): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars(flash('success')) ?></span></div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars(flash('error')) ?></span></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Adresses</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-envelope-simple"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['total'] ?></p>
                    <p class="adm-kpi-foot">au total dans la liste</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Abonnés actifs</span>
                        <span class="adm-kpi-icon adm-kpi-icon-green"><i class="ph ph-check-circle"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['actifs'] ?></p>
                    <p class="adm-kpi-foot">reçoivent vos messages</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Désinscrits</span>
                        <span class="adm-kpi-icon adm-kpi-icon-gray"><i class="ph ph-user-minus"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['desinscrits'] ?></p>
                    <p class="adm-kpi-foot">ne reçoivent plus rien</p>
                </div>
            </div>

            <div class="adm-toolbar">
                <nav class="adm-segmented" aria-label="Filtrer les abonnés">
                    <?php foreach (['all' => 'Tous', 'active' => 'Actifs', 'unsubscribed' => 'Désinscrits'] as $k => $label): ?>
                    <a href="<?= htmlspecialchars($lien_statut($k)) ?>" class="<?= $status_filter === $k ? 'is-active' : '' ?>"><?= $label ?></a>
                    <?php endforeach; ?>
                </nav>

                <div class="adm-toolbar-spacer"></div>

                <form method="get" class="adm-search" role="search">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <i class="ph ph-magnifying-glass"></i>
                    <label class="sr-only" for="newsletter-search">Rechercher une adresse e-mail</label>
                    <input type="search" id="newsletter-search" name="q" value="<?= htmlspecialchars($q) ?>"
                           placeholder="Rechercher une adresse…" autocomplete="off"
                           oninput="debounceAdminSearch(this.form)">
                </form>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <p class="text-sm text-gray-500">
                    <?= $total_filtre ?> adresse<?= $total_filtre > 1 ? 's' : '' ?>
                    <?php if ($q !== ''): ?>pour « <?= htmlspecialchars($q) ?> »<?php endif; ?>
                </p>
                <a href="export_subscribers.php?<?= htmlspecialchars(http_build_query($filtres)) ?>"
                   class="adm-btn adm-btn-secondary adm-btn-sm">
                    <i class="ph ph-download-simple"></i> Exporter en CSV
                </a>
            </div>

            <div class="adm-card overflow-hidden mb-8">
                <?php if (!$abonnes): ?>
                <div class="adm-empty">
                    <i class="ph <?= $is_filtered ? 'ph-magnifying-glass' : 'ph-envelope-simple' ?>"></i>
                    <p class="adm-empty-title"><?= $is_filtered ? 'Aucune adresse ne correspond' : 'Aucun abonné pour le moment' ?></p>
                    <p class="adm-empty-note">
                        <?= $is_filtered
                            ? 'Essayez un autre mot-clé ou revenez à tous les abonnés.'
                            : 'Les visiteurs peuvent s’inscrire depuis le site. Vous pouvez aussi écrire un premier message dès maintenant.' ?>
                    </p>
                    <?php if ($is_filtered): ?>
                    <a href="manage_newsletter.php" class="adm-btn adm-btn-secondary"><i class="ph ph-arrow-left"></i> Tous les abonnés</a>
                    <?php else: ?>
                    <a href="send_newsletter.php" class="adm-btn adm-btn-primary"><i class="ph ph-paper-plane-tilt"></i> Écrire un message</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Adresse e-mail</th>
                                <th>Statut</th>
                                <th>Inscription</th>
                                <th>Désinscription</th>
                                <th class="adm-col-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($abonnes as $a): ?>
                            <tr>
                                <td class="font-medium text-gray-900 break-all"><?= htmlspecialchars($a['email']) ?></td>
                                <td>
                                    <span class="adm-status <?= $a['status'] === 'active' ? 'is-green' : 'is-gray' ?>">
                                        <span class="adm-status-dot"></span><?= $a['status'] === 'active' ? 'Actif' : 'Désinscrit' ?>
                                    </span>
                                </td>
                                <td class="text-gray-500 whitespace-nowrap"><?= $a['created_at'] ? date('d/m/Y', strtotime($a['created_at'])) : '—' ?></td>
                                <td class="text-gray-500 whitespace-nowrap"><?= $a['unsubscribed_at'] ? date('d/m/Y', strtotime($a['unsubscribed_at'])) : '—' ?></td>
                                <td class="adm-col-actions">
                                    <form method="post" class="inline" onsubmit="return confirm('<?= $a['status'] === 'active' ? 'Désinscrire' : 'Réactiver' ?> cette adresse ?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_subscriber">
                                        <input type="hidden" name="id_subscriber" value="<?= (int)$a['id_subscriber'] ?>">
                                        <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
                                        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                                        <input type="hidden" name="page" value="<?= (int)$page ?>">
                                        <button type="submit" class="adm-action <?= $a['status'] === 'active' ? 'adm-action-danger' : 'adm-action-primary' ?>"
                                                title="<?= $a['status'] === 'active' ? 'Désinscrire' : 'Réactiver' ?>"
                                                aria-label="<?= $a['status'] === 'active' ? 'Désinscrire' : 'Réactiver' ?> cette adresse">
                                            <i class="ph <?= $a['status'] === 'active' ? 'ph-user-minus' : 'ph-envelope-simple' ?>"></i>
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

            <?php if ($total_pages > 1): ?>
            <div class="adm-pager mb-10">
                <p class="adm-pager-info">
                    <?= $offset + 1 ?>–<?= min($offset + $par_page, $total_filtre) ?> sur <?= $total_filtre ?> adresse<?= $total_filtre > 1 ? 's' : '' ?>
                </p>
                <div class="adm-pager-links">
                    <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page - 1])) ?>" aria-label="Page précédente">←</a>
                    <?php else: ?>
                    <span class="is-disabled">←</span>
                    <?php endif; ?>
                    <span class="is-current" aria-current="page"><?= $page ?></span>
                    <span class="text-gray-400" style="border:0;background:transparent">/ <?= $total_pages ?></span>
                    <?php if ($page < $total_pages): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page + 1])) ?>" aria-label="Page suivante">→</a>
                    <?php else: ?>
                    <span class="is-disabled">→</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($campagnes): ?>
            <div class="adm-card overflow-hidden mt-8">
                <div class="adm-card-head">
                    <div>
                        <h2 class="adm-card-title">Derniers messages envoyés</h2>
                        <p class="adm-card-sub">Historique des 5 dernières campagnes</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sujet</th>
                                <th>Destinataires</th>
                                <th>Envoyés</th>
                                <th>Échecs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campagnes as $c): ?>
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($c['sent_at'])) ?></td>
                                <td class="font-medium text-gray-900" style="max-width:30rem">
                                    <span class="adm-clamp-1" title="<?= htmlspecialchars($c['subject']) ?>"><?= htmlspecialchars($c['subject']) ?></span>
                                </td>
                                <td class="text-gray-600"><?= (int)$c['recipients_count'] ?></td>
                                <td class="font-semibold text-green-700"><?= (int)$c['sent_count'] ?></td>
                                <td class="<?= (int)$c['failed_count'] > 0 ? 'font-semibold text-red-600' : 'text-gray-400' ?>"><?= (int)$c['failed_count'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
    let adminSearchTimer = null;
    function debounceAdminSearch(form) {
        clearTimeout(adminSearchTimer);
        adminSearchTimer = setTimeout(function () { form.submit(); }, 450);
    }
    </script>
</body>
</html>

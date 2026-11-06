<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// ─── B2 : renvoyer le lien de téléchargement depuis l'écran Ventes ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend_link') {
    $id = (int)($_POST['id_purchase'] ?? 0);

    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token de sécurité invalide. Rechargez la page.');
    } elseif (!$id) {
        flash('error', 'Vente introuvable.');
    } else {
        $stmt = $pdo->prepare("
            SELECT pu.*,
                   f.file_name,
                   c.title AS course_title,
                   ci.title AS item_title
            FROM purchases pu
            LEFT JOIN files f ON f.id_file = pu.id_file
            LEFT JOIN courses c ON c.id_course = pu.id_course
            LEFT JOIN course_items ci ON ci.id_item = pu.id_item
            WHERE pu.id_purchase = ? AND pu.status = 'paid'
        ");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();

        if (!$sale) {
            flash('error', "Cette vente est introuvable ou n'est pas payée.");
        } elseif (empty($sale['download_token'])) {
            flash('error', "Cette vente n'a pas de lien d'accès.");
        } else {
            try {
                require_once __DIR__ . '/../config/mail.php';
                if (!empty($sale['id_file'])) {
                    send_purchase_link_email($sale, $sale['file_name'] ?: 'Votre document');
                } else {
                    $whole = !empty($sale['id_course']);
                    $titre = $whole
                        ? ($sale['course_title'] ?: 'Votre cours')
                        : ($sale['item_title'] ?: 'Votre chapitre');
                    send_course_access_email($sale, $titre, $whole);
                }
                flash('success', 'Lien renvoyé à ' . ($sale['buyer_email'] ?: 'l’acheteur') . '.');
            } catch (Throwable $e) {
                error_log('Renvoi du lien — vente #' . $id . ' : ' . $e->getMessage());
                flash('error', "L'envoi a échoué : " . $e->getMessage());
            }
        }
    }

    $back = 'manage_purchases.php';
    $qs = [];
    if (isset($_POST['status_filter']) && in_array($_POST['status_filter'], ['pending', 'paid', 'failed', 'refunded'], true)) {
        $qs['status'] = $_POST['status_filter'];
    }
    if (!empty($_POST['q'])) {
        $qs['q'] = (string)$_POST['q'];
    }
    if ($qs) {
        $back .= '?' . http_build_query($qs);
    }
    header('Location: ' . $back);
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'paid', 'failed', 'refunded'];
if (!in_array($status_filter, $allowed, true)) { $status_filter = 'all'; }

// Recherche simple : nom, e-mail ou nom du document.
$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}

$conds = [];
$params = [];
if ($status_filter !== 'all') {
    $conds[] = 'pu.status = ?';
    $params[] = $status_filter;
}
if ($q !== '') {
    $conds[] = '(pu.buyer_email LIKE ? OR pu.buyer_name LIKE ? OR f.file_name LIKE ? OR c.title LIKE ? OR ci.title LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$stmt = $pdo->prepare("
    SELECT pu.*,
           f.file_name,
           p.title AS post_title,
           c.title AS course_title,
           ci.title AS item_title,
           cc.title AS item_course_title
    FROM purchases pu
    LEFT JOIN files f ON f.id_file = pu.id_file
    LEFT JOIN posts p ON p.id_post = f.id_post
    LEFT JOIN courses c ON c.id_course = pu.id_course
    LEFT JOIN course_items ci ON ci.id_item = pu.id_item
    LEFT JOIN courses cc ON cc.id_course = ci.id_course
    $where
    ORDER BY pu.created_at DESC
    LIMIT 500
");
$stmt->execute($params);
$purchases = $stmt->fetchAll();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
        SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS paid_total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
        SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) AS refunded_count
    FROM purchases
")->fetch();

$is_filtered = $q !== '' || $status_filter !== 'all';

$lien = fn(string $statut) => 'manage_purchases.php?' . http_build_query(array_filter([
    'status' => $statut,
    'q' => $q,
], fn($v) => $v !== '' && $v !== 'all'));

$statut_meta = [
    'paid'     => ['label' => 'Payée',      'badge' => 'adm-badge-green'],
    'pending'  => ['label' => 'En attente', 'badge' => 'adm-badge-amber'],
    'failed'   => ['label' => 'Échouée',    'badge' => 'adm-badge-red'],
    'refunded' => ['label' => 'Remboursée', 'badge' => 'adm-badge-gray'],
];

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventes - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Ventes';
            $page_subtitle = 'Commandes, paiements et liens de téléchargement';
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
                        <span class="adm-kpi-label">Commandes</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-receipt"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['total'] ?></p>
                    <p class="adm-kpi-foot">depuis le lancement</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Payées</span>
                        <span class="adm-kpi-icon adm-kpi-icon-green"><i class="ph ph-check-circle"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['paid_count'] ?></p>
                    <p class="adm-kpi-foot">paiements confirmés</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">En attente</span>
                        <span class="adm-kpi-icon adm-kpi-icon-amber"><i class="ph ph-clock"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['pending_count'] ?></p>
                    <p class="adm-kpi-foot">à confirmer par le prestataire</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Encaissé</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-wallet"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= number_format((float)$stats['paid_total'], 0, ',', ' ') ?> <small>FCFA</small></p>
                    <p class="adm-kpi-foot">montant total des ventes payées</p>
                </div>
            </div>

            <div class="adm-toolbar">
                <nav class="adm-segmented" aria-label="Filtrer les ventes">
                    <?php
                    $tabs = [
                        'all'      => 'Toutes',
                        'paid'     => 'Payées',
                        'pending'  => 'En attente',
                        'failed'   => 'Échouées',
                        'refunded' => 'Remboursées',
                    ];
                    foreach ($tabs as $k => $label): ?>
                    <a href="<?= htmlspecialchars($lien($k)) ?>" class="<?= $status_filter === $k ? 'is-active' : '' ?>">
                        <?= $label ?>
                        <span class="text-gray-400"><?= (int)($k === 'all' ? $stats['total'] : $stats[$k . '_count']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </nav>

                <div class="adm-toolbar-spacer"></div>

                <form method="get" class="adm-search" role="search">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <i class="ph ph-magnifying-glass"></i>
                    <label class="sr-only" for="purchase-search">Rechercher un acheteur ou un contenu</label>
                    <input type="search" id="purchase-search" name="q" value="<?= htmlspecialchars($q) ?>"
                           placeholder="Nom, e-mail ou contenu…" autocomplete="off"
                           oninput="debounceAdminSearch(this.form)">
                </form>
            </div>

            <?php if ($is_filtered): ?>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <p class="text-sm text-gray-600">
                    <?= count($purchases) ?> résultat<?= count($purchases) > 1 ? 's' : '' ?>
                    <?php if ($q !== ''): ?>pour « <strong><?= htmlspecialchars($q) ?></strong> »<?php endif; ?>
                </p>
                <a href="manage_purchases.php" class="adm-link"><i class="ph ph-x"></i> Effacer les filtres</a>
            </div>
            <?php endif; ?>

            <div class="adm-card overflow-hidden">
                <?php if (!$purchases): ?>
                <div class="adm-empty">
                    <i class="ph <?= $is_filtered ? 'ph-magnifying-glass' : 'ph-receipt' ?>"></i>
                    <p class="adm-empty-title"><?= $is_filtered ? 'Aucune vente ne correspond' : 'Aucune vente pour le moment' ?></p>
                    <p class="adm-empty-note">
                        <?= $is_filtered
                            ? 'Essayez un autre mot-clé ou revenez à toutes les commandes.'
                            : 'Les achats de documents et de cours apparaîtront ici dès le premier paiement.' ?>
                    </p>
                    <?php if ($is_filtered): ?>
                    <a href="manage_purchases.php" class="adm-btn adm-btn-secondary"><i class="ph ph-arrow-left"></i> Toutes les ventes</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Contenu</th>
                                <th>Acheteur</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Transaction</th>
                                <th class="adm-col-actions">Lien</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $pu):
                                if (!empty($pu['id_file'])) {
                                    $content_title = $pu['file_name'] ?: 'Document supprimé';
                                    $content_sub   = trim('Document' . ($pu['post_title'] ? ' · ' . $pu['post_title'] : ''));
                                } elseif (!empty($pu['id_course'])) {
                                    $content_title = $pu['course_title'] ?: 'Cours supprimé';
                                    $content_sub   = 'Cours complet';
                                } else {
                                    $content_title = $pu['item_title'] ?: 'Chapitre supprimé';
                                    $content_sub   = trim('Chapitre' . ($pu['item_course_title'] ? ' · ' . $pu['item_course_title'] : ''));
                                }
                                $meta = $statut_meta[$pu['status']] ?? ['label' => ucfirst((string)$pu['status']), 'badge' => 'adm-badge-gray'];
                                $acheteur = trim((string)($pu['buyer_name'] ?? '')) ?: 'Acheteur';
                            ?>
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($pu['created_at'])) ?></td>
                                <td style="max-width:20rem">
                                    <p class="text-sm font-medium text-gray-900 adm-clamp-1" title="<?= htmlspecialchars($content_title) ?>"><?= htmlspecialchars($content_title) ?></p>
                                    <p class="adm-table-sub adm-clamp-1"><?= htmlspecialchars($content_sub) ?></p>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <span class="adm-avatar-xs"><?= htmlspecialchars($initials_of($acheteur)) ?></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 whitespace-nowrap"><?= htmlspecialchars($acheteur) ?></p>
                                            <p class="adm-table-sub adm-clamp-1">
                                                <?= htmlspecialchars($pu['buyer_email'] ?? '') ?>
                                                <?php if (!empty($pu['buyer_phone'])): ?> · <?= htmlspecialchars($pu['buyer_phone']) ?><?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm font-semibold text-gray-900 whitespace-nowrap">
                                    <?= number_format((float)$pu['amount'], 0, ',', ' ') ?> <span class="text-xs font-medium text-gray-500"><?= htmlspecialchars($pu['currency']) ?></span>
                                </td>
                                <td><span class="adm-badge <?= $meta['badge'] ?>"><?= $meta['label'] ?></span></td>
                                <td>
                                    <span class="text-xs text-gray-500" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace" title="<?= htmlspecialchars($pu['transaction_id'] ?? '') ?>">
                                        <?= htmlspecialchars(truncate((string)($pu['transaction_id'] ?? '—'), 18)) ?>
                                    </span>
                                </td>
                                <td class="adm-col-actions">
                                    <?php if ($pu['status'] === 'paid' && !empty($pu['download_token'])): ?>
                                    <form method="post" class="inline" onsubmit="return confirm('Renvoyer le lien de téléchargement à <?= htmlspecialchars($pu['buyer_email'] ?? 'cet acheteur', ENT_QUOTES) ?> ?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="resend_link">
                                        <input type="hidden" name="id_purchase" value="<?= (int)$pu['id_purchase'] ?>">
                                        <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
                                        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                                        <button type="submit" class="adm-action adm-action-primary" title="Renvoyer le lien" aria-label="Renvoyer le lien de téléchargement">
                                            <i class="ph ph-paper-plane-tilt"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-300">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

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

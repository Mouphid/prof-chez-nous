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
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
    FROM purchases
")->fetch();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventes - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Ventes'; $page_icon = 'ph-shopping-cart'; include "_topbar.php"; ?>
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
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-gray-800"><?= (int)$stats['total'] ?></div><div class="text-sm text-gray-500">Commandes</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-green-600"><?= (int)$stats['paid_count'] ?></div><div class="text-sm text-gray-500">Payées</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-yellow-600"><?= (int)$stats['pending_count'] ?></div><div class="text-sm text-gray-500">En attente</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-primary"><?= number_format((float)$stats['paid_total'], 0, ',', ' ') ?></div><div class="text-sm text-gray-500">FCFA encaissés</div></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap items-center gap-2">
                <?php foreach (['all' => 'Toutes', 'paid' => 'Payées', 'pending' => 'En attente', 'failed' => 'Échouées', 'refunded' => 'Remboursées'] as $k => $label): ?>
                <a href="manage_purchases.php?<?= htmlspecialchars(http_build_query(array_filter(['status' => $k, 'q' => $q], fn($v) => $v !== ''))) ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === $k ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>"><?= $label ?></a>
                <?php endforeach; ?>

                <form method="get" class="flex flex-wrap items-center gap-2 sm:ml-auto">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <label for="purchase-search" class="sr-only">Rechercher un acheteur</label>
                    <input type="search" id="purchase-search" name="q" value="<?= htmlspecialchars($q) ?>"
                           placeholder="Nom, e-mail ou contenu"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:ring-2 focus:ring-primary focus:border-primary">
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="ph ph-magnifying-glass"></i> Chercher
                    </button>
                    <?php if ($q !== ''): ?>
                    <a href="manage_purchases.php?status=<?= urlencode($status_filter) ?>" class="text-sm text-gray-500 hover:text-primary">Effacer</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if ($q !== ''): ?>
            <p class="text-sm text-gray-500 mb-3">
                <?= count($purchases) ?> résultat<?= count($purchases) > 1 ? 's' : '' ?> pour « <?= htmlspecialchars($q) ?> ».
            </p>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Contenu</th>
                            <th class="px-4 py-3">Acheteur</th>
                            <th class="px-4 py-3">Montant</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Transaction</th>
                            <th class="px-4 py-3 text-right">Lien</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!$purchases): ?>
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Aucune commande pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($purchases as $pu): ?>
                        <?php
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
                        ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= date('d/m/Y H:i', strtotime($pu['created_at'])) ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($content_title) ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($content_sub) ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($pu['buyer_name'] ?? '') ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($pu['buyer_email'] ?? '') ?> · <?= htmlspecialchars($pu['buyer_phone'] ?? '') ?></div>
                            </td>
                            <td class="px-4 py-3 font-semibold whitespace-nowrap"><?= number_format((float)$pu['amount'], 0, ',', ' ') ?> <?= htmlspecialchars($pu['currency']) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                $badge = [
                                    'paid'     => 'bg-green-100 text-green-700',
                                    'pending'  => 'bg-yellow-100 text-yellow-700',
                                    'failed'   => 'bg-red-100 text-red-700',
                                    'refunded' => 'bg-gray-100 text-gray-600',
                                ][$pu['status']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $badge ?>"><?= htmlspecialchars($pu['status']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 break-all max-w-[180px]"><?= htmlspecialchars($pu['transaction_id'] ?? '—') ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <?php if ($pu['status'] === 'paid' && !empty($pu['download_token'])): ?>
                                <form method="post" class="inline" onsubmit="return confirm('Renvoyer le lien de téléchargement à <?= htmlspecialchars($pu['buyer_email'] ?? 'cet acheteur', ENT_QUOTES) ?> ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="resend_link">
                                    <input type="hidden" name="id_purchase" value="<?= (int)$pu['id_purchase'] ?>">
                                    <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
                                    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-indigo-700 border border-gray-200 hover:border-primary px-3 py-1.5 rounded-lg transition">
                                        <i class="ph ph-paper-plane-tilt"></i> Renvoyer
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
            </div>
        </main>
    </div>
</body>
</html>

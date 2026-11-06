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

$page_action = '<a href="send_newsletter.php" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">'
    . '<i class="ph ph-paper-plane-tilt"></i> Écrire un message</a>';
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
            <?php $page_title = 'Newsletter'; $page_icon = 'ph-envelope-simple'; include "_topbar.php"; ?>
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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-gray-800"><?= (int)$stats['total'] ?></div>
                    <div class="text-sm text-gray-500">Adresses au total</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-green-600"><?= (int)$stats['actifs'] ?></div>
                    <div class="text-sm text-gray-500">Abonnés actifs</div>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center">
                    <div class="text-2xl font-extrabold text-gray-500"><?= (int)$stats['desinscrits'] ?></div>
                    <div class="text-sm text-gray-500">Désinscrits</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap items-center gap-2">
                <?php foreach (['all' => 'Tous', 'active' => 'Actifs', 'unsubscribed' => 'Désinscrits'] as $k => $label): ?>
                <a href="manage_newsletter.php?<?= htmlspecialchars(http_build_query(array_filter(['status' => $k !== 'all' ? $k : '', 'q' => $q], fn($v) => $v !== ''))) ?>"
                   class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === $k ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>"><?= $label ?></a>
                <?php endforeach; ?>

                <form method="get" class="flex flex-wrap items-center gap-2 sm:ml-auto">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                    <label for="newsletter-search" class="sr-only">Rechercher une adresse</label>
                    <input type="search" id="newsletter-search" name="q" value="<?= htmlspecialchars($q) ?>"
                           placeholder="Adresse e-mail"
                           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:ring-2 focus:ring-primary focus:border-primary">
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-primary text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        <i class="ph ph-magnifying-glass"></i> Chercher
                    </button>
                    <?php if ($q !== ''): ?>
                    <a href="manage_newsletter.php?status=<?= urlencode($status_filter) ?>" class="text-sm text-gray-500 hover:text-primary">Effacer</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <p class="text-sm text-gray-500">
                    <?= $total_filtre ?> adresse<?= $total_filtre > 1 ? 's' : '' ?>
                    <?php if ($q !== ''): ?>pour « <?= htmlspecialchars($q) ?> »<?php endif; ?>
                </p>
                <a href="export_subscribers.php?<?= htmlspecialchars(http_build_query($filtres)) ?>"
                   class="inline-flex items-center gap-1.5 border border-gray-200 hover:border-primary text-primary px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="ph ph-download-simple"></i> Exporter en CSV
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto mb-8">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Adresse e-mail</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Inscription</th>
                            <th class="px-4 py-3">Désinscription</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!$abonnes): ?>
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Aucun abonné pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($abonnes as $a): ?>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800 break-all"><?= htmlspecialchars($a['email']) ?></td>
                            <td class="px-4 py-3">
                                <?php if ($a['status'] === 'active'): ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Actif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Désinscrit</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= $a['created_at'] ? date('d/m/Y', strtotime($a['created_at'])) : '—' ?></td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= $a['unsubscribed_at'] ? date('d/m/Y', strtotime($a['unsubscribed_at'])) : '—' ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <form method="post" class="inline" onsubmit="return confirm('<?= $a['status'] === 'active' ? 'Désinscrire' : 'Réactiver' ?> cette adresse ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_subscriber">
                                    <input type="hidden" name="id_subscriber" value="<?= (int)$a['id_subscriber'] ?>">
                                    <input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
                                    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
                                    <input type="hidden" name="page" value="<?= (int)$page ?>">
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold <?= $a['status'] === 'active' ? 'text-red-600 hover:text-red-700 border-gray-200 hover:border-red-300' : 'text-primary hover:text-indigo-700 border-gray-200 hover:border-primary' ?> border px-3 py-1.5 rounded-lg transition">
                                        <i class="ph <?= $a['status'] === 'active' ? 'ph-envelope-slash' : 'ph-envelope-simple' ?>"></i>
                                        <?= $a['status'] === 'active' ? 'Désinscrire' : 'Réactiver' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-center gap-2 mb-10">
                <?php if ($page > 1): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page - 1])) ?>" class="px-3 py-2 rounded-lg text-sm bg-white border border-gray-200 hover:border-primary text-gray-600">← Précédent</a>
                <?php endif; ?>
                <span class="text-sm text-gray-500">Page <?= $page ?> sur <?= $total_pages ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="<?= htmlspecialchars($lien(['page' => $page + 1])) ?>" class="px-3 py-2 rounded-lg text-sm bg-white border border-gray-200 hover:border-primary text-gray-600">Suivant →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($campagnes): ?>
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Derniers messages envoyés</h2>
            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Sujet</th>
                            <th class="px-4 py-3">Destinataires</th>
                            <th class="px-4 py-3">Envoyés</th>
                            <th class="px-4 py-3">Échecs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($campagnes as $c): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= date('d/m/Y H:i', strtotime($c['sent_at'])) ?></td>
                            <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($c['subject']) ?></td>
                            <td class="px-4 py-3"><?= (int)$c['recipients_count'] ?></td>
                            <td class="px-4 py-3 text-green-600 font-semibold"><?= (int)$c['sent_count'] ?></td>
                            <td class="px-4 py-3 <?= (int)$c['failed_count'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' ?>"><?= (int)$c['failed_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>

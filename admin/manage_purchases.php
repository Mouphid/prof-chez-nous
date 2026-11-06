<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
$allowed = ['all', 'pending', 'paid', 'failed', 'refunded'];
if (!in_array($status_filter, $allowed, true)) { $status_filter = 'all'; }

$where = '';
$params = [];
if ($status_filter !== 'all') {
    $where = 'WHERE pu.status = ?';
    $params[] = $status_filter;
}

$stmt = $pdo->prepare("
    SELECT pu.*, f.file_name, p.title AS post_title
    FROM purchases pu
    LEFT JOIN files f ON f.id_file = pu.id_file
    LEFT JOIN posts p ON p.id_post = f.id_post
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
    <title>Ventes de documents - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Ventes de documents'; $page_icon = 'ph-shopping-cart'; include "_topbar.php"; ?>
            <div class="p-8">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-gray-800"><?= (int)$stats['total'] ?></div><div class="text-sm text-gray-500">Commandes</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-green-600"><?= (int)$stats['paid_count'] ?></div><div class="text-sm text-gray-500">Payées</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-yellow-600"><?= (int)$stats['pending_count'] ?></div><div class="text-sm text-gray-500">En attente</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-primary"><?= number_format((float)$stats['paid_total'], 0, ',', ' ') ?></div><div class="text-sm text-gray-500">FCFA encaissés</div></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-wrap gap-2">
                <?php foreach (['all' => 'Toutes', 'paid' => 'Payées', 'pending' => 'En attente', 'failed' => 'Échouées', 'refunded' => 'Remboursées'] as $k => $label): ?>
                <a href="manage_purchases.php?status=<?= $k ?>" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === $k ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Document</th>
                            <th class="px-4 py-3">Acheteur</th>
                            <th class="px-4 py-3">Montant</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Transaction</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!$purchases): ?>
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Aucune commande pour le moment.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($purchases as $pu): ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500"><?= date('d/m/Y H:i', strtotime($pu['created_at'])) ?></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($pu['file_name'] ?? 'Document supprimé') ?></div>
                                <div class="text-xs text-gray-400"><?= htmlspecialchars($pu['post_title'] ?? '') ?></div>
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

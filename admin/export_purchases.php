<?php
/**
 * Export CSV des ventes.
 * Réservé à l'administrateur. Le fichier s'ouvre dans Excel / LibreOffice.
 * Les filtres de l'écran Ventes (statut et recherche) sont respectés.
 */
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
if (!in_array($status_filter, ['all', 'pending', 'paid', 'failed', 'refunded'], true)) {
    $status_filter = 'all';
}

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
    array_push($params, $like, $like, $like, $like, $like);
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
    ORDER BY pu.created_at DESC, pu.id_purchase DESC
");
$stmt->execute($params);

$statuts = [
    'paid'     => 'Payée',
    'pending'  => 'En attente',
    'failed'   => 'Échouée',
    'refunded' => 'Remboursée',
];

$nom_fichier = 'ventes-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$sortie = fopen('php://output', 'w');

// BOM UTF-8 : accents corrects dans Excel.
fwrite($sortie, "\xEF\xBB\xBF");

// Séparateur « ; » : format attendu par Excel en français.
fputcsv($sortie, [
    'N° commande',
    'Date',
    'Statut',
    'Type',
    'Contenu',
    'Détail',
    'Acheteur',
    'E-mail',
    'Téléphone',
    'Montant',
    'Devise',
    'Prestataire',
    'Transaction',
    'Payée le',
], ';');

while ($pu = $stmt->fetch()) {
    if (!empty($pu['id_file'])) {
        $type    = 'Document';
        $contenu = $pu['file_name'] ?: 'Document supprimé';
        $detail  = (string)($pu['post_title'] ?? '');
    } elseif (!empty($pu['id_item'])) {
        $type    = 'Chapitre';
        $contenu = $pu['item_title'] ?: 'Chapitre supprimé';
        $detail  = (string)($pu['item_course_title'] ?? '');
    } else {
        $type    = 'Cours complet';
        $contenu = $pu['course_title'] ?: 'Cours supprimé';
        $detail  = '';
    }

    fputcsv($sortie, [
        (int)$pu['id_purchase'],
        date('d/m/Y H:i', strtotime($pu['created_at'])),
        $statuts[$pu['status']] ?? ucfirst((string)$pu['status']),
        $type,
        csv_safe((string)$contenu),
        csv_safe($detail),
        csv_safe((string)($pu['buyer_name'] ?? '')),
        csv_safe((string)($pu['buyer_email'] ?? '')),
        csv_safe((string)($pu['buyer_phone'] ?? '')),
        number_format((float)$pu['amount'], 2, ',', ''),
        (string)$pu['currency'],
        csv_safe((string)($pu['provider'] ?? '')),
        csv_safe((string)($pu['transaction_id'] ?? '')),
        $pu['paid_at'] ? date('d/m/Y H:i', strtotime($pu['paid_at'])) : '',
    ], ';');
}

fclose($sortie);
exit;

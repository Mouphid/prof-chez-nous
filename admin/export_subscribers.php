<?php
/**
 * Export CSV des abonnés à la newsletter.
 * Réservé à l'administrateur. Le fichier s'ouvre dans Excel / LibreOffice.
 */
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$status_filter = $_GET['status'] ?? 'all';
if (!in_array($status_filter, ['all', 'active', 'unsubscribed'], true)) {
    $status_filter = 'all';
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) {
    $q = mb_substr($q, 0, 100);
}

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

$stmt = $pdo->prepare("
    SELECT email, status, created_at, unsubscribed_at
    FROM newsletter_subscribers
    $where
    ORDER BY created_at DESC, id_subscriber DESC
");
$stmt->execute($params);

$nom_fichier = 'abonnes-newsletter-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$sortie = fopen('php://output', 'w');

// BOM UTF-8 : accents corrects dans Excel.
fwrite($sortie, "\xEF\xBB\xBF");

// Séparateur « ; » : le format attendu par Excel en français.
fputcsv($sortie, ['Adresse e-mail', 'Statut', 'Inscription', 'Désinscription'], ';');

while ($ligne = $stmt->fetch()) {
    fputcsv($sortie, [
        csv_safe((string)$ligne['email']),
        $ligne['status'] === 'active' ? 'Actif' : 'Désinscrit',
        $ligne['created_at'] ? date('d/m/Y H:i', strtotime($ligne['created_at'])) : '',
        $ligne['unsubscribed_at'] ? date('d/m/Y H:i', strtotime($ligne['unsubscribed_at'])) : '',
    ], ';');
}

fclose($sortie);
exit;

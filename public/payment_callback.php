<?php
/**
 * Retour de paiement + webhook.
 * Le prestataire est choisi dans config/payment.php (Kkiapay, FedaPay, ...).
 * La transaction est TOUJOURS revérifiée côté serveur (anti-fraude).
 */
require_once "../config/config.php";
require_once "../config/payment.php";

$isWebhook = ($_SERVER['REQUEST_METHOD'] === 'POST');

$purchaseId  = 0;
$transaction = '';

if ($isWebhook) {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true) ?: [];
    $transaction = $payload['transactionId'] ?? $payload['transaction_id'] ?? '';
    $purchaseId  = (int)($payload['data'] ?? $payload['partnerId'] ?? 0);
} else {
    $purchaseId  = isset($_GET['purchase']) ? (int)$_GET['purchase'] : 0;
    $transaction = isset($_GET['transaction']) ? trim($_GET['transaction']) : '';
}

if (!$purchaseId || $transaction === '') {
    http_response_code(400);
    if ($isWebhook) { echo 'ok'; exit; }
    die("Paramètres de paiement manquants.");
}

$stmt = $pdo->prepare("SELECT * FROM purchases WHERE id_purchase = ?");
$stmt->execute([$purchaseId]);
$purchase = $stmt->fetch();

if (!$purchase) {
    http_response_code(404);
    if ($isWebhook) { echo 'ok'; exit; }
    die("Commande introuvable.");
}

// Déjà payé → redirection directe vers le téléchargement
if ($purchase['status'] === 'paid' && !empty($purchase['download_token'])) {
    if ($isWebhook) { echo 'ok'; exit; }
    header("Location: download.php?id=" . (int)$purchase['id_file'] . "&token=" . urlencode($purchase['download_token']));
    exit;
}

if (!payment_is_configured()) {
    if ($isWebhook) { echo 'ok'; exit; }
    die("Le paiement en ligne n'est pas configuré.");
}

$result = payment_verify_transaction($transaction);
$data   = $result['data'] ?? null;
$ok     = $result['ok'] ?? false;

if ($ok) {
    // Le montant confirmé par le prestataire doit couvrir le prix de la commande.
    $paidAmount = isset($result['amount']) ? (float)$result['amount'] : 0.0;
    if ($paidAmount + 0.01 < (float)$purchase['amount']) {
        $ok = false;
    }
}

if (!$ok) {
    $upd = $pdo->prepare("UPDATE purchases SET status = 'failed', transaction_id = ? WHERE id_purchase = ? AND status = 'pending'");
    $upd->execute([$transaction, $purchaseId]);

    if ($isWebhook) { echo 'ok'; exit; }
    ?>
    <!DOCTYPE html>
    <html lang="fr"><head><meta charset="UTF-8"><title>Paiement échoué</title>
    <link rel="stylesheet" href="../assets/css/tailwind.css"><link rel="stylesheet" href="../assets/css/sobre.css"></head>
    <body class="bg-gray-50 font-sans">
    <div class="max-w-lg mx-auto mt-20 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
        <i class="ph ph-x-circle text-5xl text-red-500"></i>
        <h1 class="text-xl font-bold text-gray-900 mt-4 mb-2">Paiement non confirmé</h1>
        <p class="text-gray-500 mb-6">La transaction n'a pas pu être vérifiée. Aucun montant n'a été débité ou le paiement est en attente.</p>
        <a href="purchase.php?id=<?= (int)$purchase['id_file'] ?>" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-6 py-3 rounded-xl">Réessayer</a>
    </div>
    </body></html>
    <?php
    exit;
}

// Paiement confirmé
$token = bin2hex(random_bytes(32));
try {
    $upd = $pdo->prepare("
        UPDATE purchases
        SET status = 'paid', transaction_id = ?, download_token = ?, paid_at = NOW()
        WHERE id_purchase = ? AND status = 'pending'
    ");
    $upd->execute([$transaction, $token, $purchaseId]);
} catch (PDOException $e) {
    // Transaction déjà utilisée pour une autre commande
    error_log("Paiement: transaction déjà utilisée — " . $e->getMessage());
    if ($isWebhook) { echo 'ok'; exit; }
    die("Cette transaction a déjà été utilisée.");
}

// Relire le token réellement enregistré
$stmt = $pdo->prepare("SELECT download_token FROM purchases WHERE id_purchase = ?");
$stmt->execute([$purchaseId]);
$savedToken = $stmt->fetchColumn() ?: $token;

if ($isWebhook) { echo 'ok'; exit; }

header("Location: download.php?id=" . (int)$purchase['id_file'] . "&token=" . urlencode($savedToken));
exit;

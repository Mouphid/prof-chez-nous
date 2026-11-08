<?php
/**
 * Retour de paiement + webhook.
 * Le prestataire est choisi dans config/payment.php (Kkiapay, FedaPay, ...).
 * La transaction est TOUJOURS revérifiée côté serveur (anti-fraude).
 */
require_once "../config/config.php";
require_once "../config/payment.php";

/**
 * Adresse vers laquelle envoyer l'acheteur une fois le paiement confirmé.
 * Trois cas : document, cours complet, chapitre seul.
 */
function purchase_return_url(array $purchase): string
{
    $token = urlencode((string)($purchase['download_token'] ?? ''));

    if (!empty($purchase['id_file'])) {
        return 'download.php?id=' . (int)$purchase['id_file'] . '&token=' . $token;
    }
    if (!empty($purchase['id_course'])) {
        return 'course.php?id=' . (int)$purchase['id_course'] . '&token=' . $token;
    }
    return 'course.php?item=' . (int)$purchase['id_item'] . '&token=' . $token;
}

/**
 * Adresse de la page de paiement, pour réessayer après un échec.
 */
function purchase_retry_url(array $purchase): string
{
    if (!empty($purchase['id_file'])) {
        return 'purchase.php?id=' . (int)$purchase['id_file'];
    }
    if (!empty($purchase['id_course'])) {
        return 'purchase_course.php?course=' . (int)$purchase['id_course'];
    }
    return 'purchase_course.php?item=' . (int)$purchase['id_item'];
}

/**
 * Interroge le prestataire plusieurs fois.
 *
 * Après un paiement Mobile Money, la transaction reste parfois « en attente »
 * quelques secondes. Vérifier une seule fois tout de suite ferait croire à un
 * échec alors que le paiement est réussi. On réessaie donc brièvement.
 *
 * @param string $transaction identifiant de la transaction
 * @param int    $attempts    nombre maximal d'essais
 */
function payment_verify_with_retry(string $transaction, int $attempts = 4): array
{
    // Pauses avant chaque essai (0 s, 2 s, 3 s, 5 s → environ 10 s au total).
    $delays = [0, 2, 3, 5];
    $result = [];

    for ($i = 0; $i < $attempts; $i++) {
        if (!empty($delays[$i])) {
            sleep($delays[$i]);
        }

        $result = payment_verify_transaction($transaction);
        if (!empty($result['ok'])) {
            return $result;
        }

        $status = strtoupper((string)($result['data']['status'] ?? ''));

        // Statut clairement définitif : inutile d'attendre.
        if (in_array($status, [
            'FAILED', 'REJECTED', 'CANCELLED', 'CANCELED', 'REFUNDED',
            'INSUFFICIENT_FUND', 'PAYMENT_DECLINED', 'TRANSACTION_ERROR',
        ], true)) {
            return $result;
        }

        // Sinon (PENDING, TRANSACTION_NOT_FOUND, réponse vide…), on réessaie.
    }

    return $result;
}

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

// Déjà payé → redirection directe vers le contenu
if ($purchase['status'] === 'paid' && !empty($purchase['download_token'])) {
    if ($isWebhook) { echo 'ok'; exit; }
    header("Location: " . purchase_return_url($purchase));
    exit;
}

if (!payment_is_configured()) {
    if ($isWebhook) { echo 'ok'; exit; }
    die("Le paiement en ligne n'est pas configuré.");
}

$result = payment_verify_with_retry($transaction);
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
    // Journalise le motif exact : indispensable pour diagnostiquer un test sandbox.
    error_log(sprintf(
        'Paiement non confirmé — commande #%d, transaction %s, HTTP %s, statut %s, montant reçu %s, erreur %s',
        $purchaseId,
        (string)$transaction,
        (string)($result['http'] ?? '?'),
        (string)($data['status'] ?? 'inconnu'),
        isset($result['amount']) ? (string)$result['amount'] : 'inconnu',
        (string)($result['error'] ?? 'aucune')
    ));

    // Paiement encore « en attente » chez le prestataire : on ne marque PAS
    // la commande comme échouée. L'acheteur peut revérifier dans un instant.
    $status = strtoupper((string)($data['status'] ?? ''));
    $stillPending = in_array($status, ['', 'PENDING', 'PROCESSING', 'TRANSACTION_NOT_FOUND'], true);

    if ($stillPending) {
        $upd = $pdo->prepare("UPDATE purchases SET transaction_id = ? WHERE id_purchase = ? AND status = 'pending'");
        $upd->execute([$transaction, $purchaseId]);

        if ($isWebhook) { echo 'ok'; exit; }
        ?>
        <!DOCTYPE html>
        <html lang="fr"><head><meta charset="UTF-8"><title>Paiement en cours de vérification</title>
        <link rel="stylesheet" href="../assets/css/tailwind.css"><link rel="stylesheet" href="../assets/css/sobre.css"></head>
        <body class="bg-gray-50 font-sans">
        <div class="max-w-lg mx-auto mt-20 p-8 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
            <i class="ph ph-clock text-5xl text-amber-500"></i>
            <h1 class="text-xl font-bold text-gray-900 mt-4 mb-2">Paiement en cours de vérification</h1>
            <p class="text-gray-500 mb-6">Votre paiement est enregistré mais la confirmation n'est pas encore arrivée. Patientez quelques secondes puis cliquez ci-dessous.</p>
            <a href="<?= htmlspecialchars('payment_callback.php?purchase=' . (int)$purchaseId . '&transaction=' . urlencode((string)$transaction)) ?>" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-6 py-3 rounded-xl">Vérifier à nouveau</a>
        </div>
        </body></html>
        <?php
        exit;
    }

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
        <a href="<?= htmlspecialchars(purchase_retry_url($purchase)) ?>" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-6 py-3 rounded-xl">Réessayer</a>
    </div>
    </body></html>
    <?php
    exit;
}

// Paiement confirmé
$token = bin2hex(random_bytes(32));
$justPaid = false;
try {
    $upd = $pdo->prepare("
        UPDATE purchases
        SET status = 'paid', transaction_id = ?, download_token = ?, paid_at = NOW()
        WHERE id_purchase = ? AND status = 'pending'
    ");
    $upd->execute([$transaction, $token, $purchaseId]);
    // rowCount = 1 seulement si c'est nous qui venons de passer la commande en « payé ».
    // Cela évite d'envoyer deux fois l'e-mail si le webhook et le retour navigateur arrivent tous les deux.
    $justPaid = ($upd->rowCount() === 1);
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

// ─── B1 : envoi automatique du lien par e-mail ───
// Un échec d'envoi ne doit jamais bloquer l'accès : on journalise
// et l'acheteur garde la redirection immédiate vers son contenu.
if ($justPaid) {
    try {
        require_once __DIR__ . '/../config/mail.php';
        $purchaseWithToken = array_merge($purchase, ['download_token' => $savedToken]);

        if (!empty($purchase['id_file'])) {
            $fileStmt = $pdo->prepare("SELECT file_name FROM files WHERE id_file = ?");
            $fileStmt->execute([(int)$purchase['id_file']]);
            $fileName = $fileStmt->fetchColumn() ?: 'Votre document';
            send_purchase_link_email($purchaseWithToken, $fileName);
        } else {
            $whole = !empty($purchase['id_course']);
            if ($whole) {
                $titleStmt = $pdo->prepare("SELECT title FROM courses WHERE id_course = ?");
                $titleStmt->execute([(int)$purchase['id_course']]);
            } else {
                $titleStmt = $pdo->prepare("SELECT title FROM course_items WHERE id_item = ?");
                $titleStmt->execute([(int)$purchase['id_item']]);
            }
            $title = $titleStmt->fetchColumn() ?: ($whole ? 'Votre cours' : 'Votre chapitre');
            send_course_access_email($purchaseWithToken, $title, $whole);
        }
    } catch (Throwable $e) {
        error_log("Achat #{$purchaseId} : envoi du lien impossible — " . $e->getMessage());
    }
}

if ($isWebhook) { echo 'ok'; exit; }

header("Location: " . purchase_return_url(array_merge($purchase, ['download_token' => $savedToken])));
exit;

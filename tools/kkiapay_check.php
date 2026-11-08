<?php
/**
 * Vérification manuelle d'une transaction Kkiapay.
 *
 * Usage :
 *   php tools/kkiapay_check.php                     → affiche la configuration
 *   php tools/kkiapay_check.php ID_TRANSACTION      → interroge Kkiapay
 *
 * Ce script ne montre jamais les clés. Il sert à vérifier le mode sandbox
 * avant de tester le paiement complet dans le navigateur.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/payment.php';

$cfg = payment_config();

echo "Prestataire : " . payment_provider_label() . PHP_EOL;
echo "Mode        : " . (payment_is_sandbox() ? 'SANDBOX (test)' : 'PRODUCTION (argent réel)') . PHP_EOL;
echo "Devise      : " . strtoupper((string)$cfg['currency']) . PHP_EOL;
echo "Clés        : " . (payment_is_configured() ? 'présentes' : 'MANQUANTES') . PHP_EOL;

if (payment_provider() === 'kkiapay') {
    $base = payment_is_sandbox()
        ? 'https://api-sandbox.kkiapay.me'
        : 'https://api.kkiapay.me';
    echo "Serveur     : " . $base . PHP_EOL;
}

$transaction = trim((string)($argv[1] ?? ''));
if ($transaction === '') {
    echo PHP_EOL . "Donne un identifiant de transaction pour lancer la vérification :" . PHP_EOL;
    echo "  php tools/kkiapay_check.php ID_TRANSACTION" . PHP_EOL;
    exit(0);
}

if (!payment_is_configured()) {
    fwrite(STDERR, PHP_EOL . "Impossible de vérifier : les clés sont vides dans config/payment_config.php." . PHP_EOL);
    exit(1);
}

$result = payment_verify_transaction($transaction);

echo PHP_EOL . "Transaction : " . $transaction . PHP_EOL;
echo "Réussie     : " . (!empty($result['ok']) ? 'OUI' : 'NON') . PHP_EOL;
echo "HTTP        : " . (int)($result['http'] ?? 0) . PHP_EOL;
echo "Montant     : " . (isset($result['amount']) ? (string)$result['amount'] : 'inconnu') . PHP_EOL;
if (!empty($result['error'])) {
    echo "Erreur      : " . $result['error'] . PHP_EOL;
}

if (is_array($result['data'] ?? null)) {
    $utiles = ['status', 'amount', 'transactionId', 'source', 'reason', 'failureCode', 'failureMessage'];
    foreach ($utiles as $cle) {
        if (array_key_exists($cle, $result['data'])) {
            echo str_pad(ucfirst($cle), 12) . ': ' . (string)$result['data'][$cle] . PHP_EOL;
        }
    }
}

exit(!empty($result['ok']) ? 0 : 2);

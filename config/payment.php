<?php
/**
 * config/payment.php — Configuration et helpers de paiement (Kkiapay)
 *
 * Les clés se trouvent dans config/payment_config.php (non versionné).
 * Copier config/payment_config.example.php → config/payment_config.php
 */

function payment_config(): array {
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $defaults = [
        'provider' => 'kkiapay',
        'currency' => 'XOF',
        'sandbox'  => true,
        'kkiapay'  => [
            'public_key'  => '',
            'private_key' => '',
            'secret_key'  => '',
        ],
    ];

    $file = __DIR__ . '/payment_config.php';
    if (file_exists($file)) {
        $loaded = require $file;
        if (is_array($loaded)) {
            $defaults = array_replace_recursive($defaults, $loaded);
        }
    }

    $cfg = $defaults;
    return $cfg;
}

function payment_is_configured(): bool {
    $cfg = payment_config();
    if ($cfg['provider'] === 'kkiapay') {
        return !empty($cfg['kkiapay']['public_key'])
            && !empty($cfg['kkiapay']['private_key'])
            && !empty($cfg['kkiapay']['secret_key']);
    }
    return false;
}

function payment_amount_label(float $amount): string {
    $cfg = payment_config();
    return number_format($amount, 0, ',', ' ') . ' ' . $cfg['currency'];
}

/**
 * Vérifie une transaction Kkiapay côté serveur.
 * Retourne ['ok' => bool, 'data' => array|null, 'http' => int]
 */
function kkiapay_verify_transaction(string $transactionId): array {
    $cfg = payment_config();
    $url = 'https://api.kkiapay.me/api/v1/transactions/verify';

    $payload = json_encode(['transactionId' => $transactionId]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'x-api-key: ' . $cfg['kkiapay']['public_key'],
            'x-private-key: ' . $cfg['kkiapay']['private_key'],
            'x-secret-key: ' . $cfg['kkiapay']['secret_key'],
        ],
    ]);

    $raw  = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'data' => null, 'http' => $http, 'error' => $err];
    }

    $data = json_decode($raw, true);
    $ok = ($http >= 200 && $http < 300)
        && is_array($data)
        && (($data['status'] ?? '') === 'SUCCESS');

    return ['ok' => $ok, 'data' => $data, 'http' => $http];
}

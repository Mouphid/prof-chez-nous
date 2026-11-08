<?php
/**
 * config/payment.php — Paiement en ligne, indépendant du prestataire.
 *
 * Le reste du site n'appelle qu'une seule fonction :
 *     payment_verify_transaction($transactionId)
 * Elle choisit automatiquement le « connecteur » du prestataire configuré.
 *
 * Changer de prestataire (Kkiapay ⇄ FedaPay ⇄ autre) :
 *   1. 'provider' => 'fedapay' dans config/payment_config.php ;
 *   2. renseigner les clés de ce prestataire ;
 *   3. compléter si besoin son connecteur ci-dessous.
 *
 * Le cœur du système (prix, table purchases, lien privé, anti-rejeu)
 * ne change jamais : seule la vérification de la transaction est propre
 * à chaque prestataire.
 *
 * Les clés se trouvent dans config/payment_config.php (non versionné).
 * Copier config/payment_config.example.php → config/payment_config.php
 */

/**
 * Configuration complète (valeurs par défaut + fichier de clés).
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
        'fedapay'  => [
            'public_key'  => '',
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

/**
 * Prestataire actif : 'kkiapay', 'fedapay', ...
 */
function payment_provider(): string {
    $cfg = payment_config();
    $provider = strtolower(trim((string)($cfg['provider'] ?? '')));
    return $provider !== '' ? $provider : 'kkiapay';
}

/**
 * Nom lisible du prestataire, pour les messages et le back-office.
 */
function payment_provider_label(?string $provider = null): string {
    $provider = $provider ?? payment_provider();
    $labels = [
        'kkiapay' => 'Kkiapay',
        'fedapay' => 'FedaPay',
    ];
    return $labels[$provider] ?? ucfirst($provider);
}

/**
 * Le prestataire peut-il encaisser directement sur le site (fenêtre) ?
 * true  = fenêtre de paiement intégrée (Kkiapay)
 * false = page de paiement hébergée, avec redirection (FedaPay)
 */
function payment_supports_widget(?string $provider = null): bool {
    return ($provider ?? payment_provider()) === 'kkiapay';
}

/**
 * Sommes-nous en mode test (sandbox) ?
 * En sandbox, aucun argent réel n'est encaissé.
 */
function payment_is_sandbox(): bool {
    $cfg = payment_config();
    return !empty($cfg['sandbox']);
}

/**
 * Les clés du prestataire actif sont-elles renseignées ?
 */
function payment_is_configured(): bool {
    $cfg = payment_config();

    switch (payment_provider()) {
        case 'kkiapay':
            return !empty($cfg['kkiapay']['public_key'])
                && !empty($cfg['kkiapay']['private_key'])
                && !empty($cfg['kkiapay']['secret_key']);

        case 'fedapay':
            return !empty($cfg['fedapay']['secret_key']);

        default:
            return false;
    }
}

function payment_amount_label(float $amount): string {
    $cfg = payment_config();
    $currency = strtoupper((string)($cfg['currency'] ?? 'XOF'));
    // Les visiteurs connaissent « FCFA » ; XOF est le code international.
    $label = $currency === 'XOF' ? 'FCFA' : $currency;
    return number_format($amount, 0, ',', ' ') . ' ' . $label;
}

/**
 * Vérifie une transaction auprès du prestataire actif.
 * Point d'entrée unique utilisé par payment_callback.php.
 *
 * Retour normalisé, identique quel que soit le prestataire :
 * [
 *   'ok'       => bool,             transaction réellement validée
 *   'provider' => string,           prestataire interrogé
 *   'http'     => int,              code HTTP reçu
 *   'amount'   => float|null,       montant confirmé par le prestataire
 *   'data'     => array|null,       réponse brute (journalisation)
 *   'error'    => string|null,      message technique éventuel
 * ]
 */
function payment_verify_transaction(string $transactionId): array {
    $provider = payment_provider();

    switch ($provider) {
        case 'kkiapay':
            return kkiapay_verify_transaction($transactionId);

        case 'fedapay':
            return fedapay_verify_transaction($transactionId);

        default:
            error_log('Paiement : prestataire inconnu — ' . $provider);
            return [
                'ok'       => false,
                'provider' => $provider,
                'http'     => 0,
                'amount'   => null,
                'data'     => null,
                'error'    => 'Prestataire de paiement non pris en charge.',
            ];
    }
}

/**
 * CONNECTEUR KKIAPAY
 * Vérification côté serveur : la seule source de vérité.
 */
function kkiapay_verify_transaction(string $transactionId): array {
    $cfg = payment_config();

    // Kkiapay utilise deux serveurs distincts :
    //   - test  : https://api-sandbox.kkiapay.me
    //   - réel  : https://api.kkiapay.me
    // L'adresse de vérification officielle est /api/v1/transactions/status
    // (voir le SDK officiel kkiapay/php-sdk).
    $base = !empty($cfg['sandbox'])
        ? 'https://api-sandbox.kkiapay.me'
        : 'https://api.kkiapay.me';
    $url = $base . '/api/v1/transactions/status';

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
            'X-API-KEY: ' . $cfg['kkiapay']['public_key'],
            'X-PRIVATE-KEY: ' . $cfg['kkiapay']['private_key'],
            'X-SECRET-KEY: ' . $cfg['kkiapay']['secret_key'],
        ],
    ]);

    $raw  = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return [
            'ok'       => false,
            'provider' => 'kkiapay',
            'http'     => $http,
            'amount'   => null,
            'data'     => null,
            'error'    => $err,
        ];
    }

    $data = json_decode($raw, true);
    $ok = ($http >= 200 && $http < 300)
        && is_array($data)
        && (($data['status'] ?? '') === 'SUCCESS');

    return [
        'ok'       => $ok,
        'provider' => 'kkiapay',
        'http'     => $http,
        'amount'   => is_array($data) ? (float)($data['amount'] ?? 0) : null,
        'data'     => is_array($data) ? $data : null,
        'error'    => null,
    ];
}

/**
 * CONNECTEUR FEDAPAY — à tester dès que le compte marchand sera validé.
 *
 * FedaPay valide une transaction via son API :
 *   GET https://api.fedapay.com/v1/transactions/{id}
 *   en-tête : Authorization: Bearer <clé secrète>
 * Une transaction réellement payée a le statut « approved ».
 */
function fedapay_verify_transaction(string $transactionId): array {
    $cfg = payment_config();
    $base = !empty($cfg['sandbox'])
        ? 'https://sandbox-api.fedapay.com/v1'
        : 'https://api.fedapay.com/v1';
    $url = $base . '/transactions/' . rawurlencode($transactionId);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Bearer ' . $cfg['fedapay']['secret_key'],
        ],
    ]);

    $raw  = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return [
            'ok'       => false,
            'provider' => 'fedapay',
            'http'     => $http,
            'amount'   => null,
            'data'     => null,
            'error'    => $err,
        ];
    }

    $data = json_decode($raw, true);
    $tx   = is_array($data) && isset($data['transaction']) ? $data['transaction'] : $data;

    $status = '';
    if (is_array($tx)) {
        $status = strtolower((string)($tx['status'] ?? ''));
    }

    $ok = ($http >= 200 && $http < 300) && $status === 'approved';

    return [
        'ok'       => $ok,
        'provider' => 'fedapay',
        'http'     => $http,
        'amount'   => is_array($tx) && isset($tx['amount']) ? (float)$tx['amount'] : null,
        'data'     => is_array($data) ? $data : null,
        'error'    => null,
    ];
}

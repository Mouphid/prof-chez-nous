<?php
/**
 * Copier ce fichier en config/payment_config.php puis renseigner les clés.
 * Ce fichier n'est JAMAIS publié sur Internet (voir .gitignore).
 *
 * Choisir UN prestataire avec 'provider' :
 *   'kkiapay' → fenêtre de paiement intégrée au site
 *   'fedapay' → page de paiement hébergée (redirection)
 */
return [
    'provider' => 'kkiapay',   // 'kkiapay' ou 'fedapay'
    'currency' => 'XOF',       // FCFA
    'sandbox'  => true,        // true = mode test, false = paiements réels

    // Tableau de bord Kkiapay → menu « Développeurs » → clés API
    'kkiapay' => [
        'public_key'  => 'VOTRE_CLE_PUBLIQUE',
        'private_key' => 'VOTRE_CLE_PRIVEE',
        'secret_key'  => 'VOTRE_CLE_SECRETE',
    ],

    // Tableau de bord FedaPay → « API » → clés
    'fedapay' => [
        'public_key' => 'VOTRE_CLE_PUBLIQUE',
        'secret_key' => 'VOTRE_CLE_SECRETE',
    ],
];

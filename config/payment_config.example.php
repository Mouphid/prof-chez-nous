<?php
// Copier ce fichier en config/payment_config.php et renseigner les clés Kkiapay.
// Tableau de bord Kkiapay → Menu « Développeurs » → clés API.
return [
    'provider' => 'kkiapay',
    'currency' => 'XOF',      // FCFA
    'sandbox'  => true,        // true = mode test, false = paiements réels

    'kkiapay' => [
        'public_key'  => 'VOTRE_CLE_PUBLIQUE',
        'private_key' => 'VOTRE_CLE_PRIVEE',
        'secret_key'  => 'VOTRE_CLE_SECRETE',
    ],
];

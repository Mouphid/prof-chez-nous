<?php
/**
 * Copier ce fichier en config/db_config.php sur le serveur (non versionné par Git).
 * Ne jamais commiter config/db_config.php.
 */
return [
    'host'  => getenv('DB_HOST') ?: 'REMPLACER_PAR_HOTE_MYSQL_OVH',
    'port'  => getenv('DB_PORT') ?: '3306',
    'name'  => getenv('DB_NAME') ?: 'REMPLACER_PAR_NOM_BASE_OVH',
    'user'  => getenv('DB_USER') ?: 'REMPLACER_PAR_UTILISATEUR_BASE_OVH',
    'pass'  => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'REMPLACER_PAR_MOT_DE_PASSE_MYSQL',
    'debug' => false, // false en production : masque les erreurs à l'écran et les écrit dans logs/
];

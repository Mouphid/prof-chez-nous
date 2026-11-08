<?php
/**
 * Copier ce fichier en config/mail_config.php sur le serveur et renseigner
 * le mot de passe de la boîte e-mail créée dans OVH (MXPlan inclus dans OVH Pro).
 *
 * Serveur SMTP OVH MXPlan : ssl0.ovh.net (port 587 STARTTLS ou 465 SSL).
 */
return [
    'host'       => 'ssl0.ovh.net',
    'username'   => 'contact@joie-enseignante.com',
    'password'   => 'MOT_DE_PASSE_DE_LA_BOITE_OVH',
    'port'       => 587,
    'encryption' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
    'from_email' => 'contact@joie-enseignante.com',
    'from_name'  => 'Joie Enseignante',
];

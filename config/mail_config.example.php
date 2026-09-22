<?php
// Copier ce fichier en mail_config.php et renseigner vos identifiants SMTP
return [
    'host'     => 'smtp.gmail.com',
    'username' => 'votre.email@gmail.com',
    'password' => 'votre-mot-de-passe-app',
    'port'     => 587,
    'encryption' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
    'from_email' => 'votre.email@gmail.com',
    'from_name'  => 'Joie Enseignante',
];

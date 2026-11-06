<?php
/**
 * config/mail.php — envoi d'e-mails via PHPMailer.
 *
 * Les identifiants SMTP sont dans config/mail_config.php (non versionné).
 * Si PHPMailer ou les identifiants manquent, une erreur claire est levée :
 * les pages appelantes peuvent alors prévenir l'utilisateur au lieu de
 * prétendre que l'e-mail est parti.
 */

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

use PHPMailer\PHPMailer\PHPMailer;

function getMailer(): PHPMailer {
    if (!class_exists(PHPMailer::class)) {
        throw new RuntimeException("PHPMailer n'est pas installé : lancer « composer install ».");
    }

    $config_file = __DIR__ . '/mail_config.php';
    if (file_exists($config_file)) {
        $cfg = require $config_file;
    } else {
        $cfg = [
            'host'       => '',
            'username'   => '',
            'password'   => '',
            'port'       => 587,
            'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
            'from_email' => '',
            'from_name'  => 'Joie Enseignante',
        ];
    }

    if (empty($cfg['host']) || empty($cfg['username']) || empty($cfg['password']) || empty($cfg['from_email'])) {
        throw new RuntimeException("Configuration SMTP incomplète : créer config/mail_config.php.");
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $cfg['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $cfg['username'];
    $mail->Password   = $cfg['password'];
    $mail->SMTPSecure = $cfg['encryption'];
    $mail->Port       = $cfg['port'];
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 20;
    $mail->setFrom($cfg['from_email'], $cfg['from_name']);
    $mail->addReplyTo($cfg['from_email'], $cfg['from_name']);

    return $mail;
}

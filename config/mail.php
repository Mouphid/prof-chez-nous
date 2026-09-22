<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(): PHPMailer {
    $config_file = __DIR__ . '/mail_config.php';
    if (file_exists($config_file)) {
        $cfg = require $config_file;
    } else {
        $cfg = [
            'host'       => 'smtp.gmail.com',
            'username'   => '',
            'password'   => '',
            'port'       => 587,
            'encryption' => PHPMailer::ENCRYPTION_STARTTLS,
            'from_email' => '',
            'from_name'  => 'Joie Enseignante',
        ];
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
    $mail->setFrom($cfg['from_email'], $cfg['from_name']);
    $mail->addReplyTo($cfg['from_email'], $cfg['from_name']);

    return $mail;
}

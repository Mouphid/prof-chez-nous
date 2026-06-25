<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    // --- Configuration SMTP ---
    // En développement : utilise Mailtrap (https://mailtrap.io)
    // En production : remplace par les vrais identifiants SMTP (Gmail, SendGrid, etc.)

    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'] ?? '';
    $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($_ENV['MAIL_FROM'] ?? 'noreply@joieenseignante.com', 'Joie Enseignante');

    return $mail;
}

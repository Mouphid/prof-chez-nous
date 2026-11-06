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

/**
 * Construit l'adresse de téléchargement d'un document acheté.
 * Le jeton est propre à la commande (colonne download_token).
 */
function purchase_download_url(int $file_id, string $token): string
{
    return rtrim(SITE_URL, '/') . '/public/download.php?id=' . $file_id
        . '&token=' . urlencode($token);
}

/**
 * Construit l'adresse d'accès à un cours ou à un chapitre acheté.
 * Le jeton est propre à la commande (colonne download_token).
 */
function course_purchase_url(array $purchase): string
{
    $token = (string)($purchase['download_token'] ?? '');
    if ($token === '') {
        return rtrim(SITE_URL, '/') . '/public/cours.php';
    }

    if (!empty($purchase['id_course'])) {
        $url = rtrim(SITE_URL, '/') . '/public/course.php?id=' . (int)$purchase['id_course'];
    } else {
        $url = rtrim(SITE_URL, '/') . '/public/course.php?item=' . (int)$purchase['id_item'];
    }

    return $url . '&token=' . urlencode($token);
}

/**
 * Envoie à l'acheteur le lien de son document (B1).
 *
 * @param array  $purchase  ligne de la table purchases (id_file, buyer_name, buyer_email,
 *                          amount, currency, download_token)
 * @param string $file_name nom du document, affiché dans l'e-mail
 * @throws RuntimeException si l'adresse, le jeton ou l'envoi posent problème
 */
function send_purchase_link_email(array $purchase, string $file_name = 'Votre document'): void
{
    $data = build_purchase_email($purchase, $file_name);

    $mail = getMailer();
    $mail->addAddress($data['to_email'], $data['to_name']);
    $mail->isHTML(true);
    $mail->Subject = $data['subject'];
    $mail->Body    = $data['html'];
    $mail->AltBody = $data['text'];

    $mail->send();
}

/**
 * Prépare le contenu de l'e-mail d'achat, sans l'envoyer.
 * Séparé de l'envoi pour pouvoir le vérifier facilement.
 *
 * @return array{to_email:string,to_name:string,subject:string,html:string,text:string,link:string}
 * @throws RuntimeException si l'adresse ou le jeton manque
 */
function build_purchase_email(array $purchase, string $file_name = 'Votre document'): array
{
    $email = trim((string)($purchase['buyer_email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Adresse e-mail de l'acheteur invalide ou absente.");
    }

    $token = (string)($purchase['download_token'] ?? '');
    if ($token === '') {
        throw new RuntimeException("Cette commande n'a pas de lien de téléchargement.");
    }

    $link    = purchase_download_url((int)($purchase['id_file'] ?? 0), $token);
    $nom     = trim((string)($purchase['buyer_name'] ?? ''));
    $bonjour = $nom !== '' ? 'Bonjour ' . $nom . ',' : 'Bonjour,';
    $montant = number_format((float)($purchase['amount'] ?? 0), 0, ',', ' ')
        . ' ' . (string)($purchase['currency'] ?? 'XOF');

    $safe_name = htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8');
    $safe_link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#1f2937;">
            <p style="font-size:15px;">' . htmlspecialchars($bonjour, ENT_QUOTES, 'UTF-8') . '</p>
            <p style="font-size:15px;line-height:1.6;">
                Merci pour votre achat. Votre document <strong>' . $safe_name . '</strong>
                est prêt à être téléchargé.
            </p>
            <p style="text-align:center;margin:28px 0;">
                <a href="' . $safe_link . '" style="background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">
                    Télécharger le document
                </a>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Montant réglé : ' . htmlspecialchars($montant, ENT_QUOTES, 'UTF-8') . '<br>
                Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :<br>
                <span style="word-break:break-all;">' . $safe_link . '</span>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Conservez cet e-mail : ce lien personnel vous permet de retélécharger le document.
            </p>
            <p style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:12px;margin-top:24px;">
                Joie Enseignante — ' . htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8') . '<br>
                Une question ? Répondez simplement à cet e-mail.
            </p>
        </div>';

    $text = $bonjour . "\n\n"
        . "Merci pour votre achat. Votre document « " . $file_name . " » est prêt.\n\n"
        . "Téléchargement : " . $link . "\n\n"
        . "Montant réglé : " . $montant . "\n\n"
        . "Conservez cet e-mail : ce lien personnel vous permet de retélécharger le document.\n\n"
        . "Joie Enseignante — " . SITE_URL . "\n";

    return [
        'to_email' => $email,
        'to_name'  => $nom,
        'subject'  => 'Votre document : ' . $file_name,
        'html'     => $html,
        'text'     => $text,
        'link'     => $link,
    ];
}

/**
 * Envoie à l'acheteur le lien d'accès à un cours ou à un chapitre.
 *
 * @param array  $purchase    ligne de la table purchases
 * @param string $title       titre du cours ou du chapitre
 * @param bool   $whole_course true = cours complet, false = chapitre seul
 * @throws RuntimeException si l'adresse, le jeton ou l'envoi posent problème
 */
function send_course_access_email(array $purchase, string $title, bool $whole_course = true): void
{
    $data = build_course_email($purchase, $title, $whole_course);

    $mail = getMailer();
    $mail->addAddress($data['to_email'], $data['to_name']);
    $mail->isHTML(true);
    $mail->Subject = $data['subject'];
    $mail->Body    = $data['html'];
    $mail->AltBody = $data['text'];

    $mail->send();
}

/**
 * Prépare le contenu de l'e-mail d'accès à un cours, sans l'envoyer.
 *
 * @return array{to_email:string,to_name:string,subject:string,html:string,text:string,link:string}
 * @throws RuntimeException si l'adresse ou le jeton manque
 */
function build_course_email(array $purchase, string $title, bool $whole_course = true): array
{
    $email = trim((string)($purchase['buyer_email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Adresse e-mail de l'acheteur invalide ou absente.");
    }

    $token = (string)($purchase['download_token'] ?? '');
    if ($token === '') {
        throw new RuntimeException("Cette commande n'a pas de lien d'accès.");
    }

    $link    = course_purchase_url($purchase);
    $nom     = trim((string)($purchase['buyer_name'] ?? ''));
    $bonjour = $nom !== '' ? 'Bonjour ' . $nom . ',' : 'Bonjour,';
    $montant = number_format((float)($purchase['amount'] ?? 0), 0, ',', ' ')
        . ' ' . (string)($purchase['currency'] ?? 'XOF');

    $objet   = $whole_course ? 'Votre accès au cours' : 'Votre accès au chapitre';
    $safe_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safe_link  = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#1f2937;">
            <p style="font-size:15px;">' . htmlspecialchars($bonjour, ENT_QUOTES, 'UTF-8') . '</p>
            <p style="font-size:15px;line-height:1.6;">
                Merci pour votre achat. Votre accès ' . ($whole_course ? 'au cours' : 'au chapitre') . ' <strong>' . $safe_title . '</strong>
                est maintenant ouvert.
            </p>
            <p style="text-align:center;margin:28px 0;">
                <a href="' . $safe_link . '" style="background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">
                    Ouvrir ' . ($whole_course ? 'le cours' : 'le chapitre') . '
                </a>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Montant réglé : ' . htmlspecialchars($montant, ENT_QUOTES, 'UTF-8') . '<br>
                Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :<br>
                <span style="word-break:break-all;">' . $safe_link . '</span>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Conservez cet e-mail : ce lien personnel vous permet de revenir sur ' . ($whole_course ? 'le cours' : 'ce chapitre') . '
                à tout moment. Il ne faut pas le partager.
            </p>
            <p style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:12px;margin-top:24px;">
                Joie Enseignante — ' . htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8') . '<br>
                Une question ? Répondez simplement à cet e-mail.
            </p>
        </div>';

    $text = $bonjour . "\n\n"
        . "Merci pour votre achat. Votre accès " . ($whole_course ? "au cours" : "au chapitre")
        . " « " . $title . " » est ouvert.\n\n"
        . "Lien d'accès : " . $link . "\n\n"
        . "Montant réglé : " . $montant . "\n\n"
        . "Conservez cet e-mail : ce lien personnel vous permet de revenir sur le contenu.\n\n"
        . "Joie Enseignante — " . SITE_URL . "\n";

    return [
        'to_email' => $email,
        'to_name'  => $nom,
        'subject'  => $objet . ' : ' . $title,
        'html'     => $html,
        'text'     => $text,
        'link'     => $link,
    ];
}

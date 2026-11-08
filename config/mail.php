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
 * Adresse de la page « Retrouver mon achat ».
 */
function recovery_url(): string
{
    return rtrim(SITE_URL, '/') . '/public/retrouver-mon-achat.php';
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
                Conservez cet e-mail : ce lien personnel vous permet de retélécharger le document.<br>
                Perdu ce lien plus tard ? <a href="' . htmlspecialchars(recovery_url(), ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">Retrouver mon achat</a>.
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
        . "Retrouver tous vos liens : " . recovery_url() . "\n\n"
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
                à tout moment. Il ne faut pas le partager.<br>
                Perdu ce lien plus tard ? <a href="' . htmlspecialchars(recovery_url(), ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">Retrouver mon achat</a>.
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
        . "Retrouver tous vos liens : " . recovery_url() . "\n\n"
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

/**
 * Envoie en un seul e-mail tous les liens d'accès retrouvés pour une adresse.
 *
 * @param array<int,array> $purchases lignes de purchases (avec file_name,
 *                                                 course_title ou item_title)
 * @throws RuntimeException si l'adresse, un lien ou l'envoi posent problème
 */
function send_recovery_email(array $purchases): void
{
    $data = build_recovery_email($purchases);

    $mail = getMailer();
    $mail->addAddress($data['to_email'], $data['to_name']);
    $mail->isHTML(true);
    $mail->Subject = $data['subject'];
    $mail->Body    = $data['html'];
    $mail->AltBody = $data['text'];

    $mail->send();
}

/**
 * Prépare l'e-mail « Retrouver mon achat », sans l'envoyer.
 * Un seul message regroupe tous les achats payés de l'adresse.
 *
 * @return array{to_email:string,to_name:string,subject:string,html:string,text:string,links:array<int,string>}
 * @throws RuntimeException si l'adresse ou les liens manquent
 */
function build_recovery_email(array $purchases): array
{
    $email = '';
    $nom   = '';
    $items = [];

    foreach ($purchases as $purchase) {
        $token = (string)($purchase['download_token'] ?? '');
        if ($token === '') {
            continue;
        }

        if ($email === '') {
            $email = trim((string)($purchase['buyer_email'] ?? ''));
        }
        if ($nom === '') {
            $nom = trim((string)($purchase['buyer_name'] ?? ''));
        }

        if (!empty($purchase['id_file'])) {
            $items[] = [
                'kind'  => 'Document',
                'title' => (string)(($purchase['file_name'] ?? '') ?: 'Votre document'),
                'link'  => purchase_download_url((int)$purchase['id_file'], $token),
                'date'  => (string)($purchase['paid_at'] ?? $purchase['created_at'] ?? ''),
            ];
        } elseif (!empty($purchase['id_course'])) {
            $items[] = [
                'kind'  => 'Cours complet',
                'title' => (string)(($purchase['course_title'] ?? '') ?: 'Votre cours'),
                'link'  => course_purchase_url($purchase),
                'date'  => (string)($purchase['paid_at'] ?? $purchase['created_at'] ?? ''),
            ];
        } elseif (!empty($purchase['id_item'])) {
            $items[] = [
                'kind'  => 'Chapitre',
                'title' => (string)(($purchase['item_title'] ?? '') ?: 'Votre chapitre'),
                'link'  => course_purchase_url($purchase),
                'date'  => (string)($purchase['paid_at'] ?? $purchase['created_at'] ?? ''),
            ];
        }
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Adresse e-mail de l'acheteur invalide ou absente.");
    }
    if (!$items) {
        throw new RuntimeException("Aucun lien d'accès n'est disponible pour cette adresse.");
    }

    $bonjour = $nom !== '' ? 'Bonjour ' . $nom . ',' : 'Bonjour,';
    $safe_bonjour = htmlspecialchars($bonjour, ENT_QUOTES, 'UTF-8');

    $html_items = '';
    $text_items = '';
    foreach ($items as $item) {
        $safe_title = htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8');
        $safe_kind  = htmlspecialchars($item['kind'], ENT_QUOTES, 'UTF-8');
        $safe_link  = htmlspecialchars($item['link'], ENT_QUOTES, 'UTF-8');
        $date       = $item['date'] !== '' ? format_date($item['date']) : '';
        $date_html  = $date !== ''
            ? ' <span style="color:#9ca3af;">· achat du ' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</span>'
            : '';

        $html_items .= '
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;margin:0 0 12px;">
                <p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">' . $safe_kind . '</p>
                <p style="margin:0 0 4px;font-size:16px;font-weight:bold;color:#111827;">' . $safe_title . '</p>
                <p style="margin:0 0 12px;font-size:12px;color:#9ca3af;">Lien personnel, à ne pas partager.' . $date_html . '</p>
                <a href="' . $safe_link . '" style="background:#2563eb;color:#ffffff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:bold;display:inline-block;font-size:14px;">
                    Ouvrir
                </a>
            </div>';

        $text_items .= '• ' . $item['kind'] . ' — ' . $item['title']
            . ($date !== '' ? ' (achat du ' . $date . ')' : '') . "\n"
            . '  ' . $item['link'] . "\n\n";
    }

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#1f2937;">
            <p style="font-size:15px;">' . $safe_bonjour . '</p>
            <p style="font-size:15px;line-height:1.6;">
                Vous avez demandé à retrouver vos achats. Voici les accès liés à cette adresse e-mail.
            </p>
            ' . $html_items . '
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Ces liens sont personnels : ne les partagez pas. Si vous n\'êtes pas à l\'origine de cette demande,
                vous pouvez ignorer cet e-mail.
            </p>
            <p style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:12px;margin-top:24px;">
                Joie Enseignante — ' . htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8') . '<br>
                Une question ? Écrivez-nous à ' . htmlspecialchars(SITE_EMAIL, ENT_QUOTES, 'UTF-8') . '.
            </p>
        </div>';

    $text = $bonjour . "\n\n"
        . "Vous avez demandé à retrouver vos achats. Voici les accès liés à cette adresse e-mail.\n\n"
        . $text_items
        . "Ces liens sont personnels : ne les partagez pas.\n"
        . "Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.\n\n"
        . "Joie Enseignante — " . SITE_URL . "\n";

    return [
        'to_email' => $email,
        'to_name'  => $nom,
        'subject'  => 'Vos liens d\'accès — Joie Enseignante',
        'html'     => $html,
        'text'     => $text,
        'links'    => array_column($items, 'link'),
    ];
}

/**
 * Adresse personnelle de désinscription d'un abonné.
 */
function newsletter_unsubscribe_url(string $token): string
{
    return rtrim(SITE_URL, '/') . '/public/desinscription.php?token=' . urlencode($token);
}

/**
 * Prépare un message de newsletter pour un abonné, sans l'envoyer.
 * Le texte saisi dans l'admin est transformé en paragraphes : il est échappé,
 * donc on ne peut pas y injecter de code HTML.
 *
 * @return array{to_email:string,to_name:string,subject:string,html:string,text:string,unsubscribe_url:string}
 * @throws RuntimeException si l'adresse ou le jeton manque
 */
function build_newsletter_email(array $subscriber, string $subject, string $body_text): array
{
    $email = trim((string)($subscriber['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Adresse e-mail d'abonné invalide ou absente.");
    }

    $token = (string)($subscriber['unsubscribe_token'] ?? '');
    if ($token === '') {
        throw new RuntimeException("Cet abonné n'a pas de lien de désinscription.");
    }

    $subject = trim($subject);
    if ($subject === '') {
        $subject = 'Newsletter Joie Enseignante';
    }

    $unsub = newsletter_unsubscribe_url($token);
    $safe_subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    // Un paragraphe par double saut de ligne, retour à la ligne simple conservé.
    $paragraphs = '';
    $text_body  = '';
    $blocs = preg_split('/\R{2,}/u', trim($body_text)) ?: [];
    foreach ($blocs as $bloc) {
        $bloc = trim($bloc);
        if ($bloc === '') {
            continue;
        }
        $paragraphs .= '<p style="font-size:15px;line-height:1.7;margin:0 0 16px;">'
            . nl2br(htmlspecialchars($bloc, ENT_QUOTES, 'UTF-8'), false)
            . '</p>';
        $text_body .= $bloc . "\n\n";
    }

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#1f2937;">
            <p style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#6b7280;margin:0 0 18px;">
                Lettre pédagogique — Joie Enseignante
            </p>
            <h1 style="font-size:22px;line-height:1.35;margin:0 0 22px;color:#111827;">' . $safe_subject . '</h1>
            ' . $paragraphs . '
            <p style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:14px;margin-top:28px;line-height:1.6;">
                Vous recevez cet e-mail parce que votre adresse est inscrite à la newsletter de Joie Enseignante.<br>
                <a href="' . htmlspecialchars($unsub, ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">Se désinscrire</a>
                · <a href="' . htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">joie-enseignante.com</a>
                · <a href="mailto:' . htmlspecialchars(SITE_EMAIL, ENT_QUOTES, 'UTF-8') . '" style="color:#2563eb;">Nous écrire</a>
            </p>
        </div>';

    $text = $subject . "\n\n"
        . $text_body
        . "—\n"
        . "Vous recevez cet e-mail parce que votre adresse est inscrite à la newsletter de Joie Enseignante.\n"
        . "Se désinscrire : " . $unsub . "\n"
        . "Nous écrire : " . SITE_EMAIL . "\n"
        . "Joie Enseignante — " . SITE_URL . "\n";

    return [
        'to_email'        => $email,
        'to_name'         => '',
        'subject'         => $subject,
        'html'            => $html,
        'text'            => $text,
        'unsubscribe_url' => $unsub,
    ];
}

/**
 * Envoie un message de newsletter à une liste d'abonnés.
 * Un seul serveur SMTP est ouvert pour tout l'envoi.
 *
 * @param array<int,array> $subscribers lignes de newsletter_subscribers
 * @return array{sent:int,failed:int,total:int,first_error:?string}
 * @throws RuntimeException si la configuration SMTP manque
 */
function send_newsletter_to_subscribers(array $subscribers, string $subject, string $body_text): array
{
    $total = count($subscribers);
    if ($total === 0) {
        return ['sent' => 0, 'failed' => 0, 'total' => 0, 'first_error' => null];
    }

    $mail  = getMailer();
    $sent  = 0;
    $failed = 0;
    $first_error = null;

    foreach ($subscribers as $subscriber) {
        try {
            $data = build_newsletter_email($subscriber, $subject, $body_text);

            $mail->clearAllRecipients();
            $mail->clearCustomHeaders();
            $mail->addAddress($data['to_email']);
            $mail->isHTML(true);
            $mail->Subject = $data['subject'];
            $mail->Body    = $data['html'];
            $mail->AltBody = $data['text'];
            $mail->addCustomHeader(
                'List-Unsubscribe',
                '<' . $data['unsubscribe_url'] . '>, <mailto:' . SITE_EMAIL . '?subject=Desinscription>'
            );

            $mail->send();
            $sent++;
        } catch (Throwable $e) {
            $failed++;
            if ($first_error === null) {
                $first_error = $e->getMessage();
            }
            error_log('Newsletter — envoi à ' . ($subscriber['email'] ?? '?') . ' : ' . $e->getMessage());
        }
    }

    return [
        'sent'        => $sent,
        'failed'      => $failed,
        'total'       => $total,
        'first_error' => $first_error,
    ];
}

/**
 * Adresse de réinitialisation du mot de passe administrateur.
 */
function admin_password_reset_url(string $token): string
{
    return rtrim(SITE_URL, '/') . '/admin/reset_password.php?token=' . urlencode($token);
}

/**
 * Prépare l'e-mail de réinitialisation, sans l'envoyer.
 * Le lien est valable une heure et ne peut servir qu'une fois.
 *
 * @return array{to_email:string,to_name:string,subject:string,html:string,text:string,link:string}
 * @throws RuntimeException si l'adresse manque
 */
function build_admin_reset_email(array $user, string $token): array
{
    $email = trim((string)($user['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Adresse e-mail du compte invalide ou absente.");
    }

    $link    = admin_password_reset_url($token);
    $nom     = trim((string)($user['name'] ?? ''));
    $bonjour = $nom !== '' ? 'Bonjour ' . $nom . ',' : 'Bonjour,';

    $safe_link    = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $safe_bonjour = htmlspecialchars($bonjour, ENT_QUOTES, 'UTF-8');

    $html = '
        <div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#1f2937;">
            <p style="font-size:15px;">' . $safe_bonjour . '</p>
            <p style="font-size:15px;line-height:1.6;">
                Vous avez demandé à réinitialiser votre mot de passe administrateur.
                Cliquez sur le bouton ci-dessous pour en choisir un nouveau.
            </p>
            <p style="text-align:center;margin:28px 0;">
                <a href="' . $safe_link . '" style="background:#1E3A5F;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">
                    Choisir un nouveau mot de passe
                </a>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Ce lien est valable <strong>1 heure</strong> et ne peut être utilisé qu\'une seule fois.<br>
                Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :<br>
                <span style="word-break:break-all;">' . $safe_link . '</span>
            </p>
            <p style="font-size:13px;color:#6b7280;line-height:1.6;">
                Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet e-mail :
                votre mot de passe reste inchangé.
            </p>
            <p style="font-size:13px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:12px;margin-top:24px;">
                Joie Enseignante — ' . htmlspecialchars(SITE_URL, ENT_QUOTES, 'UTF-8') . '
            </p>
        </div>';

    $text = $bonjour . "\n\n"
        . "Vous avez demandé à réinitialiser votre mot de passe administrateur.\n\n"
        . "Choisissez un nouveau mot de passe avec ce lien (valable 1 heure) :\n"
        . $link . "\n\n"
        . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail :\n"
        . "votre mot de passe reste inchangé.\n\n"
        . "Joie Enseignante — " . SITE_URL . "\n";

    return [
        'to_email' => $email,
        'to_name'  => $nom,
        'subject'  => 'Réinitialisation de votre mot de passe — Joie Enseignante',
        'html'     => $html,
        'text'     => $text,
        'link'     => $link,
    ];
}

/**
 * Envoie le lien de réinitialisation du mot de passe.
 *
 * @throws RuntimeException si l'envoi échoue
 */
function send_admin_reset_email(array $user, string $token): void
{
    $data = build_admin_reset_email($user, $token);

    $mail = getMailer();
    $mail->addAddress($data['to_email'], $data['to_name']);
    $mail->isHTML(true);
    $mail->Subject = $data['subject'];
    $mail->Body    = $data['html'];
    $mail->AltBody = $data['text'];

    $mail->send();
}

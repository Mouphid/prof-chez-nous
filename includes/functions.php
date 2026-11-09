<?php
// includes/functions.php

function redirect($url) {
    header("Location: $url");
    exit;
}

function old($key, $default = '') {
    return $_SESSION['old'][$key] ?? $default;
}

function flash($key, $message = null, $type = 'info') {
    if ($message === null) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    $_SESSION['flash'][$key] = $message;
    $_SESSION['flash_type'][$key] = $type;
}

function hasFlash($key) {
    return isset($_SESSION['flash'][$key]);
}

function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9 ]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Protège une valeur destinée à un fichier CSV.
 * Empêche Excel / LibreOffice d'interpréter la valeur comme une formule.
 */
function csv_safe(string $valeur): string {
    if ($valeur !== '' && in_array($valeur[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
        return "'" . $valeur;
    }
    return $valeur;
}

/**
 * Enregistre une action dans le journal de l'administration.
 *
 * @param string      $action      verbe : create, update, delete, login, send, settings…
 * @param string|null $target_type type de l'élément : post, course, item, file, comment…
 * @param int|null    $target_id   identifiant de l'élément
 * @param string      $details     courte description lisible
 */
function admin_activity(string $action, ?string $target_type = null, ?int $target_id = null, string $details = ''): void {
    global $pdo;

    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return;
    }

    $id_user = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $user_name = trim((string)($_SESSION['admin_name'] ?? ''));
    if ($user_name === '') {
        $user_name = 'Inconnu';
    }

    $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
    $details = trim($details);
    if (mb_strlen($details) > 255) {
        $details = mb_substr($details, 0, 255);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (id_user, user_name, action, target_type, target_id, details, ip)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id_user,
            $user_name,
            $action,
            $target_type,
            $target_id,
            $details !== '' ? $details : null,
            $ip !== '' ? $ip : null,
        ]);
    } catch (Throwable $e) {
        error_log('Journal admin — ' . $e->getMessage());
    }
}

/**
 * Slug lisible pour un cours : conserve les lettres accentuées transposées.
 * « Littérature Orale » → « litterature-orale ».
 */
function course_slug(string $text): string {
    $text = trim($text);
    if (function_exists('iconv')) {
        $translit = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($translit !== false) {
            $text = $translit;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'cours';
}

function generateToken($length = 32) {
    if (function_exists('random_bytes')) return bin2hex(random_bytes($length));
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 62))), 0, $length);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = generateToken();
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function format_date($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $dateObj = is_string($date) ? new DateTime($date) : $date;
    return $dateObj->format($format);
}

function truncate($text, $length = 100, $end = '...') {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $end;
}

/**
 * Remet un titre écrit ENTIÈREMENT EN MAJUSCULES en casse normale
 * (première lettre en majuscule, reste en minuscules).
 * Les titres déjà saisis normalement ne sont pas modifiés.
 * Les espaces multiples sont réduits à un seul.
 * Les noms propres courants du site retrouvent leur majuscule.
 */
function normalize_title_case($title) {
    $title = trim(preg_replace('/\s+/u', ' ', (string)$title));
    if ($title === '') return $title;

    $letters = preg_replace('/[^\p{L}]/u', '', $title);
    if ($letters === '' || $letters === null) return $title;

    $uppers = preg_replace('/[^\p{Lu}]/u', '', $title);
    $ratio  = mb_strlen($uppers) / mb_strlen($letters);

    // Moins de 70 % de majuscules : le titre est déjà lisible, on n'y touche pas.
    if ($ratio >= 0.7) {
        $title = mb_strtolower($title, 'UTF-8');

        // Majuscule au début et après une ponctuation forte.
        $title = preg_replace_callback(
            '/(^|[.!?]\s+|:\s+)(\p{L})/u',
            function ($m) { return $m[1] . mb_strtoupper($m[2], 'UTF-8'); },
            $title
        );
    }

    // Noms propres fréquents (ordre : les formes longues d'abord).
    $proper = [
        'sud-benin'     => 'Sud-Bénin',
        'bénin'         => 'Bénin',
        'benin'         => 'Bénin',
        'afrique'       => 'Afrique',
        'alèkpéhanhou'  => 'Alèkpéhanhou',
        'alokpon'       => 'Alokpon',
        'fɔn'           => 'Fɔn',
        'fon'           => 'Fon',
        'maxi'          => 'Maxi',
        'denagan'       => 'Denagan',
        'janvier'       => 'Janvier',
        'honfo'         => 'Honfo',
        'loucou'        => 'Loucou',
        'michel'        => 'Michel',
        'adedoyin'      => 'Adedoyin',
        'n’dje'         => 'N’Dje',
        "n'dje"         => "N'Dje",
        'djolokoko'     => 'Djolokoko',
        'honoré'        => 'Honoré',
        'honore'        => 'Honoré',
        'bleḱpon'       => 'Bleḱpon',
        'blekpon'       => 'Blekpon',
    ];
    foreach ($proper as $needle => $replacement) {
        $title = preg_replace(
            '/(?<![\p{L}])' . preg_quote($needle, '/') . '(?![\p{L}])/iu',
            $replacement,
            $title
        );
    }

    return $title;
}

/**
 * Vignette d'article au format uniforme 16:10.
 * Utilise l'image de l'article quand elle existe (recadrée dans le cadre),
 * sinon affiche un fond neutre avec une icône.
 */
function post_thumb_html(array $post, ?string $href = null): string {
    $alt = htmlspecialchars($post['title'] ?? '');

    if (!empty($post['main_image'])) {
        $inner = '<img src="../uploads/images/' . htmlspecialchars($post['main_image']) . '"'
               . ' alt="' . $alt . '" loading="lazy" class="sbr-thumb-img">';
    } else {
        $inner = '<span class="sbr-thumb-placeholder"><i class="ph ph-article" aria-hidden="true"></i></span>';
    }

    if ($href !== null) {
        return '<a href="' . htmlspecialchars($href) . '" class="sbr-thumb-16x10 sbr-thumb-photo" aria-hidden="true" tabindex="-1">' . $inner . '</a>';
    }
    return '<div class="sbr-thumb-16x10 sbr-thumb-photo" aria-hidden="true">' . $inner . '</div>';
}

function logger($message, $type = 'info') {
    $log_file = __DIR__ . '/../logs/app.log';
    $dir = dirname($log_file);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $date = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$date] [$type] $message\n", FILE_APPEND);
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Limitation des tentatives (connexion, formulaires sensibles).
 *
 * Le compteur est enregistré dans un petit fichier temporaire lié à
 * l'adresse IP : vider ses cookies ne suffit donc plus à recommencer.
 * Si le dossier temporaire n'est pas accessible, on retombe sur la session.
 */

function rate_limit_store_path(string $key): string {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
    $dir = sys_get_temp_dir() . '/joieenseignante-ratelimit';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    return $dir . '/' . hash('sha256', $key . '|' . $ip) . '.json';
}

function rate_limit_read(string $key): array {
    $path = rate_limit_store_path($key);
    if (is_file($path)) {
        $raw = @file_get_contents($path);
        if ($raw !== false) {
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['count'], $data['first'])) {
                return ['count' => (int)$data['count'], 'first' => (int)$data['first']];
            }
        }
    }

    // Repli : compteur en session (si /tmp est interdit par l'hébergeur).
    $session_key = 'rate_' . $key;
    if (isset($_SESSION[$session_key]) && is_array($_SESSION[$session_key])) {
        return [
            'count' => (int)($_SESSION[$session_key]['count'] ?? 0),
            'first' => (int)($_SESSION[$session_key]['first'] ?? 0),
        ];
    }
    return ['count' => 0, 'first' => 0];
}

function rate_limit_write(string $key, array $data): void {
    $path = rate_limit_store_path($key);
    $json = json_encode($data);

    if (@file_put_contents($path, $json, LOCK_EX) !== false) {
        // Nettoyage occasionnel des anciens fichiers.
        if (random_int(1, 100) === 1) {
            foreach ((array)@glob(dirname($path) . '/*.json') as $old) {
                if (@filemtime($old) < time() - 86400) {
                    @unlink($old);
                }
            }
        }
        return;
    }

    $_SESSION['rate_' . $key] = $data;
}

function check_rate_limit($key, $max_attempts = 5, $window = 300) {
    $attempts = rate_limit_read($key);

    if ($attempts['count'] >= $max_attempts) {
        if (time() - $attempts['first'] < $window) {
            return false;
        }
        rate_limit_write($key, ['count' => 0, 'first' => 0]);
    }
    return true;
}

function increment_rate_limit($key) {
    $attempts = rate_limit_read($key);
    if ($attempts['count'] === 0 || time() - $attempts['first'] >= 300) {
        $attempts = ['count' => 1, 'first' => time()];
    } else {
        $attempts['count']++;
    }
    rate_limit_write($key, $attempts);
}

function validate_mime($tmp_name, $allowed_mimes) {
    if (!file_exists($tmp_name) || !is_file($tmp_name)) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);
    return in_array($mime, $allowed_mimes, true);
}

function get_file_icon($type) {
    $icons = [
        'pdf' => 'pdf',
        'word' => 'doc',
        'audio' => 'audio',
        'video' => 'video',
        'image' => 'image'
    ];
    return $icons[$type] ?? 'alt';
}
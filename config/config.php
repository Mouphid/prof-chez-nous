<?php
// config/config.php
// Charge config/db_config.php s'il existe (fichier local de production hors Git),
// sinon lit les variables d'environnement ou utilise les valeurs de développement local.
$__db_cfg = file_exists(__DIR__ . '/db_config.php') ? (require __DIR__ . '/db_config.php') : [];

$host     = $__db_cfg['host'] ?? (getenv('DB_HOST') ?: 'localhost');
$port     = $__db_cfg['port'] ?? (getenv('DB_PORT') ?: '3306');
$db_name  = $__db_cfg['name'] ?? (getenv('DB_NAME') ?: 'joieenseignante');
$username = $__db_cfg['user'] ?? (getenv('DB_USER') ?: 'root');
$password = $__db_cfg['pass'] ?? (getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
$__debug  = $__db_cfg['debug'] ?? (getenv('APP_DEBUG') === '1');

if (!$__debug) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}
unset($__db_cfg);

// Connexion avec plusieurs tentatives (prod: localhost socket / dev local: 127.0.0.1:3306)
$attempts = [
    "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
    "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4",
    "mysql:host=127.0.0.1;port=$port;dbname=$db_name;charset=utf8mb4",
    "mysql:host=$host;dbname=JoieEnseignante;charset=utf8mb4",
    "mysql:host=127.0.0.1;port=$port;dbname=JoieEnseignante;charset=utf8mb4",
];

$pdo = null;
$last_error = null;
foreach ($attempts as $dsn) {
    try {
        $pdo = new PDO($dsn, $username, $password);
        break;
    } catch (PDOException $e) {
        $last_error = $e;
    }
}
if ($pdo === null) {
    error_log("Erreur DB: " . ($last_error ? $last_error->getMessage() : 'Aucune tentative'));
    die("Erreur de connexion à la base de données");
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

date_default_timezone_set('Africa/Porto-Novo');

require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Session idle timeout (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

// ─── BASE_URL (chemin web racine) ───
$project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
if ($doc_root !== '' && str_starts_with($project_root, $doc_root)) {
    $relative = substr($project_root, strlen($doc_root));
    define('BASE_URL', rtrim($relative, '/') . '/');
} else {
    // Fallback : déploiement à la racine du domaine
    define('BASE_URL', '/');
}

require_once __DIR__ . '/theme.php';
?>
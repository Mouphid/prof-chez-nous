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

function check_rate_limit($key, $max_attempts = 5, $window = 300) {
    $rate_key = 'rate_' . $key;
    $attempts = $_SESSION[$rate_key] ?? ['count' => 0, 'first' => 0];
    if ($attempts['count'] >= $max_attempts) {
        if (time() - $attempts['first'] < $window) {
            return false;
        }
        $_SESSION[$rate_key] = ['count' => 0, 'first' => 0];
    }
    return true;
}

function increment_rate_limit($key) {
    $rate_key = 'rate_' . $key;
    $attempts = $_SESSION[$rate_key] ?? ['count' => 0, 'first' => 0];
    if ($attempts['count'] === 0) $attempts['first'] = time();
    $attempts['count']++;
    $_SESSION[$rate_key] = $attempts;
}

function get_file_icon($type) {
    $icons = [
        'pdf' => 'pdf',
        'word' => 'word',
        'audio' => 'audio',
        'video' => 'video',
        'image' => 'image'
    ];
    return $icons[$type] ?? 'alt';
}
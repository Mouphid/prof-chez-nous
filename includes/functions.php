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

function sanitize($data) {
    if (is_array($data)) return array_map('sanitize', $data);
    return strip_tags(trim($data));
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

function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function update_setting($key, $value) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function upload_file($file, $type = 'docs', $allowed_types = []) {
    if (!isset($file['error']) || is_array($file['error'])) return ['error' => 'Fichier invalide'];
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Erreur upload: ' . $file['error']];
    
    $max_size = 10 * 1024 * 1024;
    if ($file['size'] > $max_size) return ['error' => 'Fichier trop volumineux (max 10MB)'];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = $allowed_types ?: ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'mp4', 'webm'];
    
    if (!in_array($extension, $allowed)) return ['error' => 'Type de fichier non autorisé'];
    
    $dir = __DIR__ . '/../uploads/' . $type;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $new_name = time() . '_' . generateToken(8) . '.' . $extension;
    $destination = $dir . '/' . $new_name;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'file_name' => $new_name,
            'file_path' => $type . '/' . $new_name,
            'file_size' => $file['size'],
            'file_type' => $extension
        ];
    }
    return ['error' => 'Échec du déplacement du fichier'];
}

function delete_file($file_path) {
    $full_path = __DIR__ . '/../uploads/' . $file_path;
    if (file_exists($full_path)) return unlink($full_path);
    return false;
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

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_admin() {
    if (!is_admin()) redirect('../admin/login.php');
}

function pagination($total_items, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $per_page);
    if ($total_pages <= 1) return '';
    
    $html = '<div class="pagination">';
    if ($current_page > 1) $html .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '">&laquo; Précédent</a>';
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        $active = $i === $current_page ? ' class="active"' : '';
        $html .= '<a href="' . $base_url . '?page=' . $i . '"' . $active . '>' . $i . '</a>';
    }
    if ($current_page < $total_pages) $html .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '">Suivant &raquo;</a>';
    $html .= '</div>';
    return $html;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
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
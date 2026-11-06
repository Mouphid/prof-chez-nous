<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_courses.php");
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de sécurité invalide.');
    header('Location: manage_courses.php');
    exit;
}

$id = (int)($_POST['id_course'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Cours introuvable.');
    header('Location: manage_courses.php');
    exit;
}

// Ne jamais supprimer un cours déjà vendu : l'acheteur perdrait son accès.
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM purchases
    WHERE status = 'paid'
      AND (
            id_course = ?
            OR id_item IN (SELECT id_item FROM course_items WHERE id_course = ?)
          )
");
$stmt->execute([$id, $id]);
if ((int)$stmt->fetchColumn() > 0) {
    flash('error', "Ce cours a déjà été vendu. Passe-le en « Brouillon » pour le retirer du site, sans supprimer l'accès des acheteurs.");
    header('Location: edit_course.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare("SELECT pdf_path, video_path FROM course_items WHERE id_course = ?");
$stmt->execute([$id]);
$files = $stmt->fetchAll();

foreach ($files as $file) {
    if (!empty($file['pdf_path'])) {
        $path = realpath(__DIR__ . '/../uploads/cours/pdf/' . basename($file['pdf_path']));
        $root = realpath(__DIR__ . '/../uploads/cours/pdf');
        if ($path && $root && str_starts_with($path, $root) && is_file($path)) {
            @unlink($path);
        }
    }
    if (!empty($file['video_path'])) {
        $path = realpath(__DIR__ . '/../uploads/cours/video/' . basename($file['video_path']));
        $root = realpath(__DIR__ . '/../uploads/cours/video');
        if ($path && $root && str_starts_with($path, $root) && is_file($path)) {
            @unlink($path);
        }
    }
}

$pdo->prepare("DELETE FROM courses WHERE id_course = ?")->execute([$id]);

flash('success', 'Cours supprimé.');
header('Location: manage_courses.php');
exit;

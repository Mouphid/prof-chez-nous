<?php
require_once "config/config.php";
$stats = [
    'posts_total' => $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
    'posts_published' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'posts_draft' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
    'comments_total' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    'comments_visible' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'visible'")->fetchColumn(),
    'comments_pending' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn(),
    'likes_total' => $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn(),
    'categories' => $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'files' => $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn(),
    'views_total' => $pdo->query("SELECT COALESCE(SUM(views),0) FROM posts")->fetchColumn()
];
echo json_encode($stats, JSON_PRETTY_PRINT);
?>
<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$id_post = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_post) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.name as author_name
    FROM posts p
    LEFT JOIN categories c ON p.id_category = c.id_category
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.id_post = ?
");
$stmt->execute([$id_post]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: index.php");
    exit;
}

$page_title = htmlspecialchars($post['title']);

$stmt_files = $pdo->prepare("SELECT * FROM files WHERE id_post = ?");
$stmt_files->execute([$id_post]);
$files = $stmt_files->fetchAll();

$stmt_comments = $pdo->prepare("SELECT * FROM comments WHERE id_post = ? AND status = 'visible' ORDER BY created_at DESC");
$stmt_comments->execute([$id_post]);
$comments = $stmt_comments->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="prof-header">
        <div class="header-container">
            <div class="logo">
                <div class="avatar"><i class="ph ph-user"></i></div>
                <div class="logo-text">
                    <h1>Prof. [Nom]</h1>
                    <p>Département de Littérature | Université de Cotonou</p>
                </div>
            </div>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php"><i class="ph ph-house"></i> Accueil</a></li>
                    <li><a href="profil.php"><i class="ph ph-user"></i> Profil</a></li>
                    <li><a href="publications.php"><i class="ph ph-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="ph ph-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="ph ph-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')"><i class="ph ph-list"></i></button>
        </div>
    </header>

    <main>
        <section class="hero compact">
            <div class="container">
                <a href="index.php" style="color: white; text-decoration: underline;">← Retour aux publications</a>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <article class="article-full">
                    <header class="article-header">
                        <span class="article-category"><?= htmlspecialchars($post['category_name'] ?? 'Article') ?></span>
                        <h1><?= htmlspecialchars($post['title']) ?></h1>
                        <div class="article-meta">
                            <span><i class="ph ph-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Auteur') ?></span>
                            <span><i class="ph ph-calendar"></i> <?= format_date($post['created_at']) ?></span>
                            <span><i class="ph ph-eye"></i> <?= $post['views'] ?? 0 ?> vues</span>
                        </div>
                    </header>

                    <?php if (!empty($post['main_image'])): ?>
                    <div class="article-image">
                        <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    </div>
                    <?php endif; ?>

                    <div class="article-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>

                    <?php if (!empty($post['embed_link'])): ?>
                    <div class="article-embed">
                        <h4><i class="ph ph-link"></i> Lien externe</h4>
                        <a href="<?= htmlspecialchars($post['embed_link']) ?>" target="_blank"><?= htmlspecialchars($post['embed_link']) ?></a>
                    </div>
                    <?php endif; ?>

                    <?php if (count($files) > 0): ?>
                    <div class="article-files">
                        <h4><i class="ph ph-download"></i> Fichiers attachés</h4>
                        <ul>
                            <?php foreach ($files as $file): ?>
                            <li><a href="../uploads/<?= htmlspecialchars($file['file_path'] ?: $file['file_name']) ?>" target="_blank"><i class="ph ph-file"></i> <?= htmlspecialchars($file['file_name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </article>

                <div class="comments-section">
                    <h3><i class="ph ph-chats"></i> Commentaires (<?= count($comments) ?>)</h3>
                    
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong><?= htmlspecialchars($comment['author_email'] ?? 'Anonyme') ?></strong>
                            <span><?= format_date($comment['created_at']) ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer class="prof-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?= htmlspecialchars($post['author_name'] ?? 'Prof. [Nom]') ?></h4>
                    <p>Département de Littérature<br>Université de Cotonou</p>
                </div>
                <div class="footer-section">
                    <h4>Liens rapides</h4>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="publications.php">Publications</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($post['author_name'] ?? 'Prof. [Nom]') ?> - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>

<style>
.article-full { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.article-header { margin-bottom: 32px; }
.article-category { display: inline-block; padding: 6px 16px; background: #8B5CF6; color: white; border-radius: 20px; font-size: 0.875rem; margin-bottom: 16px; }
.article-header h1 { font-size: 2rem; margin-bottom: 16px; }
.article-meta { display: flex; gap: 24px; color: #6B7280; }
.article-image { margin: 24px 0; }
.article-image img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px; }
.article-content { font-size: 1.0625rem; line-height: 1.9; color: #374151; margin: 32px 0; }
.article-content p { margin-bottom: 16px; }
.article-embed, .article-files { background: #F9FAFB; padding: 20px; border-radius: 12px; margin-top: 24px; }
.article-embed h4, .article-files h4 { margin-bottom: 12px; }
.article-files ul { list-style: none; }
.article-files li { padding: 8px 0; }
.comments-section { margin-top: 48px; }
.comments-section h3 { margin-bottom: 24px; }
.comment { background: white; padding: 20px; border-radius: 12px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.comment-header { display: flex; justify-content: space-between; margin-bottom: 8px; color: #6B7280; font-size: 0.875rem; }
</style>
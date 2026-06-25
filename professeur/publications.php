<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$page_title = "Publications - " . ($admin['name'] ?? 'Prof. Professeur');
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
                    <h1><?= htmlspecialchars($admin['name'] ?? 'Prof. Professeur') ?></h1>
                    <p>Département de Littérature | Université de Cotonou</p>
                </div>
            </div>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php"><i class="ph ph-house"></i> Accueil</a></li>
                    <li><a href="profil.php"><i class="ph ph-user"></i> Profil</a></li>
                    <li><a href="publications.php" class="active"><i class="ph ph-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="ph ph-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="ph ph-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')"><i class="ph ph-list"></i></button>
        </div>
    </header>

    <main>
        <section class="hero compact">
            <div class="container text-center">
                <h2><i class="ph ph-book"></i> Publications</h2>
                <p>Découvrez tous mes travaux de recherche en littérature et linguistique africaine</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="filters">
                    <button class="filter-btn active" data-filter="all">Tous (<?= $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn() ?>)</button>
                    <?php
                    $categories = $pdo->query("SELECT * FROM categories ORDER BY name");
                    while ($cat = $categories->fetch()):
                    ?>
                    <button class="filter-btn" data-filter="<?= $cat['id_category'] ?>">
                        <i class="ph ph-folder"></i> <?= htmlspecialchars($cat['name']) ?>
                    </button>
                    <?php endwhile; ?>
                </div>

                <div class="pub-section">
                    <h3 class="section-subtitle"><i class="ph ph-list-bullets"></i> Tous les articles</h3>
                    
                    <div class="pub-list">
                        <?php
                        $stmt = $pdo->query("
                            SELECT p.*, c.name as category_name, c.id_category
                            FROM posts p
                            LEFT JOIN categories c ON p.id_category = c.id_category
                            WHERE p.status = 'published'
                            ORDER BY p.created_at DESC
                        ");
                        while ($post = $stmt->fetch()):
                        ?>
                        <div class="pub-item" data-category="<?= $post['id_category'] ?? 0 ?>">
                            <div class="pub-icon-lg"><i class="ph ph-feather"></i></div>
                            <div class="pub-details">
                                <h4><?= htmlspecialchars($post['title']) ?></h4>
                                <p class="pub-authors">
                                    <i class="ph ph-folder"></i> <?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?>
                                </p>
                                <p class="pub-abstract"><?= truncate(strip_tags($post['content']), 200) ?></p>
                                <div class="pub-meta">
                                    <span><i class="ph ph-calendar"></i> <?= format_date($post['created_at']) ?></span>
                                    <span><i class="ph ph-eye"></i> <?= $post['views'] ?? 0 ?> vues</span>
                                </div>
                                <div class="pub-actions">
                                    <a href="article.php?id=<?= $post['id_post'] ?>" class="btn btn-sm btn-primary">
                                        <i class="ph ph-book-reader"></i> Lire l'article
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        
                        <?php if ($stmt->rowCount() == 0): ?>
                        <div class="empty-state">
                            <i class="ph ph-book-open"></i>
                            <p>Aucune publication pour le moment.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="prof-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><?= htmlspecialchars($admin['name'] ?? 'Prof. [Nom]') ?></h4>
                    <p>Département de Littérature<br>Université de Cotonou<br><?= htmlspecialchars($admin['email'] ?? '') ?></p>
                </div>
                <div class="footer-section">
                    <h4>Liens rapides</h4>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="profil.php">Profil</a></li>
                        <li><a href="publications.php">Publications</a></li>
                        <li><a href="cours.php">Cours</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Suivez-moi</h4>
                    <div class="social-links">
                        <a href="#"><i class="ph ph-linkedin-logo"></i></a>
                        <a href="#"><i class="ph ph-google-logo"></i></a>
                        <a href="#"><i class="ph ph-google-logo"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($admin['name'] ?? 'Prof. [Nom]') ?> - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                document.querySelectorAll('.pub-item').forEach(function(item) {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
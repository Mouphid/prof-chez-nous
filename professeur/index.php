<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$admin_name = htmlspecialchars($admin['name'] ?? 'Professeur');
$page_title = "Accueil - Prof. $admin_name";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="prof-header">
        <div class="header-container">
            <div class="logo">
                <div class="avatar"><i class="ph ph-user"></i></div>
                <div class="logo-text">
                    <h1>Prof. <?= $admin_name ?></h1>
                    <p>Département de Littérature | Université de Cotonou</p>
                </div>
            </div>
            
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php" class="active"><i class="ph ph-house"></i> Accueil</a></li>
                    <li><a href="profil.php"><i class="ph ph-user"></i> Profil</a></li>
                    <li><a href="publications.php"><i class="ph ph-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="ph ph-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="ph ph-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')">
                <i class="ph ph-list"></i>
            </button>
            <a href="../admin/login.php" class="btn-admin"><i class="ph ph-gear"></i> Admin</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h2>Bienvenue sur mon portail académique</h2>
                    <p>Chercheur passionné en littérature et culture, je partage mes travaux, mes réflexions sur la littérature orale et écrite d'Afrique.</p>
                    <div class="hero-buttons">
                        <a href="profil.php" class="btn btn-primary"><i class="ph ph-user"></i> Découvrir mon profil</a>
                        <a href="publications.php" class="btn btn-outline"><i class="ph ph-book"></i> Voir mes publications</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="profile-photo"><i class="ph ph-user-graduate"></i></div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <?php
                    $total_posts = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn() ?: 0;
                    $total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn() ?: 0;
                    $total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn() ?: 0;
                    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?: 0;
                    ?>
                    <div class="stat-item">
                        <i class="ph ph-book-open"></i>
                        <span class="stat-number"><?= $total_posts ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat-item">
                        <i class="ph ph-users"></i>
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Étudiants</span>
                    </div>
                    <div class="stat-item">
                        <i class="ph ph-microphone-alt"></i>
                        <span class="stat-number"><?= $total_categories ?></span>
                        <span class="stat-label">Catégories</span>
                    </div>
                    <div class="stat-item">
                        <i class="ph ph-chats"></i>
                        <span class="stat-number"><?= $total_comments ?></span>
                        <span class="stat-label">Commentaires</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <h3 class="section-title"><i class="ph ph-star"></i> Publications récentes</h3>
                <div class="publications-grid">
                    <?php
                    $stmt = $pdo->query("
                        SELECT p.*, c.name as category_name
                        FROM posts p
                        LEFT JOIN categories c ON p.id_category = c.id_category
                        WHERE p.status = 'published'
                        ORDER BY p.created_at DESC
                        LIMIT 3
                    ");
                    while ($post = $stmt->fetch()):
                    ?>
                    <article class="publication-card">
                        <div class="pub-icon"><i class="ph ph-feather"></i></div>
                        <span class="pub-type"><?= htmlspecialchars($post['category_name'] ?? 'Article') ?></span>
                        <h4><?= truncate(htmlspecialchars($post['title']), 60) ?></h4>
                        <p class="pub-abstract"><?= truncate(strip_tags($post['content']), 150) ?></p>
                        <div class="pub-meta">
                            <span><i class="ph ph-calendar"></i> <?= format_date($post['created_at']) ?></span>
                            <span><i class="ph ph-eye"></i> <?= $post['views'] ?? 0 ?> vues</span>
                        </div>
                        <a href="article.php?id=<?= $post['id_post'] ?>" class="btn btn-sm">Lire plus</a>
                    </article>
                    <?php endwhile; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="publications.php" class="btn btn-secondary">Toutes les publications <i class="ph ph-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <section class="section bg-light">
            <div class="container">
                <h3 class="section-title"><i class="ph ph-folder"></i> Catégories</h3>
                <div class="courses-grid">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name LIMIT 6");
                    while ($cat = $stmt->fetch()):
                    ?>
                    <div class="course-card">
                        <div class="course-header"><i class="ph ph-folder-open"></i></div>
                        <h4><?= htmlspecialchars($cat['name']) ?></h4>
                        <p><?= truncate(strip_tags($cat['description'] ?? ''), 80) ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="contact-cta">
                    <div class="cta-content">
                        <h3>Vous avez des questions ?</h3>
                        <p>N'hésitez pas à me contacter pour toute question académique ou collaboration.</p>
                    </div>
                    <a href="contact.php" class="btn btn-primary btn-lg"><i class="ph ph-envelope"></i> Me contacter</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="prof-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Prof. <?= $admin_name ?></h4>
                    <p>Département de Littérature<br>Université de Cotonou</p>
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
                <p>&copy; <?= date('Y') ?> Prof. <?= $admin_name ?> - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
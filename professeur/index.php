<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Accueil - Prof. [Nom du Professeur]";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="prof-header">
        <div class="header-container">
            <div class="logo">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <div class="logo-text">
                    <h1>Prof. [Nom du Professeur]</h1>
                    <p>Département de Littérature | Université de Cotonou</p>
                </div>
            </div>
            
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                    <li><a href="publications.php"><i class="fas fa-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="fas fa-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')">
                <i class="fas fa-bars"></i>
            </button>
            <a href="../admin/login.php" class="btn-admin"><i class="fas fa-cog"></i> Admin</a>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h2>Bienvenue sur mon portail académique</h2>
                    <p>Chercheur passionné en littérature et culture, je partage mes travaux, mes réflexions sur la littérature orale et écrite d'Afrique.</p>
                    <div class="hero-buttons">
                        <a href="profil.php" class="btn btn-primary"><i class="fas fa-user"></i> Découvrir mon profil</a>
                        <a href="publications.php" class="btn btn-outline"><i class="fas fa-book"></i> Voir mes publications</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="profile-photo"><i class="fas fa-user-graduate"></i></div>
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
                        <i class="fas fa-book-open"></i>
                        <span class="stat-number"><?= $total_posts ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Étudiants</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-microphone-alt"></i>
                        <span class="stat-number"><?= $total_categories ?></span>
                        <span class="stat-label">Catégories</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-comments"></i>
                        <span class="stat-number"><?= $total_comments ?></span>
                        <span class="stat-label">Commentaires</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <h3 class="section-title"><i class="fas fa-star"></i> Publications récentes</h3>
                <div class="publications-grid">
                    <?php
                    $stmt = $pdo->query("
                        SELECT p.*, c.name as category_name
                        FROM posts p
                        LEFT JOIN categories c ON p.id_category = c.id_category
                        WHERE p.status = 'published' OR p.status = 'draft'
                        ORDER BY p.created_at DESC
                        LIMIT 3
                    ");
                    while ($post = $stmt->fetch()):
                    ?>
                    <article class="publication-card">
                        <div class="pub-icon"><i class="fas fa-feather-alt"></i></div>
                        <span class="pub-type"><?= htmlspecialchars($post['category_name'] ?? 'Article') ?></span>
                        <h4><?= truncate(htmlspecialchars($post['title']), 60) ?></h4>
                        <p class="pub-abstract"><?= truncate(strip_tags($post['content']), 150) ?></p>
                        <div class="pub-meta">
                            <span><i class="fas fa-calendar"></i> <?= format_date($post['created_at']) ?></span>
                            <span><i class="fas fa-eye"></i> <?= $post['views'] ?? 0 ?> vues</span>
                        </div>
                        <a href="article.php?id=<?= $post['id_post'] ?>" class="btn btn-sm">Lire plus</a>
                    </article>
                    <?php endwhile; ?>
                </div>
                <div class="text-center mt-3">
                    <a href="publications.php" class="btn btn-secondary">Toutes les publications <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <section class="section bg-light">
            <div class="container">
                <h3 class="section-title"><i class="fas fa-folder"></i> Catégories</h3>
                <div class="courses-grid">
                    <?php
                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name LIMIT 6");
                    while ($cat = $stmt->fetch()):
                    ?>
                    <div class="course-card">
                        <div class="course-header"><i class="fas fa-folder-open"></i></div>
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
                    <a href="contact.php" class="btn btn-primary btn-lg"><i class="fas fa-envelope"></i> Me contacter</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="prof-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Prof. [Nom]</h4>
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
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-researchgate"></i></a>
                        <a href="#"><i class="fab fa-google"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Prof. [Nom] - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
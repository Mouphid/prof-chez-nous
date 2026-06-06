<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$page_title = "Profil - " . ($admin['name'] ?? 'Professeur');
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
</head>
<body>
    <header class="prof-header">
        <div class="header-container">
            <div class="logo">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <div class="logo-text">
                    <h1><?= htmlspecialchars($admin['name'] ?? 'Prof. Professeur') ?></h1>
                    <p>Département de Littérature | Université de Cotonou</p>
                </div>
            </div>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="profil.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
                    <li><a href="publications.php"><i class="fas fa-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="fas fa-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <main>
        <section class="hero compact">
            <div class="container">
                <div class="profile-intro">
                    <div class="profile-photo-lg">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($admin['name'] ?? 'Prof. Professeur') ?></h2>
                        <p class="title-line"><i class="fas fa-university"></i> Professeur - Université de Cotonou</p>
                        <p class="title-line"><i class="fas fa-map-marker-alt"></i> Cotonou, Bénin</p>
                        <p class="title-line"><i class="fas fa-envelope"></i> <?= htmlspecialchars($admin['email'] ?? '') ?></p>
                        <div class="profile-social">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-researchgate"></i></a>
                            <a href="#"><i class="fas fa-graduation-cap"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="profile-grid">
                    <div class="profile-main">
                        <div class="profile-card">
                            <h3><i class="fas fa-user"></i> Biographie</h3>
                            <p>Professeur de littérature passionné, je dédié ma carrière à l'étude et à la promotion des lettres africaines. Mon travail se concentre sur la littérature orale, la critique littéraire et la didactique de la littérature.</p>
                            <p>Avec plus de 15 ans d'expérience dans l'enseignement supérieur, j'ai formé des centaines d'étudiants qui travaillent aujourd'hui dans l'éducation, la culture et les médias.</p>
                        </div>

                        <div class="profile-card">
                            <h3><i class="fas fa-search"></i> Domaines de recherche</h3>
                            <div class="tags">
                                <?php
                                $cats = $pdo->query("SELECT name FROM categories LIMIT 6");
                                while ($cat = $cats->fetch()):
                                ?>
                                <span class="tag"><?= htmlspecialchars($cat['name']) ?></span>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="profile-card">
                            <h3><i class="fas fa-award"></i> Parcours</h3>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <span class="timeline-date">2024</span>
                                    <div class="timeline-content">
                                        <h4>Prix de la Meilleure Publication</h4>
                                        <p>Colloque International de Littérature Africaine</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <span class="timeline-date">2010</span>
                                    <div class="timeline-content">
                                        <h4>Doctorat en Littérature</h4>
                                        <p>Université Félix Houphouët-Boigny</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-sidebar">
                        <div class="info-card">
                            <h3><i class="fas fa-info-circle"></i> Statistiques</h3>
                            <ul class="info-list">
                                <li><i class="fas fa-book"></i> <strong>Articles:</strong> <?= $total_posts ?></li>
                                <li><i class="fas fa-comments"></i> <strong>Commentaires:</strong> <?= $total_comments ?></li>
                                <li><i class="fas fa-heart"></i> <strong>Likes:</strong> <?= $total_likes ?></li>
                                <li><i class="fas fa-users"></i> <strong>Utilisateurs:</strong> <?= $total_users ?></li>
                            </ul>
                        </div>

                        <div class="info-card">
                            <h3><i class="fas fa-folder"></i> Catégories</h3>
                            <ul class="info-list">
                                <?php
                                $cats = $pdo->query("SELECT c.*, COUNT(p.id_post) as total FROM categories c LEFT JOIN posts p ON c.id_category = p.id_category GROUP BY c.id_category");
                                while ($cat = $cats->fetch()):
                                ?>
                                <li><i class="fas fa-folder-open"></i> <?= htmlspecialchars($cat['name']) ?> (<?= $cat['total'] ?>)</li>
                                <?php endwhile; ?>
                            </ul>
                        </div>

                        <div class="info-card">
                            <h3><i class="fas fa-graduation-cap"></i>Formation</h3>
                            <div class="edu-item">
                                <h4>Doctorat en Littérature</h4>
                                <p>Université Félix Houphouët-Boigny, 2010</p>
                            </div>
                            <div class="edu-item">
                                <h4>Master en Lettres Modernes</h4>
                                <p>Université d'Abomey-Calavi, 2005</p>
                            </div>
                        </div>
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
                <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($admin['name'] ?? 'Prof. [Nom]') ?> - Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
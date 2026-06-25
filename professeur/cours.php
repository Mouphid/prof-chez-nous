<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$page_title = "Cours - " . ($admin['name'] ?? 'Prof. Professeur');
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
    <style>
        .hero { background: linear-gradient(135deg, #1F2937 0%, #374151 100%); }
        .course-header-full { background: linear-gradient(135deg, #8B5CF6, #A78BFA); }
        .course-icon-lg { background: rgba(255,255,255,0.2); }
        .course-meta-card li i { color: #8B5CF6; }
        .resource-btn:hover { background: #8B5CF6; }
        .resource-btn i { color: #8B5CF6; }
        .resource-btn:hover i { color: white; }
        .chapter:hover { background: #F3E8FF; }
        .ch-num { background: linear-gradient(135deg, #8B5CF6, #A78BFA); }
    </style>
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
                    <li><a href="publications.php"><i class="ph ph-book"></i> Publications</a></li>
                    <li><a href="cours.php" class="active"><i class="ph ph-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php"><i class="ph ph-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')"><i class="ph ph-list"></i></button>
        </div>
    </header>

    <main>
        <section class="hero compact">
            <div class="container text-center">
                <h2><i class="ph ph-graduation-cap"></i> Cours enseignés</h2>
                <p>Ressources pédagogiques pour mes étudiants en littérature</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="courses-container">
                    
                    <div class="course-full-card">
                        <div class="course-header-full">
                            <div class="course-icon-lg"><i class="ph ph-book-open"></i></div>
                            <div>
                                <span class="course-code-badge">LIT301</span>
                                <h3>Introduction à la Littérature</h3>
                            </div>
                        </div>
                        
                        <div class="course-content-grid">
                            <div class="course-main-info">
                                <div class="info-block">
                                    <h4><i class="ph ph-info"></i> Description</h4>
                                    <p>Ce cours présente les bases de l'analyse littéraire : genres, courants littéraires, figures de style et méthodes d'interprétation des textes.</p>
                                </div>
                                
                                <div class="info-block">
                                    <h4><i class="ph ph-target"></i> Objectifs</h4>
                                    <ul class="objectives-list">
                                        <li>Maîtriser les concepts fondamentaux de la littérature</li>
                                        <li>Analyser un texte littéraire avec rigueur</li>
                                        <li>Identifier les genres et courants littéraires</li>
                                        <li>Rédaction de commentaires composés</li>
                                    </ul>
                                </div>
                                
                                <div class="info-block">
                                    <h4><i class="ph ph-list-bullets"></i> Plan du cours</h4>
                                    <div class="chapter-list">
                                        <div class="chapter">
                                            <span class="ch-num">01</span>
                                            <div>
                                                <h5>Qu'est-ce que la littérature ?</h5>
                                                <p>Définitions, enjeux et fonctions de la littérature</p>
                                            </div>
                                        </div>
                                        <div class="chapter">
                                            <span class="ch-num">02</span>
                                            <div>
                                                <h5>Les genres littéraires</h5>
                                                <p>Poésie, roman, théâtre, essayistique</p>
                                            </div>
                                        </div>
                                        <div class="chapter">
                                            <span class="ch-num">03</span>
                                            <div>
                                                <h5>Figures de style</h5>
                                                <p>Métaphores, métonymies, allitérations...</p>
                                            </div>
                                        </div>
                                        <div class="chapter">
                                            <span class="ch-num">04</span>
                                            <div>
                                                <h5>Courants littéraires</h5>
                                                <p>Classicisme, romantisme, naturalisme...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="course-sidebar-info">
                                <div class="course-meta-card">
                                    <h4><i class="ph ph-chart-line"></i> Détails</h4>
                                    <ul>
                                        <li><i class="ph ph-clock"></i> <strong>Volume:</strong> 45 heures</li>
                                        <li><i class="ph ph-users"></i> <strong>Niveau:</strong> Licence 3</li>
                                        <li><i class="ph ph-calendar"></i> <strong>Semestre:</strong> Pair</li>
                                        <li><i class="ph ph-file-alt"></i> <strong>Credits:</strong> 6</li>
                                    </ul>
                                </div>
                                
                                <div class="course-resources">
                                    <h4><i class="ph ph-download"></i> Ressources</h4>
                                    <a href="#" class="resource-btn"><i class="ph ph-file-pdf"></i> Polycopié</a>
                                    <a href="#" class="resource-btn"><i class="ph ph-pencil-alt"></i> TDs</a>
                                    <a href="#" class="resource-btn"><i class="ph ph-checklist"></i> Examens</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="course-full-card">
                        <div class="course-header-full">
                            <div class="course-icon-lg"><i class="ph ph-feather"></i></div>
                            <div>
                                <span class="course-code-badge">LIT401</span>
                                <h3>Littérature Orale Africaine</h3>
                            </div>
                        </div>
                        
                        <div class="course-content-grid">
                            <div class="course-main-info">
                                <div class="info-block">
                                    <h4><i class="ph ph-info"></i> Description</h4>
                                    <p>Étude approfondie des genres de la littérature orale africaine : contes, légendes, mythes, proverbes, devinettes et chants traditionnels.</p>
                                </div>
                                
                                <div class="info-block">
                                    <h4><i class="ph ph-target"></i> Objectifs</h4>
                                    <ul class="objectives-list">
                                        <li>Identifier et classer les genres oraux</li>
                                        <li>Comprendre les fonctions sociales de l'oraliture</li>
                                        <li>Analyser les techniques de narration traditionnelle</li>
                                        <li>Étudier la transmission orale et ses enjeux</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="course-sidebar-info">
                                <div class="course-meta-card">
                                    <h4><i class="ph ph-chart-line"></i> Détails</h4>
                                    <ul>
                                        <li><i class="ph ph-clock"></i> <strong>Volume:</strong> 60 heures</li>
                                        <li><i class="ph ph-users"></i> <strong>Niveau:</strong> Master 1</li>
                                        <li><i class="ph ph-calendar"></i> <strong>Semestre:</strong> Impair</li>
                                        <li><i class="ph ph-file-alt"></i> <strong>Credits:</strong> 8</li>
                                    </ul>
                                </div>
                                
                                <div class="course-resources">
                                    <h4><i class="ph ph-download"></i> Ressources</h4>
                                    <a href="#" class="resource-btn"><i class="ph ph-file-pdf"></i> Cours</a>
                                    <a href="#" class="resource-btn"><i class="ph ph-microphone"></i> Enregistrements</a>
                                    <a href="#" class="resource-btn"><i class="ph ph-book"></i> Corpus</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="course-full-card">
                        <div class="course-header-full">
                            <div class="course-icon-lg"><i class="ph ph-pen-fancy"></i></div>
                            <div>
                                <span class="course-code-badge">LIT302</span>
                                <h3>Critique Littéraire</h3>
                            </div>
                        </div>
                        
                        <div class="course-content-grid">
                            <div class="course-main-info">
                                <div class="info-block">
                                    <h4><i class="ph ph-info"></i> Description</h4>
                                    <p>Initiation aux différentes méthodes de critique littéraire : narratologie, analyse du discours, sociocritique et analyse psy.</p>
                                </div>
                                
                                <div class="info-block">
                                    <h4><i class="ph ph-target"></i> Objectifs</h4>
                                    <ul class="objectives-list">
                                        <li>Maîtriser les outils d'analyse textuelle</li>
                                        <li>Appliquer les méthodes de critique</li>
                                        <li>Rédiger une analyse critiqueargumentée</li>
                                        <li>Évaluer les travaux scientifiques en littérature</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="course-sidebar-info">
                                <div class="course-meta-card">
                                    <h4><i class="ph ph-chart-line"></i> Détails</h4>
                                    <ul>
                                        <li><i class="ph ph-clock"></i> <strong>Volume:</strong> 45 heures</li>
                                        <li><i class="ph ph-users"></i> <strong>Niveau:</strong> Licence 3</li>
                                        <li><i class="ph ph-calendar"></i> <strong>Semestre:</strong> Impair</li>
                                        <li><i class="ph ph-file-alt"></i> <strong>Credits:</strong> 6</li>
                                    </ul>
                                </div>
                                
                                <div class="course-resources">
                                    <h4><i class="ph ph-download"></i> Ressources</h4>
                                    <a href="#" class="resource-btn"><i class="ph ph-file-pdf"></i> Méthodologie</a>
                                    <a href="#" class="resource-btn"><i class="ph ph-file-alt"></i> Textes d'application</a>
                                </div>
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
</body>
</html>
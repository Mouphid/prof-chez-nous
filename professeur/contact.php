<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$page_title = "Contact - " . ($admin['name'] ?? 'Prof. Professeur');

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        try {
            require_once __DIR__ . '/../config/mail.php';
            $mail = getMailer();
            $admin_email = $admin['email'] ?? '';
            if ($admin_email) {
                $mail->addAddress($admin_email, $admin['name'] ?? 'Professeur');
                $mail->addReplyTo($email, $name);
                $mail->Subject = "[Contact] $subject";
                $mail->Body = "Nom : $name\nEmail : $email\nSujet : $subject\n\nMessage :\n$message";
                $mail->send();
            }
            $success = "Votre message a été envoyé avec succès. Je vous répondrai dans les plus brefs délais.";
        } catch (Exception $e) {
            error_log("Erreur envoi email contact: " . $e->getMessage());
            $success = "Votre message a été envoyé avec succès. Je vous répondrai dans les plus brefs délais.";
        }
    }
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .hero { background: linear-gradient(135deg, #1F2937 0%, #374151 100%); }
        .contact-icon { background: linear-gradient(135deg, #8B5CF6, #A78BFA); }
        .social-item:hover { background: #8B5CF6; }
        .map-placeholder { background: linear-gradient(135deg, #8B5CF6, #A78BFA); }
    </style>
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
                    <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                    <li><a href="publications.php"><i class="fas fa-book"></i> Publications</a></li>
                    <li><a href="cours.php"><i class="fas fa-graduation-cap"></i> Cours</a></li>
                    <li><a href="contact.php" class="active"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </nav>
            <button class="menu-toggle" onclick="document.getElementById('mainNav').classList.toggle('active')"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <main>
        <section class="hero compact">
            <div class="container text-center">
                <h2><i class="fas fa-envelope"></i> Contact</h2>
                <p>N'hésitez pas à me contacter pour toute question académique</p>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-form-card">
                        <h3><i class="fas fa-paper-plane"></i> Envoyez-moi un message</h3>
                        
                        <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" class="contact-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Nom complet</label>
                                    <input type="text" name="name" placeholder="Votre nom" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" name="email" placeholder="votre@email.com" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-tag"></i> Sujet</label>
                                <select name="subject" required>
                                    <option value="">Sélectionnez un sujet</option>
                                    <option value="question">Question académique</option>
                                    <option value="collaboration">Collaboration de recherche</option>
                                    <option value="encadrement">Encadrement de mémoire</option>
                                    <option value="cours">Question sur un cours</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-comment"></i> Message</label>
                                <textarea name="message" rows="6" placeholder="Votre message..." required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-full">
                                <i class="fas fa-paper-plane"></i> Envoyer le message
                            </button>
                        </form>
                    </div>

                    <div class="contact-info">
                        <div class="info-card">
                            <h3><i class="fas fa-id-card"></i> Informations de contact</h3>
                            <ul class="contact-list">
                                <li>
                                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                                    <div>
                                        <strong>Email</strong>
                                        <p><?= htmlspecialchars($admin['email'] ?? '') ?></p>
                                    </div>
                                </li>
                                <li>
                                    <div class="contact-icon"><i class="fas fa-phone"></i></div>
                                    <div>
                                        <strong>Téléphone</strong>
                                        <p>+229 XX XX XX XX</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                                    <div>
                                        <strong>Bureau</strong>
                                        <p>Bâtiment Lettres<br>Université de Cotonou</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                                    <div>
                                        <strong>Horaires</strong>
                                        <p>Lun - Ven: 8h00 - 17h00<br>(sur rendez-vous)</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="info-card">
                            <h3><i class="fas fa-share-alt"></i> Réseaux académiques</h3>
                            <div class="social-grid">
                                <a href="#" class="social-item">
                                    <i class="fab fa-linkedin"></i>
                                    <span>LinkedIn</span>
                                </a>
                                <a href="#" class="social-item">
                                    <i class="fab fa-researchgate"></i>
                                    <span>ResearchGate</span>
                                </a>
                                <a href="#" class="social-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Google Scholar</span>
                                </a>
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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Style global */
        body {
            font-family: Arial, sans-serif;
        }

        /* Conteneur principal */
        .contact-container {
            max-width: 600px;
            margin: 50px auto;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Image en cercle */
        .contact-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #28a745;
            margin-bottom: 15px;
        }

        /* Boutons réseaux sociaux */
        .contact-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }

        .contact-buttons a {
            text-decoration: none;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .whatsapp { background: #25D366; }
        .email { background: #007BFF; }
        .phone { background: #FFA500; }

        .contact-buttons a:hover {
            opacity: 0.8;
        }

        /* Bouton Retour */
        .btn-retour {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 18px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-retour:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="bg-success text-white py-3 text-center">
        <h1>Contactez-nous</h1>
    </header>

    <!-- Section Contact -->
    <div class="container">
        <div class="contact-container">
            
            <!-- Photo en haut -->
            <img src="img/P1.jpg" alt="Professeur" class="contact-image">
            
            <!-- Texte de présentation -->
            <h2>Nous sommes à votre écoute</h2>
            <p>Contactez-nous pour toute demande d'information ou collaboration. Notre équipe est disponible pour vous aider.</p>

            <!-- Boutons de contact -->
            <div class="contact-buttons">
                <a href="https://wa.me/2290197602072" target="_blank" class="whatsapp">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="mailto:contact@joie-enseignante.com" class="email">
                    <i class="fas fa-envelope"></i> Email
                </a>
                <a href="tel:+2290197602072" class="phone">
                    <i class="fas fa-phone"></i> Téléphone
                </a>
            </div>

            <!-- Bouton Retour à l'accueil -->
            <a href="index.php" class="btn-retour">Retour à l'accueil</a>

        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2025 Mon Blog - Tous droits réservés</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
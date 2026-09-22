<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Style global */
        body {
            font-family: Arial, sans-serif;
        }

        /* Conteneur principal */
        .about-container {
            max-width: 900px;
            margin: 50px auto;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.5s ease-in-out;
        }

        /* Animation d'apparition */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Image en cercle */
        .about-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #28a745;
            display: block;
            margin: 0 auto 20px;
        }

        /* Section avec icônes */
        .about-section {
            margin-bottom: 30px;
        }

        .about-section h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
            color: #28a745;
        }

        .about-section p {
            text-align: justify;
            font-size: 16px;
            line-height: 1.6;
        }

        .about-section i {
            color: #28a745;
            font-size: 24px;
        }

        /* Bouton Retour */
        .btn-retour {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 18px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-retour:hover {
            background: #218838;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="bg-success text-white py-3 text-center">
        <h1>À Propos de Sylvestre Djouamon</h1>
    </header>

    <!-- Section À Propos -->
    <div class="container">
        <div class="about-container">
            
            <!-- Photo en haut -->
            <img src="img/P1.jpg" alt="Sylvestre Djouamon" class="about-image">
            
            <!-- Biographie -->
            <div class="about-section">
                <h3><i class="fas fa-user"></i> Biographie</h3>
                <p>
                    Sylvestre Djouamon est Maître-Assistant au département des Lettres Modernes à la Faculté des Lettres, Langues, Arts et Communication (FLLAC). 
                    Sa thèse, intitulée <em>« Le fonctionnement du pessimisme dans les chansons traditionnelles modernes fons et maxis du Bénin »</em>, 
                    explore les dynamiques du discours littéraire dans la musique du Sud-Bénin.
                </p>
            </div>

            <!-- Parcours académique -->
            <div class="about-section">
                <h3><i class="fas fa-graduation-cap"></i> Parcours Académique</h3>
                <p>
                    Sylvestre Djouamon possède une formation pluridisciplinaire avec plusieurs diplômes :  
                    <ul>
                        <li>Doctorat en Lettres Modernes</li>
                        <li>Maîtrise en Droit</li>
                        <li>Expert-Évaluateur en Politiques Publiques</li>
                        <li>Master en Communication Commerciale et Stratégies</li>
                        <li>Certificat en Journalisme et Communication</li>
                    </ul>
                </p>
            </div>

            <!-- Carrière universitaire -->
            <div class="about-section">
                <h3><i class="fas fa-chalkboard-teacher"></i> Carrière Universitaire</h3>
                <p>
                    Actuellement, il est chef adjoint du département des Lettres Modernes à l'Université d'Abomey-Calavi, où il enseigne plusieurs cours :
                    <ul>
                        <li>Phénoménologie de la littérature orale</li>
                        <li>La chanson traditionnelle béninoise et ses innovations</li>
                        <li>Le conte africain</li>
                    </ul>
                </p>
            </div>

            <!-- Consultations et expertises -->
            <div class="about-section">
                <h3><i class="fas fa-briefcase"></i> Consultations et Expertise</h3>
                <p>
                    En parallèle, il intervient comme consultant pour diverses institutions :
                    <ul>
                        <li>Consultant AUF au Haut Conseil de l'Éducation en République du Congo</li>
                        <li>Rapporteur général de Conférence régionale sur l'évaluation d'impact en Afrique francophone</li>
                        <li>Consultant juridique pour l'évaluation Plan Foncier Rural (Banque mondiale)</li>
                    </ul>
                </p>
            </div>

            <!-- Engagement journalistique -->
            <div class="about-section">
                <h3><i class="fas fa-newspaper"></i> Engagement Journalistique</h3>
                <p>
                    Sylvestre Djouamon est également journaliste et directeur de publication du magazine <em>Afrique Identité</em>.  
                    Il est membre actif de l’Association des journalistes scientifiques du Bénin et coordonnateur du projet 
                    <em>École Avenir</em>, visant à améliorer le système éducatif africain.
                </p>
            </div>

            <!-- Recherche et Publications -->
            <div class="about-section">
                <h3><i class="fas fa-book"></i> Axes de Recherche</h3>
                <p>
                    Ses recherches portent principalement sur :
                    <ul>
                        <li>Littérature orale et transmission des savoirs en Afrique</li>
                        <li>Analyse du discours et représentations sociales dans les chansons béninoises</li>
                        <li>Politiques publiques et impact sur les mentalités collectives</li>
                    </ul>
                </p>
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
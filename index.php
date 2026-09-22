<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joie Enseignant</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Style général de la page */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Header */
        header {
            background-color: #333;
            color: white;
            padding: 20px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            font-size: 2.5rem;
            margin: 0;
        }

        header p {
            font-size: 1.2rem;
        }

        /* Menu hamburger */
        .hamburger-menu {
            display: none;
            font-size: 2rem;
            cursor: pointer;
            color: white;
            margin: 10px;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        nav {
            display: flex;
            justify-content: center;
        }

        nav a {
            color: white;
            padding: 10px;
            text-decoration: none;
            text-align: center;
        }

        nav a:hover {
            background-color: #575757;
        }

        /* Logo dans le header */
        header img {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1rem;
        }

        footer a {
            color: #fff;
            text-decoration: none;
        }

        /* Section avec image animée */
        .image-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .image-wrapper {
            width: 80%;
            height: 250px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: slideIn 2s ease-out forwards;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1s ease;
        }

        /* Animation pour l'image */
        .image-wrapper:hover img {
            transform: scale(1.1);
        }

        /* Animation d'entrée */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }

        /* Section principale du contenu */
        .section-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        /* Section bouton lire le blog */
        .read-blog-btn {
            text-align: center;
            margin-top: 20px;
        }

        .read-blog-btn a {
            padding: 10px 30px;
            background-color: #28a745;
            color: white;
            font-size: 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .read-blog-btn a:hover {
            background-color: #218838;
        }

        /* Section : Chaque section de contenu */
        .section {
            background-color: #f9f9f9;
            padding: 40px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .section:hover {
            background-color: #f1f1f1;
        }

        /* Sections alternées avec des couleurs de fond différentes */
        .section:nth-child(odd) {
            background-color: #e6f7ff;
        }

        .section:nth-child(even) {
            background-color: #fff3e6;
        }

        /* Style des titres des sections */
        .section h3 {
            font-size: 1.5rem;
            color: #333;
        }

        .section p {
            font-size: 1rem;
            color: #666;
        }

        /* Menu mobile hamburger */
        @media (max-width: 768px) {
            .hamburger-menu {
                display: block;
                cursor: pointer;
                font-size: 2rem;
                color: white;
                margin: 10px;
            }

            nav {
                display: none;
                flex-direction: column;
                width: 100%;
                position: absolute;
                top: 70px;
                left: 0;
                background-color: #333;
            }

            nav.active {
                display: flex;
            }

            nav a {
                text-align: left;
                padding: 15px;
                border-bottom: 1px solid #444;
            }

            nav a:last-child {
                border-bottom: none;
            }
        }

    </style>
</head>
<body>

<!-- Header avec menu -->
<header>
    <img src="img/logo.jpg" alt="Logo Joie Enseignant">
    <h1>Joie Enseignante</h1>
    <p>Accompagner les étudiants et promouvoir l'éducation de qualité</p>
    <div class="hamburger-menu" onclick="toggleMenu()">☰</div>
    <nav>
        <a href="index.php">Accueil</a>
        <a href="about.php">À propos</a>
        <a href="blog.php">Blog</a>
        <a href="contacts.php">Contact</a>
    </nav>
</header>

<!-- Section avec image animée -->
<div class="image-container">
    <div class="image-wrapper">
        <img src="img/P1.jpg"/1200x500" alt="Photo d'enseignement">
    </div>
</div>

<!-- Section de contenu principale -->
<div class="section-content">
    <h2 class="section-title">Bienvenue à Joie Enseignante</h2>
    <p>Joie Enseignante est un espace dédié à l'accompagnement des étudiants et à la promotion de l'éducation de qualité. Notre mission est de partager des connaissances, de créer des contenus éducatifs enrichissants, et de favoriser l'engagement des étudiants dans leur parcours académique.</p>
    <p>Nous nous efforçons de promouvoir des valeurs telles que l'excellence, la rigueur académique, et l'innovation dans l'enseignement. Que vous soyez étudiant ou enseignant, nous vous invitons à explorer nos ressources et à vous engager avec nous dans le monde de l'éducation.</p>
</div>

<!-- Section bouton lire le blog -->
<div class="read-blog-btn">
    <a href="blog.php">Lire notre blog</a>
</div>
<div class="section" id="introduction">
    <h2>Introduction à Joie Enseignant</h2>
    <p>Joie Enseignant est une plateforme dédiée à l'enrichissement de l'éducation, créée sous l'égide du Professeur Sylvestre Djouamon. L'objectif de cette initiative est de partager son expertise en littérature orale, en politiques publiques, ainsi qu'en communication. Ce site vise à promouvoir les savoirs et les valeurs liées à la transmission de la culture et des connaissances à travers des ressources pédagogiques et des formations spécialisées.</p>
</div>

<div class="section" id="biographie">
    <h2>Biographie de Sylvestre Djouamon</h2>
    <p>Sylvestre Djouamon est Maître-Assistant au département des Lettres Modernes à la Faculté des Lettres, Langues, Arts et Communication (FLLAC). Sa thèse, « Le fonctionnement du pessimisme dans les chansons traditionnelles modernes fons et maxis du Bénin », explore l’analyse du discours littéraire dans les chansons du Sud-Bénin. Il est aussi un consultant reconnu dans divers domaines, dont l'évaluation des politiques publiques et la gestion de projets de développement.</p>
                <a href="about.php" class="btn-retour">Lire Plus</a>
</div>

<div class="section" id="parcours">
    <h2>Parcours académique</h2>
    <p>Djouamon possède une formation pluridisciplinaire. Il détient un Doctorat en Lettres Modernes, une Maîtrise en Droit, un Master en Communication Commerciale et Stratégies, un Certificat en Journalisme et Communication, ainsi qu’une certification en Évaluation des Politiques Publiques. Ces qualifications reflètent son engagement envers l’éducation et la communication à travers une approche multidimensionnelle.</p>
</div>

<div class="section" id="carriere">
    <h2>Carrière universitaire</h2>
    <p>À l'Université d'Abomey-Calavi, Sylvestre Djouamon occupe le poste de chef adjoint du département des Lettres Modernes. Il enseigne des matières clés telles que la Phénoménologie de la littérature orale, La chanson traditionnelle béninoise et ses innovations, et Le conte africain. Ses cours visent à enrichir les connaissances des étudiants dans le domaine de la littérature orale et à encourager la réflexion critique.</p>
</div>

<div class="section" id="consultation">
    <h2>Consultation et expertise internationale</h2>
    <p>En plus de sa carrière universitaire, Sylvestre Djouamon est consultant pour des organisations internationales, notamment l'AUF, le Haut Conseil de l'Éducation en République du Congo et la FAO. Il a également travaillé pour la Banque mondiale sur le projet d'évaluation du Plan Foncier Rural. Ses contributions à l’évaluation des politiques publiques ont renforcé son rôle dans le développement international.</p>
</div>

<div class="section" id="engagement">
    <h2>Engagement journalistique et associatif</h2>
    <p>Sylvestre Djouamon est directeur de publication du magazine Afrique Identité et collaborateur du journal Le Progrès. Formateur en journalisme et communication, il est également membre actif de l'Association des journalistes et communicateurs scientifiques du Bénin. Il coordonne des projets internationaux visant à améliorer l’éducation en Afrique et est président de l'ONG Joie Enseignante.</p>
</div>

<div class="section" id="axes">
    <h2>Axes de recherche</h2>
    <p>Les travaux de Djouamon se concentrent sur des thématiques fondamentales telles que la littérature orale et la transmission des savoirs en Afrique. Il s’intéresse aussi à l'analyse du discours et des représentations sociales dans les chansons béninoises, ainsi qu'à l'impact des politiques publiques sur les mentalités collectives. Ses recherches contribuent à une meilleure compréhension de la culture et des sociétés africaines.</p>
</div>

<div class="section" id="publications">
    <h2>Publications et contributions</h2>
    <p>Sylvestre Djouamon a publié plusieurs articles et ouvrages sur la littérature orale et la communication. Il a également participé à des conférences internationales sur l’impact de la littérature orale sur les sociétés africaines et le rôle de la chanson dans l'évolution des mentalités. Ses travaux sont une ressource précieuse pour les étudiants et chercheurs du monde entier.</p>
</div>

<div class="section" id="formations">
    <h2>Formations et séminaires</h2>
    <p>Joie Enseignant propose des formateurs spécialisés en journalisme, communication, et littérature orale. Ces formations sont destinées aux étudiants, chercheurs, et professionnels souhaitant approfondir leurs connaissances dans ces domaines. Chaque programme est conçu pour offrir des compétences pratiques et théoriques, tout en soutenant le développement personnel et académique.</p>
</div>

<div class="section" id="projets">
    <h2>Projets de développement</h2>
    <p>Joie Enseignant soutient plusieurs projets de développement, notamment dans le domaine de la santé oculaire des enfants et la gestion de projets éducatifs. Ces initiatives sont menées sous la direction de Sylvestre Djouamon, avec l'objectif de répondre aux besoins de la communauté en matière de santé et d'éducation.</p>
</div>

<div class="section" id="blog">
    <h2>Blog et ressources</h2>
    <p>Nous encourageons tous les étudiants et chercheurs à visiter le blog de Joie Enseignant. Ce blog contient des articles détaillés sur des sujets variés, allant de la littérature orale à l’analyse des politiques publiques en Afrique. C’est une plateforme idéale pour échanger des idées et trouver des ressources utiles pour enrichir ses études et sa carrière.</p>
</div>

<div class="section" id="apropos">
    <h2>À propos - En savoir plus sur Sylvestre Djouamon</h2>
    <p>Pour une compréhension plus approfondie de la carrière et des contributions de Sylvestre Djouamon, n'hésitez pas à consulter la page À propos. Découvrez ses travaux de recherche, ses projets internationaux, ainsi que son engagement envers l'éducation et la culture africaine.</p>
</div>


<!-- Footer -->
<footer>
    <p>&copy; 2025 Joie Enseignant. Tous droits réservés.</p>
    <p><a href="#">Mentions légales</a> | <a href="#">Politique de confidentialité</a></p>
</footer>

<script>
    function toggleMenu() {
        const menu = document.querySelector("nav");
        menu.classList.toggle("active");
    }
</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9832264871701623"
     crossorigin="anonymous"></script>

</body>
</html>
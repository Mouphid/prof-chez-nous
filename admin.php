<?php
session_start();

// Vérification de connexion
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$publications = json_decode(file_get_contents('publications.json'), true) ?? [];
$comments = json_decode(file_get_contents('comments.json'), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titre'])) {
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $pdf = '';
    if (!empty($_FILES['pdf']['name'])) {
        $pdf = 'uploads/' . basename($_FILES['pdf']['name']);
        move_uploaded_file($_FILES['pdf']['tmp_name'], $pdf);
    }

    // Gestion des paragraphes supplémentaires
    $paragraphes = [];
    if (!empty($_POST['titre'])) {
        foreach ($_POST['titre'] as $index => $titre) {
            $para_image = '';
            // Vérification et téléchargement de l'image du paragraphe
            if (!empty($_FILES['para_image']['name'][$index])) {
                $para_image = 'uploads/' . basename($_FILES['para_image']['name'][$index]);
                move_uploaded_file($_FILES['para_image']['tmp_name'][$index], $para_image);
            }
            // Ajout du paragraphe avec l'image dans le tableau
            $paragraphes[] = [
                'sous_titre' => htmlspecialchars($titre),  // Le titre du paragraphe
                'contenu' => htmlspecialchars($_POST['para_contenu'][$index]),  // Le contenu du paragraphe
                'image' => $para_image  // L'image du paragraphe
            ];
        }
    }

    // Création de la nouvelle publication
    $newPublication = [
        'id' => uniqid(),
        'titre' => htmlspecialchars($_POST['titre']),
        'auteur' => 'Prof.Sylvestre Djouamon',
        'date' => date('Y-m-d:H:i'),
        'categorie' => htmlspecialchars($_POST['categorie']),
        'image' => $image,
        'contenu' => htmlspecialchars($_POST['contenu']),
        'pdf' => $pdf,
        'paragraphes' => $paragraphes  // Ajout des paragraphes avec images
    ];

    // Sauvegarde des publications dans le fichier JSON
    $publications[] = $newPublication;
    file_put_contents('publications.json', json_encode($publications, JSON_PRETTY_PRINT));

    // Redirection vers la page d'administration
    header('Location: admin.php');
    exit;
}

// Supprimer un commentaire
if (isset($_GET['delete_comment_id'])) {
    $comments = array_filter($comments, function ($com) {
        return $com['id'] != $_GET['delete_comment_id'];
    });
    file_put_contents('comments.json', json_encode(array_values($comments), JSON_PRETTY_PRINT));
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Blog</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script>
        function addParagraph() {
            let container = document.getElementById("paragraphes");
            let index = container.children.length;
            let div = document.createElement("div");
            div.classList.add("mb-3", "border", "p-3", "rounded");
            div.innerHTML = `
                <label class="form-label">Sous-titre</label>
                <input type="text" name="titre[]" class="form-control" required>
                <label class="form-label mt-2">Paragraphe</label>
                <textarea name="para_contenu[]" class="form-control" rows="3" required></textarea>
                <label class="form-label mt-2">Image (optionnelle)</label>
                <input type="file" name="para_image[]" class="form-control">
                <button type="button" class="btn btn-danger mt-2" onclick="this.parentElement.remove()">Supprimer</button>
            `;
            container.appendChild(div);
        }
    </script>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Blog</a>
        <a class="btn btn-warning" href="change_password.php">Changer le mot de passe</a>
        <a class="btn btn-danger" href="logout.php">Déconnexion</a>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center">Publier un article</h1>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow-sm rounded">
        <label class="form-label">Titre</label>
        <input type="text" name="titre" class="form-control" required>

        <label class="form-label mt-2">Catégorie</label>
        <select name="categorie" class="form-control">
            <option value="Infos">Infos</option>
            <option value="Articles">Articles</option>
            <option value="Annonce">Annonce</option>
        </select>

        <label class="form-label mt-2">Image</label>
        <input type="file" name="image" class="form-control">

        <label class="form-label mt-2">Contenu</label>
        <textarea name="contenu" class="form-control" rows="5" required></textarea>

        <label class="form-label mt-2">PDF</label>
        <input type="file" name="pdf" class="form-control">

        <h4 class="mt-3">Paragraphes supplémentaires</h4>
        <div id="paragraphes"></div>
        <button type="button" class="btn btn-secondary mt-2" onclick="addParagraph()">+ P</button>

        <button type="submit" class="btn btn-success mt-3">Publier</button>
    </form>
</div>

</body>
</html>
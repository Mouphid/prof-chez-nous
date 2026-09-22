<?php  
session_start();  

// Chargement des publications et des commentaires  
$publications = json_decode(file_get_contents('publications.json'), true) ?? [];  
$comments = json_decode(file_get_contents('comments.json'), true) ?? [];  

// Trier les publications par date (les plus récentes en premier)  
usort($publications, function ($a, $b) {  
    return strtotime($b['date']) - strtotime($a['date']);  
});  

// Vérifier si on affiche un article unique  
$article_id = $_GET['id'] ?? null;  
$selected_article = null;  

if ($article_id) {  
    foreach ($publications as $publication) {  
        if ($publication['id'] == $article_id) {  
            $selected_article = $publication;  
            break;  
        }  
    }  
}  

// Ajouter un commentaire  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publication_id'])) {  
    $newComment = [  
        'id' => uniqid(),  
        'publication_id' => $_POST['publication_id'],  
        'auteur' => htmlspecialchars($_POST['auteur']),  
        'contenu' => htmlspecialchars($_POST['contenu']),  
    ];  
    $comments[] = $newComment;  
    file_put_contents('comments.json', json_encode($comments, JSON_PRETTY_PRINT));  
    header('Location: blog.php?id=' . $_POST['publication_id']);  
    exit;  
}  
?>  

<!DOCTYPE html>  
<html lang="fr">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Blog - Publications</title>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">  
    <style>  
        body { background-color: #f8f9fa; }  
        .blog-container { max-width: 800px; margin: auto; }  
        .post { background: white; border-radius: 8px; padding: 20px; box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1); }  
        .excerpt { font-size: 1rem; color: #6c757d; }  
        .comment-card { background-color: #f1f1f1; padding: 15px; border-radius: 8px; margin-top: 10px; }  
    </style>  
</head>  
<body>  

<header class="bg-success text-white py-3">  
    <div class="container d-flex justify-content-between align-items-center">  
        <?php if (!$selected_article): ?>  
            <button class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#navbarNav">☰</button>  
        <?php else: ?>  
            <a href="blog.php" class="btn btn-light">⬅ Retour</a>  
        <?php endif; ?>  
        <a href="index.php" class="text-white fs-4 fw-bold">Joie Enseignant</a>  
        <img src="img/logo.jpg" alt="Logo" height="40">  
    </div>  
</header>  

<div class="container blog-container mt-5">  
    <?php if ($selected_article): ?>  
        <!-- Affichage d'un article unique complet -->  
        <div class="post">  
            <h2><?= htmlspecialchars($selected_article['titre']) ?></h2>  
            <p><strong><?= htmlspecialchars($selected_article['auteur']) ?></strong> - <?= date('d M Y', strtotime($selected_article['date'])) ?> - Catégorie: <?= htmlspecialchars($selected_article['categorie']) ?></p>  

            <?php if (!empty($selected_article['image'])): ?>  
                <img src="<?= $selected_article['image'] ?>" alt="Image de l'article" class="img-fluid my-3 rounded">  
            <?php endif; ?>  

            <p><?= nl2br(htmlspecialchars($selected_article['contenu'])) ?></p>  

            <?php if (!empty($selected_article['paragraphes'])): ?>  
                <?php foreach ($selected_article['paragraphes'] as $paragraphe): ?>  
                    <div class="my-4">  
                        <?php if (!empty($paragraphe['sous_titre'])): ?>  
                            <h4><?= htmlspecialchars($paragraphe['sous_titre']) ?></h4>  
                        <?php endif; ?>  
                        <p><?= nl2br(htmlspecialchars($paragraphe['contenu'])) ?></p>  
                        <?php if (!empty($paragraphe['image'])): ?>  
                            <img src="<?= $paragraphe['image'] ?>" alt="Image de sous-titre" class="img-fluid my-3">  
                        <?php endif; ?>  
                    </div>  
                <?php endforeach; ?>  
            <?php endif; ?>  

            <?php if (!empty($selected_article['pdf'])): ?>  
                <a href="<?= $selected_article['pdf'] ?>" class="btn btn-primary" download>📥 Télécharger le PDF</a>  
            <?php endif; ?>  

            <!-- Commentaires -->  
            <h5 class="mt-4">💬 Commentaires</h5>  
            <?php  
            $articleComments = array_filter($comments, function ($com) use ($selected_article) {  
                return $com['publication_id'] == $selected_article['id'];  
            });  
            ?>  

            <?php if (empty($articleComments)): ?>  
                <p>Aucun commentaire pour cet article.</p>  
            <?php else: ?>  
                <?php foreach ($articleComments as $comment): ?>  
                    <div class="comment-card">  
                        <strong><?= htmlspecialchars($comment['auteur']) ?></strong>: <?= htmlspecialchars($comment['contenu']) ?>  
                    </div>  
                <?php endforeach; ?>  
            <?php endif; ?>  

            <!-- Formulaire pour ajouter un commentaire -->  
            <form method="POST" class="mt-3">  
                <input type="hidden" name="publication_id" value="<?= $selected_article['id'] ?>">  
                <div class="mb-3">  
                    <label class="form-label">Votre Nom</label>  
                    <input type="text" name="auteur" class="form-control" required>  
                </div>  
                <div class="mb-3">  
                    <label class="form-label">Votre Commentaire</label>  
                    <textarea name="contenu" class="form-control" rows="3" required></textarea>  
                </div>  
                <button type="submit" class="btn btn-success">Commenter</button>  
            </form>  
        </div>  
    <?php else: ?>  
        <!-- Affichage des articles -->  
        <h2 class="text-center mb-4">📰 Articles récents</h2>  

        <?php foreach ($publications as $publication): ?>  
            <div class="post mb-4">  
                <h3><?= htmlspecialchars($publication['titre']) ?></h3>  
                <p><strong><?= htmlspecialchars($publication['auteur']) ?></strong> - <?= date('Y M d', strtotime($publication['date'])) ?> - Catégorie: <?= htmlspecialchars($publication['categorie']) ?></p>  

                <?php if (!empty($publication['image'])): ?>  
                    <img src="<?= $publication['image'] ?>" alt="Image de l'article" class="img-fluid my-3 rounded">  
                <?php endif; ?>  

                <p class="excerpt"><?= nl2br(htmlspecialchars(substr($publication['contenu'], 0, 150))) ?>...</p>  
                <a href="blog.php?id=<?= $publication['id'] ?>" class="btn btn-primary">Lire plus</a>  
            </div>  
        <?php endforeach; ?>  
    <?php endif; ?>  
</div>  

<footer class="bg-dark text-white text-center py-3 mt-5">  
    <p>&copy; 2025 Mon Blog - Tous droits réservés</p>  
</footer>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>
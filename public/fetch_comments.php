<?php
require_once "../config/config.php";

$id_post = (int)($_GET['id_post'] ?? 0);

$stmt = $pdo->prepare("SELECT id_comment, content, created_at, updated_at, author_email FROM comments WHERE id_post = ? AND status='visible' ORDER BY created_at ASC");
$stmt->execute([$id_post]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$comments) {
    echo "<p><em>Aucun commentaire pour le moment.</em></p>";
    exit;
}

foreach ($comments as $c): ?>
    <div class="comment" data-id="<?= $c['id_comment'] ?>">
        <p><em>Posté le <?= htmlspecialchars($c['created_at']) ?></em></p>
        <p class="comment-text"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
        <button class="edit-comment" data-id="<?= $c['id_comment'] ?>">✏️ Modifier</button>
        <button class="delete-comment" data-id="<?= $c['id_comment'] ?>">🗑️ Supprimer</button>
    </div>
<?php endforeach; ?>

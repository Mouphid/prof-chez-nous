<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$id_post = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_post) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, u.name as author_name
    FROM posts p
    LEFT JOIN categories c ON p.id_category = c.id_category
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.id_post = ?
");
$stmt->execute([$id_post]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: index.php");
    exit;
}

$is_visible = $post['status'] === 'published' || ($post['status'] === 'scheduled' && !empty($post['published_at']) && strtotime($post['published_at']) <= time());
$is_admin = isset($_SESSION['admin_id']);
if (!$is_visible && !$is_admin) {
    header("Location: index.php");
    exit;
}

$pdo->exec("UPDATE posts SET views = views + 1 WHERE id_post = " . $id_post);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check = $pdo->prepare("SELECT id FROM article_reads WHERE id_user = ? AND id_post = ?");
    $check->execute([$user_id, $id_post]);
    if (!$check->fetch()) {
        $insert = $pdo->prepare("INSERT INTO article_reads (id_user, id_post) VALUES (?, ?)");
        $insert->execute([$user_id, $id_post]);
    }
}

$stmt_files = $pdo->prepare("SELECT * FROM files WHERE id_post = ?");
$stmt_files->execute([$id_post]);
$files = $stmt_files->fetchAll();

$stmt_comments = $pdo->prepare("
    SELECT c.*, u.name as user_display_name 
    FROM comments c 
    LEFT JOIN users u ON c.id_user = u.id_user 
    WHERE c.id_post = ? AND c.status = 'visible' 
    ORDER BY c.created_at ASC
");
$stmt_comments->execute([$id_post]);
$comments = $stmt_comments->fetchAll();

$stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_post = ?");
$stmt_likes->execute([$id_post]);
$likes_count = $stmt_likes->fetchColumn();

$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    $has_liked = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_post = ? AND id_user = ?");
    $has_liked->execute([$id_post, $user_id]);
} else {
    $has_liked = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE id_post = ? AND ip_address = ? AND id_user IS NULL");
    $has_liked->execute([$id_post, $user_ip]);
}
$user_liked = $has_liked->fetchColumn() > 0;

$page_title = $post['title'];
$page_description = !empty($post['excerpt']) ? $post['excerpt'] : truncate(strip_tags($post['content']), 160);
$page_image = !empty($post['main_image']) ? SITE_URL . '/uploads/images/' . $post['main_image'] : null;

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include "../includes/header.php";
?>

    <section class="max-w-4xl mx-auto px-4 py-8 animate-fade-in" id="main-content">
        <article class="article-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-10">
                <a href="publications.php" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-dark-300 hover:text-primary transition mb-6"><i class="ph ph-arrow-left"></i> Retour aux publications</a>

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="bg-primary-100 text-primary-800 text-xs font-semibold px-3 py-1 rounded-full"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4"><?= htmlspecialchars($post['title']) ?></h1>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-dark-300 mb-8 pb-6 border-b border-gray-100 dark:border-dark-100">
                    <span><i class="ph ph-user text-primary"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                    <span><i class="ph ph-calendar text-primary"></i> <?= format_date($post['created_at']) ?></span>
                    <span><i class="ph ph-eye text-primary"></i> <?= ($post['views'] ?? 0) + 1 ?> vues</span>
                </div>

                <?php if (!empty($post['main_image'])): ?>
                <div class="rounded-xl overflow-hidden mb-8">
                    <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-auto">
                </div>
                <?php endif; ?>

                <div class="article-body">
                    <?php
                    // Découpe le texte en paragraphes pour un meilleur confort de lecture.
                    $paragraphs = preg_split('/\R{2,}/', trim($post['content']));
                    foreach ($paragraphs as $paragraph) {
                        if (trim($paragraph) === '') {
                            continue;
                        }
                        echo '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
                    }
                    ?>
                </div>

                <?php if (!empty($post['main_video'])): ?>
                <div class="mt-8 rounded-xl overflow-hidden bg-black">
                    <video controls class="w-full">
                        <source src="../uploads/video/<?= htmlspecialchars($post['main_video']) ?>" type="video/mp4">
                        Votre navigateur ne supporte pas la vidéo.
                    </video>
                </div>
                <?php endif; ?>

                <?php if (!empty($post['embed_link'])): ?>
                <div class="mt-8">
                    <a href="<?= htmlspecialchars($post['embed_link']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 dark:bg-dark-100 dark:hover:bg-dark-200 text-gray-700 dark:text-dark-200 px-5 py-3 rounded-lg transition font-medium">
                        <i class="ph ph-arrow-square-out"></i> Voir le lien externe
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($files)): ?>
                <section class="mt-8" aria-label="Documents à télécharger">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><i class="ph ph-paperclip text-primary"></i> Documents</h2>
                    <div class="space-y-3">
                        <?php foreach ($files as $file):
                            $is_paid = ((float)$file['price'] > 0);
                            $size_label = !empty($file['file_size']) ? round($file['file_size'] / 1024) . ' Ko' : '';
                            if ($file['file_type'] === 'pdf') { $file_icon = 'ph-file-pdf'; }
                            elseif (in_array($file['file_type'], ['doc', 'word'])) { $file_icon = 'ph-file-doc'; }
                            elseif ($file['file_type'] === 'image' || $file['file_type'] === 'images') { $file_icon = 'ph-image'; }
                            else { $file_icon = 'ph-file'; }
                        ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-gray-50 dark:bg-dark-100 border border-gray-200 dark:border-dark-100 rounded-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 flex-shrink-0 rounded-lg bg-primary-100 text-primary-800 flex items-center justify-center">
                                    <i class="ph <?= $file_icon ?> text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($file['file_name']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-dark-300"><?= $size_label ?><?= $size_label && $is_paid ? ' · ' : '' ?><?= $is_paid ? 'Document payant' : 'Gratuit' ?></p>
                                </div>
                            </div>
                            <?php if ($is_paid): ?>
                            <a href="purchase.php?id=<?= (int)$file['id_file'] ?>" class="inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-semibold px-5 py-2.5 rounded-lg transition">
                                <i class="ph ph-shopping-cart"></i> Acheter — <?= number_format((float)$file['price'], 0, ',', ' ') ?> FCFA
                            </a>
                            <?php else: ?>
                            <a href="download.php?id=<?= (int)$file['id_file'] ?>" class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-lg transition">
                                <i class="ph ph-download-simple"></i> Télécharger
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 p-6 bg-gray-50 dark:bg-dark-100 rounded-xl border border-gray-200 dark:border-dark-100">
                    <button class="like-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition <?= $user_liked ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-white dark:bg-dark-50 text-gray-600 dark:text-dark-200 border border-gray-200 dark:border-dark-100 hover:border-red-200 hover:text-red-500' ?>" data-post="<?= $id_post ?>" data-liked="<?= $user_liked ? '1' : '0' ?>">
                        <i class="ph ph-heart <?= $user_liked ? 'text-red-500' : '' ?>"></i>
                        <span class="like-count font-bold"><?= $likes_count ?></span>
                        <span class="like-text"><?= $user_liked ? 'Vous aimez' : "J'aime" ?></span>
                    </button>

                    <?php share_links('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $post['title']); ?>
                </div>
            </div>
        </article>

        <section class="mt-8 bg-white dark:bg-dark-50 rounded-xl shadow-sm border border-gray-100 dark:border-dark-100 p-6 sm:p-10" id="comments">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><i class="ph ph-chats text-primary"></i> Commentaires (<?= count($comments) ?>)</h2>

            <?php if (count($comments) > 0): ?>
            <div class="space-y-6 mb-8">
                <?php foreach ($comments as $comment): ?>
                <?php 
                $is_visiteur = strpos($comment['author_email'], 'visiteur_') === 0;
                $token = $is_visiteur ? substr($comment['author_email'], 9) : '';
                
                $can_edit = false;
                if (isset($_SESSION['user_id'])) {
                    if ($comment['id_user'] == $_SESSION['user_id']) {
                        $can_edit = true;
                    } elseif (!empty($comment['author_email']) && $comment['author_email'] == $_SESSION['user_email']) {
                        $can_edit = true;
                    }
                } elseif ($is_visiteur && $token) {
                    $can_edit = true;
                }
                ?>
                <div class="border-b border-gray-100 dark:border-dark-100 pb-6 last:border-0 last:pb-0 animate-slideUp" id="comment-<?= $comment['id_comment'] ?>" data-token="<?= htmlspecialchars($token) ?>">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                                <?= strtoupper(substr($comment['user_display_name'] ?? $comment['author_name'] ?? 'V', 0, 1)) ?>
                            </div>
                            <strong class="text-sm text-gray-900 dark:text-white">
                            <?php 
                            if (!empty($comment['user_display_name'])) {
                                echo htmlspecialchars($comment['user_display_name']);
                            } elseif (!empty($comment['author_name'])) {
                                echo htmlspecialchars($comment['author_name']);
                            } elseif (strpos($comment['author_email'] ?? '', 'visiteur_') === 0) {
                                echo 'Visiteur';
                            } else {
                                echo htmlspecialchars($comment['author_email'] ?? 'Anonyme');
                            }
                            ?>
                            </strong>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-dark-300">
                            <?= format_date($comment['created_at']) ?>
                            <?php if (!empty($comment['updated_at'])): ?>
                            <small>(modifié)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-dark-200 ml-10" id="comment-content-<?= $comment['id_comment'] ?>">
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    </div>
                    <?php if ($can_edit): ?>
                    <div class="flex items-center gap-2 mt-2 ml-10" id="comment-actions-<?= $comment['id_comment'] ?>">
                        <button class="text-xs text-primary hover:underline" onclick="editComment(<?= $comment['id_comment'] ?>)"><i class="ph ph-pencil"></i> Modifier</button>
                        <a href="manage_comment.php?action=delete&id_comment=<?= $comment['id_comment'] ?>&token=<?= urlencode($token) ?>&csrf_token=<?= urlencode(csrf_token()) ?>&from_form=1&post_id=<?= $id_post ?>" class="text-xs text-red-500 hover:underline"><i class="ph ph-trash"></i> Supprimer</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8 text-gray-400 dark:text-dark-300">
                <i class="ph ph-chat-dots text-4xl mb-3"></i>
                <p>Aucun commentaire pour le moment. Soyez le premier !</p>
            </div>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-dark-100">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4"><i class="ph ph-pen text-primary"></i> Ajouter un commentaire</h3>
                <form id="commentForm" method="post">
                    <input type="hidden" name="id_post" value="<?= $id_post ?>">
                    <div class="mb-4">
                        <textarea name="content" id="content" rows="4" required placeholder="Votre commentaire..." class="w-full px-4 py-3 border-2 border-gray-200 dark:border-dark-100 dark:bg-dark-50 dark:text-white dark:placeholder-dark-300 rounded-lg focus:border-primary focus:outline-none transition resize-none"></textarea>
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition inline-flex items-center gap-2">
                        <i class="ph ph-paper-plane-right"></i> Publier le commentaire
                    </button>
                </form>
                <div id="commentMessage" class="mt-4"></div>
            </div>
        </section>
    </section>

    <script>
    const DEBUG = true;
    const csrfToken = '<?= csrf_token() ?>';
    function logdebug(msg, data) { if (DEBUG) console.log('[DEBUG] ' + msg, data || ''); }

    const likeBtn = document.querySelector('.like-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const btn = this;
            const postId = btn.dataset.post;
            const liked = btn.dataset.liked === '1';
            logdebug('Like clicked', {postId, currentlyLiked: liked});
            btn.querySelector('.ph-heart').classList.remove('animate-heartbeat');
            void btn.querySelector('.ph-heart').offsetWidth;
            btn.querySelector('.ph-heart').classList.add('animate-heartbeat');
            fetch('like_post.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_post=' + encodeURIComponent(postId) + '&csrf_token=' + encodeURIComponent(csrfToken)
            })
            .then(r => { logdebug('Like response status', r.status); return r.json(); })
            .then(data => {
                logdebug('Like data', data);
                if (data.success) {
                    const countSpan = btn.querySelector('.like-count');
                    const textSpan = btn.querySelector('.like-text');
                    countSpan.textContent = data.total_likes;
                    if (data.action === 'liked') {
                        btn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                        btn.classList.add('bg-red-50', 'text-red-600', 'border-red-200');
                        btn.dataset.liked = '1';
                        textSpan.textContent = "Vous aimez";
                        btn.querySelector('.ph-heart').classList.add('text-red-500');
                    } else {
                        btn.classList.remove('bg-red-50', 'text-red-600', 'border-red-200');
                        btn.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                        btn.dataset.liked = '0';
                        textSpan.textContent = "J'aime";
                        btn.querySelector('.ph-heart').classList.remove('text-red-500');
                    }
                } else {
                    alert('Erreur: ' + (data.message || 'Impossible de liker'));
                }
            })
            .catch(err => { logdebug('Like error', err); alert('Erreur de connexion'); });
        });
    }

    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const formData = new FormData(this);
            formData.append('csrf_token', csrfToken);
            logdebug('Comment form submitted', Object.fromEntries(formData));
            fetch('add_comment.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                logdebug('Comment data', data);
                const msg = document.getElementById('commentMessage');
                if (data.success) {
                    if (data.token) { localStorage.setItem('comment_token_' + data.id_comment, data.token); }
                    msg.innerHTML = '<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2"><i class="ph ph-check-circle"></i> ' + data.message + '</div>';
                    this.reset();
                    if (!data.pending) { setTimeout(() => location.reload(), 1500); }
                } else {
                    msg.innerHTML = '<div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2"><i class="ph ph-x-circle"></i> ' + (data.message || 'Erreur') + '</div>';
                }
            })
            .catch(err => {
                logdebug('Comment error', err);
                const msg = document.getElementById('commentMessage');
                msg.innerHTML = '<div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg">Erreur de connexion</div>';
            });
            return false;
        });
    }

    window.editComment = function(idComment) {
        const commentDiv = document.getElementById('comment-content-' + idComment);
        const actionsDiv = document.getElementById('comment-actions-' + idComment);
        const currentContent = commentDiv.querySelector('p').textContent;
        commentDiv.innerHTML = '<textarea id="edit-content-' + idComment + '" rows="3" class="w-full px-3 py-2 border-2 border-primary rounded-lg focus:outline-none text-sm">' + currentContent + '</textarea>';
        actionsDiv.innerHTML = '<button class="text-xs text-emerald-600 hover:underline" onclick="saveComment(' + idComment + ')"><i class="ph ph-floppy-disk"></i> Enregistrer</button>' +
                           '<button class="text-xs text-gray-500 hover:underline" onclick="cancelEdit(' + idComment + ', \'' + currentContent.replace(/'/g, "\\'") + '\')"><i class="ph ph-x"></i> Annuler</button>';
    };

    function getCommentToken(id) {
        const t = localStorage.getItem('comment_token_' + id);
        if (t) return t;
        const el = document.getElementById('comment-' + id);
        return el ? el.dataset.token : null;
    }

    window.saveComment = function(idComment) {
        const newContent = document.getElementById('edit-content-' + idComment).value;
        const token = getCommentToken(idComment);
        const formData = new FormData();
        formData.append('id_comment', idComment);
        formData.append('content', newContent);
        formData.append('csrf_token', csrfToken);
        if (token) formData.append('token', token);
        fetch('manage_comment.php?action=update', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); });
    };

    window.cancelEdit = function(idComment, originalContent) {
        const commentDiv = document.getElementById('comment-content-' + idComment);
        const actionsDiv = document.getElementById('comment-actions-' + idComment);
        commentDiv.innerHTML = '<p>' + originalContent.replace(/</g, '&lt;') + '</p>';
        const token = document.getElementById('comment-' + idComment)?.dataset?.token || '';
        actionsDiv.innerHTML = '<button class="text-xs text-primary hover:underline" onclick="editComment(' + idComment + ')"><i class="ph ph-pencil"></i> Modifier</button>' +
            '<a href="manage_comment.php?action=delete&id_comment=' + idComment + '&token=' + encodeURIComponent(token) + '&from_form=1&post_id=<?= $id_post ?>" class="text-xs text-red-500 hover:underline"><i class="ph ph-trash"></i> Supprimer</a>';
    };
    </script>
    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

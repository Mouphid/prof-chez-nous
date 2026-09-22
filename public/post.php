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

$page_title = htmlspecialchars($post['title']);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); animation_styles(); ?>
    <meta property="og:title" content="<?= $page_title ?>">
    <meta property="og:description" content="<?= truncate(strip_tags($post['content']), 200) ?>">
    <?php if (!empty($post['main_image'])): ?>
    <meta property="og:image" content="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>">
    <?php endif; ?>
</head>
<body class="bg-gray-50 font-sans text-gray-800 leading-relaxed">
    <?php skip_link() ?>
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php"><img src="../img/logo.jpg" alt="Joie Enseignante" class="h-12 w-auto"></a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-house"></i> Accueil</a>
                <a href="about.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-info"></i> À propos</a>
                <a href="biography.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-user-circle"></i> Biographie</a>
                <a href="contact.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-envelope"></i> Contact</a>
                <a href="search.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-magnifying-glass"></i> Recherche</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition">
                        <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="ph ph-caret-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <a href="profile.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary rounded-t-lg"><i class="ph ph-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="ph ph-download w-5"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="ph ph-chats w-5"></i> Mes commentaires</a>
                        <hr class="border-gray-100">
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-primary hover:bg-indigo-50 transition"><i class="ph ph-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"><i class="ph ph-sign-in"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2" onclick="toggleMobileMenu(this)" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-house"></i> Accueil</a>
            <a href="about.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-info"></i> À propos</a>
            <a href="biography.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-circle"></i> Biographie</a>
            <a href="contact.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-envelope"></i> Contact</a>
            <a href="search.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-magnifying-glass"></i> Recherche</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-chats"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="ph ph-sign-out"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white text-center"><i class="ph ph-sign-in"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 animate-fadeIn" id="main-content">
        <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 sm:p-10">
                <a href="index.php" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary transition mb-6"><i class="ph ph-arrow-left"></i> Retour aux articles</a>

                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="bg-indigo-100 text-primary text-xs font-semibold px-3 py-1 rounded-full"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($post['title']) ?></h1>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 mb-8 pb-6 border-b border-gray-100">
                    <span><i class="ph ph-user text-primary"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                    <span><i class="ph ph-calendar text-primary"></i> <?= format_date($post['created_at']) ?></span>
                    <span><i class="ph ph-eye text-primary"></i> <?= ($post['views'] ?? 0) + 1 ?> vues</span>
                </div>

                <?php if (!empty($post['main_image'])): ?>
                <div class="rounded-xl overflow-hidden mb-8">
                    <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-auto">
                </div>
                <?php endif; ?>

                <div class="prose prose-gray max-w-none leading-relaxed text-gray-700">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
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
                    <a href="<?= htmlspecialchars($post['embed_link']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-3 rounded-lg transition font-medium">
                        <i class="ph ph-arrow-square-out"></i> Voir le lien externe
                    </a>
                </div>
                <?php endif; ?>

                <?php if (count($files) > 0): ?>
                <div class="mt-8 p-6 bg-gray-50 rounded-xl border border-gray-200">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="ph ph-paperclip text-primary"></i> Fichiers attachés</h2>
                    <ul class="space-y-2">
                        <?php foreach ($files as $file): ?>
                        <?php 
                            $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                            $folder = 'docs';
                            if (in_array($ext, ['jpg','jpeg','png','gif'])) $folder = 'images';
                            elseif ($ext === 'pdf') $folder = 'pdf';
                            elseif (in_array($ext, ['mp4','webm','mov'])) $folder = 'video';
                            elseif ($ext === 'mp3') $folder = 'audio';
                        ?>
                        <li>
                            <a href="download.php?type=<?= $folder ?>&file=<?= htmlspecialchars($file['file_name']) ?>&id=<?= $file['id_file'] ?>" target="_blank" class="flex items-center gap-3 px-4 py-3 bg-white rounded-lg border border-gray-200 hover:border-primary hover:text-primary transition text-gray-700">
                                <i class="ph ph-file-<?= get_file_icon($file['file_type']) ?> text-primary"></i>
                                <span class="font-medium">Télécharger <?= $ext === 'pdf' ? 'le PDF' : ($ext === 'mp4' || $ext === 'webm' || $ext === 'mov' ? 'la vidéo' : ($ext === 'mp3' ? "l'audio" : (in_array($ext, ['jpg','jpeg','png','gif']) ? "l'image" : 'le fichier'))) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="mt-8 flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 p-6 bg-gray-50 rounded-xl border border-gray-200">
                    <button class="like-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition <?= $user_liked ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-white text-gray-600 border border-gray-200 hover:border-red-200 hover:text-red-500' ?>" data-post="<?= $id_post ?>" data-liked="<?= $user_liked ? '1' : '0' ?>">
                        <i class="ph ph-heart <?= $user_liked ? 'text-red-500' : '' ?>"></i>
                        <span class="like-count font-bold"><?= $likes_count ?></span>
                        <span class="like-text"><?= $user_liked ? 'Vous aimez' : "J'aime" ?></span>
                    </button>

                    <?php share_links('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $post['title']); ?>
                </div>
            </div>
        </article>

        <section class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-10" id="comments">
            <h2 class="text-xl font-bold text-gray-900 mb-6"><i class="ph ph-chats text-primary"></i> Commentaires (<?= count($comments) ?>)</h2>

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
                <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0 animate-slideUp" id="comment-<?= $comment['id_comment'] ?>" data-token="<?= htmlspecialchars($token) ?>">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                                <?= strtoupper(substr($comment['user_display_name'] ?? $comment['author_name'] ?? 'V', 0, 1)) ?>
                            </div>
                            <strong class="text-sm text-gray-900">
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
                        <span class="text-xs text-gray-400">
                            <?= format_date($comment['created_at']) ?>
                            <?php if (!empty($comment['updated_at'])): ?>
                            <small>(modifié)</small>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="text-sm text-gray-700 ml-10" id="comment-content-<?= $comment['id_comment'] ?>">
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
            <div class="text-center py-8 text-gray-400">
                <i class="ph ph-chat-dots text-4xl mb-3"></i>
                <p>Aucun commentaire pour le moment. Soyez le premier !</p>
            </div>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4"><i class="ph ph-pen text-primary"></i> Ajouter un commentaire</h3>
                <form id="commentForm" method="post">
                    <input type="hidden" name="id_post" value="<?= $id_post ?>">
                    <div class="mb-4">
                        <textarea name="content" id="content" rows="4" required placeholder="Votre commentaire..." class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition resize-none"></textarea>
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <i class="ph ph-paper-plane-right"></i> Publier le commentaire
                    </button>
                </form>
                <div id="commentMessage" class="mt-4"></div>
            </div>
        </section>
    <?php include "../includes/footer.php"; ?>

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
                    setTimeout(() => location.reload(), 1500);
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
</body>
</html>

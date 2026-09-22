<?php
require_once "config_admin.php";

if(!has_permission('manage_posts')){
    header("Location: dashboard.php");
    exit;
}

if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: manage_posts.php");
    exit;
}

$post_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id_post = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post){
    header("Location: manage_posts.php");
    exit;
}

if($_SESSION['admin_role'] === 'auteur' && $post['id_user'] != $_SESSION['admin_id']){
    header("Location: manage_posts.php");
    exit;
}

$stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt_cat->fetchAll();

$stmt_files = $pdo->prepare("SELECT * FROM files WHERE id_post = ?");
$stmt_files->execute([$post_id]);
$files = $stmt_files->fetchAll();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])){
        $error = "Token de sécurité invalide";
    } else {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $id_category = (int)$_POST['id_category'];
        $status = isset($_POST['status']) ? $_POST['status'] : $post['status'];
        $scheduled_at = trim($_POST['scheduled_at'] ?? '');
        $published_at = $post['published_at'];
        if ($status === 'published' && $post['status'] !== 'published') {
            $published_at = date('Y-m-d H:i:s');
        } elseif ($status === 'scheduled' && !empty($scheduled_at)) {
            $published_at = $scheduled_at;
        } elseif ($status === 'draft') {
            $published_at = null;
        }

        if(empty($title) || empty($content)){
            $error = "Veuillez remplir tous les champs.";
        } else {
            $stmt_update = $pdo->prepare("UPDATE posts SET title=?, content=?, id_category=?, status=?, published_at=?, updated_at=NOW() WHERE id_post=?");
            $stmt_update->execute([$title, $content, $id_category, $status, $published_at, $post_id]);

            if(!empty($_FILES['files']['name'][0])){
                $allowed_types = ['pdf','doc','docx','mp3','mp4','jpg','jpeg','png','gif'];
                $upload_dir = "../uploads/";

                $mime_map = [
                    'pdf'  => ['application/pdf'],
                    'doc'  => ['application/msword'],
                    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                    'mp3'  => ['audio/mpeg'],
                    'mp4'  => ['video/mp4'],
                    'jpg'  => ['image/jpeg'],
                    'jpeg' => ['image/jpeg'],
                    'png'  => ['image/png'],
                    'gif'  => ['image/gif'],
                ];

                foreach($_FILES['files']['name'] as $key => $name){
                    $tmp_name = $_FILES['files']['tmp_name'][$key];
                    if (empty($tmp_name) || !is_file($tmp_name)) continue;
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                    if(in_array($ext, $allowed_types) && isset($mime_map[$ext]) && validate_mime($tmp_name, $mime_map[$ext])){
                        $type_folder = ($ext=='pdf'||$ext=='doc'||$ext=='docx') ? 'docs' :
                                       (($ext=='mp3') ? 'audio' :
                                       (($ext=='mp4') ? 'video' : 'images'));

                        if(!is_dir($upload_dir.$type_folder)){
                            mkdir($upload_dir.$type_folder, 0755, true);
                        }

                        $new_name = time().'_'.basename($name);
                        $dest_path = $upload_dir.$type_folder.'/'.$new_name;
                        move_uploaded_file($tmp_name, $dest_path);

                        $file_size = filesize($dest_path);
                        $stmt_file = $pdo->prepare("INSERT INTO files (id_post, file_name, file_type, file_path, file_size) VALUES (?, ?, ?, ?, ?)");
                        $stmt_file->execute([$post_id, $new_name, $type_folder, $dest_path, $file_size]);
                    }
                }
            }

            $success = "Article mis à jour avec succès !";
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id_post = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();
        }
    }
}

$can_view_comments = has_permission('manage_comments');
$can_view_categories = has_permission('manage_categories');
$can_view_users = has_permission('manage_users');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier article - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-100 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3"><img src="../img/logo.jpg" alt="Joie Enseignante" class="h-8 w-auto bg-white p-1 rounded"></a>
            </div>
            <nav class="p-4" aria-label="Menu admin">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-house w-5"></i> Dashboard</a></li>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-file-text w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-plus w-5"></i> Nouveau post</a></li>
                    <?php if($can_view_comments): ?>
                    <li><a href="manage_comments.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-chats w-5"></i> Commentaires</a></li>
                    <?php endif; ?>
                    <?php if($can_view_categories): ?>
                    <li><a href="manage_categories.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-folder w-5"></i> Catégories</a></li>
                    <?php endif; ?>
                    <?php if($can_view_users): ?>
                    <li><a href="manage_users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-users w-5"></i> Utilisateurs</a></li>
                    <?php endif; ?>
                    <li class="border-t border-gray-700 pt-3 mt-3">
                        <a href="../public/index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-arrow-square-out w-5"></i> Voir le site</a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-sign-out w-5"></i> Déconnexion</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="flex-1 ml-64 p-8 max-w-4xl" id="main-content">
            <h1 class="text-2xl font-bold text-gray-800 mb-8"><i class="ph ph-pencil text-primary mr-2"></i> Modifier l'article</h1>

            <?php if($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2" role="alert"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="post" enctype="multipart/form-data" class="space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label for="edit_title" class="block text-sm font-semibold text-gray-700 mb-1">Titre</label>
                        <input type="text" id="edit_title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label for="edit_content" class="block text-sm font-semibold text-gray-700 mb-1">Contenu</label>
                        <textarea id="edit_content" name="content" rows="10" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none"><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label for="edit_category" class="block text-sm font-semibold text-gray-700 mb-1">Catégorie</label>
                            <select id="edit_category" name="id_category" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                                <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id_category'] ?>" <?= $cat['id_category']==$post['id_category']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if(has_permission('publish_articles')): ?>
                        <div class="flex-1">
                            <label for="edit_status" class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                            <select name="status" id="statusSelect" onchange="toggleScheduling(this)" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                                <option value="draft" <?= $post['status']=='draft'?'selected':'' ?>>Brouillon</option>
                                <option value="published" <?= $post['status']=='published'?'selected':'' ?>>Publié</option>
                                <option value="scheduled" <?= $post['status']=='scheduled'?'selected':'' ?>>Programmé</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="schedulingDiv" style="<?= $post['status'] === 'scheduled' ? '' : 'display:none' ?>">
                        <label for="edit_scheduled" class="block text-sm font-semibold text-gray-700 mb-1">Programmer la publication</label>
                        <input type="datetime-local" id="edit_scheduled" name="scheduled_at" value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        <p class="text-xs text-gray-400 mt-1">L'article sera automatiquement visible à la date choisie</p>
                    </div>
                    <script>
                    function toggleScheduling(sel) {
                        document.getElementById('schedulingDiv').style.display = sel.value === 'scheduled' ? 'block' : 'none';
                    }
                    </script>

                    <?php if(count($files) > 0): ?>
                    <div>
                        <label for="edit_attached" class="block text-sm font-semibold text-gray-700 mb-2">Fichiers attachés</label>
                        <ul class="space-y-1">
                            <?php foreach($files as $file): ?>
                            <li class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded-lg">
                                <span><i class="ph ph-file text-primary mr-2"></i><?= htmlspecialchars($file['file_name']) ?></span>
                                <a href="delete_file.php?id=<?= $file['id_file'] ?>&post=<?= $post_id ?>&csrf_token=<?= csrf_token() ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Supprimer ce fichier ?');" aria-label="Supprimer le fichier"><i class="ph ph-x"></i></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label for="edit_add_files" class="block text-sm font-semibold text-gray-700 mb-1">Ajouter des fichiers</label>
                        <input type="file" id="edit_add_files" name="files[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-primary file:font-semibold hover:file:bg-indigo-100">
                    </div>

                    <button type="submit" class="bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center gap-2">
                        <i class="ph ph-floppy-disk"></i> Enregistrer
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

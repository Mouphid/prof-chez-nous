<?php
require_once "config_admin.php";
require_once "../includes/functions.php";

$error = '';
$success = '';

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide";
    } else {
        $title       = trim($_POST['title'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $id_category = $_POST['id_category'] ?? '';
        $id_user     = $_SESSION['admin_id'];
        $embed_link  = trim($_POST['embed_link'] ?? null);
        $status     = $_POST['status'] ?? 'draft';

        if (empty($title) || empty($content)) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } else {
        $main_image = null;
        if (!empty($_FILES['main_image']['name'])) {
            $img_ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
            $allowed_img = ['jpg','jpeg','png','gif'];
            if (in_array($img_ext, $allowed_img)) {
                $upload_dir = "../uploads/images/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $main_image = time().'_'.basename($_FILES['main_image']['name']);
                move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir.$main_image);
            }
        }

        $main_video = null;
        if (!empty($_FILES['main_video']['name'])) {
            $vid_ext = strtolower(pathinfo($_FILES['main_video']['name'], PATHINFO_EXTENSION));
            $allowed_vid = ['mp4','webm','ogg'];
            if (in_array($vid_ext, $allowed_vid)) {
                $upload_dir = "../uploads/video/";
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $main_video = time().'_'.basename($_FILES['main_video']['name']);
                move_uploaded_file($_FILES['main_video']['tmp_name'], $upload_dir.$main_video);
            }
        }

        $slug = generateSlug($title);

        $stmt_insert = $pdo->prepare("
            INSERT INTO posts (title, slug, content, id_category, id_user, embed_link, main_image, main_video, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt_insert->execute([$title, $slug, $content, $id_category, $id_user, $embed_link, $main_image, $main_video, $status]);
        $post_id = $pdo->lastInsertId();

        if (!empty($_FILES['files']['name'][0])) {
            $allowed_types = ['pdf','doc','docx','xls','xlsx','mp3','mp4','jpg','jpeg','png','gif'];
            $upload_base = "../uploads/";

            foreach ($_FILES['files']['name'] as $key => $name) {
                $tmp_name = $_FILES['files']['tmp_name'][$key];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed_types)) {
                    if ($ext === 'pdf') $type_folder = 'pdf';
                    elseif (in_array($ext, ['doc','docx'])) $type_folder = 'word';
                    elseif (in_array($ext, ['xls','xlsx'])) $type_folder = 'excel';
                    elseif ($ext === 'mp3') $type_folder = 'audio';
                    elseif (in_array($ext, ['mp4','webm','ogg'])) $type_folder = 'video';
                    else $type_folder = 'images';

                    if (!is_dir($upload_base.$type_folder)) mkdir($upload_base.$type_folder, 0755, true);

                    $new_name = time().'_'.basename($name);
                    $dest_path = $upload_base.$type_folder.'/'.$new_name;
                    move_uploaded_file($tmp_name, $dest_path);

                    $file_size = filesize($dest_path);
                    $stmt_file = $pdo->prepare("INSERT INTO files (id_post, file_name, file_type, file_path, file_size) VALUES (?, ?, ?, ?, ?)");
                    $stmt_file->execute([$post_id, $new_name, $type_folder, $dest_path, $file_size]);
                }
            }
        }

        $success = "Post ajouté avec succès !";
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
    <title>Nouveau post - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-white fixed h-full overflow-y-auto">
            <div class="p-5 border-b border-gray-700">
                <a href="dashboard.php" class="flex items-center gap-3 text-xl font-extrabold"><i class="ph ph-graduation-cap text-indigo-400"></i> JoieEnseignante</a>
            </div>
            <nav class="p-4">
                <ul class="space-y-1">
                    <li><a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-house w-5"></i> Dashboard</a></li>
                    <li><a href="manage_posts.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition"><i class="ph ph-file-alt w-5"></i> Articles</a></li>
                    <li><a href="add_post.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-700 text-white"><i class="ph ph-plus w-5"></i> Nouveau post</a></li>
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

        <main class="flex-1 ml-64 p-8 max-w-4xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-8"><i class="ph ph-plus-circle text-primary mr-2"></i> Ajouter un nouveau post</h1>

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="post" enctype="multipart/form-data" class="space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Titre</label>
                        <input type="text" name="title" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Contenu</label>
                        <textarea name="content" rows="10" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none"></textarea>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Catégorie</label>
                            <select name="id_category" required class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_category'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                            <select name="status" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                                <option value="draft">Brouillon</option>
                                <option value="published">Publié</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Image principale</label>
                        <input type="file" name="main_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-primary file:font-semibold hover:file:bg-indigo-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Vidéo principale (upload)</label>
                        <input type="file" name="main_video" accept="video/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-primary file:font-semibold hover:file:bg-indigo-100">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lien externe (YouTube, Vimeo)</label>
                        <input type="url" name="embed_link" placeholder="https://..." class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Ressources attachées (PDF, Word, Excel, audio, etc.)</label>
                        <input type="file" name="files[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-primary file:font-semibold hover:file:bg-indigo-100">
                    </div>

                    <button type="submit" class="bg-primary hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition flex items-center gap-2">
                        <i class="ph ph-plus"></i> Ajouter le post
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

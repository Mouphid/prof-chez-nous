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

            // Mise à jour du prix de chaque document existant (0 = gratuit)
            if (!empty($_POST['file_price']) && is_array($_POST['file_price'])) {
                $upd_file = $pdo->prepare("UPDATE files SET price = ?, is_free = ? WHERE id_file = ? AND id_post = ?");
                foreach ($_POST['file_price'] as $fid => $fprice) {
                    $fid    = (int)$fid;
                    $fprice = (float)$fprice;
                    if ($fprice < 0) { $fprice = 0; }
                    $upd_file->execute([$fprice, $fprice > 0 ? 0 : 1, $fid, $post_id]);
                }
            }

            if(!empty($_FILES['files']['name'][0])){
                $allowed_types = ['pdf','doc','docx','mp3','mp4','jpg','jpeg','png','gif'];
                $upload_dir = "../uploads/";

                $new_file_price = isset($_POST['file_price_new']) ? (float)$_POST['file_price_new'] : 0;
                if ($new_file_price < 0) { $new_file_price = 0; }
                $new_file_is_free = $new_file_price > 0 ? 0 : 1;

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

                        // Type accepté par la colonne `file_type` (enum)
                        $db_type = 'other';
                        if ($type_folder === 'docs') { $db_type = 'doc'; }
                        elseif ($type_folder === 'images') { $db_type = 'image'; }
                        elseif ($type_folder === 'video') { $db_type = 'video'; }
                        elseif ($type_folder === 'pdf') { $db_type = 'pdf'; }

                        $file_size = filesize($dest_path);
                        $relative_path = 'uploads/' . $type_folder . '/' . $new_name;
                        $stmt_file = $pdo->prepare("INSERT INTO files (id_post, file_name, file_type, file_path, file_size, price, is_free) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt_file->execute([$post_id, $new_name, $db_type, $relative_path, $file_size, $new_file_price, $new_file_is_free]);
                    }
                }
            }

            $success = "Article mis à jour avec succès !";
            admin_activity('update', 'post', (int)$post_id, $title);
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id_post = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();

            $stmt_files = $pdo->prepare("SELECT * FROM files WHERE id_post = ?");
            $stmt_files->execute([$post_id]);
            $files = $stmt_files->fetchAll();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = "Modifier l'article";
            $page_subtitle = 'Mettez à jour le contenu, la catégorie ou le statut';
            include "_topbar.php";
            ?>
            <div class="adm-container"><div class="adm-form-narrow">

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
                        <p class="block text-sm font-semibold text-gray-700 mb-2">Fichiers attachés</p>
                        <p class="text-xs text-gray-500 mb-3">Mettez <strong>0</strong> pour un document gratuit (téléchargement direct). Un montant supérieur active le paiement en ligne.</p>
                        <ul class="space-y-2">
                            <?php foreach($files as $file): ?>
                            <?php $is_paid = ((float)($file['price'] ?? 0) > 0); ?>
                            <li class="flex flex-col sm:flex-row sm:items-center gap-3 bg-gray-50 px-3 py-2.5 rounded-lg">
                                <span class="flex-1 min-w-0 truncate"><i class="ph ph-file text-primary mr-2"></i><?= htmlspecialchars($file['file_name']) ?></span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full <?= $is_paid ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-700' ?>">
                                    <?= $is_paid ? 'Payant' : 'Gratuit' ?>
                                </span>
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    Prix (FCFA)
                                    <input type="number" name="file_price[<?= (int)$file['id_file'] ?>]" min="0" step="1" value="<?= (int)($file['price'] ?? 0) ?>" class="w-28 px-2 py-1.5 border border-gray-300 rounded-lg focus:border-primary focus:outline-none">
                                </label>
                                <a href="delete_file.php?id=<?= $file['id_file'] ?>&post=<?= $post_id ?>&csrf_token=<?= csrf_token() ?>" class="text-red-500 hover:text-red-700 p-1" onclick="return confirm('Supprimer ce fichier ?');" aria-label="Supprimer le fichier"><i class="ph ph-x"></i></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label for="edit_add_files" class="block text-sm font-semibold text-gray-700 mb-1">Ajouter des fichiers</label>
                        <input type="file" id="edit_add_files" name="files[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-primary file:font-semibold hover:file:bg-indigo-100">
                    </div>

                    <div>
                        <label for="edit_file_price_new" class="block text-sm font-semibold text-gray-700 mb-1">Prix des nouveaux fichiers (FCFA)</label>
                        <input type="number" id="edit_file_price_new" name="file_price_new" min="0" step="1" value="0" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none">
                        <p class="text-xs text-gray-500 mt-1">Laissez <strong>0</strong> pour un téléchargement gratuit.</p>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="ph ph-floppy-disk"></i> Enregistrer
                    </button>
                </form>
            </div>
            </div></div>
        </main>
    </div>
</body>
</html>

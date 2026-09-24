<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

header("Location: index.php");
exit;

$page_title = "Mon Profil - Joie Enseignante";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (isset($_GET['delete_file'])) {
    if (!isset($_GET['csrf_token']) || !verify_csrf($_GET['csrf_token'])) {
        $_SESSION['flash_success'] = "Token de sécurité invalide";
        header("Location: profile.php");
        exit;
    }
    $file_id = (int)$_GET['delete_file'];
    $check = $pdo->prepare("SELECT f.*, p.id_user FROM files f LEFT JOIN posts p ON f.id_post = p.id_post WHERE f.id_file = ?");
    $check->execute([$file_id]);
    $file = $check->fetch();
    if ($file && $file['id_user'] == $user_id) {
        $del = $pdo->prepare("DELETE FROM files WHERE id_file = ?");
        $del->execute([$file_id]);
        $_SESSION['flash_success'] = "Fichier supprimé avec succès!";
    }
    header("Location: profile.php");
    exit;
}

$posts = $pdo->prepare("SELECT * FROM posts WHERE id_user = ? ORDER BY created_at DESC");
$posts->execute([$user_id]);
$user_posts = $posts->fetchAll();

$downloads = $pdo->prepare("
    SELECT f.*, p.title as post_title, ud.downloaded_at
    FROM user_downloads ud
    JOIN files f ON ud.id_file = f.id_file
    LEFT JOIN posts p ON f.id_post = p.id_post
    WHERE ud.id_user = ?
    ORDER BY ud.downloaded_at DESC
");
$downloads->execute([$user_id]);
$user_downloads = $downloads->fetchAll();
$my_downloads_count = count($user_downloads);

$reads_count = $pdo->prepare("SELECT COUNT(*) FROM article_reads WHERE id_user = ?");
$reads_count->execute([$user_id]);
$articles_read = $reads_count->fetchColumn();

$flash_success = $_SESSION['flash_success'] ?? '';
if ($flash_success) unset($_SESSION['flash_success']);

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = "Session invalide, veuillez réessayer.";
    } else {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    if (empty($name)) {
        $error = "Le nom est requis.";
    } else {
        $avatar = $user['avatar'] ?? null;
        if (!empty($_FILES['avatar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                $avatar = time() . '_avatar.' . $ext;
                move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/images/' . $avatar);
            }
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, avatar = ? WHERE id_user = ?");
        $stmt->execute([$name, $bio, $avatar, $user_id]);
        $_SESSION['user_name'] = $name;
        $success = "Profil mis à jour!";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); animation_styles(); ?>
</head>
<body class="bg-gray-50 font-sans text-gray-800 leading-relaxed">
    <?php skip_link() ?>
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary"><i class="ph ph-graduation-cap"></i> Joie Enseignante</a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="ph ph-house"></i> Accueil</a>
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
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2" onclick="toggleMobileMenu(this)" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-house"></i> Accueil</a>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="ph ph-sign-out"></i> Déconnexion</a>
        </div>
    </header>

    <div class="bg-gradient-to-r from-primary to-indigo-400 h-48"></div>

    <main class="max-w-5xl mx-auto px-4 -mt-24" id="main-content">
        <?php if ($flash_success): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert"><i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert"><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar profil -->
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-center sticky top-24">
                    <?php if (!empty($user['avatar'])): ?>
                    <img src="../uploads/images/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($user['name']) ?>" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg mx-auto">
                    <?php else: ?>
                    <div class="w-24 h-24 rounded-full bg-gray-200 border-4 border-white shadow-lg mx-auto flex items-center justify-center text-3xl text-gray-400"><i class="ph ph-user"></i></div>
                    <?php endif; ?>
                    <h2 class="text-lg font-bold text-gray-900 mt-3"><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full mt-2"><i class="ph ph-circle" style="font-size:6px;"></i> En ligne</span>
                    <?php if (!empty($user['bio'])): ?>
                    <p class="mt-3 px-3 py-2 bg-gray-50 rounded-lg text-gray-600 text-sm"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-3">Membre depuis <?= format_date($user['created_at']) ?></p>
                    <hr class="my-4 border-gray-100">
                    <nav class="space-y-1 text-left" aria-label="Navigation profil">
                        <a href="profile.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="ph ph-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition"><i class="ph ph-download w-5"></i> Téléchargements</a>
                        <a href="my_comments.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition"><i class="ph ph-chats w-5"></i> Commentaires</a>
                    </nav>
                </div>
            </aside>

            <!-- Contenu principal -->
            <div class="flex-1 space-y-6">
                <!-- Stats cards -->
                <h1 class="text-2xl font-bold text-gray-900">Mon Profil</h1>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md transition">
                        <div class="text-2xl font-bold text-primary"><?= $articles_read ?></div>
                        <div class="text-sm text-gray-500 mt-1">Articles lus</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md transition">
                        <div class="text-2xl font-bold text-primary"><?= $my_downloads_count ?></div>
                        <div class="text-sm text-gray-500 mt-1">Téléchargements</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center hover:shadow-md transition">
                        <div class="text-2xl font-bold text-emerald-600"><i class="ph ph-check-circle"></i></div>
                        <div class="text-sm text-gray-500 mt-1">Compte actif</div>
                    </div>
                </div>

                <!-- Activité récente -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="ph ph-clock-counter-clockwise text-primary"></i> Activité récente</h2>
                    <?php
                    $activities = [];
                    foreach ($user_downloads as $dl) {
                        $activities[] = ['type' => 'download', 'title' => $dl['file_name'], 'date' => $dl['downloaded_at']];
                    }
                    usort($activities, function($a, $b) { return strtotime($b['date']) - strtotime($a['date']); });
                    $activities = array_slice($activities, 0, 10);
                    ?>
                    <?php if (count($activities) > 0): ?>
                    <div class="flow-root">
                        <ul class="-mb-4">
                            <?php foreach ($activities as $act): ?>
                            <li class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
                                <span class="w-9 h-9 rounded-full bg-amber-50 flex items-center justify-center flex-shrink-0"><i class="ph ph-download text-amber-500"></i></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 truncate"><?= htmlspecialchars($act['title']) ?></p>
                                    <p class="text-xs text-gray-400">Téléchargé le <?= format_date($act['date']) ?></p>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <i class="ph ph-clock text-4xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500">Aucune activité récente</p>
                        <a href="index.php" class="text-primary text-sm hover:underline mt-2 inline-block">Parcourir les articles</a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Modifier le profil -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2"><i class="ph ph-pencil text-primary"></i> Modifier le profil</h2>
                    <form method="post" enctype="multipart/form-data" class="max-w-lg space-y-5">
                        <?= csrf_field() ?>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Photo de profil</label>
                            <input type="file" name="avatar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary file:text-white file:font-medium hover:file:bg-indigo-700">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom complet</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bio</label>
                            <textarea name="bio" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition resize-none"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" name="update" class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition inline-flex items-center gap-2">
                                <i class="ph ph-floppy-disk"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-900 text-gray-400 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="border-t border-gray-800 pt-6 text-center text-sm">
                &copy; <?= date('Y') ?> Joie Enseignante. Tous droits réservés.
            </div>
        </div>
    </footer>
</body>
</html>

<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Mes Téléchargements - Joie Enseignante";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$downloads = $pdo->prepare("
    SELECT f.*, p.title as post_title, ud.created_at as downloaded_at
    FROM user_downloads ud
    JOIN files f ON ud.id_file = f.id_file
    LEFT JOIN posts p ON f.id_post = p.id_post
    WHERE ud.id_user = ?
    ORDER BY ud.created_at DESC
");
$downloads->execute([$user_id]);
$rows = $downloads->fetchAll();

$my_downloads = [];
foreach ($rows as $dl) {
    $ext = strtolower(pathinfo($dl['file_name'], PATHINFO_EXTENSION));
    $folder = 'docs';
    if (in_array($ext, ['jpg','jpeg','png','gif'])) $folder = 'images';
    elseif ($ext === 'pdf') $folder = 'pdf';
    elseif (in_array($ext, ['mp4','webm','mov'])) $folder = 'video';
    elseif ($ext === 'mp3') $folder = 'audio';
    $dl['folder'] = $folder;
    $my_downloads[] = $dl;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-800">
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
            <button class="md:hidden text-gray-600 p-2" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-house"></i> Accueil</a>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="ph ph-sign-out"></i> Déconnexion</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6"><i class="ph ph-download text-primary"></i> Mes Téléchargements</h1>

        <?php if (count($my_downloads) > 0): ?>
        <div class="space-y-4">
            <?php foreach ($my_downloads as $dl): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 hover:shadow-md transition">
                <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center text-primary text-xl flex-shrink-0">
                    <i class="ph ph-file-alt"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($dl['file_name']) ?></h3>
                    <p class="text-sm text-gray-500">
                        <?php if ($dl['post_title']): ?>
                        Article: <a href="post.php?id=<?= $dl['id_post'] ?>" class="text-primary hover:underline"><?= htmlspecialchars($dl['post_title']) ?></a> &middot;
                        <?php endif; ?>
                        Téléchargé le <?= format_date($dl['downloaded_at']) ?>
                    </p>
                </div>
                <a href="download.php?type=<?= $dl['folder'] ?>&file=<?= urlencode($dl['file_name']) ?>&id=<?= $dl['id_file'] ?>" class="flex-shrink-0 bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition inline-flex items-center gap-1">
                    <i class="ph ph-download"></i> Télécharger
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
            <i class="ph ph-download text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">Vous n'avez pas encore téléchargé de ressources.</p>
            <a href="index.php" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
                <i class="ph ph-book-open"></i> Parcourir les articles
            </a>
        </div>
        <?php endif; ?>
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

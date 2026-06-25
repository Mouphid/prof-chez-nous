<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Ressources - Joie Enseignante";

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$all_files = $pdo->query("
    SELECT f.*, p.title as post_title, p.id_post
    FROM files f
    LEFT JOIN posts p ON f.id_post = p.id_post
    ORDER BY f.created_at DESC
")->fetchAll();

$total_files = count($all_files);

$files_by_ext = [];
$ext_icons = [
    'pdf' => 'fa-file-pdf',
    'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
    'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
    'mp3' => 'fa-file-audio', 'wav' => 'fa-file-audio',
    'mp4' => 'fa-file-video', 'webm' => 'fa-file-video', 'mov' => 'fa-file-video',
    'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image', 'gif' => 'fa-file-image'
];

foreach ($all_files as $file) {
    $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
    $type = $ext_icons[$ext] ?? 'fa-file-alt';
    $files_by_ext[$ext][] = $file;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#4F46E5'}}}}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
            <a href="index.php" class="flex items-center gap-2 text-xl font-extrabold text-primary"><i class="fas fa-graduation-cap"></i> Joie Enseignante</a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="index.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="fas fa-home"></i> Accueil</a>
                <a href="resources.php" class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="fas fa-folder-open"></i> Ressources</a>
                <a href="search.php" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition"><i class="fas fa-search"></i> Recherche</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-emerald-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                        <a href="profile.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary rounded-t-lg"><i class="fas fa-user-cog w-5"></i> Mon profil</a>
                        <a href="my_downloads.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="fas fa-download w-5"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-primary"><i class="fas fa-comments w-5"></i> Mes commentaires</a>
                        <hr class="border-gray-100">
                        <a href="logout.php" class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"><i class="fas fa-sign-out-alt w-5"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3 py-2 rounded-lg text-sm font-medium text-primary hover:bg-indigo-50 transition"><i class="fas fa-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <button class="md:hidden text-gray-600 p-2" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" aria-label="Menu"><i class="fas fa-bars text-xl"></i></button>
        </div>
        <div class="hidden md:hidden bg-white border-t px-4 py-3 space-y-1" id="mobileNav">
            <a href="index.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-home"></i> Accueil</a>
            <a href="resources.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-primary"><i class="fas fa-folder-open"></i> Ressources</a>
            <a href="search.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-search"></i> Recherche</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100"><i class="fas fa-comments"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2 rounded-lg text-sm font-medium text-red-600"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-lg text-sm font-medium bg-primary text-white text-center"><i class="fas fa-sign-in-alt"></i> Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2"><i class="fas fa-download text-primary"></i> Centre de Ressources</h1>
        <p class="text-gray-500 mb-8">Découvrez et téléchargez les ressources pédagogiques partagées par nos enseignants.</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <i class="fas fa-file text-2xl text-primary mb-2"></i>
                <div class="text-2xl font-bold text-gray-900"><?= $total_files ?></div>
                <div class="text-sm text-gray-500">Fichiers</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <i class="fas fa-file-pdf text-2xl text-red-500 mb-2"></i>
                <div class="text-2xl font-bold text-gray-900"><?= count($files_by_ext['pdf'] ?? []) ?></div>
                <div class="text-sm text-gray-500">PDF</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <i class="fas fa-file-image text-2xl text-emerald-500 mb-2"></i>
                <div class="text-2xl font-bold text-gray-900"><?= count($files_by_ext['jpg'] ?? []) + count($files_by_ext['png'] ?? []) + count($files_by_ext['jpeg'] ?? []) + count($files_by_ext['gif'] ?? []) ?></div>
                <div class="text-sm text-gray-500">Images</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <i class="fas fa-file-video text-2xl text-purple-500 mb-2"></i>
                <div class="text-2xl font-bold text-gray-900"><?= count($files_by_ext['mp4'] ?? []) + count($files_by_ext['webm'] ?? []) + count($files_by_ext['mov'] ?? []) ?></div>
                <div class="text-sm text-gray-500">Vidéos</div>
            </div>
        </div>

        <?php if ($total_files > 0): ?>
        <div class="space-y-4">
            <?php foreach ($all_files as $file): ?>
            <?php 
                $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                $icon = $ext_icons[$ext] ?? 'fa-file-alt';
                
                $folder = 'docs';
                if (in_array($ext, ['jpg','jpeg','png','gif'])) $folder = 'images';
                elseif ($ext === 'pdf') $folder = 'pdf';
                elseif (in_array($ext, ['mp4','webm','mov'])) $folder = 'video';
                elseif ($ext === 'mp3') $folder = 'audio';
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-primary text-xl flex-shrink-0">
                    <i class="fas <?= $icon ?>"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($file['file_name']) ?></h3>
                    <p class="text-sm text-gray-500">
                        <?php if ($file['id_post'] && $file['post_title']): ?>
                        Article: <a href="post.php?id=<?= $file['id_post'] ?>" class="text-primary hover:underline"><?= htmlspecialchars(truncate($file['post_title'], 40)) ?></a>
                        <?php else: ?>
                        Ressource générale
                        <?php endif; ?>
                    </p>
                    <p class="text-xs text-gray-400">Ajouté le <?= format_date($file['created_at'] ?? date('Y-m-d')) ?></p>
                </div>
                <div class="flex gap-2 flex-shrink-0 w-full sm:w-auto">
                    <a href="download.php?type=<?= $folder ?>&file=<?= htmlspecialchars($file['file_name']) ?>&id=<?= $file['id_file'] ?>" target="_blank" class="flex-1 sm:flex-none bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition inline-flex items-center justify-center gap-1">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                    <?php if ($file['id_post']): ?>
                    <a href="post.php?id=<?= $file['id_post'] ?>" class="flex-1 sm:flex-none border-2 border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:border-gray-300 transition inline-flex items-center justify-center gap-1">
                        <i class="fas fa-book-open"></i> Article
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
            <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 text-lg">Aucune ressource disponible pour le moment.</p>
            <p class="text-gray-400">Revenez bientôt!</p>
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

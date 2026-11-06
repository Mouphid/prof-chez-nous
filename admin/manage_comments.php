<?php
require_once "config_admin.php";

$csrf_ok = isset($_GET['csrf_token']) && verify_csrf($_GET['csrf_token']);

if (isset($_GET['approve'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE comments SET status = 'visible' WHERE id_comment = ?")->execute([$id]);
    header("Location: manage_comments.php"); exit;
}
if (isset($_GET['hide'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['hide'];
    $pdo->prepare("UPDATE comments SET status = 'hidden' WHERE id_comment = ?")->execute([$id]);
    header("Location: manage_comments.php"); exit;
}
if (isset($_GET['delete'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM comments WHERE id_comment = ?")->execute([$id]);
    header("Location: manage_comments.php"); exit;
}

$status_filter = $_GET['status'] ?? 'all';
$where = "";
if ($status_filter === 'pending') $where = "WHERE (c.status = 'pending' OR c.status IS NULL OR c.status = '')";
elseif ($status_filter === 'visible') $where = "WHERE c.status = 'visible'";
elseif ($status_filter === 'hidden') $where = "WHERE c.status = 'hidden'";

$comments = $pdo->query("SELECT c.*, p.title as post_title, p.id_post FROM comments c LEFT JOIN posts p ON c.id_post = p.id_post ORDER BY c.created_at DESC")->fetchAll();

$stats = [
    'all' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending' OR status IS NULL OR status = ''")->fetchColumn(),
    'visible' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'visible'")->fetchColumn(),
    'hidden' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'hidden'")->fetchColumn()
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commentaires - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php include "_sidebar.php"; ?>

        <!-- Main -->
        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Commentaires'; $page_icon = 'ph-chats'; include "_topbar.php"; ?>
            <div class="p-8">

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-gray-800"><?= $stats['all'] ?></div><div class="text-sm text-gray-500">Total</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-yellow-600"><?= $stats['pending'] ?></div><div class="text-sm text-gray-500">En attente</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-green-600"><?= $stats['visible'] ?></div><div class="text-sm text-gray-500">Approuvés</div></div>
                <div class="bg-white p-5 rounded-xl shadow-sm text-center"><div class="text-2xl font-extrabold text-gray-600"><?= $stats['hidden'] ?></div><div class="text-sm text-gray-500">Masqués</div></div>
            </div>

            <!-- Filters -->
            <div class="flex gap-2 mb-6 flex-wrap">
                <?php
                $tabs = [
                    'all' => "Tous ({$stats['all']})",
                    'pending' => "En attente ({$stats['pending']})",
                    'visible' => "Approuvés ({$stats['visible']})",
                    'hidden' => "Masqués ({$stats['hidden']})",
                ];
                foreach($tabs as $key => $label): ?>
                <a href="?status=<?= $key ?>" class="px-4 py-2 rounded-lg font-medium transition <?= $status_filter===$key ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <!-- Comments -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <?php
                $has_items = false;
                foreach($comments as $c):
                    $c_status = $c['status'] ?? 'pending';
                    if ($status_filter !== 'all' && $c_status !== $status_filter) continue;
                    $has_items = true;
                    $badge_class = $c_status==='visible' ? 'bg-green-100 text-green-700' : ($c_status==='hidden' ? 'bg-gray-100 text-gray-600' : 'bg-yellow-100 text-yellow-700');
                ?>
                <div class="p-5 border-b border-gray-100 hover:bg-gray-50">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-semibold text-primary"><i class="ph ph-user mr-1"></i><?= htmlspecialchars($c['author_email'] ?? 'Anonyme') ?></span>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full <?= $badge_class ?>"><?= $c_status ?: 'en attente' ?></span>
                    </div>
                    <p class="text-sm text-gray-500 mb-2">
                        Article: <a href="../public/post.php?id=<?= $c['id_post'] ?>" target="_blank" class="text-primary hover:underline"><?= $c['post_title'] ?? 'Inconnu' ?></a>
                        <span class="ml-2">- <?= format_date($c['created_at']) ?></span>
                    </p>
                    <p class="text-gray-700 mb-3"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                    <div class="flex gap-2">
                        <?php $token = csrf_token(); ?>
                        <?php if ($c_status !== 'visible'): ?>
                        <a href="?approve=<?= $c['id_comment'] ?>&csrf_token=<?= $token ?>" class="px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-sm hover:bg-green-100 transition"><i class="ph ph-check"></i> Approuver</a>
                        <?php endif; ?>
                        <?php if ($c_status === 'visible'): ?>
                        <a href="?hide=<?= $c['id_comment'] ?>&csrf_token=<?= $token ?>" class="px-3 py-1.5 bg-red-50 text-red-700 rounded-lg text-sm hover:bg-red-100 transition"><i class="ph ph-eye-slash"></i> Masquer</a>
                        <?php endif; ?>
                        <a href="?delete=<?= $c['id_comment'] ?>&csrf_token=<?= $token ?>" class="px-3 py-1.5 bg-gray-100 text-red-600 rounded-lg text-sm hover:bg-red-100 transition" onclick="return confirm('Supprimer ce commentaire ?');"><i class="ph ph-trash"></i> Supprimer</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(!$has_items): ?>
                <div class="p-10 text-center text-gray-400"><i class="ph ph-chats text-3xl block mb-2"></i> Aucun commentaire trouvé.</div>
                <?php endif; ?>
            </div>
            </div>
        </main>
    </div>
</body>
</html>

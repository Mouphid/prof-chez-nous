<?php
require_once "config_admin.php";

$csrf_ok = isset($_GET['csrf_token']) && verify_csrf($_GET['csrf_token']);

if (isset($_GET['approve'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['approve'];
    $pdo->prepare("UPDATE comments SET status = 'visible' WHERE id_comment = ?")->execute([$id]);
    flash('success', 'Commentaire approuvé.');
    header("Location: manage_comments.php"); exit;
}
if (isset($_GET['hide'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['hide'];
    $pdo->prepare("UPDATE comments SET status = 'hidden' WHERE id_comment = ?")->execute([$id]);
    flash('success', 'Commentaire masqué.');
    header("Location: manage_comments.php"); exit;
}
if (isset($_GET['delete'])) {
    if(!$csrf_ok){ die("Token de sécurité invalide"); }
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM comments WHERE id_comment = ?")->execute([$id]);
    flash('success', 'Commentaire supprimé.');
    header("Location: manage_comments.php"); exit;
}

$status_filter = $_GET['status'] ?? 'all';
if (!in_array($status_filter, ['all', 'pending', 'visible', 'hidden'], true)) {
    $status_filter = 'all';
}
$where = "";
if ($status_filter === 'pending') $where = "WHERE (c.status = 'pending' OR c.status IS NULL OR c.status = '')";
elseif ($status_filter === 'visible') $where = "WHERE c.status = 'visible'";
elseif ($status_filter === 'hidden') $where = "WHERE c.status = 'hidden'";

$comments = $pdo->query("
    SELECT c.*, p.title AS post_title, p.id_post
    FROM comments c
    LEFT JOIN posts p ON c.id_post = p.id_post
    $where
    ORDER BY c.created_at DESC
")->fetchAll();

$stats = [
    'all' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending' OR status IS NULL OR status = ''")->fetchColumn(),
    'visible' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'visible'")->fetchColumn(),
    'hidden' => $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'hidden'")->fetchColumn()
];

$initials_of = function (string $name): string {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name) ?: [$name];
    $out = mb_strtoupper(mb_substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $out .= mb_strtoupper(mb_substr(end($parts), 0, 1));
    }
    return $out;
};
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
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Commentaires';
            $page_subtitle = 'Modérez les échanges sous vos articles';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if (hasFlash('success')): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars(flash('success')) ?></span></div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars(flash('error')) ?></span></div>
            <?php endif; ?>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Total</span>
                        <span class="adm-kpi-icon adm-kpi-icon-blue"><i class="ph ph-chats-circle"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['all'] ?></p>
                    <p class="adm-kpi-foot">commentaires reçus</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">En attente</span>
                        <span class="adm-kpi-icon adm-kpi-icon-amber"><i class="ph ph-clock"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['pending'] ?></p>
                    <p class="adm-kpi-foot">à valider avant publication</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Visibles</span>
                        <span class="adm-kpi-icon adm-kpi-icon-green"><i class="ph ph-eye"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['visible'] ?></p>
                    <p class="adm-kpi-foot">affichés sous les articles</p>
                </div>
                <div class="adm-card adm-kpi">
                    <div class="adm-kpi-head">
                        <span class="adm-kpi-label">Masqués</span>
                        <span class="adm-kpi-icon adm-kpi-icon-gray"><i class="ph ph-eye-slash"></i></span>
                    </div>
                    <p class="adm-kpi-value"><?= (int)$stats['hidden'] ?></p>
                    <p class="adm-kpi-foot">retirés de la vue publique</p>
                </div>
            </div>

            <nav class="adm-segmented mb-4" aria-label="Filtrer les commentaires">
                <?php
                $tabs = [
                    'all' => 'Tous',
                    'pending' => 'En attente',
                    'visible' => 'Visibles',
                    'hidden' => 'Masqués',
                ];
                foreach($tabs as $key => $label): ?>
                <a href="?status=<?= $key ?>" class="<?= $status_filter === $key ? 'is-active' : '' ?>">
                    <?= $label ?> <span class="text-gray-400"><?= (int)($stats[$key] ?? 0) ?></span>
                </a>
                <?php endforeach; ?>
            </nav>

            <div class="adm-card overflow-hidden">
                <?php if (!$comments): ?>
                <div class="adm-empty">
                    <i class="ph ph-chats-circle"></i>
                    <p class="adm-empty-title">Aucun commentaire <?= $status_filter === 'all' ? 'pour le moment' : 'dans ce filtre' ?></p>
                    <p class="adm-empty-note">Les réactions des lecteurs apparaîtront ici, prêtes à être validées.</p>
                </div>
                <?php else: ?>
                <div class="adm-list">
                    <?php foreach($comments as $c):
                        $c_status = $c['status'] ?: 'pending';
                        $meta = [
                            'visible' => ['label' => 'Visible',  'dot' => 'is-green'],
                            'hidden'  => ['label' => 'Masqué',   'dot' => 'is-gray'],
                            'pending' => ['label' => 'En attente','dot' => 'is-amber'],
                        ][$c_status] ?? ['label' => ucfirst($c_status), 'dot' => 'is-gray'];

                        $auteur = trim((string)($c['author_email'] ?? ''));
                        $is_anon = $auteur === '' || !str_contains($auteur, '@') || str_starts_with($auteur, 'visiteur_');
                        $nom = $is_anon ? 'Visiteur anonyme' : $auteur;
                        $identifiant = $is_anon ? ($auteur !== '' ? $auteur : 'commentaire #' . (int)$c['id_comment']) : '';
                    ?>
                    <div class="adm-list-item">
                        <span class="adm-avatar-sm <?= $is_anon ? 'adm-avatar-neutral' : 'adm-avatar-blue' ?>" aria-hidden="true">
                            <?php if ($is_anon): ?><i class="ph ph-user"></i><?php else: ?><?= htmlspecialchars($initials_of($nom)) ?><?php endif; ?>
                        </span>

                        <div class="adm-list-body">
                            <div class="adm-list-head">
                                <span class="adm-list-name"><?= htmlspecialchars($nom) ?></span>
                                <?php if ($identifiant !== ''): ?>
                                <span class="adm-list-id" title="<?= htmlspecialchars($identifiant) ?>"><?= htmlspecialchars($identifiant) ?></span>
                                <?php endif; ?>
                                <span class="adm-status <?= $meta['dot'] ?> ml-auto"><span class="adm-status-dot"></span><?= $meta['label'] ?></span>
                            </div>

                            <p class="adm-list-meta">
                                <i class="ph ph-newspaper"></i>
                                <?php if (!empty($c['id_post'])): ?>
                                <a href="../public/post.php?id=<?= (int)$c['id_post'] ?>" target="_blank" rel="noopener" class="hover:text-primary">
                                    <?= htmlspecialchars(truncate((string)($c['post_title'] ?? 'Article supprimé'), 70)) ?>
                                </a>
                                <?php else: ?>
                                Article supprimé
                                <?php endif; ?>
                                <span class="mx-1">·</span>
                                <?= format_date($c['created_at']) ?>
                            </p>

                            <p class="adm-list-text"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                        </div>

                        <div class="adm-list-actions">
                            <?php $token = csrf_token(); ?>
                            <?php if ($c_status !== 'visible'): ?>
                            <a href="?approve=<?= (int)$c['id_comment'] ?>&csrf_token=<?= $token ?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Rendre visible">
                                <i class="ph ph-check"></i><span class="hidden sm:inline">Approuver</span>
                            </a>
                            <?php endif; ?>
                            <?php if ($c_status === 'visible'): ?>
                            <a href="?hide=<?= (int)$c['id_comment'] ?>&csrf_token=<?= $token ?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Masquer sans supprimer">
                                <i class="ph ph-eye-slash"></i><span class="hidden sm:inline">Masquer</span>
                            </a>
                            <?php endif; ?>
                            <a href="?delete=<?= (int)$c['id_comment'] ?>&csrf_token=<?= $token ?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Supprimer ce commentaire ?');" title="Supprimer définitivement">
                                <i class="ph ph-trash"></i><span class="hidden sm:inline">Supprimer</span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            </div>
        </main>
    </div>
</body>
</html>

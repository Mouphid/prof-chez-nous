<?php
/**
 * Barre supérieure unique des pages d'administration.
 *
 * À inclure juste après l'ouverture de <main>, avant le contenu.
 * Variables attendues avant l'include :
 * - $page_title  : titre affiché (obligatoire)
 * - $page_icon   : classe Phosphor sans le préfixe "ph " (ex. "ph-newspaper")
 * - $page_action : HTML optionnel d'un bouton d'action à droite
 *
 * Les variables $admin_name et $admin_role viennent de config_admin.php.
 */
$page_title  = $page_title  ?? 'Administration';
$page_icon   = $page_icon   ?? 'ph-squares-four';
$page_action = $page_action ?? '';

$role_labels = [
    'admin'    => 'Super Admin',
    'auteur'   => 'Auteur',
    'etudiant' => 'Étudiant',
];
$role_label   = $role_labels[$admin_role] ?? ucfirst((string) $admin_role);
$display_name = $admin_name ?? ($_SESSION['admin_name'] ?? 'Admin');

// Initiales pour l'avatar (ex. « Marie Dupont » → MD)
$name_parts = preg_split('/\s+/', trim($display_name)) ?: [$display_name];
$initials = mb_strtoupper(mb_substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) {
    $initials .= mb_strtoupper(mb_substr(end($name_parts), 0, 1));
}

// Pastille de notifications : commentaires en attente
$topbar_pending = 0;
if (has_permission('manage_comments')) {
    try {
        $topbar_pending = (int) $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending' OR status IS NULL")->fetchColumn();
    } catch (PDOException $e) {
        $topbar_pending = 0;
    }
}

// La recherche en haut renvoie vers la liste des articles
$topbar_search = basename($_SERVER['PHP_SELF'] ?? '') === 'manage_posts.php'
    ? trim((string) ($_GET['q'] ?? ''))
    : '';
?>
<header class="adm-topbar px-4 sm:px-6 py-3 flex items-center gap-3 sm:gap-5">
    <div class="flex items-center gap-3 min-w-0 shrink-0 lg:max-w-[15rem]">
        <button type="button" class="admin-menu-btn items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition"
                onclick="toggleAdminSidebar()" aria-controls="adminSidebar" aria-expanded="false" aria-label="Ouvrir le menu">
            <i class="ph ph-list text-lg"></i>
        </button>
        <h1 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2 min-w-0">
            <i class="ph <?= htmlspecialchars($page_icon) ?> text-primary text-lg hidden sm:inline"></i>
            <span class="truncate"><?= htmlspecialchars($page_title) ?></span>
        </h1>
    </div>

    <?php if (has_permission('manage_posts')): ?>
    <form class="adm-search hidden lg:block flex-1" action="manage_posts.php" method="get" role="search">
        <i class="ph ph-magnifying-glass"></i>
        <input type="search" name="q" value="<?= htmlspecialchars($topbar_search) ?>"
               placeholder="Rechercher un article, un auteur…" aria-label="Rechercher dans les articles">
        <kbd>Entrée</kbd>
    </form>
    <?php else: ?>
    <div class="flex-1"></div>
    <?php endif; ?>

    <div class="flex items-center gap-1 sm:gap-2 ml-auto shrink-0">
        <?= $page_action ?>

        <?php if (has_permission('manage_comments')): ?>
        <a href="manage_comments.php" class="adm-icon-btn" aria-label="Commentaires à valider<?= $topbar_pending > 0 ? ' (' . $topbar_pending . ')' : '' ?>">
            <i class="ph ph-bell"></i>
            <?php if ($topbar_pending > 0): ?><span class="adm-icon-dot"><?= $topbar_pending > 99 ? '99+' : $topbar_pending ?></span><?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if ($admin_role === 'admin'): ?>
        <a href="send_newsletter.php" class="adm-icon-btn hidden sm:inline-flex" aria-label="Écrire une newsletter">
            <i class="ph ph-envelope-simple"></i>
        </a>
        <?php endif; ?>

        <div class="adm-profile">
            <div class="adm-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></div>
            <div class="hidden md:block">
                <p class="adm-profile-name"><?= htmlspecialchars($display_name) ?></p>
                <p class="adm-profile-role"><?= htmlspecialchars($role_label) ?></p>
            </div>
        </div>
    </div>
</header>
<script>
function toggleAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const btn = document.querySelector('.admin-menu-btn');
    if (!sidebar) return;
    const open = sidebar.classList.toggle('is-open');
    if (overlay) overlay.classList.toggle('is-visible', open);
    if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.style.overflow = open ? 'hidden' : '';
}
function closeAdminSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    const btn = document.querySelector('.admin-menu-btn');
    if (sidebar) sidebar.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-visible');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAdminSidebar();
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#adminSidebar a').forEach(function (link) {
        link.addEventListener('click', closeAdminSidebar);
    });
});
</script>

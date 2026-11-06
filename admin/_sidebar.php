<?php
/**
 * Menu latéral unique de l'administration.
 * Inclus par toutes les pages admin : <?php include "_sidebar.php"; ?>
 *
 * Variables attendues (définies dans config_admin.php) :
 * - $admin_role, $admin_name
 * - has_permission()
 */
$current_page = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

// Regroupe les pages qui doivent surligner le même élément du menu
$active_map = [
    'dashboard.php'          => 'dashboard',
    'manage_posts.php'       => 'posts',
    'add_post.php'           => 'posts',
    'edit_post.php'          => 'posts',
    'manage_comments.php'    => 'comments',
    'manage_categories.php'  => 'categories',
    'manage_purchases.php'   => 'purchases',
    'manage_courses.php'     => 'courses',
    'add_course.php'         => 'courses',
    'edit_course.php'        => 'courses',
    'manage_newsletter.php'  => 'newsletter',
    'send_newsletter.php'    => 'newsletter',
    'export_subscribers.php' => 'newsletter',
    'manage_users.php'       => 'users',
    'change_password.php'    => 'settings',
];
$active = $active_map[$current_page] ?? '';

// Nombre de commentaires en attente (pastille du menu)
$nav_pending_comments = 0;
if (has_permission('manage_comments')) {
    try {
        $nav_pending_comments = (int) $pdo->query("SELECT COUNT(*) FROM comments WHERE status='pending' OR status IS NULL")->fetchColumn();
    } catch (PDOException $e) {
        $nav_pending_comments = 0;
    }
}

/**
 * Affiche un lien du menu.
 */
function admin_nav_link(string $key, string $href, string $icon, string $label, string $active, int $count = 0): void
{
    $is_active = ($key === $active);
    echo '<li><a href="' . htmlspecialchars($href) . '" class="adm-nav-link' . ($is_active ? ' is-active' : '') . '"'
        . ($is_active ? ' aria-current="page"' : '') . '>'
        . '<i class="ph ' . htmlspecialchars($icon) . '"></i>'
        . '<span class="truncate">' . htmlspecialchars($label) . '</span>'
        . ($count > 0 ? '<span class="adm-nav-count">' . $count . '</span>' : '')
        . '</a></li>';
}
?>
<aside id="adminSidebar" class="admin-sidebar w-64 fixed h-full overflow-y-auto z-40 flex flex-col" aria-label="Navigation administration">
    <a href="dashboard.php" class="admin-side-brand">
        <img src="../img/logo.jpg" alt="Joie Enseignante">
        <span>Joie Enseignante<small>Administration</small></span>
    </a>

    <nav class="adm-side-nav flex-1" aria-label="Menu admin">
        <p class="adm-side-section">Menu</p>
        <ul class="space-y-1">
            <?php admin_nav_link('dashboard', 'dashboard.php', 'ph-squares-four', 'Tableau de bord', $active); ?>

            <?php if (has_permission('manage_posts')): ?>
                <?php admin_nav_link('posts', 'manage_posts.php', 'ph-newspaper', 'Articles', $active); ?>
            <?php endif; ?>

            <?php if (has_permission('manage_comments')): ?>
                <?php admin_nav_link('comments', 'manage_comments.php', 'ph-chats-circle', 'Commentaires', $active, $nav_pending_comments); ?>
            <?php endif; ?>

            <?php if (has_permission('manage_categories')): ?>
                <?php admin_nav_link('categories', 'manage_categories.php', 'ph-folder-simple', 'Catégories', $active); ?>
            <?php endif; ?>
        </ul>

        <?php if ($admin_role === 'admin'): ?>
            <p class="adm-side-section">Ventes</p>
            <ul class="space-y-1">
                <?php admin_nav_link('purchases', 'manage_purchases.php', 'ph-receipt', 'Ventes', $active); ?>
                <?php admin_nav_link('courses', 'manage_courses.php', 'ph-graduation-cap', 'Cours', $active); ?>
                <?php admin_nav_link('newsletter', 'manage_newsletter.php', 'ph-envelope-simple', 'Newsletter', $active); ?>
            </ul>
        <?php endif; ?>

        <p class="adm-side-section">Général</p>
        <ul class="space-y-1">
            <?php if (has_permission('manage_users')): ?>
                <?php admin_nav_link('users', 'manage_users.php', 'ph-users', 'Utilisateurs', $active); ?>
            <?php endif; ?>
            <?php admin_nav_link('settings', 'change_password.php', 'ph-gear-six', 'Paramètres', $active); ?>
        </ul>
    </nav>

    <div class="adm-side-footer">
        <p><strong>Besoin d'un coup de main ?</strong><br>Le manuel d'administration répond aux questions courantes.</p>
        <a href="../public/index.php" target="_blank" rel="noopener">
            Voir le site <i class="ph ph-arrow-square-out"></i>
        </a>
    </div>

    <div class="px-3 pb-4">
        <a href="logout.php" class="adm-nav-link">
            <i class="ph ph-sign-out"></i>
            <span>Déconnexion</span>
        </a>
    </div>
</aside>
<div id="adminOverlay" class="admin-overlay" onclick="closeAdminSidebar()" aria-hidden="true"></div>

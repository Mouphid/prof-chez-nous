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
    'dashboard.php'         => 'dashboard',
    'manage_posts.php'      => 'posts',
    'add_post.php'          => 'add_post',
    'edit_post.php'         => 'posts',
    'manage_comments.php'   => 'comments',
    'manage_categories.php' => 'categories',
    'manage_purchases.php'  => 'purchases',
    'manage_courses.php'    => 'courses',
    'add_course.php'        => 'courses',
    'edit_course.php'       => 'courses',
    'manage_newsletter.php' => 'newsletter',
    'send_newsletter.php'   => 'newsletter',
    'export_subscribers.php' => 'newsletter',
    'manage_users.php'      => 'users',
    'change_password.php'   => 'password',
];
$active = $active_map[$current_page] ?? '';

/**
 * Affiche un lien du menu.
 */
function admin_nav_link(string $key, string $href, string $icon, string $label, string $active): void
{
    $is_active = ($key === $active);
    $classes = $is_active
        ? 'bg-indigo-600 text-white font-medium'
        : 'text-gray-300 hover:bg-gray-700 hover:text-white';
    echo '<li><a href="' . htmlspecialchars($href) . '" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition ' . $classes . '"'
        . ($is_active ? ' aria-current="page"' : '') . '>'
        . '<i class="ph ' . htmlspecialchars($icon) . ' w-5"></i> ' . htmlspecialchars($label)
        . '</a></li>';
}
?>
<aside id="adminSidebar" class="admin-sidebar w-64 bg-gray-900 text-white fixed h-full overflow-y-auto z-40" aria-label="Navigation administration">
    <div class="p-5 border-b border-gray-700">
        <a href="dashboard.php" class="flex items-center gap-3">
            <img src="../img/logo.jpg" alt="Joie Enseignante" class="h-8 w-auto bg-white p-1 rounded">
        </a>
    </div>
    <nav class="p-4" aria-label="Menu admin">
        <ul class="space-y-1">
            <?php admin_nav_link('dashboard', 'dashboard.php', 'ph-gauge', 'Dashboard', $active); ?>

            <?php if (has_permission('manage_posts')): ?>
                <?php admin_nav_link('posts', 'manage_posts.php', 'ph-newspaper', 'Articles', $active); ?>
                <?php admin_nav_link('add_post', 'add_post.php', 'ph-plus-circle', 'Nouveau post', $active); ?>
            <?php endif; ?>

            <?php if (has_permission('manage_comments')): ?>
                <?php admin_nav_link('comments', 'manage_comments.php', 'ph-chats', 'Commentaires', $active); ?>
            <?php endif; ?>

            <?php if (has_permission('manage_categories')): ?>
                <?php admin_nav_link('categories', 'manage_categories.php', 'ph-folder', 'Catégories', $active); ?>
            <?php endif; ?>

            <?php if ($admin_role === 'admin'): ?>
                <?php admin_nav_link('purchases', 'manage_purchases.php', 'ph-shopping-cart', 'Ventes', $active); ?>
                <?php admin_nav_link('courses', 'manage_courses.php', 'ph-graduation-cap', 'Cours', $active); ?>
                <?php admin_nav_link('newsletter', 'manage_newsletter.php', 'ph-envelope-simple', 'Newsletter', $active); ?>
            <?php endif; ?>

            <?php if (has_permission('manage_users')): ?>
                <?php admin_nav_link('users', 'manage_users.php', 'ph-users', 'Utilisateurs', $active); ?>
            <?php endif; ?>

            <li class="border-t border-gray-700 pt-3 mt-3 space-y-1">
                <a href="../public/index.php" target="_blank" rel="noopener" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <i class="ph ph-arrow-square-out w-5"></i> Voir le site
                </a>
                <?php admin_nav_link('password', 'change_password.php', 'ph-lock-key', 'Mot de passe', $active); ?>
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <i class="ph ph-sign-out w-5"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>
</aside>
<div id="adminOverlay" class="admin-overlay" onclick="closeAdminSidebar()" aria-hidden="true"></div>

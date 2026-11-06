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
$role_label = $role_labels[$admin_role] ?? ucfirst((string) $admin_role);
$display_name = $admin_name ?? ($_SESSION['admin_name'] ?? 'Admin');
?>
<header class="bg-white border-b border-gray-200 px-6 sm:px-8 py-4 flex items-center justify-between gap-4 sticky top-0 z-30">
    <div class="flex items-center gap-3 min-w-0">
        <button type="button" class="admin-menu-btn items-center justify-center w-10 h-10 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition"
                onclick="toggleAdminSidebar()" aria-controls="adminSidebar" aria-expanded="false" aria-label="Ouvrir le menu">
            <i class="ph ph-list text-xl"></i>
        </button>
        <h1 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-2 min-w-0">
            <i class="ph <?= htmlspecialchars($page_icon) ?> text-primary text-xl"></i>
            <span class="truncate"><?= htmlspecialchars($page_title) ?></span>
        </h1>
    </div>
    <div class="flex items-center gap-3 shrink-0">
        <?= $page_action ?>
        <span class="hidden sm:inline text-sm text-gray-500"><?= htmlspecialchars($display_name) ?></span>
        <span class="hidden md:inline bg-primary text-white text-xs font-semibold px-3 py-1 rounded-full"><?= htmlspecialchars($role_label) ?></span>
        <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold" aria-hidden="true">
            <?= htmlspecialchars(strtoupper(substr($display_name, 0, 1))) ?>
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

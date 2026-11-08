<?php
/**
 * Barre supérieure unique des pages d'administration.
 *
 * À inclure juste après l'ouverture de <main>, avant le contenu.
 * Variables attendues avant l'include :
 * - $page_title    : titre affiché (obligatoire)
 * - $page_subtitle : sous-titre gris d'une ligne (optionnel)
 * - $page_action   : HTML optionnel d'un bouton d'action à droite
 * - $page_search   : true pour afficher la recherche d'articles (défaut : false)
 *
 * Les variables $admin_name et $admin_role viennent de config_admin.php.
 */
$page_title    = $page_title    ?? 'Administration';
$page_subtitle = $page_subtitle ?? '';
$page_action   = $page_action   ?? '';
$page_search   = $page_search   ?? false;

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

// La recherche du haut ne sert qu'aux articles : jamais sur les pages
// qui ont déjà leur propre champ de recherche (Ventes, Newsletter…).
$topbar_search = ($page_search && basename($_SERVER['PHP_SELF'] ?? '') === 'manage_posts.php')
    ? trim((string) ($_GET['q'] ?? ''))
    : '';

// Bandeau d'avertissement : paiement en mode test (sandbox).
$payment_sandbox_mode = false;
if (!function_exists('payment_is_sandbox')) {
    $payment_file = __DIR__ . '/../config/payment.php';
    if (file_exists($payment_file)) {
        require_once $payment_file;
    }
}
if (function_exists('payment_is_sandbox')) {
    try {
        $payment_sandbox_mode = payment_is_sandbox() && payment_is_configured();
    } catch (Throwable $e) {
        $payment_sandbox_mode = false;
    }
}
?>
<header class="adm-topbar px-4 sm:px-6 py-2.5 flex items-center gap-3 sm:gap-5">
    <div class="flex items-center gap-3 min-w-0 shrink-0">
        <button type="button" class="admin-menu-btn items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition"
                onclick="toggleAdminSidebar()" aria-controls="adminSidebar" aria-expanded="false" aria-label="Ouvrir le menu">
            <i class="ph ph-list text-lg"></i>
        </button>
        <div class="min-w-0">
            <h1 class="adm-topbar-title"><?= htmlspecialchars($page_title) ?></h1>
            <?php if ($page_subtitle !== ''): ?>
            <p class="adm-topbar-sub"><?= htmlspecialchars($page_subtitle) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($page_search && has_permission('manage_posts')): ?>
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

        <?php if ($topbar_pending > 0): ?>
        <a href="manage_comments.php" class="adm-icon-btn" aria-label="Commentaires à valider (<?= $topbar_pending ?>)">
            <i class="ph ph-bell"></i>
            <span class="adm-icon-dot"><?= $topbar_pending > 99 ? '99+' : $topbar_pending ?></span>
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

<?php if ($payment_sandbox_mode): ?>
<div class="mx-4 sm:mx-6 mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-start gap-2" role="status">
    <i class="ph ph-flask text-lg mt-0.5"></i>
    <span>
        <strong>Mode test Kkiapay activé.</strong>
        Aucun paiement réel n'est encaissé. Avant la mise en ligne, passez
        <code>sandbox</code> à <code>false</code> dans <code>config/payment_config.php</code>.
    </span>
</div>
<?php endif; ?>
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

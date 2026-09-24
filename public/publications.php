<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Publications - Joie Enseignante";

$cat_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

if ($cat_filter) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, u.name as author_name,
               (SELECT COUNT(*) FROM likes WHERE id_post = p.id_post) as likes_count,
               (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post AND status = 'visible') as comments_count
        FROM posts p
        LEFT JOIN categories c ON p.id_category = c.id_category
        LEFT JOIN users u ON p.id_user = u.id_user
        WHERE p.id_category = ? AND (p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= NOW()))
        ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ");
    $stmt->execute([$cat_filter]);
    $posts = $stmt->fetchAll();

    $total = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_category = ? AND (status = 'published' OR (status = 'scheduled' AND published_at <= NOW()))");
    $total->execute([$cat_filter]);
    $total = $total->fetchColumn();
} else {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, u.name as author_name,
               (SELECT COUNT(*) FROM likes WHERE id_post = p.id_post) as likes_count,
               (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post AND status = 'visible') as comments_count
        FROM posts p
        LEFT JOIN categories c ON p.id_category = c.id_category
        LEFT JOIN users u ON p.id_user = u.id_user
        WHERE (p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= NOW()))
        ORDER BY p.created_at DESC
        LIMIT $per_page OFFSET $offset
    ");
    $posts = $stmt->fetchAll();

    $total = $pdo->query("SELECT COUNT(*) FROM posts WHERE (status = 'published' OR (status = 'scheduled' AND published_at <= NOW()))")->fetchColumn();
}
$total_pages = ceil($total / $per_page);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include "../includes/header.php";
?>

    <div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/hero-2.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3">Recherche &amp; partage</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Mes publications</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto"><?= number_format($total) ?> publication<?= $total > 1 ? 's' : '' ?> à découvrir</p>
    </div>
</div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 mt-4" id="main-content">
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            <a href="publications.php" class="px-4 py-2 rounded-full text-sm font-medium transition-all <?= $cat_filter === 0 ? 'bg-primary-500 text-white shadow-md shadow-primary-500/25' : 'bg-white dark:bg-dark-50 text-gray-600 dark:text-dark-400 border border-gray-100 dark:border-dark-100 hover:border-primary-200' ?>">Tous</a>
            <?php foreach ($categories as $cat):
                $nb = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_category = ? AND (status = 'published' OR (status = 'scheduled' AND published_at <= NOW()))");
                $nb->execute([$cat['id_category']]);
                $nb = $nb->fetchColumn();
                if ($nb == 0) continue;
            ?>
            <a href="publications.php?cat=<?= $cat['id_category'] ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium transition-all <?= $cat_filter === (int)$cat['id_category'] ? 'bg-primary-500 text-white shadow-md shadow-primary-500/25' : 'bg-white dark:bg-dark-50 text-gray-600 dark:text-dark-400 border border-gray-100 dark:border-dark-100 hover:border-primary-200' ?>">
                <i class="ph ph-folder"></i> <?= htmlspecialchars($cat['name']) ?> <span class="text-xs opacity-70">(<?= $nb ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $i => $post): ?>
                <article class="bg-white dark:bg-dark-50 rounded-2xl shadow-sm dark:shadow-none border border-gray-100 dark:border-dark-100 overflow-hidden hover:shadow-lg dark:hover:border-primary-800/30 transition-all duration-400 group flex flex-col <?= REVEAL ?>">
                    <?php if (!empty($post['main_image'])): ?>
                    <a href="post.php?id=<?= $post['id_post'] ?>" class="h-48 overflow-hidden block">
                        <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    </a>
                    <?php endif; ?>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 px-3 py-1 rounded-full"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                            <span class="text-xs text-gray-400 dark:text-dark-300"><?= format_date($post['created_at']) ?></span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                            <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($post['title']) ?></a>
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-dark-300 leading-relaxed mb-4 flex-1"><?= truncate(strip_tags($post['content']), 150) ?></p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-dark-100">
                            <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-dark-300">
                                <span class="inline-flex items-center gap-1.5"><i class="ph ph-heart"></i> <?= $post['likes_count'] ?? 0 ?></span>
                                <span class="inline-flex items-center gap-1.5"><i class="ph ph-chat-circle"></i> <?= $post['comments_count'] ?? 0 ?></span>
                            </div>
                            <a href="post.php?id=<?= $post['id_post'] ?>" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-all inline-flex items-center gap-1.5 group/link">
                                Lire <i class="ph ph-arrow-right group-hover/link:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php empty_state('book-open', 'Aucune publication pour le moment.', 'Revenez bientôt pour découvrir les nouveautés !'); ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="flex justify-center items-center gap-2 mt-12" aria-label="Pagination">
            <?php if ($page > 1): ?>
            <a href="?<?= $cat_filter ? 'cat=' . $cat_filter . '&' : '' ?>page=<?= $page - 1 ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50 transition-all inline-flex items-center gap-1.5"><i class="ph ph-caret-left"></i> Précédent</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?<?= $cat_filter ? 'cat=' . $cat_filter . '&' : '' ?>page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-all <?= $i === $page ? 'bg-primary-500 text-white shadow-md shadow-primary-500/25' : 'text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50' ?>" aria-current="<?= $i === $page ? 'page' : 'false' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
            <a href="?<?= $cat_filter ? 'cat=' . $cat_filter . '&' : '' ?>page=<?= $page + 1 ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50 transition-all inline-flex items-center gap-1.5">Suivant <i class="ph ph-caret-right"></i></a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </section>

    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>
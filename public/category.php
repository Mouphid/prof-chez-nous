<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$id_category = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';

if ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id_category = ?");
    $stmt->execute([$id_category]);
}
$category = $stmt->fetch();

if (!$category) {
    header("Location: index.php");
    exit;
}

$page_title = htmlspecialchars($category['name']);

$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name,
           (SELECT COUNT(*) FROM likes WHERE id_post = p.id_post) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE id_post = p.id_post AND status = 'visible') as comments_count
    FROM posts p
    LEFT JOIN users u ON p.id_user = u.id_user
    WHERE p.id_category = ? AND (p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= NOW()))
    ORDER BY p.created_at DESC
");
$stmt->execute([$category['id_category']]);
$posts = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$cat_counts = [];
foreach ($pdo->query("SELECT id_category, COUNT(*) AS n FROM posts WHERE (status = 'published' OR (status = 'scheduled' AND published_at <= NOW())) GROUP BY id_category")->fetchAll() as $row) {
    $cat_counts[(int)$row['id_category']] = (int)$row['n'];
}

include "../includes/header.php";
?>
    <section class="max-w-7xl mx-auto px-4 py-8 animate-fade-in" id="main-content">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-6">
                    <i class="ph ph-folder text-3xl text-primary"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900"><?= $page_title ?></h1>
                        <?php if ($category['description']): ?>
                        <p class="text-gray-500 mt-1"><?= nl2br(htmlspecialchars($category['description'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($posts) > 0): ?>
                <div class="space-y-6 animate-stagger">
                    <?php foreach ($posts as $post): ?>
                    <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition animate-slideUp">
                        <div class="flex flex-col sm:flex-row">
                            <div class="sm:w-48 sm:flex-shrink-0">
                                <?= post_thumb_html($post, 'post.php?id=' . (int)$post['id_post']) ?>
                            </div>
                            <div class="p-6 flex-1">
                                <h2 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                    <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary transition"><?= htmlspecialchars($post['title']) ?></a>
                                </h2>
                                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-3">
                                    <span><i class="ph ph-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                                    <span><i class="ph ph-calendar"></i> <?= format_date($post['created_at']) ?></span>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4 sbr-justify"><?= truncate(strip_tags($post['content']), 250) ?></p>
                                <div class="flex items-center gap-4 text-sm">
                                    <a href="post.php?id=<?= $post['id_post'] ?>" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition inline-flex items-center gap-1 active:scale-[0.97]">
                                        <i class="ph ph-book-open"></i> Lire plus
                                    </a>
                                    <?php if (($post['likes_count'] ?? 0) > 0): ?>
                                    <span class="text-gray-400"><i class="ph ph-heart"></i> <?= (int)$post['likes_count'] ?></span>
                                    <?php endif; ?>
                                    <?php if (($post['comments_count'] ?? 0) > 0): ?>
                                    <span class="text-gray-400"><i class="ph ph-chats"></i> <?= (int)$post['comments_count'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                    <i class="ph ph-folder-open text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">Aucun article dans cette catégorie.</p>
                    <a href="index.php" class="text-primary hover:underline mt-2 inline-block"><i class="ph ph-arrow-left"></i> Retour à l'accueil</a>
                </div>
                <?php endif; ?>
            </div>

            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="ph ph-folder text-primary mr-2"></i> Catégories</h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat):
                            $nb_cat = $cat_counts[(int)$cat['id_category']] ?? 0;
                            if ($nb_cat === 0) continue;
                        ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm <?= $cat['id_category'] == $category['id_category'] ? 'bg-primary-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50 transition' ?>">
                                <span class="w-2 h-2 rounded-full bg-primary flex-shrink-0"></span>
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>
    </section>

    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

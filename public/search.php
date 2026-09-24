<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Recherche - Joie Enseignante";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if (!empty($q)) {
    $search = "%$q%";
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM posts p
        LEFT JOIN categories c ON p.id_category = c.id_category
        WHERE (p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= NOW())) AND (p.title LIKE ? OR p.content LIKE ?)
        ORDER BY p.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll();
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include "../includes/header.php";
?>

    <section class="max-w-7xl mx-auto px-4 py-8 animate-fade-in" id="main-content">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 mb-6"><i class="ph ph-magnifying-glass text-primary"></i> Recherche</h1>

                <form method="get" class="mb-8">
                    <div class="flex gap-2">
                        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un article..." required class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:outline-none transition">
                        <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-primary-700 transition flex items-center gap-2 active:scale-[0.98]">
                            <i class="ph ph-magnifying-glass"></i> Rechercher
                        </button>
                    </div>
                </form>

                <?php if (!empty($q)): ?>
                    <p class="text-gray-500 mb-6"><?= count($results) ?> résultat(s) pour "<strong class="text-gray-900"><?= htmlspecialchars($q) ?></strong>"</p>

                    <?php if (count($results) > 0): ?>
                    <div class="space-y-4 animate-stagger">
                        <?php foreach ($results as $post): ?>
                        <article class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition animate-slideUp">
                            <h2 class="text-lg font-bold text-gray-900 mb-2">
                                <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary transition"><?= htmlspecialchars($post['title']) ?></a>
                            </h2>
                            <div class="flex flex-wrap gap-3 text-sm text-gray-500 mb-3">
                                <?php if ($post['category_name']): ?>
                                <span><i class="ph ph-folder text-primary"></i> <?= htmlspecialchars($post['category_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="ph ph-calendar text-primary"></i> <?= format_date($post['created_at']) ?></span>
                            </div>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4"><?= truncate(strip_tags($post['content']), 200) ?></p>
                            <a href="post.php?id=<?= $post['id_post'] ?>" class="inline-flex items-center gap-1 text-primary font-medium text-sm hover:underline">
                                <i class="ph ph-book-open"></i> Lire plus
                            </a>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                        <i class="ph ph-magnifying-glass text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Aucun résultat trouvé.</p>
                        <p class="text-gray-400">Essayez avec d'autres mots-clés.</p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="ph ph-folder text-primary mr-2"></i> Catégories</h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
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

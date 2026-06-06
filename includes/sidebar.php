<aside class="sidebar">
    <div class="widget">
        <h3><i class="fas fa-folder"></i> Catégories</h3>
        <ul class="category-list">
            <?php 
            $stmt_cat = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE id_category = c.id_category AND status IN ('published', 'draft')) as post_count FROM categories c ORDER BY name");
            while ($cat = $stmt_cat->fetch()): 
            ?>
            <li>
                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                    <span class="category-color"></span>
                    <?= htmlspecialchars($cat['name']) ?>
                    <span class="cat-count">(<?= $cat['post_count'] ?>)</span>
                </a>
            </li>
            <?php endwhile; ?>
            
            <?php
            // Articles non classifiés
            $uncat = $pdo->query("SELECT COUNT(*) FROM posts WHERE (id_category IS NULL OR id_category = 0) AND status IN ('published', 'draft')")->fetchColumn();
            if ($uncat > 0):
            ?>
            <li>
                <a href="category.php?id=0">
                    <span class="category-color"></span>
                    Non classé
                    <span class="cat-count">(<?= $uncat ?>)</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="widget">
        <h3><i class="fas fa-clock"></i> Articles récents</h3>
        <ul class="recent-posts">
            <?php 
            $stmt_recent = $pdo->query("SELECT id_post, title FROM posts WHERE status IN ('published', 'draft') ORDER BY created_at DESC LIMIT 5");
            while ($post = $stmt_recent->fetch()): 
            ?>
            <li><a href="post.php?id=<?= $post['id_post'] ?>"><?= truncate($post['title'], 40) ?></a></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="widget">
        <h3><i class="fas fa-archive"></i> Archives</h3>
        <ul class="archives">
            <?php 
            try {
                $stmt_arch = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM posts WHERE status IN ('published', 'draft') GROUP BY month ORDER BY month DESC LIMIT 12");
                while ($arch = $stmt_arch->fetch()): 
            ?>
            <li><a href="index.php?month=<?= $arch['month'] ?>"><?= format_date($arch['month'] . '-01', 'F Y') ?> (<?= $arch['count'] ?>)</a></li>
            <?php 
                endwhile;
            } catch (Exception $e) {}
            ?>
        </ul>
    </div>
</aside>
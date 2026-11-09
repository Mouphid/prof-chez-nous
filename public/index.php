<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Accueil - Joie Enseignante";

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 4;
$offset = ($page - 1) * $per_page;

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
$total_pages = ceil($total / $per_page);

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include "../includes/header.php";
?>
    <?php if ($page === 1): ?>
    <!-- ═══ HERO SECTION ═══ -->
    <section class="relative min-h-[85vh] flex items-center overflow-hidden" id="hero-slider">
        <?php foreach ($hero_slides as $i => $src): ?>
        <div class="hero-slide absolute inset-0 bg-cover bg-center transition-opacity duration-[1500ms] <?= $i === 0 ? 'opacity-100 hero-slide-active' : 'opacity-0' ?>" style="background-image: url('<?= $src ?>');" data-index="<?= $i ?>"></div>
        <?php endforeach; ?>
        <div class="absolute inset-0" style="background:linear-gradient(90deg, rgba(15,23,42,.82) 0%, rgba(15,23,42,.58) 55%, rgba(15,23,42,.38) 100%)"></div>

        <!-- Dots -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-2.5">
            <?php foreach ($hero_slides as $i => $src): ?>
            <button class="hero-dot w-3 h-3 rounded-full transition-all duration-500 <?= $i === 0 ? 'bg-white scale-125 w-8' : 'bg-white/40 hover:bg-white/60' ?>" data-slide="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-28 relative z-10 w-full">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold text-white uppercase tracking-widest mb-7 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-accent-400"></span>
                    Plateforme éducative dédiée aux enseignants
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-extrabold leading-[1.08] mb-6 text-white">
                    Bienvenue sur<br>
                    <span class="text-accent-400"><?= SITE_NAME ?></span>
                </h1>
                <p class="text-base sm:text-lg text-gray-200/90 leading-relaxed mb-9 max-w-xl">
                    <?= SITE_TAGLINE ?>. Partagez, apprenez et grandissez ensemble dans un cadre bienveillant.
                </p>
                <div class="flex flex-col sm:flex-row items-start gap-3">
                    <a href="#articles" class="bg-white text-primary-700 font-semibold px-6 py-3.5 rounded-xl hover:bg-gray-50 transition-all duration-300 shadow-lg active:scale-[0.98] inline-flex items-center gap-2.5">
                        <i class="ph ph-book-open text-lg"></i> Découvrir les articles
                    </a>
                    <a href="cours.php" class="bg-accent-500 text-white font-semibold px-6 py-3.5 rounded-xl hover:bg-accent-600 transition-all duration-300 shadow-lg active:scale-[0.98] inline-flex items-center gap-2.5">
                        <i class="ph ph-graduation-cap text-lg"></i> Découvrir les cours
                    </a>
                </div>
            </div>
        </div>
    </section>
    <script>
    (function(){
        const slides = document.querySelectorAll('#hero-slider .hero-slide');
        const dots = document.querySelectorAll('#hero-slider .hero-dot');
        if (!slides.length) return;
        let current = 0;
        const interval = <?= HERO_SLIDE_INTERVAL ?>;
        function goTo(index) {
            if (index === current) return;
            slides[current].classList.remove('opacity-100', 'hero-slide-active');
            slides[current].classList.add('opacity-0');
            dots[current].classList.remove('bg-white', 'scale-125', 'w-8');
            dots[current].classList.add('bg-white/40');
            current = index;
            slides[current].classList.remove('opacity-0');
            slides[current].classList.add('opacity-100', 'hero-slide-active');
            dots[current].classList.remove('bg-white/40');
            dots[current].classList.add('bg-white', 'scale-125', 'w-8');
        }
        dots.forEach(d => d.addEventListener('click', function(){ goTo(parseInt(this.dataset.slide)); }));
        setInterval(function(){ goTo((current + 1) % slides.length); }, interval);
    })();
    </script>
    <?php endif; ?>



    <!-- ═══ INTRODUCTION ═══ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div <?= REVEAL_LEFT ?>>
                <span class="text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase tracking-widest mb-3 block">Bienvenue</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-6 leading-tight">
                    Bienvenue à <span class="text-primary-600 dark:text-primary-400">Joie Enseignante</span>
                </h2>
                <div class="text-gray-600 dark:text-dark-400 leading-relaxed space-y-4 sbr-justify">
                    <p>
                        <strong class="text-gray-900 dark:text-white">Joie Enseignante</strong> est un espace dédié à l'accompagnement des étudiants 
                        et à la promotion de l'éducation de qualité. Notre mission est de partager des connaissances, de créer des 
                        contenus éducatifs enrichissants, et de favoriser l'engagement des étudiants dans leur parcours académique.
                    </p>
                    <p>
                        Nous nous efforçons de promouvoir des valeurs telles que l'excellence, la rigueur académique, et l'innovation 
                        dans l'enseignement. Que vous soyez étudiant ou enseignant, nous vous invitons à explorer nos ressources.
                    </p>
                </div>
                <a href="about.php" class="inline-flex items-center gap-2 mt-6 text-primary-600 dark:text-primary-400 font-semibold hover:text-primary-700 dark:hover:text-primary-300 transition-all group">
                    En savoir plus <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
            <div <?= REVEAL_RIGHT ?> class="relative">
                <div class="bg-white dark:bg-dark-50 rounded-3xl border border-gray-100 dark:border-dark-100 p-8 sm:p-10 shadow-sm">
                    <div class="flex items-center gap-3 mb-7">
                        <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-2xl flex items-center justify-center">
                            <i class="ph ph-graduation-cap text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-lg text-gray-900 dark:text-white">Notre engagement</p>
                            <p class="text-sm text-gray-500 dark:text-dark-300">Une éducation exigeante et accessible</p>
                        </div>
                    </div>
                    <div class="space-y-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                            <span class="text-sm text-gray-700 dark:text-dark-400">Cours structurés et de qualité</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                            <span class="text-sm text-gray-700 dark:text-dark-400">Ressources pédagogiques en ligne</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                            <span class="text-sm text-gray-700 dark:text-dark-400">Communauté bienveillante</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-accent-50 dark:bg-accent-900/20 text-accent-600 dark:text-accent-400 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                            <span class="text-sm text-gray-700 dark:text-dark-400">Innovation pédagogique</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($page === 1): ?>
    <!-- ═══ FEATURES ═══ -->
    <section class="bg-white dark:bg-dark-50 py-16 sm:py-24 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14 <?= REVEAL_UP ?>">
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-3 bg-primary-50 dark:bg-primary-900/20 px-4 py-1.5 rounded-full">Nos fonctionnalités</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-4">Tout ce dont vous avez besoin</h2>
                <p class="text-gray-500 dark:text-dark-300 max-w-xl mx-auto">Une plateforme complète pour enrichir vos cours et partager vos ressources.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                <?php
                $features = [
                    ['ph-graduation-cap', 'Cours structurés', 'Accédez à des cours complets organisés par catégories pour faciliter votre enseignement au quotidien.', 'primary'],
                    ['ph-book-open', 'Contenus pédagogiques', 'Consultez les cours et articles rédigés par le professeur pour accompagner votre apprentissage.', 'primary'],
                    ['ph-magnifying-glass', 'Recherche intelligente', 'Trouvez rapidement les ressources qui vous intéressent grâce à notre moteur de recherche.', 'accent'],
                    ['ph-star', 'Contenu exclusif', 'Profitez de publications régulières et de contenus exclusifs pour enrichir votre pédagogie.', 'primary'],
                    ['ph-users-three', 'Communauté active', 'Rejoignez une communauté d\'enseignants et d\'étudiants passionnés par l\'éducation.', 'accent'],
                    ['ph-lightbulb', 'Rigueur académique', 'Une pédagogie exigeante et bienveillante au service de la réussite de chacun.', 'primary'],
                ];
                foreach ($features as $i => $feat):
                    $tone = $feat[3] === 'accent'
                        ? 'bg-accent-50 text-accent-600 dark:bg-accent-900/20 dark:text-accent-400'
                        : 'bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400';
                ?>
                <div class="group bg-white dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:border-gray-200 <?= REVEAL ?> stagger-<?= $i + 1 ?>">
                    <div class="w-11 h-11 rounded-xl <?= $tone ?> flex items-center justify-center mb-5 transition-transform duration-300 group-hover:scale-105">
                        <i class="ph <?= $feat[0] ?> text-xl"></i>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2"><?= $feat[1] ?></h3>
                    <p class="text-sm text-gray-600 dark:text-dark-300 leading-relaxed sbr-justify"><?= $feat[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══ ARTICLES ═══ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24" id="articles" aria-label="Derniers articles">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10" <?= REVEAL_UP ?>>
            <div>
                <span class="text-xs font-semibold text-accent-600 dark:text-accent-400 uppercase tracking-widest mb-2 block">Le blog</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Derniers articles</h2>
            </div>
            <p class="text-sm text-gray-500 dark:text-dark-300"><?= number_format($total) ?> publication<?= $total > 1 ? 's' : '' ?> à découvrir</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1 min-w-0">
                <?php if (count($posts) > 0): ?>
                    <div class="space-y-5">
                        <?php foreach ($posts as $i => $post): ?>
                        <article class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 hover:shadow-lg dark:hover:border-primary-800/30 transition-all duration-300 overflow-hidden group <?= REVEAL ?> stagger-<?= ($i % 6) + 1 ?>">
                            <div class="flex flex-col sm:flex-row">
                                <?php if (!empty($post['main_image'])): ?>
                                <div class="sm:w-56 h-48 sm:h-auto overflow-hidden flex-shrink-0">
                                    <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                                <?php endif; ?>
                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-900/20 px-3 py-1 rounded-full"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                                        <span class="text-gray-300 dark:text-dark-200">·</span>
                                        <span class="text-xs text-gray-500 dark:text-dark-300"><?= format_date($post['created_at']) ?></span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 leading-snug">
                                        <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($post['title']) ?></a>
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-dark-300 leading-relaxed mb-4 flex-1 sbr-justify"><?= truncate(strip_tags($post['content']), 180) ?></p>
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-dark-100">
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-dark-300">
                                            <span class="inline-flex items-center gap-1.5"><i class="ph ph-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonyme') ?></span>
                                            <span class="inline-flex items-center gap-1.5"><i class="ph ph-heart"></i> <?= $post['likes_count'] ?? 0 ?></span>
                                            <span class="inline-flex items-center gap-1.5"><i class="ph ph-chat-circle"></i> <?= $post['comments_count'] ?? 0 ?></span>
                                        </div>
                                        <a href="post.php?id=<?= $post['id_post'] ?>" class="text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-all inline-flex items-center gap-1.5 group/link">
                                            Lire <i class="ph ph-arrow-right group-hover/link:translate-x-1 transition-transform"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav class="flex justify-center items-center gap-2 mt-12" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50 transition-all inline-flex items-center gap-1.5"><i class="ph ph-caret-left"></i> Précédent</a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-medium transition-all <?= $i === $page ? 'bg-primary-500 text-white shadow-md shadow-primary-500/25' : 'text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50' ?>" aria-current="<?= $i === $page ? 'page' : 'false' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-500 dark:text-dark-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-50 transition-all inline-flex items-center gap-1.5">Suivant <i class="ph ph-caret-right"></i></a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                <?php empty_state('book-open', 'Aucun article disponible pour le moment.', 'Revenez bientôt pour découvrir les nouveautés !'); ?>
                <?php endif; ?>
            </div>

            <!-- ═══ SIDEBAR ═══ -->
            <aside class="w-full lg:w-72 space-y-5 flex-shrink-0" aria-label="Barre latérale">
                <?php
                // Compteur par catégorie en une seule requête (évite une requête par catégorie)
                $cat_counts = [];
                $cat_count_rows = $pdo->query("
                    SELECT id_category, COUNT(*) AS n FROM posts
                    WHERE (status = 'published' OR (status = 'scheduled' AND published_at <= NOW()))
                    GROUP BY id_category
                ")->fetchAll();
                foreach ($cat_count_rows as $row) {
                    $cat_counts[(int)$row['id_category']] = (int)$row['n'];
                }
                ?>
                <!-- Catégories -->
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5 <?= REVEAL ?>">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="ph ph-folder-simple text-primary-500"></i> Catégories
                    </h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-dark-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="text-xs bg-gray-100 dark:bg-dark-100 text-gray-500 dark:text-dark-300 px-2 py-0.5 rounded-full"><?= $cat_counts[(int)$cat['id_category']] ?? 0 ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Récents -->
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5 <?= REVEAL ?>">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <i class="ph ph-clock text-primary-500"></i> Récents
                    </h3>
                    <ul class="space-y-1">
                        <?php
                        $recent = $pdo->query("SELECT id_post, title FROM posts WHERE (status = 'published' OR (status = 'scheduled' AND published_at <= NOW())) ORDER BY created_at DESC LIMIT 5");
                        while ($r = $recent->fetch()):
                        ?>
                        <li>
                            <a href="post.php?id=<?= $r['id_post'] ?>" class="text-sm text-gray-500 dark:text-dark-400 hover:text-primary-600 dark:hover:text-primary-400 transition-all block py-2 px-3 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20"><?= htmlspecialchars(truncate($r['title'], 40)) ?></a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="bg-primary-900 rounded-2xl p-5 text-white <?= REVEAL ?>">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mb-4">
                        <i class="ph ph-envelope-simple text-lg"></i>
                    </div>
                    <h3 class="text-sm font-semibold mb-2">La lettre pédagogique</h3>
                    <p class="text-xs text-primary-100/80 mb-4 leading-relaxed">Recevez les nouvelles ressources directement dans votre boîte mail.</p>
                    <form class="space-y-2" action="newsletter_subscribe.php" method="post">
                        <?= csrf_field() ?>
                        <label for="nl-sidebar-email" class="sr-only">Adresse email</label>
                        <input type="email" name="email" id="nl-sidebar-email" required placeholder="Votre email" class="w-full px-3 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 transition-all">
                        <button type="submit" class="w-full bg-accent-500 text-white font-semibold py-2.5 rounded-xl text-sm hover:bg-accent-600 transition-all duration-300">
                            S'abonner
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </section>


    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

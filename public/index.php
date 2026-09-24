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
        <div class="absolute inset-0 bg-dark/50"></div>

        <!-- Floating elements -->
        <div class="absolute top-20 left-10 w-20 h-20 bg-white/5 rounded-2xl rotate-12 animate-float hidden lg:block"></div>
        <div class="absolute bottom-32 right-16 w-16 h-16 bg-accent-500/10 rounded-full animate-float hidden lg:block" style="animation-delay: 2s;"></div>
        <div class="absolute top-40 right-20 w-12 h-12 bg-primary-400/10 rounded-xl -rotate-12 animate-float hidden lg:block" style="animation-delay: 4s;"></div>

        <!-- Dots -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-3">
            <?php foreach ($hero_slides as $i => $src): ?>
            <button class="hero-dot w-3 h-3 rounded-full transition-all duration-500 <?= $i === 0 ? 'bg-white scale-125 w-8' : 'bg-white/40 hover:bg-white/60' ?>" data-slide="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-28 relative z-10 w-full">
            <div class="max-w-3xl">
                <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-8">Plateforme éducative dédiée aux enseignants</span>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-display font-extrabold leading-[1.1] mb-6 text-white">
                    Bienvenue sur<br>
                    <span class="bg-gradient-to-r from-accent-400 to-accent-500 bg-clip-text text-transparent"><?= SITE_NAME ?></span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-200 leading-relaxed mb-10 max-w-xl">
                    <?= SITE_TAGLINE ?>. Partagez, apprenez et grandissez ensemble dans un cadre bienveillant.
                </p>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <a href="#articles" class="bg-white text-primary-700 font-bold px-8 py-4 rounded-2xl hover:bg-gray-50 transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-[0.97] transform hover:-translate-y-1 inline-flex items-center gap-2.5 text-base">
                        <i class="ph ph-book-open text-xl"></i> Découvrir les articles
                    </a>
                    <a href="cours.php" class="bg-accent-500 text-white font-bold px-8 py-4 rounded-2xl hover:bg-accent-600 transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-[0.97] transform hover:-translate-y-1 inline-flex items-center gap-2.5 text-base">
                        <i class="ph ph-graduation-cap text-xl"></i> Découvrir les cours
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
                    Bienvenue à <span class="gradient-text">Joie Enseignante</span>
                </h2>
                <div class="text-gray-600 dark:text-dark-400 leading-relaxed space-y-4">
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
                <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center">
                                <i class="ph ph-graduation-cap text-2xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-lg">Excellence</p>
                                <p class="text-white/70 text-sm">Académique</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                                <span class="text-white/90">Cours structurés et de qualité</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                                <span class="text-white/90">Ressources pédagogiques en ligne</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                                <span class="text-white/90">Communauté bienveillante</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0"><i class="ph ph-check text-sm"></i></div>
                                <span class="text-white/90">Innovation pédagogique</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($page === 1): ?>
    <!-- ═══ FEATURES ═══ -->
    <section class="bg-white dark:bg-dark-50 py-16 sm:py-24 transition-colors duration-300 relative overflow-hidden">
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-primary-100/40 dark:bg-primary-900/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-accent-100/40 dark:bg-accent-900/10 rounded-full blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14 <?= REVEAL_UP ?>">
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-3 bg-primary-50 dark:bg-primary-900/20 px-4 py-1.5 rounded-full">Nos fonctionnalités</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-4">Tout ce dont vous avez besoin</h2>
                <p class="text-gray-500 dark:text-dark-300 max-w-xl mx-auto">Une plateforme complète pour enrichir vos cours et partager vos ressources.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php
                $features = [
                    ['ph-graduation-cap', 'Cours structurés', 'Accédez à des cours complets organisés par catégories pour faciliter votre enseignement au quotidien.', 'from-primary-500 to-primary-700'],
                    ['ph-book-open', 'Contenus pédagogiques', 'Consultez les cours et articles rédigés par le professeur pour accompagner votre apprentissage.', 'from-primary-700 to-primary-900'],
                    ['ph-magnifying-glass', 'Recherche intelligente', 'Trouvez rapidement les ressources qui vous intéressent grâce à notre moteur de recherche.', 'from-accent-400 to-primary-700'],
                    ['ph-star', 'Contenu exclusif', 'Profitez de publications régulières et de contenus exclusifs pour enrichir votre pédagogie.', 'from-primary-500 to-primary-900'],
                    ['ph-users-three', 'Communauté active', 'Rejoignez une communauté d\'enseignants et d\'étudiants passionnés par l\'éducation.', 'from-accent-400 to-primary-900'],
                    ['ph-lightbulb', 'Rigueur académique', 'Une pédagogie exigeante et bienveillante au service de la réussite de chacun.', 'from-primary-700 to-primary-900'],
                ];
                foreach ($features as $i => $feat):
                ?>
                <div class="group relative bg-white dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-7 overflow-hidden transition-all duration-400 hover:-translate-y-2 hover:shadow-xl hover:shadow-primary-500/10 hover:border-primary-200 dark:hover:border-primary-800/40 <?= REVEAL ?> stagger-<?= $i + 1 ?>">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r <?= $feat[3] ?> opacity-0 group-hover:opacity-100 transition-opacity duration-400"></div>
                    <span class="absolute top-5 right-6 text-4xl font-display font-extrabold text-gray-200 dark:text-dark-50 select-none"><?= sprintf('%02d', $i + 1) ?></span>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?= $feat[3] ?> text-white flex items-center justify-center mb-5 shadow-lg shadow-primary-500/20 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                        <i class="<?= $feat[0] ?> text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?= $feat[1] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-dark-300 leading-relaxed"><?= $feat[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══ ARTICLES ═══ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-20" id="articles" aria-label="Derniers articles">
        <div class="text-center mb-12" <?= REVEAL_UP ?>>
            <span class="text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase tracking-widest mb-3 block">Blog</span>
            <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white">Derniers articles</h2>
            <p class="text-gray-500 dark:text-dark-300 mt-3"><?= number_format($total) ?> publication<?= $total > 1 ? 's' : '' ?> à découvrir</p>
        </div>
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-1 min-w-0">
                <?php if (count($posts) > 0): ?>
                    <div class="space-y-5">
                        <?php foreach ($posts as $i => $post): ?>
                        <article class="bg-white dark:bg-dark-50 rounded-2xl shadow-sm dark:shadow-none border border-gray-100 dark:border-dark-100 hover:shadow-lg dark:hover:border-primary-800/30 transition-all duration-400 group <?= REVEAL ?> stagger-<?= ($i % 6) + 1 ?>">
                            <div class="flex flex-col sm:flex-row">
                                <?php if (!empty($post['main_image'])): ?>
                                <div class="sm:w-56 h-48 sm:h-auto overflow-hidden flex-shrink-0 rounded-t-2xl sm:rounded-l-2xl sm:rounded-tr-none">
                                    <img src="../uploads/images/<?= htmlspecialchars($post['main_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <?php endif; ?>
                                <div class="p-6 flex-1 flex flex-col">
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 px-3 py-1 rounded-full"><?= htmlspecialchars($post['category_name'] ?? 'Non classé') ?></span>
                                        <span class="text-gray-300 dark:text-dark-200">·</span>
                                        <span class="text-xs text-gray-400 dark:text-dark-300"><?= format_date($post['created_at']) ?></span>
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                                        <a href="post.php?id=<?= $post['id_post'] ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($post['title']) ?></a>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-dark-300 leading-relaxed mb-4 flex-1"><?= truncate(strip_tags($post['content']), 180) ?></p>
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-dark-100">
                                        <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-dark-300">
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
                <!-- Catégories -->
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5 shadow-sm <?= REVEAL ?>">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2"><i class="ph ph-folder-open text-primary-500"></i> Catégories</h3>
                    <ul class="space-y-1">
                        <?php foreach ($categories as $cat):
                            $count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE id_category = ? AND (status = 'published' OR (status = 'scheduled' AND published_at <= NOW()))");
                            $count->execute([$cat['id_category']]);
                            $total_cat = $count->fetchColumn();
                        ?>
                        <li>
                            <a href="category.php?id=<?= $cat['id_category'] ?>" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-dark-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="text-xs bg-gray-100 dark:bg-dark-100 px-2 py-0.5 rounded-full"><?= $total_cat ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Récents -->
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5 shadow-sm <?= REVEAL ?>">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2"><i class="ph ph-clock text-primary-500"></i> Récents</h3>
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
                <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-2xl p-5 text-white <?= REVEAL ?>">
                    <i class="ph ph-envelope-simple text-2xl mb-3 block text-white/80"></i>
                    <h3 class="text-sm font-bold mb-2">Newsletter</h3>
                    <p class="text-xs text-white/70 mb-4">Recevez les dernières actualités directement dans votre boîte mail.</p>
                    <form class="space-y-2">
                        <input type="email" placeholder="Votre email" class="w-full px-3 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 transition-all">
                        <button type="submit" class="w-full bg-white text-primary-700 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-all duration-300 shadow-sm">
                            S'abonner
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </section>


    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

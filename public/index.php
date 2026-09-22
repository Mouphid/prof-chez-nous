<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Accueil - Joie Enseignante";

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
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
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); animation_styles(); ?>
</head>
<body class="bg-gray-50 dark:bg-dark font-sans text-gray-800 dark:text-dark-500 antialiased leading-relaxed transition-colors duration-300">
    <?php skip_link() ?>

    <!-- ═══ NAVBAR ═══ -->
    <header class="bg-white/80 dark:bg-dark/80 backdrop-blur-xl border-b border-gray-100 dark:border-dark-100 sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 sm:h-18">
            <a href="index.php" class="flex items-center gap-2.5 transition group">
                <img src="../img/logo.jpg" alt="<?= SITE_NAME ?>" class="h-10 sm:h-12 w-auto rounded-lg group-hover:scale-105 transition-transform duration-300">
            </a>
            <nav class="hidden md:flex items-center gap-1" aria-label="Navigation principale">
                <a href="index.php" class="px-3.5 py-2 rounded-xl text-sm font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 ring-1 ring-primary-200 dark:ring-primary-800 transition-all duration-300"><i class="ph ph-house"></i> Accueil</a>
                <a href="about.php" class="px-3.5 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white transition-all duration-300"><i class="ph ph-info"></i> À propos</a>
                <a href="biography.php" class="px-3.5 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white transition-all duration-300"><i class="ph ph-user-circle"></i> Biographie</a>
                <a href="contact.php" class="px-3.5 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white transition-all duration-300"><i class="ph ph-envelope"></i> Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 bg-primary-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-primary-600 transition-all duration-300 shadow-sm hover:shadow-md" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-user"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profil') ?> <i class="ph ph-caret-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-dark-50 rounded-2xl shadow-xl dark:shadow-dark-lg border border-gray-100 dark:border-dark-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50" role="menu">
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 rounded-t-2xl transition-colors" role="menuitem"><i class="ph ph-user-cog text-base"></i> Mon profil</a>
                        <a href="my_downloads.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" role="menuitem"><i class="ph ph-download text-base"></i> Mes téléchargements</a>
                        <a href="my_comments.php" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-dark-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" role="menuitem"><i class="ph ph-chats text-base"></i> Mes commentaires</a>
                        <hr class="border-gray-100 dark:border-dark-100">
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-b-2xl transition-colors" role="menuitem"><i class="ph ph-sign-out text-base"></i> Déconnexion</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="register.php" class="px-3.5 py-2 rounded-xl text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all duration-300"><i class="ph ph-user-plus"></i> Inscription</a>
                <a href="login.php" class="bg-primary-500 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-primary-600 transition-all duration-300 shadow-sm hover:shadow-md"><i class="ph ph-sign-in"></i> Connexion</a>
                <?php endif; ?>
            </nav>
            <div class="flex items-center gap-2 md:hidden">
                <button id="darkModeToggle" class="p-2 rounded-xl text-gray-500 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 transition-all" aria-label="Basculer le thème">
                    <i class="ph ph-moon text-lg dark:hidden"></i>
                    <i class="ph ph-sun text-lg hidden dark:block"></i>
                </button>
                <button class="text-gray-600 dark:text-dark-400 p-2 hover:bg-gray-100 dark:hover:bg-dark-50 rounded-xl transition-all" onclick="toggleMobileMenu(this)" aria-label="Menu"><i class="ph ph-list text-xl"></i></button>
            </div>
        </div>
        <div class="hidden md:hidden bg-white dark:bg-dark border-t border-gray-100 dark:border-dark-100 px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <a href="index.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400"><i class="ph ph-house"></i> Accueil</a>
            <a href="about.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-info"></i> À propos</a>
            <a href="biography.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-user-circle"></i> Biographie</a>
            <a href="contact.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-envelope"></i> Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-user-cog"></i> Mon profil</a>
            <a href="my_downloads.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-download"></i> Mes téléchargements</a>
            <a href="my_comments.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-chats"></i> Mes commentaires</a>
            <a href="logout.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 dark:text-red-400"><i class="ph ph-sign-out"></i> Déconnexion</a>
            <?php else: ?>
            <a href="login.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium bg-primary-500 text-white text-center"><i class="ph ph-sign-in"></i> Connexion</a>
            <a href="register.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-primary-600 dark:text-primary-400 text-center border border-primary-200 dark:border-primary-800"><i class="ph ph-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($page === 1): ?>
    <!-- ═══ HERO SECTION ═══ -->
    <section class="relative min-h-[85vh] flex items-center overflow-hidden" id="hero-slider">
        <?php foreach ($hero_slides as $i => $src): ?>
        <div class="hero-slide absolute inset-0 bg-cover bg-center transition-opacity duration-[1500ms] <?= $i === 0 ? 'opacity-100 hero-slide-active' : 'opacity-0' ?>" style="background-image: url('<?= $src ?>');" data-index="<?= $i ?>"></div>
        <?php endforeach; ?>
        <div class="absolute inset-0 bg-gradient-to-br from-dark/80 via-primary-900/60 to-purple-900/70"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-dark/40 via-transparent to-transparent"></div>

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
                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md text-white text-sm font-medium px-5 py-2 rounded-full mb-8 border border-white/10">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse-slow"></span> Plateforme éducative dédiée aux enseignants
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-display font-extrabold leading-[1.1] mb-6 text-white">
                    Bienvenue sur<br>
                    <span class="bg-gradient-to-r from-accent-400 to-amber-300 bg-clip-text text-transparent"><?= SITE_NAME ?></span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-200 leading-relaxed mb-10 max-w-xl">
                    <?= SITE_TAGLINE ?>. Partagez, apprenez et grandissez ensemble dans un cadre bienveillant.
                </p>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <a href="#articles" class="bg-white text-primary-700 font-bold px-8 py-4 rounded-2xl hover:bg-gray-50 transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-[0.97] transform hover:-translate-y-1 inline-flex items-center gap-2.5 text-base">
                        <i class="ph ph-book-open text-xl"></i> Découvrir les articles
                    </a>
                    <a href="register.php" class="bg-accent-500 text-white font-bold px-8 py-4 rounded-2xl hover:bg-accent-600 transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-[0.97] transform hover:-translate-y-1 inline-flex items-center gap-2.5 text-base">
                        <i class="ph ph-user-plus text-xl"></i> Rejoindre la communauté
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
                <div class="bg-gradient-to-br from-primary-500 to-purple-600 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden">
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
                                <span class="text-white/90">Ressources téléchargeables</span>
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
    <section class="bg-white dark:bg-dark-50 py-16 sm:py-24 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-14" <?= REVEAL_UP ?>>
                <span class="text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase tracking-widest mb-3 block">Nos fonctionnalités</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white mb-4">Tout ce dont vous avez besoin</h2>
                <p class="text-gray-500 dark:text-dark-300 max-w-xl mx-auto">Une plateforme complète pour enrichir vos cours et partager vos ressources.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <?php
                $features = [
                    ['ph-graduation-cap', 'bg-primary-100 dark:bg-primary-900/30', 'text-primary-600 dark:text-primary-400', 'Cours structurés', 'Accédez à des cours complets organisés par catégories pour faciliter votre enseignement au quotidien.'],
                    ['ph-download-simple', 'bg-emerald-100 dark:bg-emerald-900/30', 'text-emerald-600 dark:text-emerald-400', 'Ressources téléchargeables', 'Téléchargez des ressources pour vous accompagner dans vos travaux de recherche et améliorer votre pédagogie.'],
                    ['ph-flask', 'bg-purple-100 dark:bg-purple-900/30', 'text-purple-600 dark:text-purple-400', 'Recherche scientifique', 'Accédez à des travaux de recherche scientifique pour enrichir votre pédagogie.'],
                    ['ph-magnifying-glass', 'bg-sky-100 dark:bg-sky-900/30', 'text-sky-600 dark:text-sky-400', 'Recherche intelligente', 'Trouvez rapidement les ressources qui vous intéressent grâce à notre moteur de recherche.'],
                    ['ph-star', 'bg-amber-100 dark:bg-amber-900/30', 'text-accent-600 dark:text-accent-400', 'Contenu exclusif', 'Profitez de publications régulières et de contenus exclusifs pour enrichir votre pédagogie.'],
                    ['ph-users-three', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-600 dark:text-rose-400', 'Communauté active', 'Rejoignez une communauté d\'enseignants et d\'étudiants passionnés par l\'éducation.'],
                ];
                foreach ($features as $i => $feat):
                ?>
                <div class="group bg-gray-50 dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-7 card-hover <?= REVEAL ?> stagger-<?= $i + 1 ?>">
                    <div class="w-14 h-14 <?= $feat[1] ?> <?= $feat[2] ?> rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i class="<?= $feat[0] ?> text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2"><?= $feat[3] ?></h3>
                    <p class="text-sm text-gray-500 dark:text-dark-300 leading-relaxed"><?= $feat[4] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══ ARTICLES ═══ -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-20" id="articles" role="main">
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
                <div class="bg-gradient-to-br from-primary-500 to-purple-600 rounded-2xl p-5 text-white <?= REVEAL ?>">
                    <i class="ph ph-envelope-simple text-2xl mb-3 block text-white/80"></i>
                    <h3 class="text-sm font-bold mb-2">Newsletter</h3>
                    <p class="text-xs text-white/70 mb-4">Recevez les dernières actualités directement dans votre boîte mail.</p>
                    <form class="space-y-2">
                        <input type="email" placeholder="Votre email" class="w-full px-3 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-white/30 transition-all">
                        <button type="submit" class="w-full bg-white text-primary-600 font-semibold py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-all duration-300 shadow-sm">
                            S'abonner
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </main>



    <?php if (!isset($_SESSION['user_id'])): ?>
    <!-- ═══ CTA ═══ -->
    <section class="relative py-20 sm:py-28 overflow-hidden <?= ANIM_FADE_IN ?>">
        <div class="absolute inset-0 bg-cta-pattern"></div>
        <div class="absolute inset-0 bg-dark/20"></div>
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-white mb-5">Prêt à rejoindre la communauté ?</h2>
            <p class="text-white/70 leading-relaxed mb-10 max-w-lg mx-auto text-lg">Inscrivez-vous gratuitement et accédez à toutes les ressources pédagogiques.</p>
            <a href="register.php" class="inline-flex items-center gap-2.5 bg-accent-500 text-white font-bold px-10 py-4 rounded-2xl hover:bg-accent-600 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-accent-500/25 active:scale-[0.97] transform hover:-translate-y-1 text-base">
                <i class="ph ph-user-plus text-xl"></i> Créer un compte gratuit
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ═══ FOOTER ═══ -->
    <footer class="bg-dark dark:bg-dark-50 text-gray-400 dark:text-dark-300 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="index.php" class="flex items-center gap-2.5 mb-4 group">
                    <img src="../img/logo.jpg" alt="<?= SITE_NAME ?>" class="h-10 w-auto rounded-lg group-hover:scale-105 transition-transform">
                </a>
                <p class="leading-relaxed text-gray-500 dark:text-dark-300 text-sm mb-6"><?= SITE_TAGLINE ?></p>
                <div class="flex items-center gap-2">
                    <?php social_icons() ?>
                </div>
            </div>
            <div>
                <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Navigation</h4>
                <ul class="space-y-3">
                    <li><a href="index.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-house text-gray-600 dark:text-dark-200"></i> Accueil</a></li>
                    <li><a href="about.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-info text-gray-600 dark:text-dark-200"></i> À propos</a></li>
                    <li><a href="biography.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-user-circle text-gray-600 dark:text-dark-200"></i> Biographie</a></li>
                    <li><a href="contact.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-envelope text-gray-600 dark:text-dark-200"></i> Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Compte</h4>
                <ul class="space-y-3">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Mon profil</a></li>
                    <li><a href="logout.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Déconnexion</a></li>
                    <?php else: ?>
                    <li><a href="login.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Connexion</a></li>
                    <li><a href="register.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Inscription</a></li>
                    <?php endif; ?>
                    <li><a href="../admin/login.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-caret-right text-xs text-gray-600 dark:text-dark-200"></i> Administration</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Légal</h4>
                <ul class="space-y-3">
                    <li><a href="mentions-legales.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-file-text text-gray-600 dark:text-dark-200"></i> Mentions légales</a></li>
                    <li><a href="conditions-utilisation.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-scroll text-gray-600 dark:text-dark-200"></i> Conditions d'utilisation</a></li>
                    <li><a href="confidentialite.php" class="hover:text-white dark:hover:text-white transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-shield-check text-gray-600 dark:text-dark-200"></i> Confidentialité</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/5 py-6 text-center text-xs text-gray-600 dark:text-dark-200">
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.
        </div>
    </footer>

    <!-- Dark Mode Toggle Desktop (navbar) -->
    <script>
    (function(){
        const toggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        }
        if (toggle) {
            toggle.addEventListener('click', function() {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            });
        }
    })();
    </script>

    <?php scroll_reveal_script(); ?>
</body>
</html>

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <?php
    $__req_uri   = $_SERVER['REQUEST_URI'] ?? '/';
    $__clean_uri = preg_replace('#^/public/#', '/', $__req_uri);
    $__clean_uri = preg_replace('#^/index\.php(?=\?|$)#', '/', $__clean_uri);
    $__canonical = rtrim(SITE_URL, '/') . ($__clean_uri === '' ? '/' : $__clean_uri);
    $__og_image  = $page_image ?? (SITE_URL . '/img/bg/hero-1.jpg');
    $__self_page = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    $__noindex_pages = [
        'purchase.php', 'purchase_course.php', 'payment_callback.php',
        'retrouver-mon-achat.php', 'desinscription.php', 'login.php',
        'register.php', 'verify-email.php', 'resend-verification.php', '404.php',
    ];
    $__robots = in_array($__self_page, $__noindex_pages, true)
        ? 'noindex, nofollow'
        : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    $__og_type = ($__self_page === 'post.php' && !empty($post['id_post'])) ? 'article' : 'website';

    $__schema_graph = [
        [
            '@type'       => 'WebSite',
            '@id'         => SITE_URL . '/#website',
            'url'         => SITE_URL . '/',
            'name'        => SITE_NAME,
            'description' => SITE_TAGLINE,
            'inLanguage'  => 'fr-BJ',
            'publisher'   => ['@id' => SITE_URL . '/#person'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => SITE_URL . '/search.php?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type'       => 'Person',
            '@id'         => SITE_URL . '/#person',
            'name'        => 'Sylvestre Djouamon',
            'jobTitle'    => 'Professeur de littérature',
            'affiliation' => [
                '@type' => 'CollegeOrUniversity',
                'name'  => 'Université de Cotonou',
            ],
            'url'         => SITE_URL . '/biography.php',
        ],
    ];
    if ($__self_page === 'post.php' && !empty($post['id_post'])) {
        $__schema_graph[] = [
            '@type'            => 'ScholarlyArticle',
            'headline'         => (string)($post['title'] ?? ''),
            'description'      => (string)($page_description ?? SITE_TAGLINE),
            'image'            => [$__og_image],
            'datePublished'    => date('c', strtotime($post['published_at'] ?? $post['created_at'] ?? 'now')),
            'dateModified'     => date('c', strtotime($post['updated_at'] ?? $post['created_at'] ?? 'now')),
            'inLanguage'       => 'fr',
            'author'           => ['@id' => SITE_URL . '/#person'],
            'publisher'        => ['@id' => SITE_URL . '/#person'],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $__canonical],
        ];
    }
    ?>
    <meta name="robots" content="<?= htmlspecialchars($__robots) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($__canonical) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($__og_type) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($__canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($__og_image) ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title ?? SITE_NAME) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($__og_image) ?>">
    <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $__schema_graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php cdn_head(); animation_styles(); ?>
    <script>if(localStorage.getItem('theme')==='dark'||(!localStorage.getItem('theme')&&window.matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-50 dark:bg-dark font-sans text-gray-800 dark:text-dark-400 antialiased leading-relaxed transition-colors duration-300">
    <?php skip_link() ?>

    <!-- ═══ EN-TÊTE UNIFIÉ ═══ -->
    <header class="bg-white/85 dark:bg-dark/85 backdrop-blur-xl border-b border-gray-100 dark:border-dark-100 sticky top-0 z-50 transition-colors duration-300" id="siteHeader">
        <div class="sbr-topbar" aria-hidden="true"></div>
        <div class="sbr-header-bar max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 sm:h-[4.5rem] relative">
            <a href="<?= BASE_URL ?>" class="flex items-center gap-2.5 transition group">
                <?= logo_tag('h-10 sm:h-12', 'auto', 'group-hover:scale-105 transition-transform duration-300') ?>
            </a>
            <nav class="hidden md:flex items-center gap-2 sm:gap-2.5 pl-6" aria-label="Navigation principale">
                <?php
                $nav_items = [
                    ['',                 'ph-house',         'Accueil',      'index.php'],
                    ['publications.php', 'ph-book',          'Publications', 'publications.php'],
                    ['cours.php',        'ph-graduation-cap','Cours',        'cours.php'],
                    ['biography.php',    'ph-user-circle',   'Biographie',   'biography.php'],
                    ['contact.php',      'ph-envelope',      'Contact',      'contact.php'],
                ];
                $current = basename($_SERVER['PHP_SELF']);
                foreach ($nav_items as $nav_item):
                    $active = $nav_item[3] === $current
                        || ($nav_item[3] === 'cours.php' && in_array($current, ['course.php', 'chapter.php', 'course_file.php'], true));
                ?>
                <a href="<?= BASE_URL . $nav_item[0] ?>" class="sbr-pill gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium <?= $active ? 'is-active' : 'text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white' ?> transition-all duration-300">
                    <i class="ph <?= $nav_item[1] ?> sbr-icon"></i> <?= $nav_item[2] ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <form action="<?= BASE_URL ?>search.php" method="get" class="sbr-search hidden md:flex items-center bg-gray-100 dark:bg-dark-50 rounded-full px-2 h-9" role="search">
                    <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="sbr-search-input bg-transparent border-none pl-2 pr-1 py-1.5 text-sm focus:outline-none focus:ring-0 text-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-dark-200 focus:placeholder-accent-600 dark:focus:placeholder-primary-300">
                    <button type="submit" class="sbr-search-icon text-gray-400 dark:text-dark-300 text-lg" aria-label="Lancer la recherche"><i class="ph ph-magnifying-glass"></i></button>
                </form>
                <a href="<?= BASE_URL ?>contact.php" class="hidden md:inline-flex items-center gap-1.5 bg-accent-500 hover:bg-accent-600 text-white text-sm font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md active:scale-[0.97]">
                    <i class="ph ph-user-plus"></i> Me suivre
                </a>
                <button id="darkModeToggle" class="p-2 rounded-full text-gray-500 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 transition-all" aria-label="Basculer le thème">
                    <i class="ph ph-moon text-lg dark:hidden"></i>
                    <i class="ph ph-sun text-lg hidden dark:block"></i>
                </button>
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 dark:text-dark-400 p-2 hover:bg-gray-100 dark:hover:bg-dark-50 rounded-full transition-all" onclick="toggleMobileMenu(this)" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobileNav"><i class="ph ph-list text-xl"></i></button>
            </div>
        </div>
    </header>

    <!-- ═══ TIROIR DE NAVIGATION MOBILE (SORT DE LA GAUCHE, FOND OPAQUE) ═══ -->
    <div id="mobileNavOverlay" class="sbr-mobile-overlay md:hidden" onclick="closeMobileMenu()" aria-hidden="true"></div>
    <aside id="mobileNav" class="sbr-mobile-drawer md:hidden" role="dialog" aria-modal="true" aria-label="Navigation principale mobile">
        <div class="sbr-mobile-drawer-head">
            <a href="<?= BASE_URL ?>" class="flex items-center gap-2">
                <?= logo_tag('h-10', 'auto') ?>
            </a>
            <button type="button" class="sbr-mobile-close" onclick="closeMobileMenu()" aria-label="Fermer le menu">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <div class="sbr-mobile-drawer-search">
            <form action="<?= BASE_URL ?>search.php" method="get" class="sbr-mobile-search-form" role="search">
                <i class="ph ph-magnifying-glass text-gray-400 text-base"></i>
                <input type="text" name="q" placeholder="Rechercher un article, un cours..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="sbr-mobile-search-input">
            </form>
        </div>

        <nav class="sbr-mobile-drawer-nav" aria-label="Menu mobile">
            <p class="sbr-mobile-section-label">Navigation</p>
            <?php foreach ($nav_items as $nav_item):
                $active = $nav_item[3] === $current
                    || ($nav_item[3] === 'cours.php' && in_array($current, ['course.php', 'chapter.php', 'course_file.php'], true));
            ?>
            <a href="<?= BASE_URL . $nav_item[0] ?>" class="sbr-mobile-link <?= $active ? 'is-active' : '' ?>" <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="sbr-mobile-link-icon"><i class="ph <?= $nav_item[1] ?>"></i></span>
                <span><?= $nav_item[2] ?></span>
                <i class="ph ph-caret-right sbr-mobile-link-arrow"></i>
            </a>
            <?php endforeach; ?>

            <a href="<?= BASE_URL ?>about.php" class="sbr-mobile-link <?= $current === 'about.php' ? 'is-active' : '' ?>">
                <span class="sbr-mobile-link-icon"><i class="ph ph-info"></i></span>
                <span>À propos</span>
                <i class="ph ph-caret-right sbr-mobile-link-arrow"></i>
            </a>

            <a href="<?= BASE_URL ?>retrouver-mon-achat.php" class="sbr-mobile-link <?= $current === 'retrouver-mon-achat.php' ? 'is-active' : '' ?>">
                <span class="sbr-mobile-link-icon"><i class="ph ph-key"></i></span>
                <span>Retrouver mon achat</span>
                <i class="ph ph-caret-right sbr-mobile-link-arrow"></i>
            </a>

            <?php if (is_admin()): ?>
            <a href="<?= BASE_URL ?>admin/dashboard.php" class="sbr-mobile-link">
                <span class="sbr-mobile-link-icon"><i class="ph ph-gear"></i></span>
                <span>Administration</span>
                <i class="ph ph-caret-right sbr-mobile-link-arrow"></i>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sbr-mobile-drawer-foot">
            <a href="<?= BASE_URL ?>contact.php" class="sbr-mobile-cta">
                <i class="ph ph-user-plus"></i> Me suivre &amp; Nous contacter
            </a>
        </div>
    </aside>
    <script>
    (function(){
        var h = document.getElementById('siteHeader');
        if (!h) return;
        function onScroll(){ h.classList.toggle('scrolled', window.scrollY > 24); }
        window.addEventListener('scroll', onScroll, {passive:true});
        onScroll();
    })();
    </script>

    <?php if (hasFlash('success')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-2" role="alert">
            <i class="ph ph-check-circle"></i> <?= flash('success') ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (hasFlash('error')): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 px-4 py-3 rounded-xl flex items-center gap-2" role="alert">
            <i class="ph ph-warning-circle"></i> <?= flash('error') ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="min-h-screen">
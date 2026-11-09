<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <?php
    $__canonical = SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/');
    $__og_image  = $page_image ?? (SITE_URL . '/img/bg/hero-1.jpg');
    ?>
    <link rel="canonical" href="<?= htmlspecialchars($__canonical) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($__canonical) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($__og_image) ?>">
    <meta property="og:locale" content="fr_FR">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($page_title ?? SITE_NAME) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($page_description ?? SITE_TAGLINE) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($__og_image) ?>">
    <?php cdn_head(); animation_styles(); ?>
    <script>if(localStorage.getItem('theme')==='dark'||(!localStorage.getItem('theme')&&window.matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark');</script>
</head>
<body class="bg-gray-50 dark:bg-dark font-sans text-gray-800 dark:text-dark-400 antialiased leading-relaxed transition-colors duration-300 overflow-x-hidden">
    <?php skip_link() ?>

    <!-- ═══ EN-TÊTE UNIFIÉ ═══ -->
    <header class="bg-white/85 dark:bg-dark/85 backdrop-blur-xl border-b border-gray-100 dark:border-dark-100 sticky top-0 z-50 transition-colors duration-300" id="siteHeader">
        <div class="sbr-topbar" aria-hidden="true"></div>
        <div class="sbr-header-bar max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 sm:h-[4.5rem] relative">
            <a href="../public/index.php" class="flex items-center gap-2.5 transition group">
                <?= logo_tag('h-10 sm:h-12', 'auto', 'group-hover:scale-105 transition-transform duration-300') ?>
            </a>
            <nav class="hidden md:flex items-center gap-2 sm:gap-2.5 pl-6" aria-label="Navigation principale">
                <?php
                $nav_items = [
                    ['public/index.php',      'ph-house',         'Accueil'],
                    ['public/publications.php','ph-book',         'Publications'],
                    ['public/cours.php',      'ph-graduation-cap','Cours'],
                    ['public/biography.php',  'ph-user-circle',   'Biographie'],
                    ['public/contact.php',    'ph-envelope',      'Contact'],
                ];
                $current = basename($_SERVER['PHP_SELF']);
                foreach ($nav_items as $nav_item):
                    $active = $nav_item[0] === 'public/' . $current
                        || ($nav_item[0] === 'public/cours.php' && in_array($current, ['course.php', 'chapter.php', 'course_file.php'], true));
                ?>
                <a href="../<?= $nav_item[0] ?>" class="sbr-pill gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium <?= $active ? 'is-active' : 'text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 hover:text-gray-900 dark:hover:text-white' ?> transition-all duration-300">
                    <i class="ph <?= $nav_item[1] ?> sbr-icon"></i> <?= $nav_item[2] ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="flex items-center gap-1.5 sm:gap-2">
                <form action="../public/search.php" method="get" class="sbr-search hidden md:flex items-center bg-gray-100 dark:bg-dark-50 rounded-full px-2 h-9" role="search">
                    <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="sbr-search-input bg-transparent border-none pl-2 pr-1 py-1.5 text-sm focus:outline-none focus:ring-0 text-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-dark-200 focus:placeholder-accent-600 dark:focus:placeholder-primary-300">
                    <button type="submit" class="sbr-search-icon text-gray-400 dark:text-dark-300 text-lg" aria-label="Lancer la recherche"><i class="ph ph-magnifying-glass"></i></button>
                </form>
                <a href="../public/contact.php" class="hidden md:inline-flex items-center gap-1.5 bg-accent-500 hover:bg-accent-600 text-white text-sm font-semibold px-4 py-2 rounded-full transition-all duration-300 shadow-sm hover:shadow-md active:scale-[0.97]">
                    <i class="ph ph-user-plus"></i> Me suivre
                </a>
                <button id="darkModeToggle" class="p-2 rounded-full text-gray-500 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50 transition-all" aria-label="Basculer le thème">
                    <i class="ph ph-moon text-lg dark:hidden"></i>
                    <i class="ph ph-sun text-lg hidden dark:block"></i>
                </button>
                <button id="mobileMenuBtn" class="md:hidden text-gray-600 dark:text-dark-400 p-2 hover:bg-gray-100 dark:hover:bg-dark-50 rounded-full transition-all" onclick="toggleMobileMenu(this)" aria-label="Menu" aria-expanded="false"><i class="ph ph-list text-xl"></i></button>
            </div>
        </div>
        <div class="hidden md:hidden sbr-mobile-panel border-t border-gray-100 dark:border-dark-100 px-4 py-3 space-y-1" id="mobileNav" role="navigation">
            <?php foreach ($nav_items as $nav_item):
                $active = $nav_item[0] === 'public/' . $current
                    || ($nav_item[0] === 'public/cours.php' && in_array($current, ['course.php', 'chapter.php', 'course_file.php'], true));
            ?>
            <a href="../<?= $nav_item[0] ?>" class="block px-3 py-2.5 rounded-xl text-sm font-medium <?= $active ? 'bg-primary-100 text-primary-700' : 'text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50' ?>">
                <i class="ph <?= $nav_item[1] ?>"></i> <?= $nav_item[2] ?>
            </a>
            <?php endforeach; ?>
            <?php if (is_admin()): ?>
            <a href="../admin/dashboard.php" class="block px-3 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-dark-400 hover:bg-gray-100 dark:hover:bg-dark-50"><i class="ph ph-gear"></i> Admin</a>
            <?php endif; ?>
            <form action="../public/search.php" method="get" class="flex bg-gray-100 dark:bg-dark-50 rounded-xl px-3 mt-2">
                <button type="submit" class="text-gray-400" aria-label="Rechercher"><i class="ph ph-magnifying-glass"></i></button>
                <input type="text" name="q" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="bg-transparent border-none px-2 py-2 text-sm focus:outline-none focus:ring-0 w-full text-gray-700 dark:text-white">
            </form>
            <div class="flex items-center justify-between pt-3 pb-1">
                <a href="../public/contact.php" class="inline-flex items-center gap-1.5 bg-accent-500 hover:bg-accent-600 text-white text-sm font-semibold px-4 py-2 rounded-full transition-all"><i class="ph ph-user-plus"></i> Me suivre</a>
            </div>
        </div>
    </header>
    <script>
    (function(){
        var h = document.getElementById('siteHeader');
        if (!h) return;
        function onScroll(){ h.classList.toggle('scrolled', window.scrollY > 24); }
        window.addEventListener('scroll', onScroll, {passive:true});
        onScroll();
    })();

    /* Recherche : l'icône ouvre le champ, qui reste vide tant qu'on ne l'utilise pas */
    (function(){
        document.querySelectorAll('.sbr-search').forEach(function(form){
            var input = form.querySelector('.sbr-search-input');
            var bouton = form.querySelector('.sbr-search-icon');
            if (!input || !bouton) return;

            if (input.value.trim() !== '') form.classList.add('is-open');

            bouton.addEventListener('click', function(e){
                if (!form.classList.contains('is-open')) {
                    e.preventDefault();
                    form.classList.add('is-open');
                    input.focus();
                }
            });

            input.addEventListener('blur', function(){
                window.setTimeout(function(){
                    if (input.value.trim() === '') form.classList.remove('is-open');
                }, 120);
            });

            input.addEventListener('keydown', function(e){
                if (e.key === 'Escape') {
                    input.value = '';
                    input.blur();
                    form.classList.remove('is-open');
                }
            });
        });
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

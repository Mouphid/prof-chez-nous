<?php
// config/theme.php — Configuration centralisée (images, réseaux, animations, dégradés)

// ─── Valeurs modifiables depuis Admin → Réglages du site ───
// Elles sont stockées dans la table `settings`. Si la table n'existe pas
// encore (toute première installation), on garde les valeurs par défaut.
$__reglages = [];
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $__cles = [
            'site_email', 'site_phone', 'site_address',
            'social_facebook', 'social_twitter', 'social_linkedin',
            'social_youtube', 'social_instagram',
        ];
        $__marques = implode(',', array_fill(0, count($__cles), '?'));
        $__stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($__marques)");
        $__stmt->execute($__cles);
        foreach ($__stmt->fetchAll(PDO::FETCH_ASSOC) as $__ligne) {
            $__reglages[$__ligne['setting_key']] = (string)$__ligne['setting_value'];
        }
    } catch (Throwable $e) {
        // Table absente ou base non initialisée : valeurs par défaut ci-dessous.
    }
}

// ─── Site ───
define('SITE_NAME', 'Joie Enseignante');
define('SITE_URL', 'https://joie-enseignante.com');
define('SITE_EMAIL', $__reglages['site_email'] ?? 'contact@joie-enseignante.com');
define('SITE_PHONE', $__reglages['site_phone'] ?? '+229 XX XX XX XX');
define('SITE_ADDRESS', $__reglages['site_address'] ?? 'Cotonou, Bénin');
define('SITE_TAGLINE', 'Plateforme pédagogique collaborative pour enseignants et étudiants');

// ─── Uploads ───
define('UPLOAD_IMAGES', '../uploads/images/');
define('UPLOAD_VIDEO', '../uploads/video/');
define('UPLOAD_FILES', '../uploads/files/');

// ─── Images ───
define('DEFAULT_AVATAR', '../uploads/images/default-avatar.png');
define('DEFAULT_OG_IMAGE', '../uploads/images/og-default.jpg');
define('HERO_SLIDE_INTERVAL', 6000);

// ─── Hero slider ───
$hero_slides = [
    BASE_URL . 'img/bg/hero-1.jpg',
    BASE_URL . 'img/bg/hero-2.jpg',
    BASE_URL . 'img/bg/hero-3.jpg',
];



// ─── Réseaux sociaux ───
define('SOCIAL_FACEBOOK', $__reglages['social_facebook'] ?? 'https://facebook.com/joiedenseignante');
define('SOCIAL_TWITTER', $__reglages['social_twitter'] ?? 'https://twitter.com/joiedenseignante');
define('SOCIAL_LINKEDIN', $__reglages['social_linkedin'] ?? 'https://linkedin.com/company/joiedenseignante');
define('SOCIAL_YOUTUBE', $__reglages['social_youtube'] ?? 'https://youtube.com/@joiedenseignante');
define('SOCIAL_INSTAGRAM', $__reglages['social_instagram'] ?? 'https://instagram.com/joiedenseignante');

// ─── Dégradés ───
define('GRADIENT_HERO', 'bg-hero-pattern');
define('GRADIENT_CTA', 'bg-cta-pattern');
define('GRADIENT_AUTH', 'from-primary-500 to-primary-700');
define('GRADIENT_PROFILE', 'from-primary-500 to-primary-700');
define('GRADIENT_EMPTY', 'from-primary-50 to-primary-100');
define('GRADIENT_TESTIMONIAL', 'bg-gradient-to-b from-gray-50 to-white dark:from-dark-50 dark:to-dark');

// ─── Classes d'animations ───
define('ANIM_FADE_IN', 'animate-fade-in');
define('ANIM_FADE_IN_UP', 'animate-fade-in-up');
define('ANIM_SLIDE_UP', 'animate-slide-up');
define('ANIM_HEARTBEAT', 'animate-heartbeat');
define('ANIM_STAGGER', 'animate-stagger');
define('ANIM_PULSE', 'animate-pulse-slow');

// ─── Scroll reveal ───
define('REVEAL', 'data-reveal');
define('REVEAL_LEFT', 'data-reveal="left"');
define('REVEAL_RIGHT', 'data-reveal="right"');
define('REVEAL_UP', 'data-reveal="up"');
define('REVEAL_SCALE', 'data-reveal="scale"');

// ─── Coins info ───
define('BTN_PRIMARY', 'bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-all duration-300 shadow-sm hover:shadow-md');
define('BTN_CTA', 'bg-accent-500 text-white font-bold hover:bg-accent-600 transition-all duration-300 shadow-lg hover:shadow-xl active:scale-[0.97] transform hover:-translate-y-0.5');

// ─── Styles de statut ───
function badge_status($status) {
    $map = [
        'published' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'draft'     => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'hidden'    => 'bg-gray-100 text-gray-600 dark:bg-dark-100 dark:text-dark-400',
        'visible'   => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'pending'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-600 dark:bg-dark-100 dark:text-dark-400';
}

function badge_role($role) {
    $map = [
        'admin'    => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'auteur'   => 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400',
        'etudiant' => 'bg-gray-100 text-gray-600 dark:bg-dark-100 dark:text-dark-400',
    ];
    return $map[$role] ?? 'bg-gray-100 text-gray-600 dark:bg-dark-100 dark:text-dark-400';
}

// ─── États vides ───
function empty_state($icon, $title, $subtitle = '') {
    echo '<div class="text-center py-24 bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm">';
    echo '<div class="w-20 h-20 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">';
    echo '<i class="ph ph-' . htmlspecialchars($icon) . ' text-3xl text-primary-400 dark:text-primary-300"></i></div>';
    echo '<p class="text-lg font-semibold text-gray-700 dark:text-dark-500">' . htmlspecialchars($title) . '</p>';
    if ($subtitle) echo '<p class="text-sm text-gray-400 dark:text-dark-300 mt-1">' . htmlspecialchars($subtitle) . '</p>';
    echo '</div>';
}

// ─── Partage réseaux ───
function share_links($url, $title = '') {
    $encoded_title = urlencode($title);
    $page_url = urlencode('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI']);
    ?>
    <div class="share-links flex items-center gap-2 text-sm text-gray-600 dark:text-dark-300 flex-wrap">
        <span class="font-medium">Partager :</span>
        <a href="https://wa.me/?text=<?= $encoded_title ?>%20<?= $page_url ?>" target="_blank" rel="noopener" class="share-btn" title="Partager sur WhatsApp" aria-label="Partager sur WhatsApp"><i class="ph ph-whatsapp-logo"></i></a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $page_url ?>" target="_blank" rel="noopener" class="share-btn" title="Partager sur Facebook" aria-label="Partager sur Facebook"><i class="ph ph-facebook-logo"></i></a>
        <a href="https://twitter.com/intent/tweet?url=<?= $page_url ?>&text=<?= $encoded_title ?>" target="_blank" rel="noopener" class="share-btn" title="Partager sur X" aria-label="Partager sur X"><i class="ph ph-x-logo"></i></a>
        <a href="mailto:?subject=<?= $encoded_title ?>&body=<?= $page_url ?>" class="share-btn" title="Partager par e-mail" aria-label="Partager par e-mail"><i class="ph ph-envelope"></i></a>
    </div>
    <?php
}

// ─── Icônes sociales (footer) ───
/**
 * Liste des réseaux sociaux réellement renseignés.
 * Un réseau laissé vide dans Admin → Réglages du site n'est pas renvoyé.
 *
 * @return array<int, array{icon: string, url: string, label: string}>
 */
function social_links(): array {
    $networks = [
        'facebook-logo'  => [SOCIAL_FACEBOOK,  'Facebook'],
        'x-logo'         => [SOCIAL_TWITTER,   'X (Twitter)'],
        'youtube-logo'   => [SOCIAL_YOUTUBE,   'YouTube'],
        'linkedin-logo'  => [SOCIAL_LINKEDIN,  'LinkedIn'],
        'instagram-logo' => [SOCIAL_INSTAGRAM, 'Instagram'],
    ];
    $liens = [];
    foreach ($networks as $icon => [$url, $label]) {
        if (trim((string)$url) === '') {
            continue;
        }
        $liens[] = ['icon' => $icon, 'url' => $url, 'label' => $label];
    }
    return $liens;
}

function social_icons() {
    foreach (social_links() as $lien):
    ?>
    <a href="<?= htmlspecialchars($lien['url']) ?>" target="_blank" rel="noopener" class="w-10 h-10 bg-white/10 hover:bg-primary-500 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" title="<?= htmlspecialchars($lien['label']) ?>">
        <i class="ph ph-<?= $lien['icon'] ?>"></i>
    </a>
    <?php endforeach;
}

// ─── CSS Animations (à insérer dans <head>) ───
function animation_styles() {
    ?>
    <style>
        /* Scroll Reveal */
        [data-reveal] { opacity: 0; transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1); }
        [data-reveal="up"] { transform: translateY(40px); }
        [data-reveal="left"] { transform: translateX(-40px); }
        [data-reveal="right"] { transform: translateX(40px); }
        [data-reveal="scale"] { transform: scale(0.9); }
        [data-reveal].revealed { opacity: 1; transform: translate(0) scale(1); }

        /* Stagger delay */
        .stagger-1 { transition-delay: 0.1s; }
        .stagger-2 { transition-delay: 0.2s; }
        .stagger-3 { transition-delay: 0.3s; }
        .stagger-4 { transition-delay: 0.4s; }
        .stagger-5 { transition-delay: 0.5s; }
        .stagger-6 { transition-delay: 0.6s; }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes heartbeat { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.3); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
        .animate-fadeInUp { animation: fadeInUp 0.7s ease-out forwards; }
        .animate-slideUp { animation: slideUp 0.5s ease-out forwards; }
        .animate-heartbeat { animation: heartbeat 0.4s ease-in-out; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }

        /* Hero Ken Burns */
        @keyframes kenBurns {
            0% { transform: scale(1) translate(0, 0); }
            50% { transform: scale(1.1) translate(-1%, -1%); }
            100% { transform: scale(1) translate(0, 0); }
        }
        .hero-slide-active { animation: kenBurns 20s ease-in-out infinite; }

        /* Glassmorphism */
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); }

        /* Card hover lift */
        .card-hover { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12); }

        /* Gradient text */
        .gradient-text { background: linear-gradient(135deg, #32608c, #244360); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
            [data-reveal] { opacity: 1; transform: none; }
        }
    </style>
    <?php
}

// ─── Skip link (accessibilité) ───
function skip_link() {
    ?>
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[9999] focus:bg-primary-500 focus:text-white focus:px-4 focus:py-2 focus:rounded-xl focus:shadow-lg focus:outline-none">Aller au contenu principal</a>
    <?php
}

// ─── Scroll Reveal JS ───
function scroll_reveal_script() {
    ?>
    <script>
    (function(){
        const reveals = document.querySelectorAll('[data-reveal]');
        if (!reveals.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        reveals.forEach(el => observer.observe(el));
    })();
    </script>
    <?php
}

// ─── Dark Mode Toggle JS ───
function dark_mode_script() {
    ?>
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
    <?php
}

// ─── Counter Animation JS ───
function counter_animation_script() {
    ?>
    <script>
    (function(){
        const counters = document.querySelectorAll('[data-count]');
        if (!counters.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = el.dataset.count;
                    const numericTarget = parseInt(target.replace(/[^0-9]/g, ''));
                    const suffix = target.replace(/[0-9]/g, '');
                    const duration = 2000;
                    const startTime = performance.now();
                    function update(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const current = Math.floor(eased * numericTarget);
                        el.textContent = current.toLocaleString('fr-FR') + suffix;
                        if (progress < 1) requestAnimationFrame(update);
                    }
                    requestAnimationFrame(update);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(el => observer.observe(el));
    })();
    </script>
    <?php
}

// ─── Logo adapté au thème ───
/**
 * Balise <img> du logo Joie Enseignante.
 *
 * @param string $classes classes de hauteur Tailwind (ex. « h-10 sm:h-12 »)
 * @param string $variant « auto » = gris en clair / blanc en sombre,
 *                        « light » = noir, « dark » = blanc
 * @param string $extra   classes supplémentaires (ex. « mx-auto mb-2 »)
 */
function logo_tag(string $classes = 'h-10', string $variant = 'auto', string $extra = ''): string
{
    $base = BASE_URL . 'img/';
    $alt  = htmlspecialchars(SITE_NAME, ENT_QUOTES);
    $cls  = trim($classes . ' w-auto ' . $extra);

    if ($variant === 'dark') {
        return '<img src="' . $base . 'logo-blanc.png" alt="' . $alt . '" class="' . $cls . '">';
    }
    if ($variant === 'light') {
        return '<img src="' . $base . 'logo-noir.png" alt="' . $alt . '" class="' . $cls . '">';
    }

    // « auto » : gris sur fond clair, blanc sur fond sombre.
    return '<img src="' . $base . 'logo-gris.png" alt="' . $alt . '" class="' . $cls . ' dark:hidden">'
        . '<img src="' . $base . 'logo-blanc.png" alt="" aria-hidden="true" class="' . $cls . ' hidden dark:block">';
}

// ─── Head (Tailwind local + Phosphor + Inter local) ───
function cdn_head() {
    $a = BASE_URL;
    ?>
    <link rel="icon" href="<?= $a ?>img/favicon-32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= $a ?>img/favicon-192.png" sizes="192x192" type="image/png">
    <link rel="shortcut icon" href="<?= $a ?>img/favicon.ico">
    <link rel="apple-touch-icon" href="<?= $a ?>img/apple-touch-icon.png">
    <link href="<?= $a ?>assets/css/tailwind.css" rel="stylesheet">
    <style>:root{--font-sans:'Inter',ui-sans-serif,system-ui,sans-serif;--font-display:'Inter',ui-sans-serif,system-ui,sans-serif}</style>
    <link href="<?= $a ?>assets/css/sobre.css?v=20261110a" rel="stylesheet">
    <link href="<?= $a ?>assets/phosphor/phosphor.css" rel="stylesheet">
    <link href="<?= $a ?>assets/fonts/inter/index.css" rel="stylesheet">
    <script>
    function toggleMobileMenu(b){
        var m=document.getElementById('mobileNav');
        if(!m)return;
        m.classList.toggle('hidden');
        if(b)b.setAttribute('aria-expanded',!m.classList.contains('hidden'));
    }
    document.addEventListener('click',function(e){
        var m=document.getElementById('mobileNav'),b=document.getElementById('mobileMenuBtn');
        if(!m||m.classList.contains('hidden'))return;
        if(!m.contains(e.target)&&(!b||!b.contains(e.target))){
            m.classList.add('hidden');
            if(b)b.setAttribute('aria-expanded','false');
        }
    });
    document.addEventListener('keydown',function(e){
        if(e.key==='Escape'){
            var m=document.getElementById('mobileNav'),b=document.getElementById('mobileMenuBtn');
            if(m&&!m.classList.contains('hidden')){
                m.classList.add('hidden');
                if(b)b.setAttribute('aria-expanded','false');
            }
        }
    });
    </script>
    <?php
}

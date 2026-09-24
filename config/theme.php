<?php
// config/theme.php — Configuration centralisée (images, réseaux, animations, dégradés)

// ─── Site ───
define('SITE_NAME', 'Joie Enseignante');
define('SITE_EMAIL', 'contact@joieenseignante.com');
define('SITE_PHONE', '+229 XX XX XX XX');
define('SITE_ADDRESS', 'Cotonou, Bénin');
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
define('SOCIAL_FACEBOOK', 'https://facebook.com/joieenseignante');
define('SOCIAL_TWITTER', 'https://twitter.com/joieenseignante');
define('SOCIAL_LINKEDIN', 'https://linkedin.com/company/joieenseignante');
define('SOCIAL_YOUTUBE', 'https://youtube.com/@joieenseignante');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/joieenseignante');

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
    $encoded_url = urlencode($url);
    $encoded_title = urlencode($title);
    $page_url = urlencode('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI']);
    ?>
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-dark-300 flex-wrap">
        <span class="font-medium">Partager :</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $page_url ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-300" title="Facebook"><i class="ph ph-facebook-logo"></i></a>
        <a href="https://twitter.com/intent/tweet?url=<?= $page_url ?>&text=<?= $encoded_title ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all duration-300" title="X (Twitter)"><i class="ph ph-x-logo"></i></a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= $page_url ?>" target="_blank" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-300" title="LinkedIn"><i class="ph ph-linkedin-logo"></i></a>
        <a href="mailto:?subject=<?= $encoded_title ?>&body=<?= $page_url ?>" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-dark-100 text-gray-600 dark:text-dark-400 hover:bg-gray-200 dark:hover:bg-dark-200 transition-all duration-300" title="Email"><i class="ph ph-envelope"></i></a>
    </div>
    <?php
}

// ─── Icônes sociales (footer) ───
function social_icons() {
    $networks = [
        'facebook-logo' => SOCIAL_FACEBOOK,
        'x-logo'        => SOCIAL_TWITTER,
        'youtube-logo'  => SOCIAL_YOUTUBE,
        'linkedin-logo' => SOCIAL_LINKEDIN,
    ];
    foreach ($networks as $icon => $url):
    ?>
    <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="w-10 h-10 bg-white/10 hover:bg-primary-500 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-110" title="<?= ucfirst(str_replace('-logo', '', $icon)) ?>">
        <i class="ph ph-<?= $icon ?>"></i>
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
        .gradient-text { background: linear-gradient(135deg, #5c7b97, #3d5268); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

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

// ─── Head (Tailwind local + Phosphor + Inter local) ───
function cdn_head() {
    $a = BASE_URL;
    ?>
    <link href="<?= $a ?>assets/css/tailwind.css" rel="stylesheet">
    <style>:root{--font-sans:'Inter',ui-sans-serif,system-ui,sans-serif;--font-display:'Inter',ui-sans-serif,system-ui,sans-serif}</style>
    <link href="<?= $a ?>assets/css/sobre.css" rel="stylesheet">
    <link href="<?= $a ?>assets/phosphor/phosphor.css" rel="stylesheet">
    <link href="<?= $a ?>assets/fonts/inter/index.css" rel="stylesheet">
    <script>function toggleMobileMenu(b){var m=document.getElementById('mobileNav');if(m){m.classList.toggle('hidden');b.setAttribute('aria-expanded',!m.classList.contains('hidden'));}}</script>
    <?php
}

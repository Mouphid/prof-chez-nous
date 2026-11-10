</main>

    <?php $__pub_base = str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/public/') ? BASE_URL . 'public/' : BASE_URL; ?>
    <!-- ═══ PIED DE PAGE DYNAMIQUE ═══ -->
    <footer class="relative overflow-hidden bg-white dark:bg-dark-50 border-t border-gray-100 dark:border-dark-100 mt-16 transition-colors duration-300" id="siteFooter">
        <div class="sbr-footergrad"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="lg:col-span-1">
                <a href="<?= $__pub_base === BASE_URL ? BASE_URL : $__pub_base . 'index.php' ?>" class="flex items-center gap-2.5 mb-4 group">
                    <?= logo_tag('h-10', 'auto', 'group-hover:scale-105 transition-transform') ?>
                </a>
                <p class="leading-relaxed text-gray-500 dark:text-dark-300 text-sm mb-6"><?= SITE_TAGLINE ?></p>
                <div class="flex items-center gap-2 sbr-socials">
                    <?php social_icons() ?>
                </div>
                <p class="text-xs text-gray-400 dark:text-dark-300 mt-6"><i class="ph ph-map-pin"></i> <?= SITE_ADDRESS ?></p>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Explorer</h4>
                <ul class="space-y-3">
                    <li><a href="<?= $__pub_base === BASE_URL ? BASE_URL : $__pub_base . 'index.php' ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-house text-gray-400 dark:text-dark-200"></i> Accueil</a></li>
                    <li><a href="<?= $__pub_base ?>publications.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-book text-gray-400 dark:text-dark-200"></i> Publications</a></li>
                    <li><a href="<?= $__pub_base ?>cours.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-graduation-cap text-gray-400 dark:text-dark-200"></i> Cours</a></li>
                    <li><a href="<?= $__pub_base ?>retrouver-mon-achat.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-key text-gray-400 dark:text-dark-200"></i> Retrouver mon achat</a></li>
                    <li><a href="<?= $__pub_base ?>biography.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-user-circle text-gray-400 dark:text-dark-200"></i> Biographie</a></li>
                    <li><a href="<?= $__pub_base ?>contact.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-envelope text-gray-400 dark:text-dark-200"></i> Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Nos rubriques</h4>
                <ul class="space-y-3">
                    <?php
                    if (isset($pdo)) {
                        $footer_cats = $pdo->query("
                            SELECT c.id_category, c.name, COUNT(p.id_post) AS n
                            FROM categories c
                            LEFT JOIN posts p ON p.id_category = c.id_category
                                AND (p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= NOW()))
                            GROUP BY c.id_category, c.name
                            HAVING n > 0
                            ORDER BY c.name
                        ")->fetchAll();
                        foreach ($footer_cats as $fc):
                    ?>
                    <li>
                        <a href="<?= $__pub_base ?>category.php?id=<?= (int)$fc['id_category'] ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm">
                            <i class="ph ph-folder text-gray-400 dark:text-dark-200"></i>
                            <span><?= htmlspecialchars($fc['name']) ?></span>
                            <span class="text-xs text-gray-400 dark:text-dark-300">(<?= (int)$fc['n'] ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; } ?>
                </ul>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Légal</h4>
                <ul class="space-y-3">
                    <li><a href="<?= $__pub_base ?>mentions-legales.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-file-text text-gray-400 dark:text-dark-200"></i> Mentions légales</a></li>
                    <li><a href="<?= $__pub_base ?>conditions-utilisation.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-scroll text-gray-400 dark:text-dark-200"></i> Conditions d'utilisation</a></li>
                    <li><a href="<?= $__pub_base ?>conditions-vente.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-shopping-cart text-gray-400 dark:text-dark-200"></i> Conditions de vente</a></li>
                    <li><a href="<?= $__pub_base ?>confidentialite.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-shield-check text-gray-400 dark:text-dark-200"></i> Confidentialité</a></li>
                    <li><a href="<?= $__pub_base ?>about.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-info text-gray-400 dark:text-dark-200"></i> À propos</a></li>
                </ul>
            </div>
        </div>
        <div class="relative z-10 border-t border-gray-100 dark:border-dark-100 py-6 text-center text-xs text-gray-400 dark:text-dark-300">
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.
        </div>
    </footer>

    <!-- Bouton retour en haut -->
    <button id="backToTop" class="sbr-topbtn" aria-label="Retour en haut" title="Retour en haut"><i class="ph ph-arrow-up"></i></button>

    <!-- Pop-up de consentement (cookies & utilisation des données) -->
    <div id="cookieConsentPopup" class="sbr-cookie-popup" role="dialog" aria-live="polite" aria-label="Consentement à l'utilisation des cookies et des données" aria-hidden="true">
        <div class="sbr-cookie-card">
            <div class="sbr-cookie-head">
                <span class="sbr-cookie-icon" aria-hidden="true"><i class="ph ph-shield-check"></i></span>
                <div class="sbr-cookie-body">
                    <p class="sbr-cookie-title">Cookies &amp; confidentialité des données</p>
                    <p class="sbr-cookie-text">
                        Nous utilisons des cookies et traitons vos données pour assurer le bon fonctionnement du site, améliorer votre expérience et vous envoyer nos actualités pédagogiques si vous vous abonnez à la lettre d'information. Consultez notre
                        <a href="<?= $__pub_base ?>confidentialite.php" class="sbr-cookie-link">politique de confidentialité</a>.
                    </p>
                </div>
            </div>
            <div class="sbr-cookie-actions">
                <button type="button" id="cookieDeclineBtn" class="sbr-cookie-btn-secondary">Refuser</button>
                <button type="button" id="cookieAcceptBtn" class="sbr-cookie-btn-primary">
                    <i class="ph ph-check-circle"></i> J'accepte et je continue
                </button>
            </div>
        </div>
    </div>

    <script>
    (function(){
        const toggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        if (toggle) {
            toggle.addEventListener('click', function() {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            });
        }

        /* Pop-up de consentement aux cookies et données (apparaît après ~1,2 s) */
        const cookiePopup = document.getElementById('cookieConsentPopup');
        const acceptBtn   = document.getElementById('cookieAcceptBtn');
        const declineBtn  = document.getElementById('cookieDeclineBtn');
        const CLE_CONSENT = 'je_cookie_consent_v1';

        if (cookiePopup && !localStorage.getItem(CLE_CONSENT)) {
            window.setTimeout(function() {
                cookiePopup.classList.add('is-visible');
                cookiePopup.setAttribute('aria-hidden', 'false');
            }, 1200);

            function fermerConsentement(choix) {
                try {
                    localStorage.setItem(CLE_CONSENT, JSON.stringify({
                        status: choix,
                        date: new Date().toISOString()
                    }));
                } catch (e) {}
                cookiePopup.classList.remove('is-visible');
                cookiePopup.setAttribute('aria-hidden', 'true');
            }

            if (acceptBtn)  acceptBtn.addEventListener('click',  function() { fermerConsentement('accepted'); });
            if (declineBtn) declineBtn.addEventListener('click', function() { fermerConsentement('declined'); });
        }
        const topBtn = document.getElementById('backToTop');
        if (topBtn) {
            const hero = document.getElementById('hero-slider');
            const paginations = document.querySelectorAll('nav[aria-label="Pagination"]');

            function seuilHero() {
                if (!hero) return 500;
                const bas = hero.getBoundingClientRect().bottom + window.scrollY;
                return Math.max(400, bas - 120);
            }

            function majBouton() {
                let visible = window.scrollY > seuilHero();
                // Ne jamais recouvrir la pagination (bouton « Suivant »)
                paginations.forEach(function (nav) {
                    const r = nav.getBoundingClientRect();
                    if (r.bottom > 8 && r.top < window.innerHeight - 8) visible = false;
                });
                topBtn.classList.toggle('show', visible);
                topBtn.setAttribute('aria-hidden', visible ? 'false' : 'true');
            }

            window.addEventListener('scroll', majBouton, {passive:true});
            window.addEventListener('resize', majBouton, {passive:true});
            majBouton();

            topBtn.addEventListener('click', function() {
                window.scrollTo({top:0, behavior:'smooth'});
            });
        }
    })();
    </script>
    <?php scroll_reveal_script(); ?>
</body>
</html>
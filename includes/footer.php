</main>

    <!-- ═══ PIED DE PAGE DYNAMIQUE ═══ -->
    <footer class="relative overflow-hidden bg-white dark:bg-dark-50 border-t border-gray-100 dark:border-dark-100 mt-16 transition-colors duration-300" id="siteFooter">
        <div class="sbr-footergrad"></div>
        <div class="sbr-watermark" aria-hidden="true">JOIE&nbsp;ENSEIGNANTE</div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div class="lg:col-span-1">
                <a href="../public/index.php" class="flex items-center gap-2.5 mb-4 group">
                    <img src="../img/logo.jpg" alt="<?= SITE_NAME ?>" class="h-10 w-auto rounded-lg group-hover:scale-105 transition-transform">
                </a>
                <p class="leading-relaxed text-gray-500 dark:text-dark-300 text-sm mb-6"><?= SITE_TAGLINE ?></p>
                <div class="flex items-center gap-2">
                    <?php social_icons() ?>
                </div>
                <p class="text-xs text-gray-400 dark:text-dark-300 mt-6"><i class="ph ph-map-pin"></i> <?= SITE_ADDRESS ?></p>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Explorer</h4>
                <ul class="space-y-3">
                    <li><a href="../public/index.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-house text-gray-400 dark:text-dark-200"></i> Accueil</a></li>
                    <li><a href="../public/publications.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-book text-gray-400 dark:text-dark-200"></i> Publications</a></li>
                    <li><a href="../public/cours.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-graduation-cap text-gray-400 dark:text-dark-200"></i> Cours</a></li>
                    <li><a href="../public/biography.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-user-circle text-gray-400 dark:text-dark-200"></i> Biographie</a></li>
                    <li><a href="../public/contact.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-envelope text-gray-400 dark:text-dark-200"></i> Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Derniers articles</h4>
                <ul class="space-y-3">
                    <?php
                    if (isset($pdo)) {
                        $footer_posts = $pdo->query("SELECT id_post, title, main_image FROM posts WHERE (status = 'published' OR (status = 'scheduled' AND published_at <= NOW())) ORDER BY created_at DESC LIMIT 4");
                        while ($fp = $footer_posts->fetch()):
                    ?>
                    <li>
                        <a href="../public/post.php?id=<?= $fp['id_post'] ?>" class="flex items-center gap-3 text-sm text-gray-600 dark:text-dark-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors group">
                            <span class="sbr-thumb">
                                <?php if (!empty($fp['main_image'])): ?><img src="../uploads/images/<?= htmlspecialchars($fp['main_image']) ?>" alt="" loading="lazy"><?php else: ?><i class="ph ph-note-pencil"></i><?php endif; ?>
                            </span>
                            <span class="font-medium leading-snug"><?= htmlspecialchars(truncate($fp['title'], 38)) ?></span>
                        </a>
                    </li>
                    <?php endwhile; } ?>
                </ul>
            </div>
            <div>
                <h4 class="text-gray-900 dark:text-white font-bold mb-5 text-sm uppercase tracking-wider">Légal</h4>
                <ul class="space-y-3">
                    <li><a href="../public/mentions-legales.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-file-text text-gray-400 dark:text-dark-200"></i> Mentions légales</a></li>
                    <li><a href="../public/conditions-utilisation.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-scroll text-gray-400 dark:text-dark-200"></i> Conditions d'utilisation</a></li>
                    <li><a href="../public/confidentialite.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-shield-check text-gray-400 dark:text-dark-200"></i> Confidentialité</a></li>
                    <li><a href="../public/about.php" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors inline-flex items-center gap-2 text-sm"><i class="ph ph-info text-gray-400 dark:text-dark-200"></i> À propos</a></li>
                </ul>
            </div>
        </div>
        <div class="relative z-10 border-t border-gray-100 dark:border-dark-100 py-6 text-center text-xs text-gray-400 dark:text-dark-300">
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.
        </div>
    </footer>

    <!-- Bouton retour en haut -->
    <button id="backToTop" class="sbr-topbtn" aria-label="Retour en haut" title="Retour en haut"><i class="ph ph-arrow-up"></i></button>

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
        const topBtn = document.getElementById('backToTop');
        if (topBtn) {
            window.addEventListener('scroll', function() {
                topBtn.classList.toggle('show', window.scrollY > 500);
            }, {passive:true});
            topBtn.addEventListener('click', function() {
                window.scrollTo({top:0, behavior:'smooth'});
            });
        }
    })();
    </script>
<script>function toggleMobileMenu(b){var m=document.getElementById('mobileNav');if(m){m.classList.toggle('hidden');b.setAttribute('aria-expanded',!m.classList.contains('hidden'));}}</script>
    <?php scroll_reveal_script(); ?>
</body>
</html>
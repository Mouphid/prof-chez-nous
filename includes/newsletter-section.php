<?php if (!defined('NEWSLETTER_INCLUDED')): define('NEWSLETTER_INCLUDED', true); ?>
<!-- ═══ SECTION NEWSLETTER (à part, avant le footer) ═══ -->
<section class="relative overflow-hidden mt-16 sm:mt-20 <?= REVEAL_UP ?>">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="relative bg-gradient-to-br from-primary-700 via-primary-800 to-primary-900 rounded-[2rem] p-8 sm:p-12 lg:p-16 overflow-hidden text-center">
            <!-- Décor animé -->
            <div class="absolute -top-16 -left-16 w-56 h-56 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-20 -right-10 w-72 h-72 bg-white/5 rounded-full"></div>
            <div class="absolute top-8 right-10 w-10 h-10 bg-accent-500/20 rounded-2xl rotate-12 animate-float hidden lg:block"></div>
            <div class="absolute bottom-10 left-12 w-12 h-12 bg-white/10 rounded-2xl -rotate-12 animate-float hidden lg:block" style="animation-delay: 2s;"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md text-white text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full border border-white/15 mb-6">
                    <i class="ph ph-envelope-simple text-accent-400"></i> Lettre pédagogique
                </span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-white mb-3">
                    Recevez nos nouveaux articles
                </h2>
                <p class="text-primary-100/90 text-base leading-relaxed mb-8 max-w-lg mx-auto">
                    Une sélection de ressources, cours et publications, une fois par semaine, directement dans votre boîte mail.
                </p>

                <form class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto" action="../public/contact.php" method="get" novalidate>
                    <label for="nl-email" class="sr-only">Adresse email</label>
                    <input type="email" name="contact_email" id="nl-email" required placeholder="Votre adresse email"
                        class="flex-1 px-5 py-4 rounded-2xl bg-white/10 border border-white/20 text-white placeholder-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-accent-400/60 focus:border-transparent backdrop-blur-md transition-all">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-4 rounded-2xl text-sm transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-accent-500/25 active:scale-[0.97] transform hover:-translate-y-0.5">
                        S'abonner <i class="ph ph-arrow-right"></i>
                    </button>
                </form>

                <ul class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 mt-6 text-xs text-primary-100/80">
                    <li class="inline-flex items-center gap-1.5"><i class="ph ph-check-circle text-accent-400"></i> Sans spam</li>
                    <li class="inline-flex items-center gap-1.5"><i class="ph ph-check-circle text-accent-400"></i> 1 email / semaine</li>
                    <li class="inline-flex items-center gap-1.5"><i class="ph ph-check-circle text-accent-400"></i> Désinscription en 1 clic</li>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
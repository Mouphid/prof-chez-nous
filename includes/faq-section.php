<?php
/**
 * FAQ accordion section with toggle functionality.
 * Usage: <?php include "includes/faq-section.php"; ?>
 *
 * Variables (all optional):
 *   @param string $faq_title    Section heading
 *   @param array  $faq_items    Array of [question, answer]
 */
$faq_title = $faq_title ?? 'Questions fréquentes';
$faq_subtitle = $faq_subtitle ?? 'Retrouvez les réponses aux questions les plus courantes.';
$faq_items = $faq_items ?? [
    ['Comment créer un compte ?', 'Rendez-vous sur la page d\'inscription, remplissez le formulaire avec vos informations et validez votre email. L\'inscription est gratuite et prend moins de 2 minutes.'],
    ['Puis-je télécharger des ressources ?', 'Oui, les utilisateurs inscrits peuvent télécharger les ressources attachées aux articles. Le nombre de téléchargements dépend de votre abonnement.'],
    ['Comment partager mes propres ressources ?', 'Pour publier des articles et ressources, vous devez avoir un compte avec le rôle auteur. Contactez l\'administration pour obtenir ce statut.'],
    ['Les ressources sont-elles gratuites ?', 'Une grande partie des ressources sont gratuites. Certains contenus premium sont réservés aux abonnés.'],
    ['Puis-je commenter les articles ?', 'Absolument ! Une fois connecté, vous pouvez commenter tous les articles publiés et échanger avec la communauté.'],
    ['Comment contacter le support ?', 'Utilisez le formulaire de contact ou écrivez-nous directement à ' . SITE_EMAIL . '. Nous vous répondrons dans les plus brefs délais.'],
];
?>
<section class="max-w-3xl mx-auto px-4 py-16 sm:py-20">
    <div class="text-center mb-10">
        <span class="text-xs font-semibold text-primary uppercase tracking-widest">FAQ</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2"><?= $faq_title ?></h2>
        <p class="text-gray-500 mt-2"><?= $faq_subtitle ?></p>
    </div>
    <div class="space-y-3" id="faq-accordion">
        <?php foreach ($faq_items as $i => $faq): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <button class="w-full flex items-center justify-between px-5 py-4 text-left text-sm font-semibold text-gray-900 hover:bg-gray-50 transition" onclick="this.parentElement.classList.toggle('is-open'); this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.faq-icon').classList.toggle('rotate-45');" aria-expanded="false">
                <?= $faq[0] ?>
                <i class="faq-icon ph ph-plus text-gray-400 text-lg transition-transform duration-200"></i>
            </button>
            <div class="hidden px-5 pb-4 text-sm text-gray-500 leading-relaxed"><?= $faq[1] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

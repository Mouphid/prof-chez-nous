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
    ['Faut-il créer un compte pour télécharger un document ?', 'Non. Les documents gratuits se téléchargent directement, sans inscription.'],
    ['Comment télécharger une publication en PDF ?', 'Ouvrez l\'article concerné : la liste des documents apparaît en bas de page. Cliquez sur « Télécharger » pour un document gratuit.'],
    ['Certains documents sont-ils payants ?', 'Oui. Certains documents sont marqués « payant ». Le prix est affiché en FCFA et le paiement se fait en ligne par Mobile Money ou carte bancaire. Après paiement, le téléchargement se lance automatiquement.'],
    ['Quels moyens de paiement sont acceptés ?', 'Le paiement en ligne est assuré par Kkiapay (Mobile Money et carte bancaire). Le compte marchand est en cours d\'activation : en attendant, contactez-nous pour obtenir un document payant.'],
    ['Puis-je commenter les articles ?', 'Oui, les commentaires sont ouverts à tous. Chaque commentaire est relu par l\'administrateur avant d\'être publié.'],
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

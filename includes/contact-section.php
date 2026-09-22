<?php
/**
 * Contact section with form and info.
 * Usage: <?php include "includes/contact-section.php"; ?>
 *
 * Variables (all optional):
 *   @param string $contact_email    Recipient email (default: SITE_EMAIL)
 *   @param string $contact_phone    Phone display
 *   @param string $contact_address  Address display
 */
$contact_email   = $contact_email ?? SITE_EMAIL;
$contact_phone   = $contact_phone ?? '+229 XX XX XX XX';
$contact_address = $contact_address ?? 'Cotonou, Bénin';
$contact_title   = $contact_title ?? 'Contactez-nous';
$contact_subtitle = $contact_subtitle ?? 'Une question, une suggestion ? Écrivez-nous !';
?>
<section class="max-w-7xl mx-auto px-4 py-16 sm:py-20">
    <div class="text-center mb-10">
        <span class="text-xs font-semibold text-primary uppercase tracking-widest">Contact</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2"><?= $contact_title ?></h2>
        <p class="text-gray-500 mt-2"><?= $contact_subtitle ?></p>
    </div>
    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm">
            <form action="contact.php" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                        <input type="text" id="contact-name" name="name" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                    </div>
                    <div>
                        <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="contact-email" name="email" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                    </div>
                </div>
                <div>
                    <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">Sujet <span class="text-red-500">*</span></label>
                    <input type="text" id="contact-subject" name="subject" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                </div>
                <div>
                    <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                    <textarea id="contact-message" name="message" rows="5" required class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-y"></textarea>
                </div>
                <button type="submit" name="send_contact" class="bg-primary text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm inline-flex items-center gap-2">
                    <i class="ph ph-paper-plane-right"></i> Envoyer
                </button>
            </form>
        </div>
        <div class="space-y-6">
            <?php $contact_infos = [
                ['ph-envelope', 'Email', $contact_email],
                ['ph-phone', 'Téléphone', $contact_phone],
                ['ph-map-pin', 'Adresse', $contact_address],
            ]; ?>
            <?php foreach ($contact_infos as $info): ?>
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex items-start gap-4">
                <div class="w-11 h-11 bg-indigo-50 text-primary rounded-lg flex items-center justify-center flex-shrink-0"><i class="<?= $info[0] ?> text-xl"></i></div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900"><?= $info[1] ?></h3>
                    <p class="text-sm text-gray-500"><?= $info[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="bg-gradient-to-br <?= GRADIENT_CTA ?> text-white rounded-xl p-6 shadow-sm">
                <h3 class="font-bold mb-1">Suivez-nous</h3>
                <p class="text-sm text-indigo-200 mb-4">Restez connecté avec la communauté.</p>
                <div class="flex gap-2">
                    <a href="#" class="w-9 h-9 bg-white/15 hover:bg-white/30 rounded-lg flex items-center justify-center transition" title="Facebook"><i class="ph ph-facebook-logo"></i></a>
                    <a href="#" class="w-9 h-9 bg-white/15 hover:bg-white/30 rounded-lg flex items-center justify-center transition" title="Twitter"><i class="ph ph-x-logo"></i></a>
                    <a href="#" class="w-9 h-9 bg-white/15 hover:bg-white/30 rounded-lg flex items-center justify-center transition" title="LinkedIn"><i class="ph ph-linkedin-logo"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

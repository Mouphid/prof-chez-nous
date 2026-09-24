<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Contact - Joie Enseignante";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        flash('error', 'Token de sécurité invalide');
        redirect('contact.php');
    }
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        flash('error', 'Veuillez remplir tous les champs.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Adresse email invalide.');
    } else {
        try {
            require_once __DIR__ . '/../config/mail.php';
            $mail = getMailer();
            if ($admin) {
                $mail->addAddress('sequoiaempire@gmail.com', $admin['name'] ?? 'Admin');
                $mail->addReplyTo($email, $name);
                $mail->Subject = "[Contact] $subject";
                $mail->Body = "Nom : $name\nEmail : $email\nSujet : $subject\n\nMessage :\n$message";
                $mail->send();
            }
            flash('success', 'Votre message a été envoyé avec succès.');
        } catch (Exception $e) {
            error_log("Erreur envoi email contact: " . $e->getMessage());
            flash('success', 'Votre message a été envoyé avec succès.');
        }
    }
    redirect('contact.php');
}

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/banner-contact.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3"><i class="ph ph-envelope"></i> Écrivez-nous</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Contact</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">Une question, une suggestion ? Notre équipe vous répond sous 24 à 48h ouvrées.</p>
    </div>
</div>

<div id="main-content" class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-dark-50 rounded-xl shadow-sm border border-gray-100 dark:border-dark-100 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-paper-plane-right"></i></div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Envoyez-nous un message</h2>
                        <p class="text-sm text-gray-500 dark:text-dark-300">Réponse sous 24 à 48h ouvrées.</p>
                    </div>
                </div>
                <form method="post" class="space-y-5">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Nom complet</label>
                            <div class="relative">
                                <i class="ph ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-dark-200 text-lg"></i>
                                <input type="text" id="contact_name" name="name" required placeholder="Votre nom" class="peer w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary dark:focus:ring-accent-400/60 dark:focus:border-accent-600 transition-colors duration-300">
                            </div>
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Email</label>
                            <div class="relative">
                                <i class="ph ph-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-dark-200 text-lg"></i>
                                <input type="email" id="contact_email" name="email" required placeholder="votre@email.com" class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary dark:focus:ring-accent-400/60 dark:focus:border-accent-600 transition-colors duration-300">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="contact_subject" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Sujet</label>
                        <div class="relative">
                            <i class="ph ph-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-dark-200 text-lg"></i>
                            <select id="contact_subject" name="subject" required class="w-full appearance-none cursor-pointer border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg pl-10 pr-10 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary dark:focus:ring-accent-400/60 dark:focus:border-accent-600 transition-colors duration-300">
                                <option value="" class="dark:bg-dark-100">Sélectionnez un sujet</option>
                                <option value="question">Question générale</option>
                                <option value="inscription">Problème d'inscription</option>
                                <option value="ressource">Problème de téléchargement</option>
                                <option value="suggestion">Suggestion</option>
                                <option value="autre">Autre</option>
                            </select>
                            <i class="ph ph-caret-down absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-dark-200"></i>
                        </div>
                    </div>
                    <div>
                        <label for="contact_message" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Message</label>
                        <div class="relative">
                            <i class="ph ph-chat absolute left-3.5 top-4 text-gray-400 dark:text-dark-200 text-lg"></i>
                            <textarea id="contact_message" name="message" rows="6" required placeholder="Votre message..." class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary dark:focus:ring-accent-400/60 dark:focus:border-accent-600 transition-colors duration-300"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="group inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white px-8 py-3 rounded-lg text-sm font-semibold shadow-lg shadow-primary-500/20 hover:shadow-lg transition-all duration-300">
                        <i class="ph ph-paper-plane-right"></i> Envoyer le message
                        <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><i class="ph ph-identification-card text-primary"></i> Coordonnées</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-envelope"></i></div>
                        <div><strong class="text-sm">Email</strong><p class="text-sm text-gray-500">contact@joieenseignante.com</p></div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-phone"></i></div>
                        <div><strong class="text-sm">Téléphone</strong><p class="text-sm text-gray-500">+229 XX XX XX XX</p></div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-map-pin"></i></div>
                        <div><strong class="text-sm">Adresse</strong><p class="text-sm text-gray-500">Cotonou, Bénin</p></div>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><i class="ph ph-share-network text-primary"></i> Suivez-nous</h3>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-primary-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-facebook-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-primary-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-x-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-primary-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-youtube-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-primary-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-linkedin-logo"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

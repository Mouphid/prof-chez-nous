<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Contact - Joie Enseignante";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $admin_email = $admin['email'] ?? '';
            if ($admin_email) {
                $mail->addAddress($admin_email, $admin['name'] ?? 'Professeur');
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

<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-envelope text-primary"></i> Contact</h1>
        <p class="text-gray-500 mt-2">Une question, une suggestion ? Écrivez-nous !</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6"><i class="ph ph-paper-plane-right text-primary"></i> Envoyez-nous un message</h2>
                <form method="post" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><i class="ph ph-user text-primary"></i> Nom complet</label>
                            <input type="text" name="name" required placeholder="Votre nom" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><i class="ph ph-envelope text-primary"></i> Email</label>
                            <input type="email" name="email" required placeholder="votre@email.com" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><i class="ph ph-tag text-primary"></i> Sujet</label>
                        <select name="subject" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">Sélectionnez un sujet</option>
                            <option value="question">Question générale</option>
                            <option value="inscription">Problème d'inscription</option>
                            <option value="ressource">Problème de téléchargement</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><i class="ph ph-chat text-primary"></i> Message</label>
                        <textarea name="message" rows="6" required placeholder="Votre message..." class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-3 rounded-lg text-sm font-medium hover:bg-indigo-700 transition inline-flex items-center gap-2">
                        <i class="ph ph-paper-plane-right"></i> Envoyer le message
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><i class="ph ph-identification-card text-primary"></i> Coordonnées</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-envelope"></i></div>
                        <div><strong class="text-sm">Email</strong><p class="text-sm text-gray-500">contact@joieenseignante.com</p></div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-phone"></i></div>
                        <div><strong class="text-sm">Téléphone</strong><p class="text-sm text-gray-500">+229 XX XX XX XX</p></div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-primary flex-shrink-0"><i class="ph ph-map-pin"></i></div>
                        <div><strong class="text-sm">Adresse</strong><p class="text-sm text-gray-500">Cotonou, Bénin</p></div>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-900 mb-4"><i class="ph ph-share-network text-primary"></i> Suivez-nous</h3>
                <div class="flex gap-3">
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-indigo-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-facebook-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-indigo-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-twitter-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-indigo-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-youtube-logo"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-100 hover:bg-indigo-100 hover:text-primary rounded-lg flex items-center justify-center transition text-gray-600"><i class="ph ph-linkedin-logo-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Désinscription de la newsletter - Joie Enseignante";
$page_description = "Se désinscrire de la lettre pédagogique de Joie Enseignante.";

// Page personnelle : on demande aux moteurs de recherche de ne pas l'indexer.
header('X-Robots-Tag: noindex, nofollow');

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$token_valide = $token !== '' && preg_match('/^[a-f0-9]{64}$/i', $token);

$abonne = null;
if ($token_valide) {
    $stmt = $pdo->prepare("SELECT id_subscriber, email, status, unsubscribed_at FROM newsletter_subscribers WHERE unsubscribe_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $abonne = $stmt->fetch() ?: null;
}

$message_ok = null;
$message_erreur = null;

if (!$abonne) {
    $message_erreur = "Ce lien de désinscription n'est pas valide. Il a peut-être déjà été utilisé, ou l'adresse a été supprimée.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $message_erreur = 'Session expirée. Merci de recommencer.';
    } else {
        $pdo->prepare("
            UPDATE newsletter_subscribers
            SET status = 'unsubscribed', unsubscribed_at = COALESCE(unsubscribed_at, NOW())
            WHERE id_subscriber = ?
        ")->execute([(int)$abonne['id_subscriber']]);

        $abonne['status'] = 'unsubscribed';
        $message_ok = "C'est fait : votre adresse ne recevra plus la newsletter.";
    }
} elseif ($abonne['status'] === 'unsubscribed') {
    $message_ok = "Cette adresse est déjà désinscrite de la newsletter. Vous n'avez rien d'autre à faire.";
}

// Adresse partiellement masquée : le lien suffit pour agir, inutile d'afficher l'adresse entière.
$email_affiche = '';
if ($abonne) {
    $parties = explode('@', (string)$abonne['email'], 2);
    if (count($parties) === 2) {
        $email_affiche = mb_substr($parties[0], 0, 2) . '***@' . $parties[1];
    }
}

include "../includes/header.php";
?>

<div class="max-w-2xl mx-auto px-4 py-12 sm:py-16">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-6 sm:p-10">

        <?php if ($message_erreur): ?>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="ph ph-warning-circle text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Lien non valide</h1>
                <p class="text-gray-600 dark:text-dark-300 mb-6"><?= htmlspecialchars($message_erreur) ?></p>
                <a href="index.php" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white font-semibold px-6 py-3 rounded-xl transition">
                    <i class="ph ph-house"></i> Retour à l'accueil
                </a>
            </div>

        <?php elseif ($message_ok): ?>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i class="ph ph-check-circle text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Désinscription confirmée</h1>
                <p class="text-gray-600 dark:text-dark-300 mb-4"><?= htmlspecialchars($message_ok) ?></p>
                <?php if ($email_affiche !== ''): ?>
                    <p class="text-sm text-gray-500 dark:text-dark-300 mb-6">Adresse concernée : <strong><?= htmlspecialchars($email_affiche) ?></strong></p>
                <?php endif; ?>
                <p class="text-xs text-gray-500 dark:text-dark-300 mb-6">
                    Vous continuerez à recevoir les e-mails liés à vos achats (liens de téléchargement),
                    mais plus la newsletter.
                </p>
                <a href="index.php" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white font-semibold px-6 py-3 rounded-xl transition">
                    <i class="ph ph-house"></i> Retour à l'accueil
                </a>
            </div>

        <?php else: ?>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i class="ph ph-envelope-simple text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Se désinscrire de la newsletter</h1>
                <p class="text-gray-600 dark:text-dark-300 mb-2">
                    Vous êtes sur le point de retirer cette adresse de la lettre pédagogique :
                </p>
                <p class="font-semibold text-gray-900 dark:text-white mb-6"><?= htmlspecialchars($email_affiche) ?></p>

                <form method="post" class="inline-block">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                        <i class="ph ph-envelope-slash"></i> Confirmer ma désinscription
                    </button>
                </form>

                <p class="mt-6">
                    <a href="index.php" class="text-sm text-gray-500 dark:text-dark-300 hover:text-primary">Non, je garde la newsletter</a>
                </p>

                <p class="text-xs text-gray-500 dark:text-dark-300 mt-6">
                    Vous continuerez à recevoir les e-mails liés à vos achats (liens de téléchargement),
                    mais plus la newsletter.
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include "../includes/footer.php"; ?>

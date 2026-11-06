<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Retrouver mon achat - Joie Enseignante";
$page_description = "Vous avez perdu l'e-mail contenant votre lien de téléchargement ? Indiquez votre adresse pour le recevoir à nouveau.";

// Message volontairement identique, que l'adresse ait acheté quelque chose ou non :
// on ne révèle jamais si une adresse est cliente du site.
$message_generique = 'Si cette adresse correspond à un achat, un e-mail vient de partir. '
    . 'Pensez à vérifier les indésirables (spam). Si rien n\'arrive d\'ici quelques minutes, '
    . 'écrivez-nous à ' . SITE_EMAIL . '.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        flash('error', 'Session expirée. Merci de recommencer.');
        redirect('retrouver-mon-achat.php');
    }

    // Piège à robots : un visiteur normal ne remplit jamais ce champ caché.
    if (trim((string)($_POST['website'] ?? '')) !== '') {
        flash('success', $message_generique);
        redirect('retrouver-mon-achat.php');
    }

    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    if ($email === '' || mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Merci de saisir une adresse e-mail valide.');
        redirect('retrouver-mon-achat.php');
    }

    // ─── Limitation anti-abus ───
    // 3 demandes par adresse et par heure, 10 par connexion et par heure.
    // On enregistre d'abord la demande, avant de savoir si l'adresse a acheté :
    // la limite ne peut donc pas servir à deviner qui est client.
    $max_par_email = 3;
    $max_par_ip    = 10;
    $email_hash = hash('sha256', $email);
    $ip_hash    = hash('sha256', get_client_ip() . '|' . SITE_URL);
    $limite     = false;

    try {
        // Nettoyage des traces de plus de 7 jours (la table reste petite).
        $pdo->exec("DELETE FROM purchase_recovery_requests WHERE created_at < (NOW() - INTERVAL 7 DAY)");

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM purchase_recovery_requests
            WHERE email_hash = ? AND created_at > (NOW() - INTERVAL 1 HOUR)
        ");
        $stmt->execute([$email_hash]);
        $nb_email = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM purchase_recovery_requests
            WHERE ip_hash = ? AND created_at > (NOW() - INTERVAL 1 HOUR)
        ");
        $stmt->execute([$ip_hash]);
        $nb_ip = (int)$stmt->fetchColumn();

        if ($nb_email >= $max_par_email || $nb_ip >= $max_par_ip) {
            $limite = true;
        } else {
            $pdo->prepare("INSERT INTO purchase_recovery_requests (email_hash, ip_hash) VALUES (?, ?)")
                ->execute([$email_hash, $ip_hash]);
        }
    } catch (Throwable $e) {
        // Table absente (migration oubliée) : on garde une limite de secours en session.
        error_log('Retrouver mon achat — limitation : ' . $e->getMessage());

        $maintenant = time();
        $essais = array_values(array_filter(
            $_SESSION['recovery_attempts'] ?? [],
            fn($t) => is_int($t) && $t > $maintenant - 3600
        ));
        if (count($essais) >= $max_par_ip) {
            $limite = true;
        } else {
            $essais[] = $maintenant;
            $_SESSION['recovery_attempts'] = $essais;
        }
    }

    if ($limite) {
        flash('error', 'Trop de demandes en peu de temps. Merci de réessayer dans une heure.');
        redirect('retrouver-mon-achat.php');
    }

    // ─── Recherche des achats payés ───
    $achats = [];
    try {
        $stmt = $pdo->prepare("
            SELECT pu.*,
                   f.file_name,
                   c.title AS course_title,
                   ci.title AS item_title
            FROM purchases pu
            LEFT JOIN files f ON f.id_file = pu.id_file
            LEFT JOIN courses c ON c.id_course = pu.id_course
            LEFT JOIN course_items ci ON ci.id_item = pu.id_item
            WHERE pu.status = 'paid'
              AND pu.buyer_email = ?
              AND pu.download_token IS NOT NULL
              AND pu.download_token <> ''
            ORDER BY COALESCE(pu.paid_at, pu.created_at) DESC
            LIMIT 20
        ");
        $stmt->execute([$email]);
        $achats = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Retrouver mon achat — recherche : ' . $e->getMessage());
    }

    if ($achats) {
        try {
            require_once __DIR__ . '/../config/mail.php';
            send_recovery_email($achats);
        } catch (Throwable $e) {
            // L'acheteur ne voit pas la différence, pour ne pas révéler l'existence
            // de l'adresse. Le problème est noté dans les journaux.
            error_log('Retrouver mon achat — envoi : ' . $e->getMessage());
        }
    }

    flash('success', $message_generique);
    redirect('retrouver-mon-achat.php');
}

include "../includes/header.php";
?>

<div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/hero-1.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3">
            <i class="ph ph-key"></i> Accès à vos achats
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Retrouver mon achat</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">
            Votre lien de téléchargement est perdu ? Nous vous le renvoyons par e-mail.
        </p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-6 sm:p-10">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Votre adresse e-mail d'achat</h2>
        <p class="text-sm text-gray-500 dark:text-dark-300 mb-6">
            Indiquez l'adresse utilisée lors du paiement. Nous vous renvoyons tous les liens
            correspondant à cette adresse : documents, cours complets et chapitres.
        </p>

        <form method="post" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label for="recovery_email" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Adresse e-mail</label>
                <div class="relative">
                    <i class="ph ph-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-dark-200 text-lg"></i>
                    <input type="email" id="recovery_email" name="email" required autocomplete="email"
                           placeholder="votre@email.com"
                           class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary dark:focus:ring-accent-400/60 dark:focus:border-accent-600 transition-colors duration-300">
                </div>
            </div>

            <!-- Champ caché : piège à robots. -->
            <div style="display:none;" aria-hidden="true">
                <label for="recovery_website">Site web</label>
                <input type="text" id="recovery_website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="group inline-flex items-center gap-2 bg-primary hover:bg-primary-600 text-white px-8 py-3 rounded-lg text-sm font-semibold shadow-lg shadow-primary-500/20 transition-all duration-300">
                <i class="ph ph-paper-plane-right"></i> Recevoir mes liens
                <i class="ph ph-arrow-right group-hover:translate-x-1 transition-transform duration-300"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-dark-100 space-y-3 text-sm text-gray-600 dark:text-dark-300">
            <p><i class="ph ph-shield-check text-primary"></i>
                Aucun compte n'est nécessaire : le lien reçu par e-mail est votre clé d'accès.</p>
            <p><i class="ph ph-clock text-primary"></i>
                Pour éviter les abus, la demande est limitée à 3 envois par heure pour une même adresse.</p>
            <p><i class="ph ph-envelope-open text-primary"></i>
                Pensez à regarder dans les indésirables (spam) et dans les promotions.</p>
        </div>
    </div>

    <div class="mt-6 text-center text-sm text-gray-500 dark:text-dark-300">
        <p>Vous n'avez encore rien acheté ?
            <a href="cours.php" class="text-primary hover:underline font-medium">Découvrir les cours</a>
            ou
            <a href="publications.php" class="text-primary hover:underline font-medium">les publications</a>.
        </p>
        <p class="mt-2">Une autre question ?
            <a href="contact.php" class="text-primary hover:underline font-medium">Écrivez-nous</a>.
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

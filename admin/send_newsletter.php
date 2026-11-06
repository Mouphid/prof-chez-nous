<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$nb_actifs = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'")->fetchColumn();

$etape    = 'form';
$sujet    = '';
$message  = '';
$apercu   = null;
$erreurs  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $erreurs[] = 'Token de sécurité invalide. Rechargez la page.';
    }

    $action  = (string)($_POST['action'] ?? 'preview');
    $sujet   = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if (mb_strlen($sujet) < 3) {
        $erreurs[] = 'Le sujet doit contenir au moins 3 caractères.';
    } elseif (mb_strlen($sujet) > 200) {
        $erreurs[] = 'Le sujet ne doit pas dépasser 200 caractères.';
    }

    if (mb_strlen($message) < 20) {
        $erreurs[] = 'Le message doit contenir au moins 20 caractères.';
    } elseif (mb_strlen($message) > 20000) {
        $erreurs[] = 'Le message ne doit pas dépasser 20 000 caractères.';
    }

    if ($action === 'edit') {
        $etape = 'form';
    } elseif (!$erreurs && $action === 'preview') {
        require_once __DIR__ . '/../config/mail.php';
        try {
            // Aperçu avec une adresse et un jeton fictifs : rien n'est envoyé.
            $apercu = build_newsletter_email([
                'email'             => SITE_EMAIL,
                'unsubscribe_token' => str_repeat('a', 64),
            ], $sujet, $message);
            $etape = 'apercu';
        } catch (Throwable $e) {
            $erreurs[] = "Impossible de préparer l'aperçu : " . $e->getMessage();
        }
    } elseif (!$erreurs && $action === 'send') {
        if ($nb_actifs === 0) {
            $erreurs[] = "Aucun abonné actif : il n'y a personne à qui envoyer le message.";
        } else {
            // L'envoi peut durer plusieurs minutes : on ne coupe pas le script
            // et on continue même si le navigateur est fermé.
            @set_time_limit(0);
            ignore_user_abort(true);

            try {
                require_once __DIR__ . '/../config/mail.php';

                $abonnes = $pdo->query("
                    SELECT email, unsubscribe_token
                    FROM newsletter_subscribers
                    WHERE status = 'active' AND unsubscribe_token <> ''
                    ORDER BY id_subscriber ASC
                ")->fetchAll();

                $resultat = send_newsletter_to_subscribers($abonnes, $sujet, $message);

                $pdo->prepare("
                    INSERT INTO newsletter_campaigns (subject, body, recipients_count, sent_count, failed_count)
                    VALUES (?, ?, ?, ?, ?)
                ")->execute([
                    $sujet,
                    $message,
                    (int)$resultat['total'],
                    (int)$resultat['sent'],
                    (int)$resultat['failed'],
                ]);

                if ($resultat['sent'] > 0 && $resultat['failed'] === 0) {
                    flash('success', 'Message envoyé à ' . $resultat['sent'] . ' abonné' . ($resultat['sent'] > 1 ? 's' : '') . '.');
                } elseif ($resultat['sent'] > 0) {
                    flash('success', 'Message envoyé à ' . $resultat['sent'] . ' abonné' . ($resultat['sent'] > 1 ? 's' : '')
                        . ', ' . $resultat['failed'] . ' échec' . ($resultat['failed'] > 1 ? 's' : '') . '. Voir le journal pour le détail.');
                } else {
                    $detail = $resultat['first_error'] ? ' Détail : ' . $resultat['first_error'] : '';
                    flash('error', "Aucun message n'a pu être envoyé." . $detail);
                }
            } catch (Throwable $e) {
                error_log('Newsletter — envoi : ' . $e->getMessage());
                flash('error', "L'envoi a échoué : " . $e->getMessage());
            }

            header('Location: manage_newsletter.php');
            exit;
        }
    }
}

$page_action = '<a href="manage_newsletter.php" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-primary">'
    . '<i class="ph ph-arrow-left"></i> Liste des abonnés</a>';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Écrire une newsletter - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php $page_title = 'Écrire une newsletter'; $page_icon = 'ph-paper-plane-tilt'; include "_topbar.php"; ?>
            <div class="p-8 max-w-5xl">

            <?php if ($erreurs): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-5 text-sm" role="alert">
                <?php foreach ($erreurs as $err): ?><p><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($err) ?></p><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($etape === 'apercu' && $apercu): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">Aperçu de l'e-mail</p>
                            <div class="border border-gray-200 rounded-xl p-5 bg-white">
                                <?= $apercu['html'] ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">
                                Le lien de désinscription sera personnalisé pour chaque abonné.
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <p class="text-3xl font-extrabold text-primary"><?= $nb_actifs ?></p>
                            <p class="text-sm text-gray-500 mb-1">abonné<?= $nb_actifs > 1 ? 's' : '' ?> actif<?= $nb_actifs > 1 ? 's' : '' ?></p>
                            <p class="text-xs text-gray-500">recevront ce message.</p>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm p-6 space-y-3">
                            <form method="post" onsubmit="return confirm('Envoyer ce message à <?= $nb_actifs ?> abonné(s) ? Cette action est définitive.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="send">
                                <input type="hidden" name="subject" value="<?= htmlspecialchars($sujet) ?>">
                                <input type="hidden" name="message" value="<?= htmlspecialchars($message) ?>">
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-5 py-3 rounded-xl transition shadow-lg">
                                    <i class="ph ph-paper-plane-tilt"></i> Envoyer maintenant
                                </button>
                            </form>

                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="subject" value="<?= htmlspecialchars($sujet) ?>">
                                <input type="hidden" name="message" value="<?= htmlspecialchars($message) ?>">
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-5 py-3 rounded-xl transition">
                                    <i class="ph ph-pencil-simple"></i> Modifier le message
                                </button>
                            </form>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-5 text-sm">
                            <p class="font-semibold mb-1"><i class="ph ph-info"></i> Avant d'envoyer</p>
                            <p>L'envoi est définitif. Vérifiez le texte et le sujet. Ne fermez pas la page pendant l'envoi.</p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <form method="post" class="bg-white rounded-xl shadow-sm p-6 space-y-5">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="preview">

                            <div>
                                <label for="newsletter_subject" class="block text-sm font-medium text-gray-700 mb-1.5">Sujet de l'e-mail</label>
                                <input type="text" id="newsletter_subject" name="subject" required maxlength="200"
                                       value="<?= htmlspecialchars($sujet) ?>"
                                       placeholder="Ex. : Trois ressources pour préparer vos cours"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="newsletter_message" class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                                <textarea id="newsletter_message" name="message" rows="14" required
                                          placeholder="Écrivez votre message. Laissez une ligne vide entre deux paragraphes."
                                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($message) ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Le texte est affiché tel quel (pas de code HTML). Un double saut de ligne crée un nouveau paragraphe.
                                </p>
                            </div>

                            <button type="submit" class="inline-flex items-center gap-2 bg-primary text-white font-semibold px-6 py-3 rounded-xl hover:bg-indigo-700 transition">
                                <i class="ph ph-eye"></i> Aperçu avant envoi
                            </button>
                        </form>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <p class="text-3xl font-extrabold text-primary"><?= $nb_actifs ?></p>
                            <p class="text-sm text-gray-500">abonné<?= $nb_actifs > 1 ? 's' : '' ?> actif<?= $nb_actifs > 1 ? 's' : '' ?></p>
                            <?php if ($nb_actifs === 0): ?>
                                <p class="text-xs text-amber-700 mt-2">Aucun abonné actif pour l'instant.</p>
                            <?php endif; ?>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm p-6 text-sm text-gray-600 space-y-3">
                            <p class="font-semibold text-gray-900"><i class="ph ph-lightbulb text-primary"></i> Conseils</p>
                            <p>Un sujet court et concret donne envie d'ouvrir.</p>
                            <p>Une seule idée principale par message fonctionne mieux.</p>
                            <p>Chaque e-mail contient automatiquement le lien de désinscription.</p>
                            <p>L'historique des envois est visible sur la page des abonnés.</p>
                        </div>

                        <?php if ($nb_actifs > 0): ?>
                        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-5 text-sm">
                            <p class="font-semibold mb-1"><i class="ph ph-info"></i> Envoi réel</p>
                            <p>Le message partira vraiment. Sans SMTP configuré, l'envoi échouera avec un message clair.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>

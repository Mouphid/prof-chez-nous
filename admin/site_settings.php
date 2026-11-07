<?php
/**
 * Réglages du site — coordonnées et réseaux sociaux.
 *
 * Les valeurs sont enregistrées dans la table `settings` et lues par
 * `config/theme.php` à chaque chargement. Elles alimentent le pied de page,
 * la page Contact, les pages légales et les e-mails.
 */
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$champs = [
    'site_email'       => SITE_EMAIL,
    'site_phone'       => SITE_PHONE,
    'site_address'     => SITE_ADDRESS,
    'social_facebook'  => SOCIAL_FACEBOOK,
    'social_twitter'   => SOCIAL_TWITTER,
    'social_linkedin'  => SOCIAL_LINKEDIN,
    'social_youtube'   => SOCIAL_YOUTUBE,
    'social_instagram' => SOCIAL_INSTAGRAM,
];
$valeurs = $champs;
$erreurs = [];

/**
 * Vérifie qu'une adresse de réseau social est bien une URL http(s).
 * Une valeur vide est acceptée (réseau non utilisé).
 */
function reglage_url_valide(string $url): bool
{
    if ($url === '') {
        return true;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $schema = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return in_array($schema, ['http', 'https'], true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $erreurs[] = "Token de sécurité invalide. Rechargez la page.";
    } else {
        foreach (array_keys($champs) as $cle) {
            $valeurs[$cle] = trim((string)($_POST[$cle] ?? ''));
        }

        if ($valeurs['site_email'] === '' || !filter_var($valeurs['site_email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "L'adresse e-mail de contact n'est pas valide.";
        }
        if ($valeurs['site_phone'] === '') {
            $erreurs[] = "Le numéro de téléphone ne peut pas être vide.";
        }
        if ($valeurs['site_address'] === '') {
            $erreurs[] = "L'adresse postale ne peut pas être vide.";
        }

        $reseaux = [
            'social_facebook'  => 'Facebook',
            'social_twitter'   => 'X (Twitter)',
            'social_linkedin'  => 'LinkedIn',
            'social_youtube'   => 'YouTube',
            'social_instagram' => 'Instagram',
        ];
        foreach ($reseaux as $cle => $label) {
            if (!reglage_url_valide($valeurs[$cle])) {
                $erreurs[] = "L'adresse " . $label . " doit commencer par http:// ou https://.";
            }
        }

        if (!$erreurs) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            foreach ($valeurs as $cle => $valeur) {
                $stmt->execute([$cle, $valeur]);
            }

            admin_activity('update', 'settings', null, 'Coordonnées et réseaux sociaux mis à jour');
            flash('success', 'Les réglages du site ont bien été enregistrés.');
            redirect('site_settings.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réglages du site - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Réglages du site';
            $page_subtitle = 'Coordonnées et réseaux sociaux affichés publiquement';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if (hasFlash('success')): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars(flash('success')) ?></span></div>
            <?php endif; ?>

            <?php if ($erreurs): ?>
            <div class="adm-alert adm-alert-error" role="alert">
                <i class="ph ph-warning-circle"></i>
                <span><?= htmlspecialchars(implode(' ', $erreurs)) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <?= csrf_field() ?>

                <div class="adm-card mb-6">
                    <div class="adm-card-head">
                        <div>
                            <h2 class="adm-card-title"><i class="ph ph-address-book"></i> Coordonnées</h2>
                            <p class="adm-card-sub">Utilisées sur la page Contact, le pied de page, les pages légales et les e-mails.</p>
                        </div>
                    </div>
                    <div class="adm-card-body">
                        <div class="adm-form-row mb-4">
                            <div>
                                <label for="site_email" class="adm-label">Adresse e-mail de contact</label>
                                <div class="adm-field">
                                    <input type="email" id="site_email" name="site_email" required
                                           value="<?= htmlspecialchars($valeurs['site_email']) ?>" class="adm-input"
                                           placeholder="contact@joiedenseignante.com">
                                    <i class="ph ph-envelope adm-field-icon"></i>
                                </div>
                            </div>
                            <div>
                                <label for="site_phone" class="adm-label">Téléphone</label>
                                <div class="adm-field">
                                    <input type="text" id="site_phone" name="site_phone" required
                                           value="<?= htmlspecialchars($valeurs['site_phone']) ?>" class="adm-input"
                                           placeholder="+229 90 00 00 00">
                                    <i class="ph ph-phone adm-field-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="site_address" class="adm-label">Adresse postale</label>
                            <div class="adm-field">
                                <input type="text" id="site_address" name="site_address" required
                                       value="<?= htmlspecialchars($valeurs['site_address']) ?>" class="adm-input"
                                       placeholder="Cotonou, Bénin">
                                <i class="ph ph-map-pin adm-field-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="adm-card mb-6">
                    <div class="adm-card-head">
                        <div>
                            <h2 class="adm-card-title"><i class="ph ph-share-network"></i> Réseaux sociaux</h2>
                            <p class="adm-card-sub">Laissez vide un réseau que vous n'utilisez pas. Les icônes correspondantes n'apparaîtront pas.</p>
                        </div>
                    </div>
                    <div class="adm-card-body">
                        <?php
                        $reseaux = [
                            'social_facebook'  => ['Facebook', 'ph-facebook-logo', 'https://facebook.com/votre-page'],
                            'social_twitter'   => ['X (Twitter)', 'ph-x-logo', 'https://x.com/votre-compte'],
                            'social_linkedin'  => ['LinkedIn', 'ph-linkedin-logo', 'https://linkedin.com/company/votre-page'],
                            'social_youtube'   => ['YouTube', 'ph-youtube-logo', 'https://youtube.com/@votre-chaine'],
                            'social_instagram' => ['Instagram', 'ph-instagram-logo', 'https://instagram.com/votre-compte'],
                        ];
                        foreach ($reseaux as $cle => [$label, $icone, $exemple]): ?>
                        <div class="mb-4">
                            <label for="<?= $cle ?>" class="adm-label">
                                <i class="ph <?= $icone ?>"></i> <?= htmlspecialchars($label) ?>
                            </label>
                            <div class="adm-field">
                                <input type="url" id="<?= $cle ?>" name="<?= $cle ?>"
                                       value="<?= htmlspecialchars($valeurs[$cle]) ?>" class="adm-input"
                                       placeholder="<?= htmlspecialchars($exemple) ?>">
                                <i class="ph ph-link adm-field-icon"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="adm-note-soft">
                        <i class="ph ph-info"></i>
                        Les modifications sont visibles immédiatement sur le site public.
                    </p>
                    <button type="submit" class="adm-btn adm-btn-primary">
                        <i class="ph ph-floppy-disk"></i> Enregistrer les réglages
                    </button>
                </div>
            </form>

            </div>
        </main>
    </div>
</body>
</html>

<?php
require_once "config_admin.php";

$error = '';
$success = '';
$active_tab = (($_GET['tab'] ?? '') === 'securite') ? 'securite' : 'profil';

$stmt = $pdo->prepare("SELECT id_user, name, email, password, role FROM users WHERE id_user = ?");
$stmt->execute([$admin_id]);
$me = $stmt->fetch();

if (!$me) {
    header("Location: logout.php");
    exit;
}

// ─── Mise à jour du profil ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $active_tab = 'profil';

    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide. Rechargez la page.";
    } else {
        $new_name  = trim((string)($_POST['name'] ?? ''));
        $new_email = trim((string)($_POST['email'] ?? ''));

        if ($new_name === '') {
            $error = "Le nom ne peut pas être vide.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse e-mail n'est pas valide.";
        } else {
            $check = $pdo->prepare("SELECT id_user FROM users WHERE email = ? AND id_user <> ?");
            $check->execute([$new_email, $admin_id]);
            if ($check->fetch()) {
                $error = "Cette adresse e-mail est déjà utilisée par un autre compte.";
            } else {
                $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id_user = ?")
                    ->execute([$new_name, $new_email, $admin_id]);
                $me['name'] = $new_name;
                $me['email'] = $new_email;
                $admin_name = $new_name;
                $_SESSION['admin_name'] = $new_name;
                $success = "Vos informations ont été mises à jour.";
            }
        }
    }
}

// ─── Changement de mot de passe ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_profile'])) {
    $active_tab = 'securite';

    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $error = "Token de sécurité invalide. Rechargez la page.";
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $assez_fort = mb_strlen($new) >= 10
            && preg_match('/[A-Z]/', $new)
            && preg_match('/[a-z]/', $new)
            && preg_match('/[0-9]/', $new)
            && preg_match('/[^A-Za-z0-9]/', $new);

        if ($current === '' || $new === '' || $confirm === '') {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!password_verify($current, $me['password'])) {
            $error = "Le mot de passe actuel est incorrect.";
        } elseif (!$assez_fort) {
            $error = "Le nouveau mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } elseif ($new === $current) {
            $error = "Le nouveau mot de passe doit être différent de l'ancien.";
        } elseif ($new !== $confirm) {
            $error = "Les deux nouveaux mots de passe ne correspondent pas.";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $upd->execute([$hash, $admin_id]);
            $success = "Votre mot de passe a été modifié avec succès.";
        }
    }
}

$role_labels = ['admin' => 'Super Admin', 'auteur' => 'Auteur', 'etudiant' => 'Étudiant'];
$role_label = $role_labels[$me['role'] ?? $admin_role] ?? ucfirst((string)($me['role'] ?? $admin_role));

$name_parts = preg_split('/\s+/', trim((string)$me['name'])) ?: [(string)$me['name']];
$initials = mb_strtoupper(mb_substr($name_parts[0], 0, 1));
if (count($name_parts) > 1) {
    $initials .= mb_strtoupper(mb_substr(end($name_parts), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Paramètres';
            $page_subtitle = 'Votre compte et la sécurité de votre accès';
            include "_topbar.php";
            ?>
            <div class="adm-container">

            <?php if($error): ?>
            <div class="adm-alert adm-alert-error" role="alert"><i class="ph ph-warning-circle"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <?php if($success): ?>
            <div class="adm-alert adm-alert-success" role="status"><i class="ph ph-check-circle"></i><span><?= htmlspecialchars($success) ?></span></div>
            <?php endif; ?>

            <div class="adm-settings-grid">

                <!-- Carte compte -->
                <aside class="adm-card overflow-hidden">
                    <div class="adm-card-head">
                        <div class="flex items-center gap-3">
                            <span class="adm-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
                            <div class="min-w-0">
                                <p class="adm-card-title">Connecté en tant que</p>
                                <p class="adm-card-sub"><?= htmlspecialchars($me['email']) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="adm-account-card">
                        <div class="adm-account-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></div>
                        <p class="adm-account-name"><?= htmlspecialchars($me['name']) ?></p>
                        <p class="adm-account-mail"><?= htmlspecialchars($me['email']) ?></p>
                        <div class="adm-account-rows">
                            <div class="adm-account-row"><span>Rôle</span><span><?= htmlspecialchars($role_label) ?></span></div>
                            <div class="adm-account-row"><span>Compte</span><span>Actif</span></div>
                            <div class="adm-account-row"><span>Identifiant</span><span>#<?= (int)$me['id_user'] ?></span></div>
                        </div>
                        <p class="adm-note-soft mt-4" style="margin-top:1rem">
                            <i class="ph ph-info"></i>
                            Seul un administrateur peut modifier les rôles. Votre mot de passe reste personnel.
                        </p>
                    </div>
                </aside>

                <!-- Onglets -->
                <section>
                    <div class="adm-tabs mb-4" role="tablist" aria-label="Paramètres du compte">
                        <button type="button" class="adm-tab <?= $active_tab === 'profil' ? 'is-active' : '' ?>"
                                data-settings-tab="profil" role="tab" aria-selected="<?= $active_tab === 'profil' ? 'true' : 'false' ?>">
                            <i class="ph ph-user"></i> Profil
                        </button>
                        <button type="button" class="adm-tab <?= $active_tab === 'securite' ? 'is-active' : '' ?>"
                                data-settings-tab="securite" role="tab" aria-selected="<?= $active_tab === 'securite' ? 'true' : 'false' ?>">
                            <i class="ph ph-lock-key"></i> Sécurité
                        </button>
                    </div>

                    <!-- Profil -->
                    <div class="adm-card" data-settings-panel="profil" <?= $active_tab !== 'profil' ? 'hidden' : '' ?>>
                        <div class="adm-card-head">
                            <div>
                                <h2 class="adm-card-title">Informations du profil</h2>
                                <p class="adm-card-sub">Le nom signe vos articles ; l'e-mail sert à vous connecter.</p>
                            </div>
                        </div>
                        <div class="adm-card-body">
                            <form method="post" autocomplete="off">
                                <?= csrf_field() ?>
                                <div class="adm-form-row mb-4">
                                    <div>
                                        <label for="profile_name" class="adm-label">Nom complet</label>
                                        <input type="text" id="profile_name" name="name" value="<?= htmlspecialchars($me['name']) ?>" required class="adm-input">
                                    </div>
                                    <div>
                                        <label for="profile_email" class="adm-label">Adresse e-mail</label>
                                        <input type="email" id="profile_email" name="email" value="<?= htmlspecialchars($me['email']) ?>" required class="adm-input">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <span class="adm-label">Rôle</span>
                                    <div class="flex items-center gap-2">
                                        <span class="adm-badge <?= ($me['role'] ?? '') === 'admin' ? 'adm-badge-blue' : 'adm-badge-gray' ?>"><?= htmlspecialchars($role_label) ?></span>
                                        <span class="text-xs text-gray-400">Ce champ se modifie depuis la page Utilisateurs.</span>
                                    </div>
                                </div>
                                <button type="submit" name="update_profile" class="adm-btn adm-btn-primary">
                                    <i class="ph ph-floppy-disk"></i> Enregistrer les informations
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Sécurité -->
                    <div class="adm-card" data-settings-panel="securite" <?= $active_tab !== 'securite' ? 'hidden' : '' ?>>
                        <div class="adm-card-head">
                            <div class="flex items-center gap-3">
                                <span class="adm-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
                                <div>
                                    <h2 class="adm-card-title">Mot de passe</h2>
                                    <p class="adm-card-sub">Connecté en tant que <?= htmlspecialchars($me['email']) ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="adm-card-body">
                            <form method="post" autocomplete="off" id="passwordForm">
                                <?= csrf_field() ?>

                                <div class="mb-4">
                                    <label for="current_password" class="adm-label">Mot de passe actuel</label>
                                    <div class="adm-field">
                                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="adm-input">
                                        <button type="button" class="adm-eye-btn" onclick="togglePw(this)" aria-label="Afficher le mot de passe">
                                            <i class="ph ph-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="new_password" class="adm-label">Nouveau mot de passe</label>
                                    <div class="adm-field">
                                        <input type="password" id="new_password" name="new_password" required minlength="10" autocomplete="new-password" class="adm-input" aria-describedby="pwLabel">
                                        <button type="button" class="adm-eye-btn" onclick="togglePw(this)" aria-label="Afficher le mot de passe">
                                            <i class="ph ph-eye"></i>
                                        </button>
                                    </div>
                                    <div class="adm-strength" aria-hidden="true"><span id="pwBar"></span></div>
                                    <p id="pwLabel" class="text-xs text-gray-500 mt-1">Choisissez un mot de passe solide.</p>
                                    <ul class="adm-check-list">
                                        <li data-rule="length"><i class="ph ph-circle"></i> Au moins 10 caractères</li>
                                        <li data-rule="upper"><i class="ph ph-circle"></i> Une majuscule</li>
                                        <li data-rule="lower"><i class="ph ph-circle"></i> Une minuscule</li>
                                        <li data-rule="digit"><i class="ph ph-circle"></i> Un chiffre</li>
                                        <li data-rule="special"><i class="ph ph-circle"></i> Un caractère spécial (!, @, #…)</li>
                                    </ul>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="adm-label">Confirmer le nouveau mot de passe</label>
                                    <div class="adm-field">
                                        <input type="password" id="confirm_password" name="confirm_password" required minlength="10" autocomplete="new-password" class="adm-input">
                                        <button type="button" class="adm-eye-btn" onclick="togglePw(this)" aria-label="Afficher le mot de passe">
                                            <i class="ph ph-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="adm-btn adm-btn-primary">
                                    <i class="ph ph-lock-key"></i> Enregistrer le nouveau mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </div>

            </div>
        </main>
    </div>

    <script>
    function togglePw(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'ph ph-eye-slash';
            btn.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
            input.type = 'password';
            icon.className = 'ph ph-eye';
            btn.setAttribute('aria-label', 'Afficher le mot de passe');
        }
    }

    (function () {
        // Onglets Profil / Sécurité
        const tabs = Array.from(document.querySelectorAll('[data-settings-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const name = tab.dataset.settingsTab;
                tabs.forEach(function (t) {
                    const on = t === tab;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                panels.forEach(function (p) {
                    p.hidden = p.dataset.settingsPanel !== name;
                });
            });
        });

        // Force du mot de passe
        const pw = document.getElementById('new_password');
        const bar = document.getElementById('pwBar');
        const label = document.getElementById('pwLabel');
        const rules = {
            length:  function (v) { return v.length >= 10; },
            upper:   function (v) { return /[A-Z]/.test(v); },
            lower:   function (v) { return /[a-z]/.test(v); },
            digit:   function (v) { return /[0-9]/.test(v); },
            special: function (v) { return /[^A-Za-z0-9]/.test(v); }
        };
        function updateStrength() {
            if (!pw || !bar) return;
            const value = pw.value || '';
            let score = 0;
            Object.keys(rules).forEach(function (key) {
                const ok = rules[key](value);
                if (ok) score++;
                const li = document.querySelector('[data-rule="' + key + '"]');
                if (li) {
                    li.classList.toggle('is-ok', ok);
                    const icon = li.querySelector('i');
                    if (icon) icon.className = ok ? 'ph ph-check-circle' : 'ph ph-circle';
                }
            });
            const pct = (score / 5) * 100;
            bar.style.width = pct + '%';
            bar.style.background = score <= 2 ? '#f04438' : (score <= 4 ? '#f79009' : '#12b76a');
            if (label) {
                label.textContent = value === '' ? 'Choisissez un mot de passe solide.'
                    : score <= 2 ? 'Mot de passe faible'
                    : score <= 4 ? 'Mot de passe moyen'
                    : 'Mot de passe solide';
            }
        }
        if (pw) {
            pw.addEventListener('input', updateStrength);
            updateStrength();
        }
    })();
    </script>
</body>
</html>

<?php
require_once "../config/config.php";
require_once "../config/payment.php";
require_once "../includes/functions.php";
require_once "../includes/courses.php";

$page_title = "Acheter un cours - Joie Enseignante";

$course_id = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$item_id   = isset($_GET['item']) ? (int)$_GET['item'] : 0;

$purchase_type = '';
$target_id     = 0;
$amount        = 0.0;
$title         = '';
$subtitle      = '';
$back_url      = 'cours.php';

// ─── Achat d'un chapitre seul ───
if ($item_id > 0) {
    $stmt = $pdo->prepare("
        SELECT ci.*,
               c.id_course AS c_id, c.title AS c_title, c.slug AS c_slug,
               c.price AS c_price, c.status AS c_status
        FROM course_items ci
        JOIN courses c ON c.id_course = ci.id_course
        WHERE ci.id_item = ?
        LIMIT 1
    ");
    $stmt->execute([$item_id]);
    $row = $stmt->fetch();

    if ($row && $row['c_status'] === 'published') {
        $course = [
            'id_course' => (int)$row['c_id'],
            'title'     => $row['c_title'],
            'slug'      => $row['c_slug'],
            'price'     => (float)$row['c_price'],
        ];
        $back_url = course_url($course);

        // Chapitre gratuit, en aperçu, ou cours entier gratuit : rien à payer.
        if (!course_item_is_free($course, $row) && (float)$row['price'] > 0) {
            $purchase_type = 'item';
            $target_id     = (int)$row['id_item'];
            $amount        = (float)$row['price'];
            $title         = $row['title'];
            $subtitle      = $row['c_title'];
        }
    }

// ─── Achat du cours complet ───
} else {
    $course = course_find_published($pdo, $course_id);
    if ($course) {
        $back_url = course_url($course);
    }
    if ($course && !course_is_free($course)) {
        $purchase_type = 'course';
        $target_id     = (int)$course['id_course'];
        $amount        = (float)$course['price'];
        $title         = $course['title'];
        $subtitle      = 'Cours complet';
    }
}

if ($purchase_type === '') {
    header('Location: ' . $back_url);
    exit;
}

$cfg      = payment_config();
$provider = payment_provider();
$errors   = [];
$purchase = null;

// Même règle que pour les documents : on n'affiche le paiement que si les
// clés sont présentes et que le prestataire encaisse dans une fenêtre intégrée.
$paymentsReady = payment_is_configured() && payment_supports_widget();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide. Rechargez la page.";
    } else {
        $name  = trim($_POST['buyer_name'] ?? '');
        $email = trim($_POST['buyer_email'] ?? '');
        $phone = trim($_POST['buyer_phone'] ?? '');

        if ($name === '' || $email === '' || $phone === '') {
            $errors[] = "Veuillez remplir votre nom, votre email et votre téléphone.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Adresse email invalide.";
        } else {
            if ($purchase_type === 'course') {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO purchases (id_course, buyer_name, buyer_email, buyer_phone, amount, currency, provider, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
            } else {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO purchases (id_item, buyer_name, buyer_email, buyer_phone, amount, currency, provider, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
            }
            $stmt_insert->execute([
                $target_id, $name, $email, $phone,
                $amount, $cfg['currency'], $provider,
            ]);

            $purchase = [
                'id'    => (int)$pdo->lastInsertId(),
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
            ];
        }
    }
}

include "../includes/header.php";
?>

<div class="max-w-3xl mx-auto px-4 py-12">
    <a href="<?= htmlspecialchars($back_url) ?>" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary transition mb-6">
        <i class="ph ph-arrow-left"></i> Retour au cours
    </a>

    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-6 sm:p-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
            <?= $purchase_type === 'course' ? 'Acheter le cours complet' : 'Acheter ce chapitre' ?>
        </h1>
        <p class="text-gray-500 dark:text-dark-300 mb-8">Paiement sécurisé par Mobile Money ou carte bancaire.</p>

        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-dark rounded-xl border border-gray-200 dark:border-dark-100 mb-8">
            <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-primary-100 text-primary-800 flex items-center justify-center">
                <i class="ph <?= $purchase_type === 'course' ? 'ph-graduation-cap' : 'ph-file-text' ?> text-2xl"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($title) ?></p>
                <p class="text-sm text-gray-500 dark:text-dark-300"><?= htmlspecialchars($subtitle) ?></p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xl font-extrabold text-primary-600 dark:text-primary-400"><?= payment_amount_label($amount) ?></p>
            </div>
        </div>

        <?php if ($errors): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl mb-6" role="alert">
            <?php foreach ($errors as $err): ?><p><i class="ph ph-warning-circle"></i> <?= htmlspecialchars($err) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!$paymentsReady): ?>
            <div class="bg-amber-50 text-amber-800 border border-amber-200 rounded-xl p-5 mb-6">
                <p class="font-semibold mb-1"><i class="ph ph-info"></i> Paiement en ligne bientôt disponible</p>
                <p class="text-sm">Le module de paiement n'est pas encore activé. Contactez-nous pour obtenir l'accès à ce cours.</p>
            </div>
            <a href="contact.php" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                <i class="ph ph-envelope"></i> Nous contacter
            </a>

        <?php elseif ($purchase): ?>
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-5 mb-6">
                <p class="font-semibold"><i class="ph ph-check-circle"></i> Commande enregistrée</p>
                <p class="text-sm">Cliquez sur le bouton ci-dessous pour payer <?= payment_amount_label($amount) ?>.</p>
            </div>
            <button type="button" onclick="payerMaintenant()" class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-8 py-4 rounded-xl transition shadow-lg">
                <i class="ph ph-credit-card"></i> Payer maintenant
            </button>

        <?php else: ?>
            <form method="post" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label for="buyer_name" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Nom complet</label>
                    <input type="text" id="buyer_name" name="buyer_name" required class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="buyer_email" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Email</label>
                        <input type="email" id="buyer_email" name="buyer_email" required class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="buyer_phone" class="block text-sm font-medium text-gray-700 dark:text-dark-400 mb-1.5">Téléphone</label>
                        <input type="tel" id="buyer_phone" name="buyer_phone" required placeholder="+229 ..." class="w-full border border-gray-300 dark:border-dark-100 dark:bg-dark-100/40 dark:text-white rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-dark-300">
                    <i class="ph ph-info"></i>
                    Après le paiement, un lien privé vous est envoyé par e-mail. Aucun compte à créer.
                </p>
                <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                    <i class="ph ph-arrow-right"></i> Continuer vers le paiement
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($paymentsReady && $purchase): ?>
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
var achatId = <?= (int)$purchase['id'] ?>;
function payerMaintenant() {
    openKkiapayWidget({
        amount: <?= (int)round($amount) ?>,
        key: <?= json_encode($cfg['kkiapay']['public_key']) ?>,
        sandbox: <?= $cfg['sandbox'] ? 'true' : 'false' ?>,
        position: "center",
        theme: "#2563eb",
        name: <?= json_encode($purchase['name']) ?>,
        email: <?= json_encode($purchase['email']) ?>,
        phone: <?= json_encode($purchase['phone']) ?>,
        data: String(achatId),
        callback: ""
    });
}
addSuccessListener(function (response) {
    window.location.href = "payment_callback.php?purchase=" + achatId + "&transaction=" + encodeURIComponent(response.transactionId);
});
addFailedListener(function () {
    alert("Le paiement n'a pas abouti. Vous pouvez réessayer.");
});
</script>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>

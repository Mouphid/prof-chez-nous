<?php
require_once "../config/config.php";
require_once "../config/payment.php";

$page_title = "Acheter un document - Joie Enseignante";

$file_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT f.*, p.title AS post_title, p.id_post
    FROM files f
    LEFT JOIN posts p ON p.id_post = f.id_post
    WHERE f.id_file = ?
");
$stmt->execute([$file_id]);
$file = $stmt->fetch();

if (!$file || (float)$file['price'] <= 0) {
    header("Location: publications.php");
    exit;
}

$cfg      = payment_config();
$provider = payment_provider();
$errors   = [];
$purchase = null;

// Le paiement n'est proposé que si les clés sont présentes ET que le
// prestataire peut encaisser directement sur le site (fenêtre intégrée).
// Pour un prestataire à redirection (FedaPay), il faudra brancher ici
// la création de la page de paiement ; en attendant, le site affiche
// proprement « paiement bientôt disponible » plutôt qu'un bouton inactif.
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
            $stmt_insert = $pdo->prepare("
                INSERT INTO purchases (id_file, buyer_name, buyer_email, buyer_phone, amount, currency, provider, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt_insert->execute([
                $file_id, $name, $email, $phone,
                (float)$file['price'], $cfg['currency'], $provider,
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
    <a href="<?= $file['id_post'] ? 'post.php?id=' . (int)$file['id_post'] : 'publications.php' ?>" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-primary transition mb-6">
        <i class="ph ph-arrow-left"></i> Retour
    </a>

    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-6 sm:p-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">Acheter ce document</h1>
        <p class="text-gray-500 dark:text-dark-300 mb-8">Paiement sécurisé par Mobile Money ou carte bancaire.</p>

        <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-dark rounded-xl border border-gray-200 dark:border-dark-100 mb-8">
            <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-primary-100 text-primary-800 flex items-center justify-center">
                <i class="ph ph-file-pdf text-2xl"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($file['file_name']) ?></p>
                <p class="text-sm text-gray-500 dark:text-dark-300"><?= htmlspecialchars($file['post_title'] ?? 'Document') ?></p>
            </div>
            <div class="ml-auto text-right">
                <p class="text-xl font-extrabold text-primary-600 dark:text-primary-400"><?= payment_amount_label((float)$file['price']) ?></p>
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
                <p class="text-sm">Le module de paiement n'est pas encore activé. Contactez-nous pour obtenir ce document.</p>
            </div>
            <a href="contact.php" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                <i class="ph ph-envelope"></i> Nous contacter
            </a>

        <?php elseif ($purchase): ?>
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-5 mb-6">
                <p class="font-semibold"><i class="ph ph-check-circle"></i> Commande enregistrée</p>
                <p class="text-sm">Cliquez sur le bouton ci-dessous pour payer <?= payment_amount_label((float)$file['price']) ?>.</p>
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
        amount: <?= (int)round((float)$file['price']) ?>,
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

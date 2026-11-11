<?php
/**
 * Page de comparaison des logos (temporaire).
 * Accessible uniquement à l'administrateur connecté.
 * Sert à choisir la bonne variante sur fond clair, sombre et bleu nuit.
 */
require_once "../config/config.php";
require_once "../includes/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$page_title = "Comparaison des logos - Joie Enseignante";

$variants = [
    'logo-gris.png'  => 'Gris (doux)',
    'logo-noir.png'  => 'Noir (contrasté)',
    'logo-blanc.png' => 'Blanc',
];

$backgrounds = [
    ['Clair — en-tête du site', 'background:#ffffff;color:#111827'],
    ['Gris très clair — pied de page', 'background:#f9fafb;color:#111827'],
    ['Sombre — thème nuit', 'background:#111827;color:#ffffff'],
    ['Bleu nuit — barre admin', 'background:#1e3a5f;color:#ffffff'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <?php cdn_head(); ?>
</head>
<body class="bg-white font-sans text-gray-800 p-6 sm:p-10">

<h1 class="text-2xl font-bold mb-2">Comparaison des logos</h1>
<p class="text-gray-500 mb-8 max-w-3xl">
    Regardez chaque ligne et dites-moi laquelle vous préférez pour le site clair,
    le site sombre et l’administration. La page n’est visible que par vous.
</p>

<?php foreach ($backgrounds as [$label, $style]): ?>
<section class="mb-8 rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-2 bg-gray-100 text-sm font-semibold text-gray-700"><?= htmlspecialchars($label) ?></div>
    <div class="p-6 flex flex-wrap items-center gap-10" style="<?= $style ?>">
        <?php foreach ($variants as $file => $name): ?>
            <div class="text-center">
                <img src="../img/<?= $file ?>" alt="<?= htmlspecialchars($name) ?>" class="h-14 w-auto mx-auto">
                <p class="text-xs mt-2 opacity-70"><?= htmlspecialchars($name) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<h2 class="text-xl font-bold mt-10 mb-2">Favicons (taille réelle)</h2>
<p class="text-gray-500 mb-4">Rendu dans l’onglet du navigateur, sur fond clair et sur fond sombre.</p>

<div class="flex flex-wrap gap-8 items-end">
    <?php foreach ([16, 32, 48, 192] as $size): ?>
        <?php $file = $size === 192 ? 'favicon-192.png' : "favicon-$size.png"; ?>
        <div class="text-center">
            <div class="bg-white border border-gray-200 rounded-lg p-3 inline-flex items-center justify-center">
                <img src="../img/<?= $file ?>" width="<?= $size ?>" height="<?= $size ?>" alt="Favicon <?= $size ?>">
            </div>
            <div class="bg-[#202124] rounded-lg p-3 mt-2 inline-flex items-center justify-center">
                <img src="../img/<?= $file ?>" width="<?= $size ?>" height="<?= $size ?>" alt="">
            </div>
            <p class="text-xs mt-1 text-gray-500"><?= $size ?> px</p>
        </div>
    <?php endforeach; ?>
</div>

<p class="mt-10 text-sm text-gray-500">
    <a href="dashboard.php" class="text-primary hover:underline">← Retour au tableau de bord</a>
</p>

</body>
</html>

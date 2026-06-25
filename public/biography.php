<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Biographie - Joie Enseignante";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();

include "../includes/header.php";
?>

<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900"><i class="ph ph-user-tie text-primary"></i> Biographie</h1>
        <p class="text-gray-500 mt-2">Découvrez le parcours du fondateur</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-12">
        <div class="flex flex-col sm:flex-row items-start gap-8">
            <div class="w-28 h-28 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl flex items-center justify-center text-primary text-5xl flex-shrink-0 mx-auto sm:mx-0">
                <i class="ph ph-user-tie"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($admin['name'] ?? 'Professeur') ?></h2>
                <p class="text-sm text-primary font-medium mb-6">Fondateur de Joie Enseignante</p>

                <div class="prose prose-gray max-w-none space-y-4">
                    <p class="text-gray-600 leading-relaxed">
                        Passionné par l'éducation et la transmission du savoir, j'ai consacré ma carrière à 
                        l'enseignement et à l'accompagnement des étudiants. Fort de plusieurs années d'expérience 
                        dans le domaine éducatif, j'ai fondé <strong class="text-primary">Joie Enseignante</strong> 
                        avec la conviction que l'apprentissage doit être accessible, moderne et inspirant.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Ma vision est de créer un pont entre les enseignants et les étudiants, en offrant une 
                        plateforme où le partage de connaissances devient une expérience enrichissante pour tous. 
                        Chaque cours, chaque ressource, chaque échange est une opportunité de grandir ensemble.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Au-delà de l'enseignement, je crois fermement au pouvoir de l'éducation comme moteur 
                        de développement personnel et collectif. Rejoignez-moi dans cette aventure et contribuons 
                        ensemble à bâtir une communauté d'apprentissage dynamique et bienveillante.
                    </p>
                </div>

                <?php if ($admin && $admin['email']): ?>
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-sm text-gray-400 flex items-center gap-2">
                        <i class="ph ph-envelope"></i> <?= htmlspecialchars($admin['email']) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

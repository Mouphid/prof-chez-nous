<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "À propos - Joie Enseignante";

$admin = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1")->fetch();

include "../includes/header.php";
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900"><i class="fas fa-info-circle text-primary"></i> À propos</h1>
        <p class="text-gray-500 mt-2">Découvrez notre mission et ceux qui font vivre cette plateforme</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center text-primary text-2xl mb-4"><i class="fas fa-handshake"></i></div>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Qui sommes-nous ?</h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                <strong class="text-primary">Joie Enseignante</strong> est une plateforme pédagogique dédiée aux enseignants et aux étudiants. 
                Notre mission est de faciliter le partage de connaissances et de ressources éducatives de qualité.
            </p>
            <p class="text-gray-600 leading-relaxed mb-4">
                Nous croyons que l'éducation est la clé du développement et que chaque apprenant mérite 
                d'avoir accès à des contenus pédagogiques riches et variés.
            </p>
            <p class="text-gray-600 leading-relaxed">
                Que vous soyez enseignant souhaitant partager vos cours ou étudiant à la recherche de 
                ressources pour approfondir vos connaissances, notre plateforme est conçue pour vous.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center text-primary text-2xl mb-4"><i class="fas fa-bullseye"></i></div>
            <h2 class="text-xl font-bold text-gray-900 mb-4">Notre mission</h2>
            <ul class="space-y-4">
                <li class="flex gap-3">
                    <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                    <div><strong class="text-gray-900">Accessibilité</strong><p class="text-sm text-gray-500">Rendre l'éducation accessible à tous, partout et à tout moment.</p></div>
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                    <div><strong class="text-gray-900">Qualité</strong><p class="text-sm text-gray-500">Proposer des ressources pédagogiques fiables et bien structurées.</p></div>
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                    <div><strong class="text-gray-900">Partage</strong><p class="text-sm text-gray-500">Créer une communauté d'apprentissage où savoir rime avec partage.</p></div>
                </li>
                <li class="flex gap-3">
                    <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                    <div><strong class="text-gray-900">Innovation</strong><p class="text-sm text-gray-500">Utiliser les technologies modernes pour améliorer l'expérience d'apprentissage.</p></div>
                </li>
            </ul>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="w-24 h-24 bg-indigo-50 rounded-xl flex items-center justify-center text-primary text-4xl flex-shrink-0">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Biographie</h2>
                    <h3 class="text-lg font-semibold text-primary mb-3"><?= htmlspecialchars($admin['name'] ?? 'Professeur') ?></h3>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Fondateur de <strong class="text-primary">Joie Enseignante</strong>, 
                        passionné par l'éducation et la transmission du savoir. 
                        Avec plusieurs années d'expérience dans l'enseignement, j'ai créé cette plateforme 
                        pour offrir un espace d'apprentissage moderne et accessible à tous.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Ma vision est de bâtir une communauté éducative dynamique où enseignants et étudiants 
                        peuvent échanger, collaborer et grandir ensemble. Chaque ressource partagée est une 
                        opportunité d'apprendre et de s'améliorer.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        Rejoignez-nous dans cette aventure éducative et contribuez à faire de l'éducation 
                        un levier de développement pour tous.
                    </p>
                    <?php if ($admin && $admin['email']): ?>
                    <p class="mt-4 text-sm text-gray-400"><i class="fas fa-envelope"></i> <?= htmlspecialchars($admin['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

<?php
require_once "../config/config.php";
require_once "../includes/functions.php";
require_once "../includes/courses.php";

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug  = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

$course = course_find_published($pdo, $id, $slug);

if (!$course) {
    http_response_code(404);
    $page_title = "Cours introuvable - Joie Enseignante";
    include "../includes/header.php";
    ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
        <i class="ph ph-graduation-cap text-5xl text-gray-300 dark:text-dark-200 block mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ce cours n'existe pas ou n'est plus disponible</h1>
        <p class="text-gray-500 dark:text-dark-300 mb-6">Il a peut-être été retiré du site.</p>
        <a href="cours.php" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            <i class="ph ph-arrow-left"></i> Voir tous les cours
        </a>
    </div>
    <?php
    include "../includes/footer.php";
    exit;
}

$items = course_items($pdo, (int)$course['id_course']);

// Jeton du lien privé : ne donne accès qu'aux chapitres de CE cours.
$purchase = course_purchase_by_token($pdo, $token);
if ($purchase) {
    $belongs = ((int)($purchase['id_course'] ?? 0) === (int)$course['id_course'])
        || in_array((int)($purchase['id_item'] ?? 0), array_map(fn($i) => (int)$i['id_item'], $items), true);
    if (!$belongs) {
        $purchase = null;
    }
}

$free        = course_is_free($course);
$full_access = $free
    || ($purchase && (int)($purchase['id_course'] ?? 0) === (int)$course['id_course']);

$objectifs = course_objectives($course);
$page_title = $course['title'] . " - Cours - Joie Enseignante";
$page_description = mb_substr(trim((string)$course['description']), 0, 160);
?>
<?php include "../includes/header.php"; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
    <nav class="text-sm text-gray-500 dark:text-dark-300 mb-6" aria-label="Fil d'Ariane">
        <a href="cours.php" class="hover:text-primary transition">Cours</a>
        <span class="mx-2 text-gray-300 dark:text-dark-200">/</span>
        <span class="text-gray-700 dark:text-dark-400"><?= htmlspecialchars($course['title']) ?></span>
    </nav>

    <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-6 sm:p-10 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10 flex flex-wrap items-center gap-5">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="ph <?= htmlspecialchars($course['icon'] ?: 'ph-book-open') ?> text-3xl"></i>
            </div>
            <div class="min-w-0">
                <?php if ($course['code']): ?>
                <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-1"><?= htmlspecialchars($course['code']) ?></span>
                <?php endif; ?>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-display"><?= htmlspecialchars($course['title']) ?></h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/80 mt-2">
                    <?php if ($course['level']): ?><span><i class="ph ph-users"></i> <?= htmlspecialchars($course['level']) ?></span><?php endif; ?>
                    <?php if ($course['duration']): ?><span><i class="ph ph-clock"></i> <?= htmlspecialchars($course['duration']) ?></span><?php endif; ?>
                    <span><i class="ph ph-list-bullets"></i> <?= count($items) ?> chapitre<?= count($items) > 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="sm:ml-auto text-left sm:text-right">
                <span class="inline-block bg-white text-primary-700 text-lg font-extrabold px-5 py-2.5 rounded-full">
                    <?= course_price_label((float)$course['price']) ?>
                </span>
            </div>
        </div>
    </div>

    <?php if ($purchase): ?>
    <div class="mt-6 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl px-5 py-4 text-sm flex items-start gap-3" role="status">
        <i class="ph ph-check-circle text-xl flex-shrink-0"></i>
        <div>
            <p class="font-semibold">Votre accès est confirmé</p>
            <p><?= $full_access ? 'Vous pouvez ouvrir tous les chapitres de ce cours.' : 'Vous pouvez ouvrir le chapitre que vous avez acheté.' ?> Conservez précieusement le lien reçu par e-mail.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-8 mt-8">
        <div class="lg:col-span-2 space-y-8">
            <section>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><i class="ph ph-info text-primary-500"></i> Description</h2>
                <p class="text-gray-600 dark:text-dark-400 leading-relaxed"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
            </section>

            <?php if ($objectifs): ?>
            <section>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><i class="ph ph-target text-primary-500"></i> Objectifs</h2>
                <ul class="space-y-2">
                    <?php foreach ($objectifs as $obj): ?>
                    <li class="flex items-start gap-2.5 text-gray-600 dark:text-dark-400">
                        <i class="ph ph-check-circle text-emerald-500 mt-1 flex-shrink-0"></i>
                        <span><?= htmlspecialchars($obj) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <section id="chapitres" class="scroll-mt-24">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Chapitres</h2>

                <?php if (!$items): ?>
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-8 text-center text-gray-500 dark:text-dark-300">
                    <i class="ph ph-list-bullets text-3xl text-gray-300 dark:text-dark-200 block mb-2"></i>
                    Les chapitres seront ajoutés prochainement.
                </div>
                <?php endif; ?>

                <div class="space-y-3">
                    <?php foreach ($items as $ci => $item): ?>
                    <?php
                    $unlocked = course_item_is_unlocked($course, $item, $purchase);
                    $item_free = course_item_is_free($course, $item);
                    $has_file = !empty($item['pdf_path']) || !empty($item['video_path']);
                    $file_token = $unlocked && $purchase ? '&token=' . rawurlencode($token) : '';
                    ?>
                    <div class="bg-white dark:bg-dark-50 rounded-2xl border <?= $unlocked ? 'border-gray-100 dark:border-dark-100' : 'border-gray-200 dark:border-dark-100' ?> p-5">
                        <div class="flex items-start gap-4">
                            <span class="w-10 h-10 flex-shrink-0 rounded-xl <?= $unlocked ? 'bg-gradient-to-br from-primary-500 to-primary-700 text-white' : 'bg-gray-100 dark:bg-dark text-gray-500 dark:text-dark-300' ?> text-sm font-bold flex items-center justify-center">
                                <?= str_pad((string)($ci + 1), 2, '0', STR_PAD_LEFT) ?>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($item['title']) ?></h3>
                                    <?php if ($item_free && !$free): ?>
                                    <span class="text-[11px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Aperçu gratuit</span>
                                    <?php endif; ?>
                                    <?php if ($item['pdf_path']): ?>
                                    <span class="text-[11px] text-gray-500 dark:text-dark-300 inline-flex items-center gap-1"><i class="ph ph-file-pdf text-red-500"></i> PDF</span>
                                    <?php endif; ?>
                                    <?php if ($item['video_path']): ?>
                                    <span class="text-[11px] text-gray-500 dark:text-dark-300 inline-flex items-center gap-1"><i class="ph ph-video text-blue-500"></i> Vidéo</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($item['description']): ?>
                                <p class="text-sm text-gray-500 dark:text-dark-300 mt-1"><?= htmlspecialchars($item['description']) ?></p>
                                <?php endif; ?>

                                <div class="flex flex-wrap items-center gap-2 mt-3">
                                    <?php if ($unlocked): ?>
                                        <?php if ($item['pdf_path']): ?>
                                        <a href="course_file.php?item=<?= (int)$item['id_item'] ?>&type=pdf<?= $file_token ?>" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 hover:bg-primary-100 text-xs font-semibold px-3 py-2 rounded-lg transition">
                                            <i class="ph ph-file-pdf"></i> Ouvrir le PDF
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($item['video_path']): ?>
                                        <a href="course_file.php?item=<?= (int)$item['id_item'] ?>&type=video<?= $file_token ?>" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-dark text-gray-700 dark:text-dark-200 hover:bg-gray-200 text-xs font-semibold px-3 py-2 rounded-lg transition">
                                            <i class="ph ph-play-circle"></i> Regarder la vidéo
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!$has_file): ?>
                                        <span class="text-xs text-gray-400 dark:text-dark-300"><i class="ph ph-hourglass"></i> Contenu en préparation</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-dark-300 mr-1">
                                            <i class="ph ph-lock-simple"></i> Réservé à l'achat
                                        </span>
                                        <?php if ((float)$item['price'] > 0): ?>
                                        <a href="purchase_course.php?item=<?= (int)$item['id_item'] ?>"
                                           class="inline-flex items-center gap-1.5 bg-accent-500 hover:bg-accent-600 text-white text-xs font-bold px-3 py-2 rounded-lg transition">
                                            <i class="ph ph-shopping-cart"></i> Acheter ce chapitre — <?= course_price_label((float)$item['price']) ?>
                                        </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <i class="ph <?= $unlocked ? 'ph-lock-open text-emerald-500' : 'ph-lock-simple text-gray-400' ?> text-lg mt-1 flex-shrink-0" aria-hidden="true"></i>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside>
            <div class="sticky top-24 space-y-4">
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2"><i class="ph ph-chart-line text-primary-500"></i> Détails du cours</h2>
                    <ul class="space-y-3 text-sm">
                        <?php if ($course['duration']): ?>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-clock text-primary-500"></i> <span><strong>Volume :</strong> <?= htmlspecialchars($course['duration']) ?></span></li>
                        <?php endif; ?>
                        <?php if ($course['level']): ?>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-users text-primary-500"></i> <span><strong>Niveau :</strong> <?= htmlspecialchars($course['level']) ?></span></li>
                        <?php endif; ?>
                        <?php if ($course['semester']): ?>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-calendar text-primary-500"></i> <span><strong>Semestre :</strong> <?= htmlspecialchars($course['semester']) ?></span></li>
                        <?php endif; ?>
                        <?php if ($course['credits']): ?>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-file-text text-primary-500"></i> <span><strong>Crédits :</strong> <?= htmlspecialchars($course['credits']) ?></span></li>
                        <?php endif; ?>
                        <li class="flex items-center gap-3 text-gray-600 dark:text-dark-400"><i class="ph ph-list-bullets text-primary-500"></i> <span><strong>Chapitres :</strong> <?= count($items) ?></span></li>
                    </ul>
                </div>

                <?php if ($free): ?>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5">
                    <p class="font-bold text-emerald-800 dark:text-emerald-300 mb-1"><i class="ph ph-check-circle"></i> Cours gratuit</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-400">Tous les chapitres sont accessibles librement.</p>
                    <?php if ($items): ?>
                    <a href="#chapitres" class="mt-4 inline-flex w-full items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-3 rounded-xl transition">
                        Commencer <i class="ph ph-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>

                <?php elseif ($full_access): ?>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-5">
                    <p class="font-bold text-emerald-800 dark:text-emerald-300 mb-1"><i class="ph ph-check-circle"></i> Accès complet</p>
                    <p class="text-sm text-emerald-700 dark:text-emerald-400">Ce lien vous donne accès à tous les chapitres du cours.</p>
                    <?php if ($items): ?>
                    <a href="#chapitres" class="mt-4 inline-flex w-full items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-3 rounded-xl transition">
                        Ouvrir les chapitres <i class="ph ph-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>

                <?php elseif ((float)$course['price'] > 0): ?>
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-5">
                    <p class="text-sm text-gray-500 dark:text-dark-300">Cours complet</p>
                    <p class="text-2xl font-extrabold text-primary-600 dark:text-primary-400 mb-4"><?= course_price_label((float)$course['price']) ?></p>
                    <a href="purchase_course.php?course=<?= (int)$course['id_course'] ?>"
                       class="inline-flex w-full items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-5 py-3.5 rounded-xl transition shadow-sm">
                        <i class="ph ph-shopping-cart"></i> Acheter le cours complet
                    </a>
                    <p class="text-xs text-gray-500 dark:text-dark-300 mt-3">
                        <i class="ph ph-info"></i>
                        Après le paiement, vous recevez un lien privé par e-mail pour ouvrir les chapitres. Aucun compte à créer.
                    </p>
                    <p class="text-xs text-gray-500 dark:text-dark-300 mt-2">
                        Vous pouvez aussi acheter un chapitre seul, plus bas dans la liste.
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include "../includes/footer.php"; ?>

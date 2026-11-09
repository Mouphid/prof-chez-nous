<?php
/**
 * public/chapter.php — Lecture d'un chapitre directement sur la plateforme.
 *
 * Affiche le titre, le descriptif et le contenu rédigé dans l'administration,
 * puis le PDF et/ou la vidéo associés. L'accès respecte les règles du cours :
 * chapitre gratuit, aperçu gratuit, achat du chapitre ou achat du cours complet.
 */
require_once "../config/config.php";
require_once "../includes/functions.php";
require_once "../includes/courses.php";

$item_id = isset($_GET['item']) ? (int)$_GET['item'] : 0;
$token   = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

if (!$item_id) {
    http_response_code(404);
    $page_title = "Chapitre introuvable - Joie Enseignante";
    include "../includes/header.php";
    ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
        <i class="ph ph-book-open text-5xl text-gray-300 dark:text-dark-200 block mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ce chapitre n'existe pas</h1>
        <a href="cours.php" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
            <i class="ph ph-arrow-left"></i> Voir tous les cours
        </a>
    </div>
    <?php
    include "../includes/footer.php";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM course_items WHERE id_item = ? LIMIT 1");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

$course = $item ? course_find_published($pdo, (int)$item['id_course']) : null;

if (!$item || !$course) {
    http_response_code(404);
    $page_title = "Chapitre introuvable - Joie Enseignante";
    include "../includes/header.php";
    ?>
    <div class="max-w-3xl mx-auto px-4 py-20 text-center">
        <i class="ph ph-book-open text-5xl text-gray-300 dark:text-dark-200 block mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ce chapitre n'est plus disponible</h1>
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

$free         = course_is_free($course);
$full_access  = $free || ($purchase && (int)($purchase['id_course'] ?? 0) === (int)$course['id_course']);
$unlocked     = course_item_is_unlocked($course, $item, $purchase);
$item_free    = course_item_is_free($course, $item);
$has_content  = trim((string)($item['content'] ?? '')) !== '';
$has_pdf      = !empty($item['pdf_path']);
$has_video    = !empty($item['video_path']);

// Position du chapitre dans le cours (pour la numérotation et la navigation).
$position = 0;
foreach ($items as $idx => $it) {
    if ((int)$it['id_item'] === (int)$item['id_item']) {
        $position = $idx;
        break;
    }
}
$previous = $position > 0 ? $items[$position - 1] : null;
$following = $items[$position + 1] ?? null;

$link_token = $purchase ? $token : '';
$file_token = $purchase ? '&token=' . rawurlencode($token) : '';
$pdf_url    = $has_pdf ? 'course_file.php?item=' . (int)$item['id_item'] . '&type=pdf' . $file_token : '';
$video_url  = $has_video ? 'course_file.php?item=' . (int)$item['id_item'] . '&type=video' . $file_token : '';

$page_title = $item['title'] . ' - ' . $course['title'] . ' - Joie Enseignante';
$page_description = mb_substr(trim((string)($item['description'] ?: $course['description'])), 0, 160);
?>
<?php include "../includes/header.php"; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
    <nav class="text-sm text-gray-500 dark:text-dark-300 mb-6 flex flex-wrap items-center gap-x-2 gap-y-1" aria-label="Fil d'Ariane">
        <a href="cours.php" class="hover:text-primary transition">Cours</a>
        <span class="text-gray-300 dark:text-dark-200">/</span>
        <a href="<?= htmlspecialchars(course_url($course)) ?>" class="hover:text-primary transition"><?= htmlspecialchars($course['title']) ?></a>
        <span class="text-gray-300 dark:text-dark-200">/</span>
        <span class="text-gray-700 dark:text-dark-400"><?= htmlspecialchars($item['title']) ?></span>
    </nav>

    <!-- ═══ EN-TÊTE DU CHAPITRE ═══ -->
    <header class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-6 sm:p-10 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="ph ph-book-open"></i>
                    Chapitre <?= str_pad((string)($position + 1), 2, '0', STR_PAD_LEFT) ?><?= count($items) ? ' / ' . count($items) : '' ?>
                </span>
                <?php if ($item_free && !$free): ?>
                <span class="inline-flex items-center gap-1.5 bg-emerald-400/20 text-emerald-50 text-xs font-semibold px-3 py-1 rounded-full">
                    <i class="ph ph-gift"></i> Aperçu gratuit
                </span>
                <?php endif; ?>
                <?php if ($has_pdf): ?>
                <span class="inline-flex items-center gap-1.5 bg-white/15 text-white/90 text-xs font-semibold px-3 py-1 rounded-full"><i class="ph ph-file-pdf"></i> PDF</span>
                <?php endif; ?>
                <?php if ($has_video): ?>
                <span class="inline-flex items-center gap-1.5 bg-white/15 text-white/90 text-xs font-semibold px-3 py-1 rounded-full"><i class="ph ph-video"></i> Vidéo</span>
                <?php endif; ?>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold font-display"><?= htmlspecialchars($item['title']) ?></h1>

            <?php if ($item['description']): ?>
            <p class="text-white/80 mt-3 max-w-3xl leading-relaxed"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!$unlocked): ?>
    <!-- ═══ CHAPITRE RÉSERVÉ À L'ACHAT ═══ -->
    <div class="mt-8 max-w-3xl mx-auto bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-8 text-center">
        <span class="w-16 h-16 rounded-2xl bg-accent-100 text-accent-600 flex items-center justify-center mx-auto mb-4">
            <i class="ph ph-lock-simple text-3xl"></i>
        </span>
        <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">Ce chapitre est réservé à l'achat</h2>
        <p class="text-gray-500 dark:text-dark-300 mt-2 text-sm max-w-lg mx-auto">
            Le descriptif est visible librement. Le contenu complet, le PDF et la vidéo s'ouvrent après l'achat,
            avec un lien privé envoyé par e-mail. Aucun compte à créer.
        </p>

        <div class="flex flex-wrap justify-center gap-3 mt-6">
            <?php if ((float)$item['price'] > 0): ?>
            <a href="purchase_course.php?item=<?= (int)$item['id_item'] ?>"
               class="inline-flex items-center gap-2 bg-accent-500 hover:bg-accent-600 text-white font-bold px-6 py-3 rounded-xl transition">
                <i class="ph ph-shopping-cart"></i> Acheter ce chapitre — <?= course_price_label((float)$item['price']) ?>
            </a>
            <?php endif; ?>
            <?php if ((float)$course['price'] > 0): ?>
            <a href="purchase_course.php?course=<?= (int)$course['id_course'] ?>"
               class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white font-bold px-6 py-3 rounded-xl transition">
                <i class="ph ph-graduation-cap"></i> Acheter le cours complet — <?= course_price_label((float)$course['price']) ?>
            </a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars(course_url($course)) ?>"
               class="inline-flex items-center gap-2 border border-gray-200 dark:border-dark-100 text-gray-700 dark:text-dark-300 hover:bg-gray-50 dark:hover:bg-dark-100 font-semibold px-6 py-3 rounded-xl transition">
                <i class="ph ph-arrow-left"></i> Retour au cours
            </a>
        </div>
    </div>

    <?php else: ?>
    <div class="grid lg:grid-cols-3 gap-8 mt-8">
        <div class="lg:col-span-2 space-y-8">
            <?php if ($purchase): ?>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl px-5 py-4 text-sm flex items-start gap-3" role="status">
                <i class="ph ph-check-circle text-xl flex-shrink-0"></i>
                <div>
                    <p class="font-semibold">Votre accès est confirmé</p>
                    <p><?= $full_access ? 'Ce lien ouvre tous les chapitres du cours.' : 'Ce lien ouvre le chapitre que vous avez acheté.' ?> Conservez-le précieusement.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ CONTENU RÉDIGÉ ═══ -->
            <section aria-labelledby="contenu-chapitre">
                <h2 id="contenu-chapitre" class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="ph ph-text-align-left text-primary-500"></i> Contenu du chapitre
                </h2>

                <?php if ($has_content): ?>
                <article class="chapter-content sbr-justify bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-6 sm:p-8">
                    <?= course_content_html($item['content']) ?>
                </article>
                <?php else: ?>
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-8 text-center text-gray-500 dark:text-dark-300">
                    <i class="ph ph-hourglass text-3xl text-gray-300 dark:text-dark-200 block mb-2"></i>
                    Le contenu écrit de ce chapitre sera bientôt disponible.
                </div>
                <?php endif; ?>
            </section>

            <!-- ═══ RESSOURCES : PDF ET VIDÉO ═══ -->
            <?php if ($has_pdf || $has_video): ?>
            <section aria-labelledby="ressources-chapitre">
                <h2 id="ressources-chapitre" class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <i class="ph ph-paperclip text-primary-500"></i> Ressources du chapitre
                </h2>

                <div class="space-y-4">
                    <?php if ($has_pdf): ?>
                    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5 flex flex-wrap items-center gap-4">
                        <span class="w-11 h-11 rounded-xl bg-accent-50 text-accent-600 dark:bg-accent-900/20 dark:text-accent-400 flex items-center justify-center flex-shrink-0">
                            <i class="ph ph-file-pdf text-xl"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">Support PDF</p>
                            <p class="text-xs text-gray-500 dark:text-dark-300">Pour relire le cours ou l'imprimer.</p>
                        </div>
                        <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 hover:bg-primary-100 text-xs font-semibold px-4 py-2.5 rounded-lg transition">
                            <i class="ph ph-file-pdf"></i> Ouvrir le PDF
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($has_video): ?>
                    <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-11 h-11 rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/20 dark:text-primary-400 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-video text-xl"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">Vidéo du chapitre</p>
                                <p class="text-xs text-gray-500 dark:text-dark-300">Lecture directe sur la plateforme, sans téléchargement.</p>
                            </div>
                        </div>
                        <video controls preload="metadata" playsinline class="chapter-video" src="<?= htmlspecialchars($video_url) ?>">
                            Votre navigateur ne peut pas lire cette vidéo.
                            <a href="<?= htmlspecialchars($video_url) ?>" target="_blank" rel="noopener">Ouvrir la vidéo dans un nouvel onglet</a>.
                        </video>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- ═══ NAVIGATION ENTRE CHAPITRES ═══ -->
            <nav class="flex flex-row items-center justify-between gap-3 border-t border-gray-100 dark:border-dark-100 pt-6" aria-label="Navigation entre les chapitres">
                <?php if ($previous): ?>
                <a href="<?= htmlspecialchars(course_item_url($course, $previous, $link_token)) ?>"
                   title="<?= htmlspecialchars($previous['title']) ?>"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-dark-100 bg-white dark:bg-dark-50 text-sm font-semibold text-gray-700 dark:text-dark-300 hover:bg-gray-50 dark:hover:bg-dark-100 hover:text-primary transition shadow-sm">
                    <i class="ph ph-arrow-left text-base"></i>
                    <span>Précédent</span>
                </a>
                <?php else: ?><span></span><?php endif; ?>

                <?php if ($following): ?>
                <a href="<?= htmlspecialchars(course_item_url($course, $following, $link_token)) ?>"
                   title="<?= htmlspecialchars($following['title']) ?>"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold transition shadow-sm ml-auto">
                    <span>Suivant</span>
                    <i class="ph ph-arrow-right text-base"></i>
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <aside>
            <div class="sticky top-24 space-y-4">
                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Cours</p>
                    <a href="<?= htmlspecialchars(course_url($course)) ?>" class="font-bold text-gray-900 dark:text-white hover:text-primary transition">
                        <?= htmlspecialchars($course['title']) ?>
                    </a>
                    <?php if ($course['level'] || $course['duration']): ?>
                    <p class="text-xs text-gray-500 dark:text-dark-300 mt-1">
                        <?php if ($course['level']): ?><i class="ph ph-users"></i> <?= htmlspecialchars($course['level']) ?><?php endif; ?>
                        <?php if ($course['duration']): ?><span class="mx-1">·</span><i class="ph ph-clock"></i> <?= htmlspecialchars($course['duration']) ?><?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <p class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold <?= $free ? 'text-emerald-600' : 'text-primary-600' ?>">
                        <i class="ph <?= $free ? 'ph-check-circle' : 'ph-lock-open' ?>"></i>
                        <?= $free ? 'Cours gratuit' : ($full_access ? 'Accès complet' : 'Accès par chapitre') ?>
                    </p>
                </div>

                <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <i class="ph ph-list-bullets text-primary-500"></i> Chapitres
                    </h2>
                    <ol class="space-y-1">
                        <?php foreach ($items as $idx => $it): ?>
                        <?php
                        $it_unlocked = course_item_is_unlocked($course, $it, $purchase);
                        $is_current  = (int)$it['id_item'] === (int)$item['id_item'];
                        ?>
                        <li>
                            <a href="<?= htmlspecialchars(course_item_url($course, $it, $link_token)) ?>"
                               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition <?= $is_current ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-semibold' : 'text-gray-600 dark:text-dark-300 hover:bg-gray-50 dark:hover:bg-dark-100' ?>">
                                <span class="w-6 h-6 rounded-md flex items-center justify-center text-[11px] font-bold flex-shrink-0 <?= $is_current ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-dark text-gray-500 dark:text-dark-300' ?>">
                                    <?= str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) ?>
                                </span>
                                <span class="truncate"><?= htmlspecialchars($it['title']) ?></span>
                                <i class="ph <?= $it_unlocked ? 'ph-lock-open text-primary-500' : 'ph-lock-simple text-gray-400' ?> ml-auto flex-shrink-0" aria-hidden="true"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>

                <a href="<?= htmlspecialchars(course_url($course)) ?>"
                   class="inline-flex w-full items-center justify-center gap-2 border border-gray-200 dark:border-dark-100 text-gray-700 dark:text-dark-300 hover:bg-white dark:hover:bg-dark-50 font-semibold px-5 py-3 rounded-xl transition text-sm">
                    <i class="ph ph-arrow-left"></i> Retour au cours
                </a>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>

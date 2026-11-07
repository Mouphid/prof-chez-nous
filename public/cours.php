<?php
require_once "../config/config.php";
require_once "../includes/functions.php";
require_once "../includes/courses.php";

$page_title = "Cours - Joie Enseignante";
$page_description = "Cours de littérature du professeur : chapitres en PDF et en vidéo, certains offerts en aperçu.";

$courses = $pdo->query("
    SELECT c.*,
           COUNT(ci.id_item) AS items_count
    FROM courses c
    LEFT JOIN course_items ci ON ci.id_course = c.id_course
    WHERE c.status = 'published'
    GROUP BY c.id_course
    ORDER BY c.created_at ASC, c.id_course ASC
")->fetchAll();

// Tous les chapitres en une seule requête, puis regroupés par cours.
$items_by_course = [];
if ($courses) {
    $ids = array_map(fn($c) => (int)$c['id_course'], $courses);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT * FROM course_items
        WHERE id_course IN ($placeholders)
        ORDER BY sort_order ASC, id_item ASC
    ");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $item) {
        $items_by_course[(int)$item['id_course']][] = $item;
    }
}
?>
<?php include "../includes/header.php"; ?>

    <div class="relative overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>img/bg/hero-1.jpg');">
    <div class="sbr-overlay"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-16 text-center">
        <span class="inline-block text-xs font-semibold text-accent-400 uppercase tracking-widest mb-3">Formation universitaire</span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-white">Cours du professeur</h1>
        <p class="text-white/70 mt-4 text-base max-w-xl mx-auto">Ressources pédagogiques pour mes étudiants en littérature</p>
    </div>
</div>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 py-12 mt-4" id="main-content">

        <?php if (!$courses): ?>
        <div class="bg-white dark:bg-dark-50 rounded-2xl border border-gray-100 dark:border-dark-100 p-10 text-center">
            <i class="ph ph-graduation-cap text-4xl text-gray-300 dark:text-dark-200 block mb-3"></i>
            <p class="text-gray-600 dark:text-dark-400">Les cours sont en préparation. Revenez bientôt.</p>
        </div>
        <?php endif; ?>

        <div class="space-y-8">
            <?php foreach ($courses as $course): ?>
            <?php
            $items     = $items_by_course[(int)$course['id_course']] ?? [];
            $free      = course_is_free($course);
            $objectifs = course_objectives($course);
            $previews  = count(array_filter($items, fn($i) => course_item_is_free($course, $i) && !$free));
            ?>
            <article class="bg-white dark:bg-dark-50 rounded-2xl shadow-sm dark:shadow-none border border-gray-100 dark:border-dark-100 overflow-hidden <?= REVEAL ?>">
                <div class="p-6 sm:p-8">
                    <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="relative z-10 flex flex-wrap items-center gap-4">
                            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="ph <?= htmlspecialchars($course['icon'] ?: 'ph-book-open') ?> text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <?php if ($course['code']): ?>
                                <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-1"><?= htmlspecialchars($course['code']) ?></span>
                                <?php endif; ?>
                                <h2 class="text-xl sm:text-2xl font-bold"><?= htmlspecialchars($course['title']) ?></h2>
                            </div>
                            <div class="sm:ml-auto text-left sm:text-right">
                                <span class="inline-block bg-white text-primary-700 text-sm font-extrabold px-4 py-2 rounded-full">
                                    <?= course_price_label((float)$course['price']) ?>
                                </span>
                                <?php if (!$free && $previews > 0): ?>
                                <p class="text-white/80 text-xs mt-1.5"><?= $previews ?> chapitre<?= $previews > 1 ? 's' : '' ?> offert<?= $previews > 1 ? 's' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-3 gap-8 mt-8">
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2"><i class="ph ph-info text-primary-500"></i> Description</h3>
                                <p class="text-gray-600 dark:text-dark-400 leading-relaxed"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                            </div>

                            <?php if ($objectifs): ?>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><i class="ph ph-target text-primary-500"></i> Objectifs</h3>
                                <ul class="space-y-2">
                                    <?php foreach ($objectifs as $obj): ?>
                                    <li class="flex items-start gap-2.5 text-gray-600 dark:text-dark-400">
                                        <i class="ph ph-check-circle text-emerald-500 mt-1 flex-shrink-0"></i>
                                        <span><?= htmlspecialchars($obj) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2"><i class="ph ph-list-bullets text-primary-500"></i> Plan du cours <span class="text-xs font-normal text-gray-400">(<?= count($items) ?> chapitre<?= count($items) > 1 ? 's' : '' ?>)</span></h3>
                                <?php if (!$items): ?>
                                <p class="text-sm text-gray-500 dark:text-dark-300">Les chapitres seront ajoutés prochainement.</p>
                                <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach ($items as $ci => $chapter): ?>
                                    <?php $chapter_free = course_item_is_free($course, $chapter); ?>
                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-dark-100">
                                        <span class="w-8 h-8 flex-shrink-0 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 text-white text-xs font-bold flex items-center justify-center"><?= str_pad((string)($ci + 1), 2, '0', STR_PAD_LEFT) ?></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($chapter['title']) ?></h4>
                                                <?php if ($chapter_free && !$free): ?>
                                                <span class="text-[11px] font-semibold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Aperçu gratuit</span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($chapter['description']): ?>
                                            <p class="text-xs text-gray-500 dark:text-dark-300 mt-0.5"><?= htmlspecialchars($chapter['description']) ?></p>
                                            <?php endif; ?>
                                            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-[11px] text-gray-500 dark:text-dark-300">
                                                <?php if ($chapter['pdf_path']): ?>
                                                <span class="inline-flex items-center gap-1"><i class="ph ph-file-pdf text-red-500"></i> PDF</span>
                                                <?php endif; ?>
                                                <?php if ($chapter['video_path']): ?>
                                                <span class="inline-flex items-center gap-1"><i class="ph ph-video text-blue-500"></i> Vidéo</span>
                                                <?php endif; ?>
                                                <?php if (!$chapter_free): ?>
                                                <span class="inline-flex items-center gap-1 font-semibold text-gray-700 dark:text-dark-200">
                                                    <i class="ph ph-tag"></i>
                                                    <?= (float)$chapter['price'] > 0 ? course_price_label((float)$chapter['price']) . ' seul' : 'Inclus dans le cours' ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <i class="ph <?= $chapter_free ? 'ph-lock-open text-emerald-500' : 'ph-lock-simple text-gray-400' ?> text-lg mt-1 flex-shrink-0" aria-hidden="true"></i>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <aside>
                            <div class="bg-gray-50 dark:bg-dark rounded-2xl border border-gray-100 dark:border-dark-100 p-5">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2"><i class="ph ph-chart-line text-primary-500"></i> Détails</h3>
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
                                </ul>

                                <a href="<?= course_url($course) ?>" class="mt-5 inline-flex w-full items-center justify-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-xl transition">
                                    <?= $free ? 'Commencer le cours' : 'Voir le cours' ?> <i class="ph ph-arrow-right"></i>
                                </a>
                            </div>
                        </aside>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php include "../includes/newsletter-section.php"; ?>
<?php include "../includes/footer.php"; ?>

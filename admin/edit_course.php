<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

/**
 * Enregistre un fichier de cours et renvoie son nom, ou null si aucun fichier.
 * Les fichiers vont dans uploads/cours/pdf ou uploads/cours/video (non accessibles
 * directement : ils passent par public/course_file.php).
 */
function course_store_upload(string $field, string $type): ?string
{
    if (empty($_FILES[$field]['name'])) {
        return null;
    }

    $rules = [
        'pdf' => [
            'ext'  => ['pdf'],
            'mime' => ['application/pdf'],
            'dir'  => __DIR__ . '/../uploads/cours/pdf',
        ],
        'video' => [
            'ext'  => ['mp4', 'webm', 'ogg'],
            'mime' => ['video/mp4', 'video/webm', 'video/ogg'],
            'dir'  => __DIR__ . '/../uploads/cours/video',
        ],
    ];

    if (!isset($rules[$type])) {
        throw new RuntimeException("Type de fichier inconnu.");
    }
    $rule = $rules[$type];

    if (($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Le fichier n'a pas pu être envoyé (taille ou connexion).");
    }

    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $rule['ext'], true) || !validate_mime($_FILES[$field]['tmp_name'], $rule['mime'])) {
        throw new RuntimeException($type === 'pdf'
            ? "Le document doit être un fichier PDF valide."
            : "La vidéo doit être au format MP4, WebM ou OGG.");
    }

    if (!is_dir($rule['dir']) && !mkdir($rule['dir'], 0755, true)) {
        throw new RuntimeException("Impossible de créer le dossier de stockage.");
    }

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $rule['dir'] . '/' . $name)) {
        throw new RuntimeException("Impossible d'enregistrer le fichier sur le serveur.");
    }

    return $name;
}

/** Supprime un fichier de cours du disque (sans sortir des dossiers prévus). */
function course_remove_upload(string $type, ?string $name): void
{
    if (!$name) {
        return;
    }
    $dir = $type === 'pdf'
        ? realpath(__DIR__ . '/../uploads/cours/pdf')
        : realpath(__DIR__ . '/../uploads/cours/video');
    $path = realpath(($type === 'pdf'
        ? __DIR__ . '/../uploads/cours/pdf/'
        : __DIR__ . '/../uploads/cours/video/') . basename($name));

    if ($dir && $path && str_starts_with($path, $dir) && is_file($path)) {
        @unlink($path);
    }
}

$id = (int)($_POST['id_course'] ?? $_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Cours introuvable.');
    header('Location: manage_courses.php');
    exit;
}

$error = '';
$item_error = '';

$icons = [
    'ph-book-open'      => 'Livre ouvert',
    'ph-feather'        => 'Plume',
    'ph-pen-nib'        => 'Stylo',
    'ph-graduation-cap' => 'Diplôme',
    'ph-video'          => 'Vidéo',
    'ph-file-text'      => 'Document',
];
if (!isset($icons[$course['icon']])) {
    $course['icon'] = 'ph-book-open';
}

$next_sort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM course_items WHERE id_course = " . (int)$id)->fetchColumn();

$item_edit = [
    'id_item'         => 0,
    'title'           => '',
    'description'     => '',
    'price'           => '0',
    'is_free_preview' => 0,
    'sort_order'      => $next_sort,
    'pdf_path'        => null,
    'video_path'      => null,
];

// Modification d'un chapitre existant (lien « Modifier »)
if (!empty($_GET['item'])) {
    $stmt = $pdo->prepare("SELECT * FROM course_items WHERE id_item = ? AND id_course = ?");
    $stmt->execute([(int)$_GET['item'], $id]);
    $found = $stmt->fetch();
    if ($found) {
        $item_edit = $found;
    }
}

// ─── Traitement des formulaires ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide. Rechargez la page.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_course') {
            $data = [
                'code'        => trim((string)($_POST['code'] ?? '')),
                'title'       => trim((string)($_POST['title'] ?? '')),
                'description' => trim((string)($_POST['description'] ?? '')),
                'objectives'  => trim((string)($_POST['objectives'] ?? '')),
                'level'       => trim((string)($_POST['level'] ?? '')),
                'duration'    => trim((string)($_POST['duration'] ?? '')),
                'semester'    => trim((string)($_POST['semester'] ?? '')),
                'credits'     => trim((string)($_POST['credits'] ?? '')),
                'icon'        => $_POST['icon'] ?? 'ph-book-open',
                'price'       => max(0, (float)($_POST['price'] ?? 0)),
                'status'      => ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            ];

            if (!isset($icons[$data['icon']])) {
                $data['icon'] = 'ph-book-open';
            }
            if ($data['title'] === '') {
                $error = 'Le titre du cours est obligatoire.';
            } elseif ($data['description'] === '') {
                $error = 'La description du cours est obligatoire.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE courses
                    SET code = ?, title = ?, description = ?, objectives = ?, level = ?, duration = ?,
                        semester = ?, credits = ?, icon = ?, price = ?, status = ?
                    WHERE id_course = ?
                ");
                $stmt->execute([
                    $data['code'], $data['title'], $data['description'], $data['objectives'],
                    $data['level'], $data['duration'], $data['semester'], $data['credits'],
                    $data['icon'], $data['price'], $data['status'], $id,
                ]);
                flash('success', 'Cours mis à jour.');
                header('Location: edit_course.php?id=' . $id);
                exit;
            }
            $course = array_merge($course, $data);

        } elseif ($action === 'save_item') {
            $item_edit = [
                'id_item'         => (int)($_POST['id_item'] ?? 0),
                'title'           => trim((string)($_POST['title'] ?? '')),
                'description'     => trim((string)($_POST['description'] ?? '')),
                'price'           => max(0, (float)($_POST['price'] ?? 0)),
                'is_free_preview' => !empty($_POST['is_free_preview']) ? 1 : 0,
                'sort_order'      => (int)($_POST['sort_order'] ?? $next_sort),
                'pdf_path'        => null,
                'video_path'      => null,
            ];

            // Fichiers déjà en place (en modification)
            $existing = null;
            if ($item_edit['id_item'] > 0) {
                $stmt = $pdo->prepare("SELECT * FROM course_items WHERE id_item = ? AND id_course = ?");
                $stmt->execute([$item_edit['id_item'], $id]);
                $existing = $stmt->fetch() ?: null;
                if (!$existing) {
                    $item_error = 'Ce chapitre est introuvable.';
                }
            }

            if ($item_error === '' && $item_edit['title'] === '') {
                $item_error = 'Le titre du chapitre est obligatoire.';
            }

            $new_pdf = null;
            $new_video = null;
            if ($item_error === '') {
                try {
                    $new_pdf   = course_store_upload('pdf_file', 'pdf');
                    $new_video = course_store_upload('video_file', 'video');
                } catch (RuntimeException $e) {
                    $item_error = $e->getMessage();
                    if ($new_pdf)   { course_remove_upload('pdf', $new_pdf); }
                    if ($new_video) { course_remove_upload('video', $new_video); }
                }
            }

            if ($item_error === '') {
                $pdf_path   = $new_pdf   ?: ($existing['pdf_path'] ?? null);
                $video_path = $new_video ?: ($existing['video_path'] ?? null);

                // Cases « supprimer le fichier actuel »
                if (!empty($_POST['remove_pdf']) && !$new_pdf) {
                    course_remove_upload('pdf', $pdf_path);
                    $pdf_path = null;
                }
                if (!empty($_POST['remove_video']) && !$new_video) {
                    course_remove_upload('video', $video_path);
                    $video_path = null;
                }

                if ($item_edit['id_item'] > 0) {
                    $stmt = $pdo->prepare("
                        UPDATE course_items
                        SET title = ?, description = ?, pdf_path = ?, video_path = ?, price = ?,
                            is_free_preview = ?, sort_order = ?
                        WHERE id_item = ? AND id_course = ?
                    ");
                    $stmt->execute([
                        $item_edit['title'], $item_edit['description'], $pdf_path, $video_path,
                        $item_edit['price'], $item_edit['is_free_preview'], $item_edit['sort_order'],
                        $item_edit['id_item'], $id,
                    ]);

                    // Remplacer un fichier existant : on supprime l'ancien.
                    if ($new_pdf && !empty($existing['pdf_path']) && $existing['pdf_path'] !== $new_pdf) {
                        course_remove_upload('pdf', $existing['pdf_path']);
                    }
                    if ($new_video && !empty($existing['video_path']) && $existing['video_path'] !== $new_video) {
                        course_remove_upload('video', $existing['video_path']);
                    }

                    flash('success', 'Chapitre mis à jour.');
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO course_items (id_course, title, description, pdf_path, video_path, price, is_free_preview, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $id, $item_edit['title'], $item_edit['description'], $pdf_path, $video_path,
                        $item_edit['price'], $item_edit['is_free_preview'], $item_edit['sort_order'],
                    ]);
                    flash('success', 'Chapitre ajouté.');
                }

                header('Location: edit_course.php?id=' . $id . '#chapitres');
                exit;
            }

        } elseif ($action === 'delete_item') {
            $item_id = (int)($_POST['id_item'] ?? 0);
            $stmt = $pdo->prepare("SELECT * FROM course_items WHERE id_item = ? AND id_course = ?");
            $stmt->execute([$item_id, $id]);
            $item = $stmt->fetch();

            if ($item) {
                course_remove_upload('pdf', $item['pdf_path']);
                course_remove_upload('video', $item['video_path']);
                $pdo->prepare("DELETE FROM course_items WHERE id_item = ?")->execute([$item_id]);
                flash('success', 'Chapitre supprimé.');
            } else {
                flash('error', 'Chapitre introuvable.');
            }
            header('Location: edit_course.php?id=' . $id . '#chapitres');
            exit;
        }
    }
}

// Recharger le cours après une éventuelle mise à jour
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

// Chapitres
$stmt = $pdo->prepare("SELECT * FROM course_items WHERE id_course = ? ORDER BY sort_order ASC, id_item ASC");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$price_label = fn(float $n): string => $n > 0
    ? number_format($n, 0, ',', ' ') . ' FCFA'
    : 'Gratuit';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le cours - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="bg-gray-50 font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Modifier le cours';
            $page_icon = 'ph-pencil';
            $page_action = '<div class="flex gap-2">'
                . '<a href="../public/course.php?id=' . (int)$id . '" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:border-primary hover:text-primary transition"><i class="ph ph-arrow-square-out"></i> Voir</a>'
                . '<a href="manage_courses.php" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:border-primary hover:text-primary transition"><i class="ph ph-arrow-left"></i> Retour</a>'
                . '</div>';
            include "_topbar.php";
            ?>
            <div class="p-8 max-w-5xl">

            <?php if (hasFlash('success')): ?>
            <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl px-4 py-3 mb-4 text-sm" role="status">
                <i class="ph ph-check-circle"></i> <?= htmlspecialchars(flash('success')) ?>
            </div>
            <?php endif; ?>
            <?php if (hasFlash('error')): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars(flash('error')) ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Présentation du cours -->
            <form method="post" class="space-y-6 mb-10">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save_course">

                <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-gray-900 mb-4">Présentation</h2>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Titre du cours *</label>
                            <input type="text" id="title" name="title" required value="<?= htmlspecialchars($course['title']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code</label>
                            <input type="text" id="code" name="code" value="<?= htmlspecialchars($course['code']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>

                    <div class="mt-4">
                        <label for="objectives" class="block text-sm font-medium text-gray-700 mb-1.5">Objectifs (un par ligne)</label>
                        <textarea id="objectives" name="objectives" rows="4"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($course['objectives']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-1.5">Niveau</label>
                            <input type="text" id="level" name="level" value="<?= htmlspecialchars($course['level']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-1.5">Volume</label>
                            <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($course['duration']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1.5">Semestre</label>
                            <input type="text" id="semester" name="semester" value="<?= htmlspecialchars($course['semester']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="credits" class="block text-sm font-medium text-gray-700 mb-1.5">Crédits</label>
                            <input type="text" id="credits" name="credits" value="<?= htmlspecialchars($course['credits']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1.5">Prix du cours complet (FCFA)</label>
                            <input type="number" id="price" name="price" min="0" step="50" value="<?= htmlspecialchars((string)(float)$course['price']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">0 = cours gratuit.</p>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Statut</label>
                            <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="draft" <?= $course['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                                <option value="published" <?= $course['status'] === 'published' ? 'selected' : '' ?>>Publié</option>
                            </select>
                        </div>
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-1.5">Icône</label>
                            <select id="icon" name="icon" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                                <?php foreach ($icons as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $course['icon'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                    <i class="ph ph-check"></i> Enregistrer le cours
                </button>
            </form>

            <!-- Chapitres -->
            <section id="chapitres" class="scroll-mt-24">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Chapitres <span class="text-sm font-normal text-gray-500">(<?= count($items) ?>)</span></h2>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-sm font-semibold text-gray-600">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Chapitre</th>
                                    <th class="px-4 py-3">Contenus</th>
                                    <th class="px-4 py-3">Prix</th>
                                    <th class="px-4 py-3">Accès</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if (!$items): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                        Aucun chapitre. Utilise le formulaire ci-dessous pour ajouter le premier.
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php foreach ($items as $item): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-400"><?= (int)$item['sort_order'] ?></td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($item['title']) ?></div>
                                        <?php if ($item['description']): ?>
                                        <div class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars(truncate($item['description'], 80)) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                        <?php if ($item['pdf_path']): ?>
                                        <span class="inline-flex items-center gap-1 mr-2"><i class="ph ph-file-pdf text-red-500"></i> PDF</span>
                                        <?php endif; ?>
                                        <?php if ($item['video_path']): ?>
                                        <span class="inline-flex items-center gap-1"><i class="ph ph-video text-blue-500"></i> Vidéo</span>
                                        <?php endif; ?>
                                        <?php if (!$item['pdf_path'] && !$item['video_path']): ?>
                                        <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold <?= (float)$item['price'] > 0 ? 'text-gray-900' : 'text-emerald-600' ?>">
                                        <?= $price_label((float)$item['price']) ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php if ((int)$item['is_free_preview'] === 1 || (float)$item['price'] <= 0): ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Aperçu gratuit</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Réservé à l'achat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <a href="edit_course.php?id=<?= (int)$id ?>&item=<?= (int)$item['id_item'] ?>#chapitres" class="text-gray-400 hover:text-primary transition mx-1" aria-label="Modifier le chapitre" title="Modifier"><i class="ph ph-pencil"></i></a>
                                        <form method="post" class="inline" onsubmit="return confirm('Supprimer ce chapitre et ses fichiers ?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="id_course" value="<?= (int)$id ?>">
                                            <input type="hidden" name="id_item" value="<?= (int)$item['id_item'] ?>">
                                            <button type="submit" class="text-gray-400 hover:text-red-500 transition mx-1" aria-label="Supprimer le chapitre" title="Supprimer"><i class="ph ph-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Formulaire chapitre -->
                <form method="post" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="save_item">
                    <input type="hidden" name="id_course" value="<?= (int)$id ?>">
                    <input type="hidden" name="id_item" value="<?= (int)$item_edit['id_item'] ?>">

                    <h3 class="text-sm font-bold text-gray-900 mb-4">
                        <?= (int)$item_edit['id_item'] > 0 ? 'Modifier le chapitre' : 'Ajouter un chapitre' ?>
                    </h3>

                    <?php if ($item_error): ?>
                    <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm" role="alert">
                        <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($item_error) ?>
                    </div>
                    <?php endif; ?>

                    <div class="grid sm:grid-cols-4 gap-4">
                        <div class="sm:col-span-3">
                            <label for="item_title" class="block text-sm font-medium text-gray-700 mb-1.5">Titre du chapitre *</label>
                            <input type="text" id="item_title" name="title" required value="<?= htmlspecialchars($item_edit['title']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="item_sort" class="block text-sm font-medium text-gray-700 mb-1.5">Ordre</label>
                            <input type="number" id="item_sort" name="sort_order" min="1" value="<?= (int)$item_edit['sort_order'] ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="item_description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea id="item_description" name="description" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($item_edit['description']) ?></textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-1.5">Document PDF</label>
                            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf,.pdf"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700">
                            <?php if (!empty($item_edit['pdf_path'])): ?>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-500 mt-2">
                                <input type="checkbox" name="remove_pdf" value="1" class="rounded border-gray-300">
                                Supprimer le PDF actuel
                            </label>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="video_file" class="block text-sm font-medium text-gray-700 mb-1.5">Vidéo</label>
                            <input type="file" id="video_file" name="video_file" accept="video/mp4,video/webm,video/ogg,.mp4,.webm,.ogg"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:bg-gray-100 file:text-gray-700">
                            <?php if (!empty($item_edit['video_path'])): ?>
                            <label class="inline-flex items-center gap-2 text-xs text-gray-500 mt-2">
                                <input type="checkbox" name="remove_video" value="1" class="rounded border-gray-300">
                                Supprimer la vidéo actuelle
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label for="item_price" class="block text-sm font-medium text-gray-700 mb-1.5">Prix du chapitre seul (FCFA)</label>
                            <input type="number" id="item_price" name="price" min="0" step="50" value="<?= htmlspecialchars((string)(float)$item_edit['price']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">0 = gratuit pour tous.</p>
                        </div>
                        <div class="flex items-end pb-2">
                            <label for="is_free_preview" class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" id="is_free_preview" name="is_free_preview" value="1" <?= (int)$item_edit['is_free_preview'] === 1 ? 'checked' : '' ?> class="rounded border-gray-300">
                                Aperçu gratuit avant achat
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 mt-6">
                        <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                            <i class="ph <?= (int)$item_edit['id_item'] > 0 ? 'ph-check' : 'ph-plus' ?>"></i>
                            <?= (int)$item_edit['id_item'] > 0 ? 'Enregistrer le chapitre' : 'Ajouter le chapitre' ?>
                        </button>
                        <?php if ((int)$item_edit['id_item'] > 0): ?>
                        <a href="edit_course.php?id=<?= (int)$id ?>#chapitres" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-xl transition hover:border-primary hover:text-primary">
                            Annuler la modification
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            </div>
        </main>
    </div>
</body>
</html>

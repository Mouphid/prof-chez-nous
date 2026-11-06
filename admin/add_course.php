<?php
require_once "config_admin.php";

if ($admin_role !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$old = [
    'title'       => '',
    'code'        => '',
    'description' => '',
    'objectives'  => '',
    'level'       => '',
    'duration'    => '',
    'semester'    => '',
    'credits'     => '',
    'icon'        => 'ph-book-open',
    'price'       => '0',
    'status'      => 'draft',
];

$icons = [
    'ph-book-open'    => 'Livre ouvert',
    'ph-feather'      => 'Plume',
    'ph-pen-nib'      => 'Stylo',
    'ph-graduation-cap' => 'Diplôme',
    'ph-video'        => 'Vidéo',
    'ph-file-text'    => 'Document',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $value) {
        $old[$key] = trim((string)($_POST[$key] ?? ''));
    }
    $old['price']  = (string)(float)($_POST['price'] ?? 0);
    $old['status'] = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
    if (!isset($icons[$old['icon']])) {
        $old['icon'] = 'ph-book-open';
    }

    $price = (float)$old['price'];
    if ($price < 0) {
        $price = 0;
    }

    if ($old['title'] === '') {
        $error = "Le titre du cours est obligatoire.";
    } elseif ($old['description'] === '') {
        $error = "La description du cours est obligatoire.";
    } else {
        // Slug unique : « litterature-orale », puis « litterature-orale-2 », etc.
        $base = course_slug($old['title']);
        $slug = $base;
        $n = 2;
        $check = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE slug = ?");
        while (true) {
            $check->execute([$slug]);
            if (!(int)$check->fetchColumn()) {
                break;
            }
            $slug = $base . '-' . $n++;
        }

        $stmt = $pdo->prepare("
            INSERT INTO courses (code, slug, title, description, objectives, level, duration, semester, credits, icon, price, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $old['code'], $slug, $old['title'], $old['description'], $old['objectives'],
            $old['level'], $old['duration'], $old['semester'], $old['credits'],
            $old['icon'], $price, $old['status'],
        ]);

        $id = (int)$pdo->lastInsertId();
        flash('success', 'Cours créé. Ajoute maintenant les chapitres ci-dessous.');
        header('Location: edit_course.php?id=' . $id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau cours - Admin</title>
    <?php cdn_head(); ?>
</head>
<body class="admin-shell font-sans leading-relaxed">
    <?php skip_link() ?>
    <div class="flex min-h-screen">
        <?php include "_sidebar.php"; ?>

        <main class="admin-main flex-1 ml-64" id="main-content">
            <?php
            $page_title = 'Nouveau cours';
            $page_icon = 'ph-plus-circle';
            $page_action = '<a href="manage_courses.php" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:border-primary hover:text-primary transition"><i class="ph ph-arrow-left"></i> Retour</a>';
            include "_topbar.php";
            ?>
            <div class="p-8 max-w-4xl">

            <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 mb-4 text-sm" role="alert">
                <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <?= csrf_field() ?>

                <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-gray-900 mb-4">Présentation</h2>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1.5">Titre du cours *</label>
                            <input type="text" id="title" name="title" required value="<?= htmlspecialchars($old['title']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code</label>
                            <input type="text" id="code" name="code" value="<?= htmlspecialchars($old['code']) ?>" placeholder="LIT301"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($old['description']) ?></textarea>
                    </div>

                    <div class="mt-4">
                        <label for="objectives" class="block text-sm font-medium text-gray-700 mb-1.5">Objectifs (un par ligne)</label>
                        <textarea id="objectives" name="objectives" rows="4" placeholder="Maîtriser les concepts fondamentaux&#10;Analyser un texte littéraire"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($old['objectives']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label for="level" class="block text-sm font-medium text-gray-700 mb-1.5">Niveau</label>
                            <input type="text" id="level" name="level" value="<?= htmlspecialchars($old['level']) ?>" placeholder="Licence 3"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700 mb-1.5">Volume</label>
                            <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($old['duration']) ?>" placeholder="45 heures"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1.5">Semestre</label>
                            <input type="text" id="semester" name="semester" value="<?= htmlspecialchars($old['semester']) ?>" placeholder="Pair"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div>
                            <label for="credits" class="block text-sm font-medium text-gray-700 mb-1.5">Crédits</label>
                            <input type="text" id="credits" name="credits" value="<?= htmlspecialchars($old['credits']) ?>" placeholder="6"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-sm font-bold text-gray-900 mb-4">Vente et affichage</h2>

                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1.5">Prix du cours complet (FCFA)</label>
                            <input type="number" id="price" name="price" min="0" step="50" value="<?= htmlspecialchars($old['price']) ?>"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                            <p class="text-xs text-gray-500 mt-1">0 = cours gratuit.</p>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Statut</label>
                            <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="draft" <?= $old['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                                <option value="published" <?= $old['status'] === 'published' ? 'selected' : '' ?>>Publié</option>
                            </select>
                        </div>
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-1.5">Icône</label>
                            <select id="icon" name="icon" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary">
                                <?php foreach ($icons as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>" <?= $old['icon'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </section>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 bg-primary hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-xl transition">
                        <i class="ph ph-check"></i> Créer le cours
                    </button>
                    <a href="manage_courses.php" class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-6 py-3 rounded-xl transition hover:border-primary hover:text-primary">
                        Annuler
                    </a>
                </div>
            </form>

            </div>
        </main>
    </div>
</body>
</html>

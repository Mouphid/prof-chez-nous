<?php
/**
 * includes/courses.php — Fonctions partagées pour les cours en ligne.
 *
 * Modèle retenu :
 * - un cours a un prix global (0 = cours gratuit) ;
 * - chaque chapitre peut avoir son propre prix et/ou être un aperçu gratuit ;
 * - un achat donne accès au cours complet (id_course) ou à un seul chapitre (id_item) ;
 * - l'acheteur n'a pas de compte : il reçoit un lien privé contenant un jeton
 *   (la colonne purchases.download_token).
 */

/**
 * Le cours complet est-il gratuit ?
 */
function course_is_free(array $course): bool
{
    return (float)($course['price'] ?? 0) <= 0;
}

/**
 * Retrouve un cours publié par son identifiant ou son slug.
 */
function course_find_published(PDO $pdo, int $id = 0, string $slug = ''): ?array
{
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id_course = ? AND status = 'published'");
        $stmt->execute([$id]);
    } elseif ($slug !== '') {
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
    } else {
        return null;
    }

    $course = $stmt->fetch();
    return $course ?: null;
}

/**
 * Chapitres d'un cours, dans l'ordre d'affichage.
 */
function course_items(PDO $pdo, int $id_course): array
{
    $stmt = $pdo->prepare("
        SELECT * FROM course_items
        WHERE id_course = ?
        ORDER BY sort_order ASC, id_item ASC
    ");
    $stmt->execute([$id_course]);
    return $stmt->fetchAll();
}

/**
 * Retrouve un achat payé à partir de son jeton (lien privé).
 * Renvoie null si le jeton est vide ou inconnu.
 */
function course_purchase_by_token(PDO $pdo, string $token): ?array
{
    $token = trim($token);
    if ($token === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE download_token = ? AND status = 'paid' LIMIT 1");
    $stmt->execute([$token]);
    $purchase = $stmt->fetch();

    return $purchase ?: null;
}

/**
 * Ce chapitre est-il accessible sans payer ?
 * (cours gratuit, chapitre gratuit ou chapitre marqué « aperçu gratuit »)
 */
function course_item_is_free(array $course, array $item): bool
{
    // Cours gratuit : tous ses chapitres sont ouverts.
    if (course_is_free($course)) {
        return true;
    }

    // Cours payant : seuls les chapitres marqués « aperçu gratuit » sont ouverts.
    // Un chapitre à 0 FCFA est inclus dans l'achat du cours ; il n'est pas
    // offert aux visiteurs (sinon le prix du cours n'aurait aucun effet).
    return (int)($item['is_free_preview'] ?? 0) === 1;
}

/**
 * L'acheteur peut-il ouvrir ce chapitre ?
 *
 * @param array|null $purchase Achat payé correspondant au jeton du lien privé.
 */
function course_item_is_unlocked(array $course, array $item, ?array $purchase): bool
{
    if (course_item_is_free($course, $item)) {
        return true;
    }
    if (!$purchase) {
        return false;
    }

    // Achat du cours complet
    if (!empty($purchase['id_course']) && (int)$purchase['id_course'] === (int)$course['id_course']) {
        return true;
    }

    // Achat du chapitre seul
    if (!empty($purchase['id_item']) && (int)$purchase['id_item'] === (int)$item['id_item']) {
        return true;
    }

    return false;
}

/**
 * Prix affiché : « Gratuit » ou « 5 000 FCFA ».
 */
function course_price_label(float $price): string
{
    return $price > 0
        ? number_format($price, 0, ',', ' ') . ' FCFA'
        : 'Gratuit';
}

/**
 * Découpe la liste d'objectifs (une ligne par objectif).
 */
function course_objectives(array $course): array
{
    $lines = preg_split('/\r\n|\r|\n/', (string)($course['objectives'] ?? ''));
    return array_values(array_filter(array_map('trim', $lines), fn($line) => $line !== ''));
}

/**
 * Adresse publique d'un cours.
 */
function course_url(array $course): string
{
    return 'course.php?slug=' . rawurlencode((string)$course['slug']);
}

/**
 * Adresse publique d'un chapitre (page de lecture).
 *
 * @param string $token Jeton du lien privé, pour les cours payants.
 */
function course_item_url(array $course, array $item, string $token = ''): string
{
    $url = 'chapter.php?item=' . (int)$item['id_item'];
    if ($token !== '') {
        $url .= '&token=' . rawurlencode($token);
    }
    return $url;
}

/**
 * Transforme le texte saisi dans l'administration en HTML propre.
 *
 * Mise en forme acceptée (volontairement simple) :
 * - « ## Titre »    → sous-titre ;
 * - « - élément »   → liste à puces ;
 * - « **texte** »   → gras ;
 * - ligne vide      → nouveau paragraphe.
 *
 * Tout le reste est échappé : aucun HTML dangereux ne peut être injecté.
 */
function course_content_html(?string $text): string
{
    $text = trim((string)$text);
    if ($text === '') {
        return '';
    }

    $inline = static function (string $line): string {
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        $line = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $line);
        $line = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $line);
        return $line;
    };

    $blocks    = [];
    $paragraph = [];
    $list      = [];

    $flushParagraph = static function () use (&$paragraph, &$blocks, $inline): void {
        if ($paragraph) {
            $blocks[] = '<p>' . implode('<br>', array_map($inline, $paragraph)) . '</p>';
            $paragraph = [];
        }
    };
    $flushList = static function () use (&$list, &$blocks, $inline): void {
        if ($list) {
            $items = array_map(static fn($li) => '<li>' . $inline($li) . '</li>', $list);
            $blocks[] = '<ul>' . implode('', $items) . '</ul>';
            $list = [];
        }
    };

    foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
        $line = trim($line);

        if ($line === '') {
            $flushParagraph();
            $flushList();
            continue;
        }

        if (preg_match('/^##\s+(.+)$/u', $line, $m)) {
            $flushParagraph();
            $flushList();
            $blocks[] = '<h3>' . $inline($m[1]) . '</h3>';
            continue;
        }

        if (preg_match('/^[-•]\s+(.+)$/u', $line, $m)) {
            $flushParagraph();
            $list[] = $m[1];
            continue;
        }

        $flushList();
        $paragraph[] = $line;
    }

    $flushParagraph();
    $flushList();

    return implode("\n", $blocks);
}


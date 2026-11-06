-- ============================================================
-- Reprise des 3 cours de démonstration dans la base.
-- À exécuter UNE FOIS après sql/migration_cours.sql.
-- Les cours sont gratuits (price = 0) : tu pourras fixer les prix
-- depuis Admin → Cours.
-- Le script peut être relancé sans créer de doublon.
-- ============================================================

INSERT INTO `courses`
    (`code`, `slug`, `title`, `description`, `objectives`, `level`, `duration`, `semester`, `credits`, `icon`, `price`, `status`)
VALUES
(
    'LIT301',
    'introduction-a-la-litterature',
    'Introduction à la Littérature',
    'Ce cours présente les bases de l''analyse littéraire : genres, courants littéraires, figures de style et méthodes d''interprétation des textes.',
    'Maîtriser les concepts fondamentaux de la littérature\nAnalyser un texte littéraire avec rigueur\nIdentifier les genres et courants littéraires\nRédiger un commentaire composé',
    'Licence 3', '45 heures', 'Pair', '6', 'ph-book-open', 0, 'published'
),
(
    'LIT401',
    'litterature-orale-africaine',
    'Littérature Orale Africaine',
    'Étude approfondie des genres de la littérature orale africaine : contes, légendes, mythes, proverbes, devinettes et chants traditionnels.',
    'Identifier et classer les genres oraux\nComprendre les fonctions sociales de l''oraliture\nAnalyser les techniques de narration traditionnelle\nÉtudier la transmission orale et ses enjeux',
    'Master 1', '60 heures', 'Impair', '8', 'ph-feather', 0, 'published'
),
(
    'LIT302',
    'critique-litteraire',
    'Critique Littéraire',
    'Initiation aux différentes méthodes de critique littéraire : narratologie, analyse du discours, sociocritique et analyse psychocritique.',
    'Maîtriser les outils d''analyse textuelle\nAppliquer les méthodes de critique\nRédiger une analyse critique argumentée\nÉvaluer les travaux scientifiques en littérature',
    'Licence 3', '45 heures', 'Impair', '6', 'ph-pen-nib', 0, 'published'
)
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`);

-- Chapitres : chaque titre n'est ajouté qu'une seule fois par cours.
INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Qu''est-ce que la littérature ?', 'Définitions, enjeux et fonctions de la littérature', 0, 1, 1
FROM `courses` c WHERE c.slug = 'introduction-a-la-litterature'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Qu''est-ce que la littérature ?');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Les genres littéraires', 'Poésie, roman, théâtre, essai', 0, 0, 2
FROM `courses` c WHERE c.slug = 'introduction-a-la-litterature'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Les genres littéraires');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Figures de style', 'Métaphores, métonymies, allitérations…', 0, 0, 3
FROM `courses` c WHERE c.slug = 'introduction-a-la-litterature'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Figures de style');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Courants littéraires', 'Classicisme, romantisme, naturalisme…', 0, 0, 4
FROM `courses` c WHERE c.slug = 'introduction-a-la-litterature'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Courants littéraires');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Genres de l''oralité', 'Contes, mythes, légendes et épopées', 0, 1, 1
FROM `courses` c WHERE c.slug = 'litterature-orale-africaine'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Genres de l''oralité');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'L''art du conteur', 'Techniques de narration et performance', 0, 0, 2
FROM `courses` c WHERE c.slug = 'litterature-orale-africaine'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'L''art du conteur');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Proverbes et devinettes', 'Fonction didactique et sociale', 0, 0, 3
FROM `courses` c WHERE c.slug = 'litterature-orale-africaine'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Proverbes et devinettes');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Chants traditionnels', 'Lien entre musique, parole et mémoire collective', 0, 0, 4
FROM `courses` c WHERE c.slug = 'litterature-orale-africaine'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Chants traditionnels');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Narratologie', 'Le récit, ses instances et ses techniques', 0, 1, 1
FROM `courses` c WHERE c.slug = 'critique-litteraire'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Narratologie');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Analyse du discours', 'Énonciation et pragmatique du texte', 0, 0, 2
FROM `courses` c WHERE c.slug = 'critique-litteraire'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Analyse du discours');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Sociocritique', 'Le texte littéraire et son contexte social', 0, 0, 3
FROM `courses` c WHERE c.slug = 'critique-litteraire'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Sociocritique');

INSERT INTO `course_items` (`id_course`, `title`, `description`, `price`, `is_free_preview`, `sort_order`)
SELECT c.id_course, 'Méthodes d''analyse', 'Panorama des approches critiques contemporaines', 0, 0, 4
FROM `courses` c WHERE c.slug = 'critique-litteraire'
AND NOT EXISTS (SELECT 1 FROM `course_items` ci WHERE ci.id_course = c.id_course AND ci.title = 'Méthodes d''analyse');

-- ============================================================
-- Migration : cours en ligne payants
-- Projet : Joie Enseignante
-- À exécuter APRÈS sql/migration_paiement.sql et l'import de la base.
-- ============================================================

-- 1) Les cours
CREATE TABLE IF NOT EXISTS `courses` (
    `id_course`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(20)  NOT NULL DEFAULT '',
    `slug`        VARCHAR(190) NOT NULL,
    `title`       VARCHAR(190) NOT NULL,
    `description` TEXT,
    `objectives`  TEXT,                 -- une ligne par objectif
    `level`       VARCHAR(80)  NOT NULL DEFAULT '',
    `duration`    VARCHAR(80)  NOT NULL DEFAULT '',
    `semester`    VARCHAR(40)  NOT NULL DEFAULT '',
    `credits`     VARCHAR(20)  NOT NULL DEFAULT '',
    `icon`        VARCHAR(50)  NOT NULL DEFAULT 'ph-book-open',
    `cover_image` VARCHAR(255) DEFAULT NULL,
    `price`       DECIMAL(10,2) NOT NULL DEFAULT 0,   -- prix du cours complet
    `status`      ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_course`),
    UNIQUE KEY `uniq_slug` (`slug`),
    KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Les chapitres d'un cours
CREATE TABLE IF NOT EXISTS `course_items` (
    `id_item`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_course`       INT UNSIGNED NOT NULL,
    `title`           VARCHAR(190) NOT NULL,
    `description`     TEXT,
    `pdf_path`        VARCHAR(255) DEFAULT NULL,   -- nom du fichier dans uploads/cours/pdf
    `video_path`      VARCHAR(255) DEFAULT NULL,   -- nom du fichier dans uploads/cours/video
    `price`           DECIMAL(10,2) NOT NULL DEFAULT 0,  -- 0 = gratuit
    `is_free_preview` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order`      INT NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_item`),
    KEY `idx_course` (`id_course`),
    CONSTRAINT `fk_item_course`
        FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Les achats peuvent concerner : un document, un cours complet ou un chapitre.
--    On rend donc id_file facultatif et on ajoute id_course / id_item.
ALTER TABLE `purchases`
    MODIFY COLUMN `id_file` INT(11) NULL,
    ADD COLUMN `id_course` INT UNSIGNED NULL AFTER `id_file`,
    ADD COLUMN `id_item`   INT UNSIGNED NULL AFTER `id_course`,
    ADD KEY `idx_course` (`id_course`),
    ADD KEY `idx_item` (`id_item`);

ALTER TABLE `purchases`
    ADD CONSTRAINT `fk_purchase_course`
        FOREIGN KEY (`id_course`) REFERENCES `courses` (`id_course`) ON DELETE SET NULL,
    ADD CONSTRAINT `fk_purchase_item`
        FOREIGN KEY (`id_item`) REFERENCES `course_items` (`id_item`) ON DELETE SET NULL;

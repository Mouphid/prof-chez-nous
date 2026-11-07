-- ============================================================
-- Migration : journal des actions de l'administration
--
-- Enregistre qui a fait quoi : création, modification, suppression,
-- envoi de newsletter, changement de réglages, etc.
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin_activity_log` (
    `id_log`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_user`     INT NULL DEFAULT NULL,
    `user_name`   VARCHAR(150) NOT NULL DEFAULT '',
    `action`      VARCHAR(50) NOT NULL,
    `target_type` VARCHAR(50) NULL DEFAULT NULL,
    `target_id`   INT NULL DEFAULT NULL,
    `details`     VARCHAR(255) NULL DEFAULT NULL,
    `ip`          VARCHAR(45) NULL DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    KEY `idx_journal_date` (`created_at`),
    KEY `idx_journal_user` (`id_user`),
    KEY `idx_journal_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

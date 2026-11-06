-- ============================================================
-- Migration : abonnés à la newsletter
-- Projet : Joie Enseignante
-- À exécuter APRÈS l'import de sql/export_production.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id_subscriber` INT(11) NOT NULL AUTO_INCREMENT,
    `email`        VARCHAR(190) NOT NULL,
    `status`       ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_subscriber`),
    UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Migration : documents gratuits / payants + achats
-- Projet : Joie Enseignante
-- À exécuter APRÈS l'import de sql/export_production.sql
-- ============================================================

-- 1) Prix d'un document (0 = gratuit)
ALTER TABLE `files`
    ADD COLUMN `price` DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER `file_size`,
    ADD COLUMN `is_free` TINYINT(1) NOT NULL DEFAULT 1 AFTER `price`;

-- 2) Table des achats / commandes
CREATE TABLE IF NOT EXISTS `purchases` (
    `id_purchase`      INT(11) NOT NULL AUTO_INCREMENT,
    `id_file`          INT(11) NOT NULL,
    `buyer_name`       VARCHAR(150) DEFAULT NULL,
    `buyer_email`      VARCHAR(150) DEFAULT NULL,
    `buyer_phone`      VARCHAR(50)  DEFAULT NULL,
    `amount`           DECIMAL(10,2) NOT NULL DEFAULT 0,
    `currency`         VARCHAR(10) NOT NULL DEFAULT 'XOF',
    `provider`         VARCHAR(50) NOT NULL DEFAULT 'kkiapay',
    `transaction_id`   VARCHAR(120) DEFAULT NULL,
    `status`           ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `download_token`   VARCHAR(64) DEFAULT NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `paid_at`          DATETIME DEFAULT NULL,
    PRIMARY KEY (`id_purchase`),
    UNIQUE KEY `uniq_provider_tx` (`provider`, `transaction_id`),
    KEY `id_file` (`id_file`),
    KEY `status` (`status`),
    KEY `download_token` (`download_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

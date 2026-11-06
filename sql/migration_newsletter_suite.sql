-- ─────────────────────────────────────────────────────────────
--  Newsletter — suite (envoi réel et désinscription)
--  À exécuter APRÈS sql/migration_newsletter.sql
--
--  - jeton unique de désinscription pour chaque abonné ;
--  - date de désinscription ;
--  - historique des messages envoyés.
-- ─────────────────────────────────────────────────────────────

ALTER TABLE newsletter_subscribers
    ADD COLUMN unsubscribe_token CHAR(64) NULL AFTER status,
    ADD COLUMN unsubscribed_at DATETIME NULL AFTER unsubscribe_token;

-- Donne un jeton aux abonnés déjà inscrits avant cette migration.
UPDATE newsletter_subscribers
SET unsubscribe_token = SHA2(CONCAT(email, RAND(), NOW()), 256)
WHERE unsubscribe_token IS NULL OR unsubscribe_token = '';

ALTER TABLE newsletter_subscribers
    MODIFY unsubscribe_token CHAR(64) NOT NULL,
    ADD UNIQUE KEY uniq_unsubscribe_token (unsubscribe_token),
    ADD KEY idx_status (status);

CREATE TABLE IF NOT EXISTS newsletter_campaigns (
    id_campaign      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    subject          VARCHAR(200) NOT NULL,
    body             TEXT NOT NULL,
    recipients_count INT UNSIGNED NOT NULL DEFAULT 0,
    sent_count       INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count     INT UNSIGNED NOT NULL DEFAULT 0,
    sent_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_campaign),
    KEY idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

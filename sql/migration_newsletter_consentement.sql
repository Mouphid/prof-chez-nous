-- ═══════════════════════════════════════════════════════════════
--  Newsletter : preuve du consentement (case à cocher + CGU)
--  À exécuter une seule fois sur la base de production.
-- ═══════════════════════════════════════════════════════════════

ALTER TABLE newsletter_subscribers
    ADD COLUMN consent_at DATETIME NULL DEFAULT NULL AFTER unsubscribed_at;

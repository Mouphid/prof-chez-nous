-- ============================================================
-- Migration : mot de passe oublié pour l'administration
--
-- Ajoute deux colonnes à la table `users` :
--   reset_token   : empreinte SHA-256 du jeton de réinitialisation
--   reset_expires : date d'expiration du lien (1 heure)
--
-- Le jeton en clair n'est jamais stocké : seule son empreinte l'est.
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL AFTER `is_active`,
    ADD COLUMN `reset_expires` DATETIME NULL DEFAULT NULL AFTER `reset_token`;

CREATE INDEX `idx_users_reset_token` ON `users` (`reset_token`);

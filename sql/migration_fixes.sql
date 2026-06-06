-- Migration: Corrections de sécurité et alignement DB/code
-- Exécuter ce fichier dans phpMyAdmin ou via MySQL CLI

-- 1. Tables manquantes
CREATE TABLE IF NOT EXISTS `article_reads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_read` (`id_user`, `id_post`),
  KEY `id_user` (`id_user`),
  KEY `id_post` (`id_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user_downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_file` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_download` (`id_user`, `id_file`),
  KEY `id_user` (`id_user`),
  KEY `id_file` (`id_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Colonnes manquantes dans comments
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `id_user` int(11) DEFAULT NULL AFTER `id_comment`;
ALTER TABLE `comments` ADD COLUMN IF NOT EXISTS `token_hash` varchar(255) DEFAULT NULL AFTER `id_user`;

-- 3. Aligner la colonne likes.ip_address -> user_ip
-- (déjà faite dans le code, vérifier que la colonne existe)
-- Si la colonne s'appelle déjà user_ip, pas de changement nécessaire
-- Sinon, exécuter: ALTER TABLE likes CHANGE ip_address user_ip varchar(45) DEFAULT NULL;

-- 4. Ajouter les colonnes manquantes dans files (si elles n'existent pas)
ALTER TABLE `files` ADD COLUMN IF NOT EXISTS `file_path` varchar(255) DEFAULT NULL;
ALTER TABLE `files` ADD COLUMN IF NOT EXISTS `file_size` bigint(20) DEFAULT NULL;
ALTER TABLE `files` ADD COLUMN IF NOT EXISTS `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- 5. Mettre à jour les rôles existants pour correspondre au code (admin, auteur, etudiant)
-- Convertir les anciens rôles
UPDATE `users` SET `role` = 'admin' WHERE `role` IN ('Admin', 'ADMIN');
UPDATE `users` SET `role` = 'auteur' WHERE `role` IN ('teacher', 'Teacher', 'TEACHER', 'author', 'Author');
UPDATE `users` SET `role` = 'etudiant' WHERE `role` IN ('user', 'User', 'USER', 'student', 'Student', 'STUDENT');

-- 6. Ajouter des index pour les performances
ALTER TABLE `posts` ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);
ALTER TABLE `comments` ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);
ALTER TABLE `files` ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`);

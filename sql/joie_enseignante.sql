-- Base de données JoieEnseignante
-- Schéma complet

CREATE DATABASE IF NOT EXISTS `JoieEnseignante` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `JoieEnseignante`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Table users (utilisateurs)
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','teacher') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_user`),
  KEY `email` (`email`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id_category` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text,
  `color` varchar(7) DEFAULT '#007BFF',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_category`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id_post` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `id_category` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `content` text NOT NULL,
  `excerpt` text,
  `main_image` varchar(255) DEFAULT NULL,
  `main_video` varchar(255) DEFAULT NULL,
  `embed_link` varchar(500) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_post`),
  KEY `id_user` (`id_user`),
  KEY `id_category` (`id_category`),
  KEY `status` (`status`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table files (fichiers attachés)
CREATE TABLE IF NOT EXISTS `files` (
  `id_file` int(11) NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('pdf','image','video','doc','other') DEFAULT 'other',
  `file_size` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_file`),
  KEY `id_post` (`id_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id_comment` int(11) NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `author_email` varchar(150) NOT NULL,
  `author_name` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('pending','visible','hidden') DEFAULT 'pending',
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comment`),
  KEY `id_post` (`id_post`),
  KEY `status` (`status`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table likes
CREATE TABLE IF NOT EXISTS `likes` (
  `id_like` int(11) NOT NULL AUTO_INCREMENT,
  `id_post` int(11) NOT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_like`),
  KEY `id_post` (`id_post`),
  KEY `user_ip` (`user_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table settings (configuration du site)
CREATE TABLE IF NOT EXISTS `settings` (
  `id_setting` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL UNIQUE,
  `setting_value` text,
  PRIMARY KEY (`id_setting`),
  KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des données par défaut

-- Catégories par défaut
INSERT INTO `categories` (`name`, `slug`, `description`, `color`) VALUES
('Cours', 'cours', 'Documents de cours', '#28a745'),
('Exercices', 'exercices', 'Exercices et travaux dirigés', '#dc3545'),
('Examens', 'examens', 'Sujets d\'examens', '#ffc107'),
('Ressources', 'ressources', 'Ressources pédagogiques', '#17a2b8'),
('Actualités', 'actualites', 'Actualités du site', '#6f42c1');

-- Utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Administrateur', 'admin@joieenseignante.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oRoEaW6m9RlX7xHkJqHvT3fFMzN5JKW', 'admin');

-- Paramètres par défaut
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'Joie Enseignante'),
('site_description', 'Plateforme pédagogique pour enseignants et étudiants'),
('site_logo', ''),
('items_per_page', '10'),
('comments_moderation', '1'),
('allow_registration', '1');

-- Contraintes de clés étrangères
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE SET NULL;
ALTER TABLE `posts` ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`id_category`) REFERENCES `categories`(`id_category`) ON DELETE SET NULL;
ALTER TABLE `files` ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts`(`id_post`) ON DELETE CASCADE;
ALTER TABLE `comments` ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts`(`id_post`) ON DELETE CASCADE;
ALTER TABLE `likes` ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `posts`(`id_post`) ON DELETE CASCADE;
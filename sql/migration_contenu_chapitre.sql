-- ============================================================
-- Migration : contenu écrit des chapitres
-- Projet : Joie Enseignante
-- À exécuter une seule fois sur la base existante.
-- ============================================================

-- Le contenu rédigé dans l'administration pour chaque chapitre.
ALTER TABLE `course_items`
    ADD COLUMN `content` LONGTEXT NULL AFTER `description`;

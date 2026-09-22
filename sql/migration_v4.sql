-- Migration v4 : Programmation des articles
-- Ajoute le statut 'scheduled' à l'ENUM status + index sur published_at

ALTER TABLE posts MODIFY COLUMN status ENUM('draft','published','scheduled','archived') DEFAULT 'draft';
ALTER TABLE posts ADD INDEX idx_published_at (published_at);

-- Migration v2: ajout id_user dans likes + index
ALTER TABLE likes ADD COLUMN id_user INT(11) DEFAULT NULL AFTER id_post;
ALTER TABLE likes ADD KEY id_user (id_user);

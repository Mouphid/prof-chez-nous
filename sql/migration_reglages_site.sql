-- ============================================================
-- Migration : réglages du site modifiables depuis l'administration
--
-- Ajoute les coordonnées et les réseaux sociaux dans la table
-- `settings`. `config/theme.php` lit ces valeurs au chargement ;
-- si une clé manque, il retombe sur ses valeurs par défaut.
-- ============================================================

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('site_email',       'contact@joiedenseignante.com'),
    ('site_phone',       '+229 XX XX XX XX'),
    ('site_address',     'Cotonou, Bénin'),
    ('social_facebook',  'https://facebook.com/joiedenseignante'),
    ('social_twitter',   'https://twitter.com/joiedenseignante'),
    ('social_linkedin',  'https://linkedin.com/company/joiedenseignante'),
    ('social_youtube',   'https://youtube.com/@joiedenseignante'),
    ('social_instagram', 'https://instagram.com/joiedenseignante')
ON DUPLICATE KEY UPDATE `setting_value` = `setting_value`;

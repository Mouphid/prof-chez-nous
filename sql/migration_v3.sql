-- Migration v3: vérification email
ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active,
  ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL AFTER email_verified,
  ADD COLUMN token_expires DATETIME DEFAULT NULL AFTER verification_token;

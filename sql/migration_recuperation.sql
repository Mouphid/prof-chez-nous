-- ─────────────────────────────────────────────────────────────
--  B3 — « Retrouver mon achat »
--  Journal des demandes, uniquement pour limiter les abus.
--  Aucune adresse e-mail en clair : on stocke une empreinte (hash).
-- ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS purchase_recovery_requests (
    id_request INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email_hash CHAR(64) NOT NULL,
    ip_hash    CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_request),
    KEY idx_email (email_hash, created_at),
    KEY idx_ip (ip_hash, created_at),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

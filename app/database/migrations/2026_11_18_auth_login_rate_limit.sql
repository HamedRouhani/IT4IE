-- Failed login rate-limit storage. Run before deploying the matching PHP changes.
CREATE TABLE IF NOT EXISTS auth_login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    attempted_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_auth_login_attempts_ip_time (ip_hash, attempted_at),
    KEY idx_auth_login_attempts_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

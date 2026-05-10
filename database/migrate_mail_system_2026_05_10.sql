-- Mail system foundation: outbox, password reset tokens, optional parent_email for leads

CREATE TABLE IF NOT EXISTS email_outbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(100) NOT NULL DEFAULT 'raw',
    to_email VARCHAR(190) NOT NULL,
    to_name VARCHAR(190) DEFAULT NULL,
    subject VARCHAR(255) NOT NULL,
    html_body MEDIUMTEXT NOT NULL,
    text_body MEDIUMTEXT NOT NULL,
    payload_json JSON DEFAULT NULL,
    headers_json JSON DEFAULT NULL,
    meta_json JSON DEFAULT NULL,
    status ENUM('pending', 'sending', 'retrying', 'sent', 'failed') NOT NULL DEFAULT 'pending',
    attempts INT NOT NULL DEFAULT 0,
    provider_message_id VARCHAR(190) DEFAULT NULL,
    provider_response TEXT DEFAULT NULL,
    last_error TEXT DEFAULT NULL,
    available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_email_outbox_status_available (status, available_at),
    KEY idx_email_outbox_recipient (to_email),
    KEY idx_email_outbox_locked (locked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(190) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    requested_ip VARCHAR(64) DEFAULT NULL,
    requested_user_agent VARCHAR(500) DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_password_reset_tokens_hash (token_hash),
    KEY idx_password_reset_tokens_email_created (email, created_at),
    KEY idx_password_reset_tokens_user_active (user_id, consumed_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_parent_email_column := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'student_leads'
      AND COLUMN_NAME = 'parent_email'
);

SET @parent_email_sql := IF(
    @has_parent_email_column = 0,
    'ALTER TABLE student_leads ADD COLUMN parent_email VARCHAR(190) DEFAULT NULL AFTER parent_phone',
    'SELECT "Skip: student_leads.parent_email exists"'
);
PREPARE stmt_parent_email FROM @parent_email_sql;
EXECUTE stmt_parent_email;
DEALLOCATE PREPARE stmt_parent_email;

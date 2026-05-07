USE english_center_db;

ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS sender_id BIGINT UNSIGNED NULL AFTER id,
    MODIFY COLUMN user_id BIGINT UNSIGNED NULL,
    ADD INDEX IF NOT EXISTS idx_notifications_sender_id (sender_id);

SET @has_fk_notifications_sender := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'notifications'
      AND CONSTRAINT_NAME = 'fk_notifications_sender'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @sql_notifications_sender_fk := IF(
    @has_fk_notifications_sender = 0,
    'ALTER TABLE notifications ADD CONSTRAINT fk_notifications_sender FOREIGN KEY (sender_id) REFERENCES users(id)',
    'SELECT 1'
);
PREPARE stmt_notifications_sender_fk FROM @sql_notifications_sender_fk;
EXECUTE stmt_notifications_sender_fk;
DEALLOCATE PREPARE stmt_notifications_sender_fk;

CREATE TABLE IF NOT EXISTS notification_targets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id BIGINT UNSIGNED NOT NULL,
    target_type ENUM('ALL', 'ROLE', 'CLASS', 'GROUP', 'USER') NOT NULL,
    target_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_targets_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    UNIQUE KEY uq_notification_target (notification_id, target_type, target_id),
    KEY idx_notification_targets_type_id (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_reads (
    notification_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (notification_id, user_id),
    CONSTRAINT fk_notification_reads_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_reads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_notification_reads_user (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO notification_targets (notification_id, target_type, target_id, created_at)
SELECT n.id, 'USER', n.user_id, n.created_at
FROM notifications n
LEFT JOIN notification_targets nt
    ON nt.notification_id = n.id
   AND nt.target_type = 'USER'
   AND nt.target_id = n.user_id
WHERE n.user_id IS NOT NULL
  AND nt.id IS NULL;

INSERT INTO notification_reads (notification_id, user_id, read_at)
SELECT n.id, n.user_id, COALESCE(n.updated_at, n.created_at)
FROM notifications n
LEFT JOIN notification_reads nr
    ON nr.notification_id = n.id
   AND nr.user_id = n.user_id
WHERE n.user_id IS NOT NULL
  AND n.is_read = 1
  AND nr.notification_id IS NULL;

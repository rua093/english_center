USE english_center_db;

SET NAMES utf8mb4;

SET @has_notifications := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'notifications'
);

SET @has_notification_targets := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'notification_targets'
);

SET @has_classes := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'classes'
);

SET @has_role_permissions := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'role_permissions'
);

SET @teacher_role_id := (
    SELECT id
    FROM roles
    WHERE role_name = 'teacher'
    LIMIT 1
);

SET @notifications_view_permission_id := (
    SELECT id
    FROM permissions
    WHERE slug = 'notifications.view'
    LIMIT 1
);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @teacher_role_id, @notifications_view_permission_id
WHERE @has_role_permissions = 1
  AND @teacher_role_id IS NOT NULL
  AND @notifications_view_permission_id IS NOT NULL;

INSERT IGNORE INTO notification_targets (notification_id, target_type, target_id)
SELECT n.id, 'USER', c.teacher_id
FROM notifications n
INNER JOIN notification_targets nt
    ON nt.notification_id = n.id
   AND nt.target_type = 'CLASS'
INNER JOIN classes c
    ON c.id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(n.action_url, 'class_id=', -1), '&', 1) AS UNSIGNED)
WHERE @has_notifications = 1
  AND @has_notification_targets = 1
  AND @has_classes = 1
  AND n.title = 'Có bài nộp mới'
  AND n.action_url LIKE '%class_id=%'
  AND c.teacher_id > 0;

DELETE nt
FROM notification_targets nt
INNER JOIN notifications n ON n.id = nt.notification_id
WHERE @has_notifications = 1
  AND @has_notification_targets = 1
  AND nt.target_type = 'CLASS'
  AND n.title = 'Có bài nộp mới';

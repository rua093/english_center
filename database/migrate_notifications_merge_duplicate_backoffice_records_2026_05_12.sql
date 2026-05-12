USE english_center_db;

DROP TEMPORARY TABLE IF EXISTS tmp_notification_merge_map;

CREATE TEMPORARY TABLE tmp_notification_merge_map (
    duplicate_id BIGINT UNSIGNED PRIMARY KEY,
    keep_id BIGINT UNSIGNED NOT NULL
);

INSERT INTO tmp_notification_merge_map (duplicate_id, keep_id)
SELECT duplicate_row.id AS duplicate_id, grouped.keep_id
FROM notifications duplicate_row
INNER JOIN (
    SELECT
        MIN(id) AS keep_id,
        sender_id,
        title,
        message,
        COALESCE(action_url, '') AS normalized_action_url,
        created_at
    FROM notifications
    GROUP BY sender_id, title, message, COALESCE(action_url, ''), created_at
    HAVING COUNT(*) > 1
) grouped
    ON grouped.sender_id <=> duplicate_row.sender_id
   AND grouped.title = duplicate_row.title
   AND grouped.message = duplicate_row.message
   AND grouped.normalized_action_url = COALESCE(duplicate_row.action_url, '')
   AND grouped.created_at = duplicate_row.created_at
WHERE duplicate_row.id <> grouped.keep_id;

INSERT IGNORE INTO notification_targets (notification_id, target_type, target_id, created_at)
SELECT
    merge_map.keep_id,
    targets.target_type,
    targets.target_id,
    targets.created_at
FROM tmp_notification_merge_map merge_map
INNER JOIN notification_targets targets
    ON targets.notification_id = merge_map.duplicate_id;

INSERT IGNORE INTO notification_reads (notification_id, user_id, read_at)
SELECT
    merge_map.keep_id,
    reads.user_id,
    reads.read_at
FROM tmp_notification_merge_map merge_map
INNER JOIN notification_reads reads
    ON reads.notification_id = merge_map.duplicate_id;

DELETE duplicate_notifications
FROM notifications duplicate_notifications
INNER JOIN tmp_notification_merge_map merge_map
    ON merge_map.duplicate_id = duplicate_notifications.id;

DROP TEMPORARY TABLE IF EXISTS tmp_notification_merge_map;

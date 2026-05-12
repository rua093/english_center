SET @speaking_youtube_url_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'exams'
      AND column_name = 'speaking_youtube_url'
);

SET @ddl := IF(
    @speaking_youtube_url_exists = 0,
    'ALTER TABLE exams ADD COLUMN speaking_youtube_url VARCHAR(500) DEFAULT NULL AFTER score_speaking',
    'SELECT ''skip speaking_youtube_url'''
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'migration_complete' AS status;

-- Simplify feedbacks to the final structure:
-- sender_id, rating, content, is_public_web, created_at
SET @has_feedbacks := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
);

SET @has_feedbacks_status := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND column_name = 'status'
);

SET @has_feedbacks_class := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND column_name = 'class_id'
);

SET @has_feedbacks_teacher := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND column_name = 'teacher_id'
);

SET @has_feedbacks_is_public_web := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND column_name = 'is_public_web'
);

SET @has_fk_feedbacks_class := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND constraint_name = 'fk_feedbacks_class'
      AND constraint_type = 'FOREIGN KEY'
);

SET @has_fk_feedbacks_teacher := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
      AND constraint_name = 'fk_feedbacks_teacher'
      AND constraint_type = 'FOREIGN KEY'
);

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_is_public_web = 0,
    "ALTER TABLE feedbacks ADD COLUMN is_public_web TINYINT(1) NOT NULL DEFAULT 0 AFTER content",
    "SELECT 'Skip: feedbacks.is_public_web already exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_status = 1,
    "UPDATE feedbacks SET is_public_web = CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END",
    "SELECT 'Skip: feedbacks.status backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_fk_feedbacks_class = 1,
    "ALTER TABLE feedbacks DROP FOREIGN KEY fk_feedbacks_class",
    "SELECT 'Skip: feedbacks.class FK drop not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_fk_feedbacks_teacher = 1,
    "ALTER TABLE feedbacks DROP FOREIGN KEY fk_feedbacks_teacher",
    "SELECT 'Skip: feedbacks.teacher FK drop not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_class = 1,
    "ALTER TABLE feedbacks DROP COLUMN class_id",
    "SELECT 'Skip: feedbacks.class_id drop not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_teacher = 1,
    "ALTER TABLE feedbacks DROP COLUMN teacher_id",
    "SELECT 'Skip: feedbacks.teacher_id drop not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_status = 1,
    "ALTER TABLE feedbacks DROP COLUMN status",
    "SELECT 'Skip: feedbacks.status drop not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

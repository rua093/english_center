USE english_center_db;

SET NAMES utf8mb4;

-- Upgrade approvals.type enum for execution-based workflow.
SET @has_approvals := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'approvals'
);
SET @sql := IF(
    @has_approvals = 1,
    "ALTER TABLE approvals MODIFY COLUMN type ENUM('tuition_discount', 'tuition_delete', 'finance_adjust', 'teacher_leave', 'schedule_change') NOT NULL",
    "SELECT 'Skip: approvals table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure approvals.content exists and can store JSON payload text.
SET @has_content := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'approvals'
      AND column_name = 'content'
);
SET @sql := IF(
    @has_approvals = 1 AND @has_content = 1,
    "ALTER TABLE approvals MODIFY COLUMN content TEXT NOT NULL",
    "SELECT 'Skip: approvals.content not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

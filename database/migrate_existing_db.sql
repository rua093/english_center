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

-- Normalize tuition statuses to debt/paid only.
SET @has_tuition := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
);

SET @sql := IF(
    @has_tuition = 1,
    "UPDATE tuition_fees SET status = 'debt' WHERE status = 'pending'",
    "SELECT 'Skip: tuition_fees table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_tuition = 1,
    "ALTER TABLE tuition_fees MODIFY COLUMN status ENUM('paid', 'debt') NOT NULL DEFAULT 'debt'",
    "SELECT 'Skip: tuition_fees table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_tuition = 1,
    "UPDATE tuition_fees SET status = CASE WHEN amount_paid >= total_amount THEN 'paid' ELSE 'debt' END",
    "SELECT 'Skip: tuition_fees table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Auto-create tuition row when a student enrolls into a class.
DROP TRIGGER IF EXISTS trg_class_students_auto_tuition;
CREATE TRIGGER trg_class_students_auto_tuition
AFTER INSERT ON class_students
FOR EACH ROW
INSERT INTO tuition_fees (
        student_id,
        class_id,
        package_id,
        base_amount,
        discount_type,
        discount_amount,
        total_amount,
        amount_paid,
        payment_plan,
        status
)
SELECT
        NEW.student_id,
        NEW.class_id,
        NULL,
        COALESCE(c.base_price, 0),
        NULL,
        0,
        COALESCE(c.base_price, 0),
        0,
        'full',
        'debt'
FROM classes cl
INNER JOIN courses c ON c.id = cl.course_id
WHERE cl.id = NEW.class_id
    AND NOT EXISTS (
            SELECT 1
            FROM tuition_fees tf
            WHERE tf.student_id = NEW.student_id
                AND tf.class_id = NEW.class_id
    );

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

-- Ensure extracurricular_activities.location exists.
SET @has_activities := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'extracurricular_activities'
);

SET @has_activity_location := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'extracurricular_activities'
      AND column_name = 'location'
);

SET @sql := IF(
    @has_activities = 1 AND @has_activity_location = 0,
    "ALTER TABLE extracurricular_activities ADD COLUMN location VARCHAR(180) NULL AFTER content",
    "SELECT 'Skip: extracurricular_activities.location exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

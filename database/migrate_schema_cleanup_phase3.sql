SET @db := DATABASE();

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'activity_registrations' AND column_name = 'updated_at'
    ),
    'ALTER TABLE activity_registrations DROP COLUMN updated_at',
    "SELECT 'Skip: activity_registrations.updated_at missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'activity_registrations' AND column_name = 'created_at'
    ),
    'ALTER TABLE activity_registrations DROP COLUMN created_at',
    "SELECT 'Skip: activity_registrations.created_at missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

DROP TABLE IF EXISTS bank_accounts;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'class_students' AND column_name = 'learning_status'
    ),
    'ALTER TABLE class_students DROP COLUMN learning_status',
    "SELECT 'Skip: class_students.learning_status missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'courses' AND column_name = 'image_thumbnail'
    ),
    "SELECT 'Skip: courses.image_thumbnail exists' AS info",
    'ALTER TABLE courses ADD COLUMN image_thumbnail VARCHAR(255) NULL AFTER total_sessions'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'exams' AND column_name = 'level_suggested'
    ),
    'ALTER TABLE exams DROP COLUMN level_suggested',
    "SELECT 'Skip: exams.level_suggested missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'lessons' AND column_name = 'attachment_file_path'
    ),
    "SELECT 'Skip: lessons.attachment_file_path exists' AS info",
    'ALTER TABLE lessons ADD COLUMN attachment_file_path VARCHAR(255) NULL AFTER actual_content'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @materialsFk := (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = @db
      AND table_name = 'materials'
      AND column_name = 'course_id'
      AND referenced_table_name IS NOT NULL
    LIMIT 1
);
SET @sql := IF(
    @materialsFk IS NOT NULL,
    CONCAT('ALTER TABLE materials DROP FOREIGN KEY ', @materialsFk),
    "SELECT 'Skip: materials.course_id foreign key missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'materials' AND column_name = 'course_id'
    ),
    'ALTER TABLE materials DROP COLUMN course_id',
    "SELECT 'Skip: materials.course_id missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'payment_transactions' AND column_name = 'transaction_no'
    ),
    'ALTER TABLE payment_transactions DROP COLUMN transaction_no',
    "SELECT 'Skip: payment_transactions.transaction_no missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'payment_transactions' AND column_name = 'raw_response'
    ),
    'ALTER TABLE payment_transactions DROP COLUMN raw_response',
    "SELECT 'Skip: payment_transactions.raw_response missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
    EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_schema = @db AND table_name = 'staff_profiles' AND column_name = 'approval_limit'
    ),
    'ALTER TABLE staff_profiles DROP COLUMN approval_limit',
    "SELECT 'Skip: staff_profiles.approval_limit missing' AS info"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

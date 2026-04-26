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

-- Migrate legacy course_packages to promotions schema.
SET @has_promotions := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
);

SET @has_course_packages := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'course_packages'
);

SET @sql := IF(
    @has_promotions = 0 AND @has_course_packages = 1,
    "RENAME TABLE course_packages TO promotions",
    "SELECT 'Skip: rename course_packages -> promotions not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_promotions := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
);

SET @sql := IF(
    @has_promotions = 0,
    "CREATE TABLE promotions (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, course_id BIGINT UNSIGNED NULL, name VARCHAR(150) NOT NULL, promo_type ENUM('DURATION', 'SOCIAL', 'EVENT', 'GROUP') NOT NULL DEFAULT 'DURATION', discount_value DECIMAL(5,2) NOT NULL DEFAULT 0, start_date DATE NULL, end_date DATE NULL, CONSTRAINT ck_promotions_discount_value CHECK (discount_value >= 0 AND discount_value <= 100), CONSTRAINT ck_promotions_date_range CHECK (start_date IS NULL OR end_date IS NULL OR start_date <= end_date), CONSTRAINT fk_promotions_course FOREIGN KEY (course_id) REFERENCES courses(id), KEY idx_promotions_scope_dates (course_id, start_date, end_date), KEY idx_promotions_promo_type (promo_type)) ENGINE=InnoDB",
    "SELECT 'Skip: promotions table exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_course_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'course_id'
);

SET @pr_course_id_nullable := (
    SELECT COALESCE(MAX(CASE WHEN is_nullable = 'YES' THEN 1 ELSE 0 END), 0)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'course_id'
);

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_course_id = 1 AND @pr_course_id_nullable = 0,
    "ALTER TABLE promotions MODIFY COLUMN course_id BIGINT UNSIGNED NULL",
    "SELECT 'Skip: promotions.course_id already nullable or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Restore feedbacks to sender/class/teacher/rating/content/status.
SET @has_feedbacks := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'feedbacks'
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

SET @default_feedback_class_id := (
    SELECT id FROM classes ORDER BY id ASC LIMIT 1
);

SET @default_feedback_teacher_id := (
    SELECT teacher_id FROM classes ORDER BY id ASC LIMIT 1
);

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_class = 0,
    "ALTER TABLE feedbacks ADD COLUMN class_id BIGINT UNSIGNED NULL AFTER sender_id",
    "SELECT 'Skip: feedbacks.class_id already exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_teacher = 0,
    "ALTER TABLE feedbacks ADD COLUMN teacher_id BIGINT UNSIGNED NULL AFTER class_id",
    "SELECT 'Skip: feedbacks.teacher_id already exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_class = 0 AND @default_feedback_class_id IS NOT NULL,
    CONCAT('UPDATE feedbacks SET class_id = ', @default_feedback_class_id, ' WHERE class_id IS NULL'),
    "SELECT 'Skip: feedbacks.class_id backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_teacher = 0 AND @default_feedback_teacher_id IS NOT NULL,
    CONCAT('UPDATE feedbacks SET teacher_id = ', @default_feedback_teacher_id, ' WHERE teacher_id IS NULL'),
    "SELECT 'Skip: feedbacks.teacher_id backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_class = 1,
    "UPDATE feedbacks SET class_id = COALESCE(class_id, (SELECT id FROM classes ORDER BY id ASC LIMIT 1))",
    "SELECT 'Skip: feedbacks.class_id normalize not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_teacher = 1,
    "UPDATE feedbacks SET teacher_id = COALESCE(teacher_id, (SELECT teacher_id FROM classes ORDER BY id ASC LIMIT 1))",
    "SELECT 'Skip: feedbacks.teacher_id normalize not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_class = 1,
    "ALTER TABLE feedbacks MODIFY COLUMN class_id BIGINT UNSIGNED NOT NULL",
    "SELECT 'Skip: feedbacks.class_id not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_feedbacks_teacher = 1,
    "ALTER TABLE feedbacks MODIFY COLUMN teacher_id BIGINT UNSIGNED NOT NULL",
    "SELECT 'Skip: feedbacks.teacher_id not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_fk_feedbacks_class = 0,
    "ALTER TABLE feedbacks ADD CONSTRAINT fk_feedbacks_class FOREIGN KEY (class_id) REFERENCES classes(id)",
    "SELECT 'Skip: feedbacks.class foreign key exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_feedbacks = 1 AND @has_fk_feedbacks_teacher = 0,
    "ALTER TABLE feedbacks ADD CONSTRAINT fk_feedbacks_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)",
    "SELECT 'Skip: feedbacks.teacher foreign key exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'name'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_pr_name = 0,
    "ALTER TABLE promotions ADD COLUMN name VARCHAR(150) NOT NULL DEFAULT '' AFTER course_id",
    "SELECT 'Skip: promotions.name exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_promo_type := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'promo_type'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_pr_promo_type = 0,
    "ALTER TABLE promotions ADD COLUMN promo_type ENUM('DURATION', 'SOCIAL', 'EVENT', 'GROUP') NOT NULL DEFAULT 'DURATION' AFTER name",
    "SELECT 'Skip: promotions.promo_type exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_discount_value := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'discount_value'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_pr_discount_value = 0,
    "ALTER TABLE promotions ADD COLUMN discount_value DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER promo_type",
    "SELECT 'Skip: promotions.discount_value exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_start_date := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'start_date'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_pr_start_date = 0,
    "ALTER TABLE promotions ADD COLUMN start_date DATE NULL AFTER discount_value",
    "SELECT 'Skip: promotions.start_date exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_end_date := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'end_date'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_pr_end_date = 0,
    "ALTER TABLE promotions ADD COLUMN end_date DATE NULL AFTER start_date",
    "SELECT 'Skip: promotions.end_date exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_pr_package_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'package_name'
);
SET @has_pr_number_of_weeks := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'number_of_weeks'
);
SET @has_pr_discount_rate := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'discount_rate'
);

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_package_name = 1,
    "UPDATE promotions SET name = package_name WHERE TRIM(name) = ''",
    "SELECT 'Skip: promotions.name backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_discount_rate = 1,
    "UPDATE promotions SET discount_value = LEAST(100, GREATEST(0, discount_rate))",
    "SELECT 'Skip: promotions.discount_value backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_number_of_weeks = 1,
    "UPDATE promotions SET promo_type = CASE WHEN number_of_weeks >= 12 THEN 'GROUP' WHEN number_of_weeks >= 8 THEN 'DURATION' WHEN number_of_weeks >= 4 THEN 'EVENT' ELSE 'SOCIAL' END",
    "SELECT 'Skip: promotions.promo_type backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1,
    "UPDATE promotions SET name = CONCAT('Promo #', id) WHERE TRIM(name) = ''",
    "SELECT 'Skip: promotions table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1,
    "UPDATE promotions SET discount_value = LEAST(100, GREATEST(0, discount_value))",
    "SELECT 'Skip: promotions table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1,
    "ALTER TABLE promotions MODIFY COLUMN name VARCHAR(150) NOT NULL, MODIFY COLUMN promo_type ENUM('DURATION', 'SOCIAL', 'EVENT', 'GROUP') NOT NULL, MODIFY COLUMN discount_value DECIMAL(5,2) NOT NULL DEFAULT 0, MODIFY COLUMN start_date DATE NULL, MODIFY COLUMN end_date DATE NULL",
    "SELECT 'Skip: promotions table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_package_name = 1,
    "ALTER TABLE promotions DROP COLUMN package_name",
    "SELECT 'Skip: promotions.package_name missing or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_number_of_weeks = 1,
    "ALTER TABLE promotions DROP COLUMN number_of_weeks",
    "SELECT 'Skip: promotions.number_of_weeks missing or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_promotions = 1 AND @has_pr_discount_rate = 1,
    "ALTER TABLE promotions DROP COLUMN discount_rate",
    "SELECT 'Skip: promotions.discount_rate missing or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_pr_scope_dates := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND index_name = 'idx_promotions_scope_dates'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_idx_pr_scope_dates = 0,
    "ALTER TABLE promotions ADD INDEX idx_promotions_scope_dates (course_id, start_date, end_date)",
    "SELECT 'Skip: idx_promotions_scope_dates exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_pr_promo_type := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND index_name = 'idx_promotions_promo_type'
);
SET @sql := IF(
    @has_promotions = 1 AND @has_idx_pr_promo_type = 0,
    "ALTER TABLE promotions ADD INDEX idx_promotions_promo_type (promo_type)",
    "SELECT 'Skip: idx_promotions_promo_type exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fk_tuition_package := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND constraint_name = 'fk_tuition_package'
      AND constraint_type = 'FOREIGN KEY'
);

SET @tuition_fk_target := (
    SELECT COALESCE(MAX(referenced_table_name), '')
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'tuition_fees'
      AND constraint_name = 'fk_tuition_package'
);

SET @sql := IF(
    @has_tuition = 1 AND @has_promotions = 1 AND @has_fk_tuition_package = 1 AND @tuition_fk_target <> 'promotions',
    "ALTER TABLE tuition_fees DROP FOREIGN KEY fk_tuition_package, ADD CONSTRAINT fk_tuition_package FOREIGN KEY (package_id) REFERENCES promotions(id)",
    "SELECT 'Skip: tuition_fees.fk_tuition_package already points to promotions or missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_tuition = 1 AND @has_promotions = 1 AND @has_fk_tuition_package = 0,
    "ALTER TABLE tuition_fees ADD CONSTRAINT fk_tuition_package FOREIGN KEY (package_id) REFERENCES promotions(id)",
    "SELECT 'Skip: tuition_fees.fk_tuition_package already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_course_packages := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'course_packages'
);

SET @course_packages_ref_count := (
    SELECT COUNT(*)
    FROM information_schema.referential_constraints
    WHERE constraint_schema = DATABASE()
      AND referenced_table_name = 'course_packages'
);

SET @sql := IF(
    @has_course_packages = 1 AND @course_packages_ref_count = 0,
    "DROP TABLE course_packages",
    "SELECT 'Skip: legacy course_packages cleanup not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Disable legacy auto tuition trigger; tuition is now created from explicit registration flow.
DROP TRIGGER IF EXISTS trg_class_students_auto_tuition;

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

-- Ensure materials.description exists.
SET @has_materials := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'materials'
);

SET @has_material_description := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'materials'
      AND column_name = 'description'
);

SET @sql := IF(
    @has_materials = 1 AND @has_material_description = 0,
    "ALTER TABLE materials ADD COLUMN description TEXT NULL AFTER title",
    "SELECT 'Skip: materials.description exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_material_type := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'materials'
      AND column_name = 'type'
);

SET @sql := IF(
    @has_materials = 1 AND @has_material_type = 1,
    "UPDATE materials SET description = CONCAT('Tai lieu ', type) WHERE description IS NULL OR TRIM(description) = ''",
    "SELECT 'Skip: materials.description backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_materials = 1 AND @has_material_type = 1,
    "ALTER TABLE materials DROP COLUMN type",
    "SELECT 'Skip: materials.type missing or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure schedules has overlap-prevention constraints and triggers.
SET @has_schedules := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'schedules'
);

-- Migrate lessons.lesson_date to nullable lessons.schedule_id (plan first, attach schedule later).
SET @has_lessons := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
);

SET @has_lessons_schedule_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND column_name = 'schedule_id'
);

SET @sql := IF(
    @has_lessons = 1 AND @has_lessons_schedule_id = 0,
    "ALTER TABLE lessons ADD COLUMN schedule_id BIGINT UNSIGNED NULL AFTER actual_content",
    "SELECT 'Skip: lessons.schedule_id exists or lessons table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_lessons_schedule_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND column_name = 'schedule_id'
);

SET @has_lessons_lesson_date := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND column_name = 'lesson_date'
);

SET @sql := IF(
    @has_lessons = 1 AND @has_schedules = 1 AND @has_lessons_schedule_id = 1 AND @has_lessons_lesson_date = 1,
    "UPDATE lessons l LEFT JOIN (SELECT s.class_id, s.study_date, MIN(s.id) AS schedule_id FROM schedules s GROUP BY s.class_id, s.study_date) sm ON sm.class_id = l.class_id AND sm.study_date = l.lesson_date SET l.schedule_id = sm.schedule_id WHERE l.schedule_id IS NULL",
    "SELECT 'Skip: lessons.schedule_id backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_lessons = 1 AND @has_schedules = 1 AND @has_lessons_schedule_id = 1,
    "UPDATE lessons l INNER JOIN schedules s ON s.id = l.schedule_id SET l.schedule_id = NULL WHERE s.class_id <> l.class_id",
    "SELECT 'Skip: lessons.class/schedule alignment cleanup not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_lessons = 1 AND @has_schedules = 1 AND @has_lessons_schedule_id = 1,
    "UPDATE lessons l LEFT JOIN schedules s ON s.id = l.schedule_id SET l.schedule_id = NULL WHERE l.schedule_id IS NOT NULL AND s.id IS NULL",
    "SELECT 'Skip: lessons orphan schedule cleanup not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_lessons = 1 AND @has_lessons_schedule_id = 1,
    "ALTER TABLE lessons MODIFY COLUMN schedule_id BIGINT UNSIGNED NULL",
    "SELECT 'Skip: lessons.schedule_id normalize type not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_lessons_class_schedule := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND index_name = 'idx_lessons_class_schedule'
);

SET @sql := IF(
    @has_lessons = 1 AND @has_lessons_schedule_id = 1 AND @has_idx_lessons_class_schedule = 0,
    "ALTER TABLE lessons ADD INDEX idx_lessons_class_schedule (class_id, schedule_id)",
    "SELECT 'Skip: idx_lessons_class_schedule exists or lessons table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fk_lessons_schedule := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND constraint_name = 'fk_lessons_schedule'
      AND constraint_type = 'FOREIGN KEY'
);

SET @lessons_schedule_fk_target := (
    SELECT COALESCE(MAX(referenced_table_name), '')
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'lessons'
      AND constraint_name = 'fk_lessons_schedule'
);

SET @sql := IF(
    @has_lessons = 1 AND @has_schedules = 1 AND @has_lessons_schedule_id = 1 AND @has_fk_lessons_schedule = 1 AND @lessons_schedule_fk_target <> 'schedules',
    "ALTER TABLE lessons DROP FOREIGN KEY fk_lessons_schedule, ADD CONSTRAINT fk_lessons_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL",
    "SELECT 'Skip: lessons.fk_lessons_schedule retarget not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_lessons = 1 AND @has_schedules = 1 AND @has_lessons_schedule_id = 1 AND @has_fk_lessons_schedule = 0,
    "ALTER TABLE lessons ADD CONSTRAINT fk_lessons_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL",
    "SELECT 'Skip: lessons.fk_lessons_schedule exists or lessons/schedules missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_lessons = 1 AND @has_lessons_lesson_date = 1,
    "ALTER TABLE lessons DROP COLUMN lesson_date",
    "SELECT 'Skip: lessons.lesson_date missing or lessons table not found' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_schedule_time_check := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'schedules'
      AND constraint_name = 'ck_schedules_time_range'
      AND constraint_type = 'CHECK'
);

SET @invalid_schedule_ranges := 0;
SET @sql := IF(
    @has_schedules = 1,
    "SELECT COUNT(*) INTO @invalid_schedule_ranges FROM schedules WHERE start_time >= end_time",
    "SELECT 0 INTO @invalid_schedule_ranges"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_schedules = 1 AND @has_schedule_time_check = 0 AND @invalid_schedule_ranges = 0,
    "ALTER TABLE schedules ADD CONSTRAINT ck_schedules_time_range CHECK (start_time < end_time)",
    "SELECT 'Skip: schedules time-range CHECK exists, invalid data found, or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_schedule_class := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'schedules'
      AND index_name = 'idx_schedules_class_date_time'
);
SET @sql := IF(
    @has_schedules = 1 AND @has_idx_schedule_class = 0,
    "ALTER TABLE schedules ADD INDEX idx_schedules_class_date_time (class_id, study_date, start_time, end_time)",
    "SELECT 'Skip: idx_schedules_class_date_time exists or schedules table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_schedule_teacher := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'schedules'
      AND index_name = 'idx_schedules_teacher_date_time'
);
SET @sql := IF(
    @has_schedules = 1 AND @has_idx_schedule_teacher = 0,
    "ALTER TABLE schedules ADD INDEX idx_schedules_teacher_date_time (teacher_id, study_date, start_time, end_time)",
    "SELECT 'Skip: idx_schedules_teacher_date_time exists or schedules table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx_schedule_room := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'schedules'
      AND index_name = 'idx_schedules_room_date_time'
);
SET @sql := IF(
    @has_schedules = 1 AND @has_idx_schedule_room = 0,
    "ALTER TABLE schedules ADD INDEX idx_schedules_room_date_time (room_id, study_date, start_time, end_time)",
    "SELECT 'Skip: idx_schedules_room_date_time exists or schedules table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TRIGGER IF EXISTS trg_schedules_prevent_overlap_insert;
DROP TRIGGER IF EXISTS trg_schedules_prevent_overlap_update;

DELIMITER $$

CREATE TRIGGER trg_schedules_prevent_overlap_insert
BEFORE INSERT ON schedules
FOR EACH ROW
BEGIN
    DECLARE v_conflict_count INT DEFAULT 0;

    IF NEW.start_time >= NEW.end_time THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Khung gio khong hop le: gio ket thuc phai sau gio bat dau.';
    END IF;

    SELECT COUNT(*)
    INTO v_conflict_count
    FROM schedules s
    WHERE s.study_date = NEW.study_date
      AND s.class_id = NEW.class_id
      AND s.start_time < NEW.end_time
      AND s.end_time > NEW.start_time;

    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lop hoc da co lich trung gio.';
    END IF;

    SELECT COUNT(*)
    INTO v_conflict_count
    FROM schedules s
    WHERE s.study_date = NEW.study_date
      AND s.teacher_id = NEW.teacher_id
      AND s.start_time < NEW.end_time
      AND s.end_time > NEW.start_time;

    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Giao vien da co lich trung gio.';
    END IF;

    IF NEW.room_id IS NOT NULL THEN
        SELECT COUNT(*)
        INTO v_conflict_count
        FROM schedules s
        WHERE s.study_date = NEW.study_date
          AND s.room_id = NEW.room_id
          AND s.start_time < NEW.end_time
          AND s.end_time > NEW.start_time;

        IF v_conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Phong hoc da co lich trung gio.';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_schedules_prevent_overlap_update
BEFORE UPDATE ON schedules
FOR EACH ROW
BEGIN
    DECLARE v_conflict_count INT DEFAULT 0;

    IF NEW.start_time >= NEW.end_time THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Khung gio khong hop le: gio ket thuc phai sau gio bat dau.';
    END IF;

    SELECT COUNT(*)
    INTO v_conflict_count
    FROM schedules s
    WHERE s.id <> NEW.id
      AND s.study_date = NEW.study_date
      AND s.class_id = NEW.class_id
      AND s.start_time < NEW.end_time
      AND s.end_time > NEW.start_time;

    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Lop hoc da co lich trung gio.';
    END IF;

    SELECT COUNT(*)
    INTO v_conflict_count
    FROM schedules s
    WHERE s.id <> NEW.id
      AND s.study_date = NEW.study_date
      AND s.teacher_id = NEW.teacher_id
      AND s.start_time < NEW.end_time
      AND s.end_time > NEW.start_time;

    IF v_conflict_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Giao vien da co lich trung gio.';
    END IF;

    IF NEW.room_id IS NOT NULL THEN
        SELECT COUNT(*)
        INTO v_conflict_count
        FROM schedules s
        WHERE s.id <> NEW.id
          AND s.study_date = NEW.study_date
          AND s.room_id = NEW.room_id
          AND s.start_time < NEW.end_time
          AND s.end_time > NEW.start_time;

        IF v_conflict_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Phong hoc da co lich trung gio.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- Add dedicated permissions for course and roadmap management.
INSERT INTO permissions (permission_name, slug) VALUES
('Xem khoa hoc', 'academic.courses.view'),
('Tao khoa hoc', 'academic.courses.create'),
('Cap nhat khoa hoc', 'academic.courses.update'),
('Xoa khoa hoc', 'academic.courses.delete'),
('Xem roadmap khoa hoc', 'academic.roadmaps.view'),
('Tao roadmap khoa hoc', 'academic.roadmaps.create'),
('Cap nhat roadmap khoa hoc', 'academic.roadmaps.update'),
('Xoa roadmap khoa hoc', 'academic.roadmaps.delete')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

-- Copy existing class CRUD grants to the new course/roadmap permissions.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT rp.role_id, p_target.id
FROM role_permissions rp
INNER JOIN permissions p_source ON p_source.id = rp.permission_id
INNER JOIN (
    SELECT 'academic.classes.view' AS source_slug, 'academic.courses.view' AS target_slug
    UNION ALL SELECT 'academic.classes.create', 'academic.courses.create'
    UNION ALL SELECT 'academic.classes.update', 'academic.courses.update'
    UNION ALL SELECT 'academic.classes.delete', 'academic.courses.delete'
    UNION ALL SELECT 'academic.classes.view', 'academic.roadmaps.view'
    UNION ALL SELECT 'academic.classes.create', 'academic.roadmaps.create'
    UNION ALL SELECT 'academic.classes.update', 'academic.roadmaps.update'
    UNION ALL SELECT 'academic.classes.delete', 'academic.roadmaps.delete'
) permission_map ON permission_map.source_slug = p_source.slug
INNER JOIN permissions p_target ON p_target.slug = permission_map.target_slug;

-- Add detailed exam component scores.
SET @score_listening_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'exams'
      AND column_name = 'score_listening'
);
SET @ddl := IF(
    @score_listening_exists = 0,
    'ALTER TABLE exams ADD COLUMN score_listening DECIMAL(5,2) DEFAULT NULL AFTER exam_date',
    'SELECT ''skip score_listening'''
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @score_speaking_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'exams'
      AND column_name = 'score_speaking'
);
SET @ddl := IF(
    @score_speaking_exists = 0,
    'ALTER TABLE exams ADD COLUMN score_speaking DECIMAL(5,2) DEFAULT NULL AFTER score_listening',
    'SELECT ''skip score_speaking'''
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @score_reading_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'exams'
      AND column_name = 'score_reading'
);
SET @ddl := IF(
    @score_reading_exists = 0,
    'ALTER TABLE exams ADD COLUMN score_reading DECIMAL(5,2) DEFAULT NULL AFTER score_speaking',
    'SELECT ''skip score_reading'''
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @score_writing_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'exams'
      AND column_name = 'score_writing'
);
SET @ddl := IF(
    @score_writing_exists = 0,
    'ALTER TABLE exams ADD COLUMN score_writing DECIMAL(5,2) DEFAULT NULL AFTER score_reading',
    'SELECT ''skip score_writing'''
);
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create website intake table for student consultation leads.
SET @has_student_leads := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
);

SET @sql := IF(
    @has_student_leads = 0,
    "CREATE TABLE student_leads (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(150) NOT NULL,
        gender VARCHAR(20) DEFAULT NULL,
        dob DATE DEFAULT NULL,
        interests TEXT,
        school_info VARCHAR(255) DEFAULT NULL,
        personality TEXT,
        parent_contact VARCHAR(255) DEFAULT NULL,
        referral_source VARCHAR(120) DEFAULT NULL,
        current_level VARCHAR(120) DEFAULT NULL,
        study_time VARCHAR(180) DEFAULT NULL,
        parent_expectation TEXT,
        status ENUM('new', 'entry_tested', 'trial_completed', 'official', 'cancelled') NOT NULL DEFAULT 'new',
        admin_note TEXT,
        converted_user_id BIGINT UNSIGNED DEFAULT NULL,
        converted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_student_leads_user FOREIGN KEY (converted_user_id) REFERENCES users(id),
        KEY idx_student_leads_status_created (status, created_at),
        KEY idx_student_leads_parent_contact (parent_contact)
    ) ENGINE=InnoDB",
    "SELECT 'Skip: student_leads table exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill student_leads with new columns for existing deployments.
SET @student_leads_has_student_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'student_name'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_student_name = 0,
    'ALTER TABLE student_leads ADD COLUMN student_name VARCHAR(150) DEFAULT NULL',
    "SELECT 'Skip: student_leads.student_name exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_gender := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'gender'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_gender = 0,
    'ALTER TABLE student_leads ADD COLUMN gender VARCHAR(20) DEFAULT NULL',
    "SELECT 'Skip: student_leads.gender exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_dob := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'dob'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_dob = 0,
    'ALTER TABLE student_leads ADD COLUMN dob DATE DEFAULT NULL',
    "SELECT 'Skip: student_leads.dob exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_interests := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'interests'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_interests = 0,
    'ALTER TABLE student_leads ADD COLUMN interests TEXT',
    "SELECT 'Skip: student_leads.interests exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_school_info := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'school_info'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_school_info = 0,
    'ALTER TABLE student_leads ADD COLUMN school_info VARCHAR(255) DEFAULT NULL',
    "SELECT 'Skip: student_leads.school_info exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_personality := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'personality'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_personality = 0,
    'ALTER TABLE student_leads ADD COLUMN personality TEXT',
    "SELECT 'Skip: student_leads.personality exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_parent_contact := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'parent_contact'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_parent_contact = 0,
    'ALTER TABLE student_leads ADD COLUMN parent_contact VARCHAR(255) DEFAULT NULL',
    "SELECT 'Skip: student_leads.parent_contact exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_referral_source := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'referral_source'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_referral_source = 0,
    'ALTER TABLE student_leads ADD COLUMN referral_source VARCHAR(120) DEFAULT NULL',
    "SELECT 'Skip: student_leads.referral_source exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_current_level := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'current_level'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_current_level = 0,
    'ALTER TABLE student_leads ADD COLUMN current_level VARCHAR(120) DEFAULT NULL',
    "SELECT 'Skip: student_leads.current_level exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_study_time := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'study_time'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_study_time = 0,
    'ALTER TABLE student_leads ADD COLUMN study_time VARCHAR(180) DEFAULT NULL',
    "SELECT 'Skip: student_leads.study_time exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_parent_expectation := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'parent_expectation'
);
SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_parent_expectation = 0,
    'ALTER TABLE student_leads ADD COLUMN parent_expectation TEXT',
    "SELECT 'Skip: student_leads.parent_expectation exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @student_leads_has_student_name := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'student_name'
);
SET @student_leads_has_interests := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'interests'
);
SET @student_leads_has_school_info := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'school_info'
);
SET @student_leads_has_parent_contact := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'parent_contact'
);
SET @student_leads_has_referral_source := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'referral_source'
);
SET @student_leads_has_current_level := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'current_level'
);
SET @student_leads_has_study_time := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'study_time'
);
SET @student_leads_has_parent_expectation := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'student_leads'
            AND column_name = 'parent_expectation'
);

SET @student_leads_has_full_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'full_name'
);
SET @student_leads_has_school_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'school_name'
);
SET @student_leads_has_target_score := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'target_score'
);
SET @student_leads_has_desired_schedule := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'desired_schedule'
);
SET @student_leads_has_source := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'source'
);
SET @student_leads_has_note := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'note'
);
SET @student_leads_has_target_program := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'target_program'
);
SET @student_leads_has_parent_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'parent_name'
);
SET @student_leads_has_parent_phone := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'parent_phone'
);
SET @student_leads_has_phone := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'phone'
);
SET @student_leads_has_email := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND column_name = 'email'
);

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_student_name = 1 AND @student_leads_has_full_name = 1,
    'UPDATE student_leads SET student_name = COALESCE(NULLIF(student_name, ''''), full_name) WHERE (student_name IS NULL OR student_name = '''') AND full_name IS NOT NULL',
    "SELECT 'Skip: student_name backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_school_info = 1 AND @student_leads_has_school_name = 1,
    'UPDATE student_leads SET school_info = COALESCE(NULLIF(school_info, ''''), school_name) WHERE (school_info IS NULL OR school_info = '''') AND school_name IS NOT NULL',
    "SELECT 'Skip: school_info backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_current_level = 1 AND @student_leads_has_target_score = 1,
    'UPDATE student_leads SET current_level = COALESCE(NULLIF(current_level, ''''), target_score) WHERE (current_level IS NULL OR current_level = '''') AND target_score IS NOT NULL',
    "SELECT 'Skip: current_level backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_study_time = 1 AND @student_leads_has_desired_schedule = 1,
    'UPDATE student_leads SET study_time = COALESCE(NULLIF(study_time, ''''), desired_schedule) WHERE (study_time IS NULL OR study_time = '''') AND desired_schedule IS NOT NULL',
    "SELECT 'Skip: study_time backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_referral_source = 1 AND @student_leads_has_source = 1,
    'UPDATE student_leads SET referral_source = COALESCE(NULLIF(referral_source, ''''), source) WHERE (referral_source IS NULL OR referral_source = '''') AND source IS NOT NULL',
    "SELECT 'Skip: referral_source backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_parent_expectation = 1 AND @student_leads_has_note = 1,
    'UPDATE student_leads SET parent_expectation = COALESCE(NULLIF(parent_expectation, ''''), note) WHERE (parent_expectation IS NULL OR parent_expectation = '''') AND note IS NOT NULL',
    "SELECT 'Skip: parent_expectation backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1 AND @student_leads_has_interests = 1 AND @student_leads_has_target_program = 1,
    'UPDATE student_leads SET interests = COALESCE(NULLIF(interests, ''''), target_program) WHERE (interests IS NULL OR interests = '''') AND target_program IS NOT NULL',
    "SELECT 'Skip: interests backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_student_leads = 1
    AND @student_leads_has_parent_contact = 1
    AND @student_leads_has_parent_name = 1
    AND @student_leads_has_parent_phone = 1
    AND @student_leads_has_phone = 1
    AND @student_leads_has_email = 1,
    'UPDATE student_leads SET parent_contact = COALESCE(NULLIF(parent_contact, ''''), CONCAT_WS('' | '', NULLIF(parent_name, ''''), NULLIF(parent_phone, ''''), NULLIF(phone, ''''), NULLIF(email, ''''))) WHERE (parent_contact IS NULL OR parent_contact = '''')',
    "SELECT 'Skip: parent_contact backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_student_leads_parent_contact_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'student_leads'
      AND index_name = 'idx_student_leads_parent_contact'
);
SET @sql := IF(
    @has_student_leads = 1 AND @idx_student_leads_parent_contact_exists = 0,
    'ALTER TABLE student_leads ADD KEY idx_student_leads_parent_contact (parent_contact)',
    "SELECT 'Skip: idx_student_leads_parent_contact exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create website intake table for teacher job applications.
SET @has_job_applications := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
);

SET @sql := IF(
    @has_job_applications = 0,
    "CREATE TABLE job_applications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        contact_info VARCHAR(255) NOT NULL,
        position_applied VARCHAR(150) DEFAULT NULL,
        education_history TEXT,
        work_experience TEXT,
        skills_set TEXT,
        personal_intro TEXT,
        start_date_available VARCHAR(120) DEFAULT NULL,
        salary_expectation VARCHAR(120) DEFAULT NULL,
        cv_file_url VARCHAR(255) DEFAULT NULL,
        status ENUM('new', 'interviewed', 'official', 'rejected') NOT NULL DEFAULT 'new',
        hr_note TEXT,
        converted_user_id BIGINT UNSIGNED DEFAULT NULL,
        converted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_job_applications_user FOREIGN KEY (converted_user_id) REFERENCES users(id),
        KEY idx_job_applications_status_created (status, created_at),
        KEY idx_job_applications_contact_info (contact_info)
    ) ENGINE=InnoDB",
    "SELECT 'Skip: job_applications table exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Backfill job_applications with new columns for existing deployments.
SET @job_applications_has_contact_info := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'contact_info'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_contact_info = 0,
    'ALTER TABLE job_applications ADD COLUMN contact_info VARCHAR(255) DEFAULT NULL',
    "SELECT 'Skip: job_applications.contact_info exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_position_applied := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'position_applied'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_position_applied = 0,
    'ALTER TABLE job_applications ADD COLUMN position_applied VARCHAR(150) DEFAULT NULL',
    "SELECT 'Skip: job_applications.position_applied exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_education_history := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'education_history'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_education_history = 0,
    'ALTER TABLE job_applications ADD COLUMN education_history TEXT',
    "SELECT 'Skip: job_applications.education_history exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_work_experience := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'work_experience'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_work_experience = 0,
    'ALTER TABLE job_applications ADD COLUMN work_experience TEXT',
    "SELECT 'Skip: job_applications.work_experience exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_skills_set := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'skills_set'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_skills_set = 0,
    'ALTER TABLE job_applications ADD COLUMN skills_set TEXT',
    "SELECT 'Skip: job_applications.skills_set exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_personal_intro := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'personal_intro'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_personal_intro = 0,
    'ALTER TABLE job_applications ADD COLUMN personal_intro TEXT',
    "SELECT 'Skip: job_applications.personal_intro exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_start_date_available := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'start_date_available'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_start_date_available = 0,
    'ALTER TABLE job_applications ADD COLUMN start_date_available VARCHAR(120) DEFAULT NULL',
    "SELECT 'Skip: job_applications.start_date_available exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_salary_expectation := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'salary_expectation'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_salary_expectation = 0,
    'ALTER TABLE job_applications ADD COLUMN salary_expectation VARCHAR(120) DEFAULT NULL',
    "SELECT 'Skip: job_applications.salary_expectation exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_cv_file_url := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'cv_file_url'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_cv_file_url = 0,
    'ALTER TABLE job_applications ADD COLUMN cv_file_url VARCHAR(255) DEFAULT NULL',
    "SELECT 'Skip: job_applications.cv_file_url exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_hr_note := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'hr_note'
);
SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_hr_note = 0,
    'ALTER TABLE job_applications ADD COLUMN hr_note TEXT',
    "SELECT 'Skip: job_applications.hr_note exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @job_applications_has_contact_info := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'contact_info'
);
SET @job_applications_has_position_applied := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'position_applied'
);
SET @job_applications_has_education_history := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'education_history'
);
SET @job_applications_has_work_experience := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'work_experience'
);
SET @job_applications_has_personal_intro := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'personal_intro'
);
SET @job_applications_has_start_date_available := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'start_date_available'
);
SET @job_applications_has_hr_note := (
        SELECT COUNT(*)
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
            AND table_name = 'job_applications'
            AND column_name = 'hr_note'
);

SET @job_applications_has_phone := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'phone'
);
SET @job_applications_has_email := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'email'
);
SET @job_applications_has_applying_position := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'applying_position'
);
SET @job_applications_has_degree := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'degree'
);
SET @job_applications_has_experience_years := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'experience_years'
);
SET @job_applications_has_intro := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'intro'
);
SET @job_applications_has_available_schedule := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'available_schedule'
);
SET @job_applications_has_admin_note := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND column_name = 'admin_note'
);

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_contact_info = 1 AND @job_applications_has_phone = 1 AND @job_applications_has_email = 1,
    'UPDATE job_applications SET contact_info = COALESCE(NULLIF(contact_info, ''''), CONCAT_WS('' | '', NULLIF(phone, ''''), NULLIF(email, ''''))) WHERE (contact_info IS NULL OR contact_info = '''')',
    "SELECT 'Skip: contact_info backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_position_applied = 1 AND @job_applications_has_applying_position = 1,
    'UPDATE job_applications SET position_applied = COALESCE(NULLIF(position_applied, ''''), applying_position) WHERE (position_applied IS NULL OR position_applied = '''') AND applying_position IS NOT NULL',
    "SELECT 'Skip: position_applied backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_education_history = 1 AND @job_applications_has_degree = 1,
    'UPDATE job_applications SET education_history = COALESCE(NULLIF(education_history, ''''), degree) WHERE (education_history IS NULL OR education_history = '''') AND degree IS NOT NULL',
    "SELECT 'Skip: education_history backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_work_experience = 1 AND @job_applications_has_experience_years = 1,
    'UPDATE job_applications SET work_experience = COALESCE(NULLIF(work_experience, ''''), CONCAT(CAST(experience_years AS CHAR), '' years'')) WHERE (work_experience IS NULL OR work_experience = '''') AND experience_years IS NOT NULL',
    "SELECT 'Skip: work_experience backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_personal_intro = 1 AND @job_applications_has_intro = 1,
    'UPDATE job_applications SET personal_intro = COALESCE(NULLIF(personal_intro, ''''), intro) WHERE (personal_intro IS NULL OR personal_intro = '''') AND intro IS NOT NULL',
    "SELECT 'Skip: personal_intro backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_start_date_available = 1 AND @job_applications_has_available_schedule = 1,
    'UPDATE job_applications SET start_date_available = COALESCE(NULLIF(start_date_available, ''''), available_schedule) WHERE (start_date_available IS NULL OR start_date_available = '''') AND available_schedule IS NOT NULL',
    "SELECT 'Skip: start_date_available backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_job_applications = 1 AND @job_applications_has_hr_note = 1 AND @job_applications_has_admin_note = 1,
    'UPDATE job_applications SET hr_note = COALESCE(NULLIF(hr_note, ''''), admin_note) WHERE (hr_note IS NULL OR hr_note = '''') AND admin_note IS NOT NULL',
    "SELECT 'Skip: hr_note backfill' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_job_applications_contact_info_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'job_applications'
      AND index_name = 'idx_job_applications_contact_info'
);
SET @sql := IF(
    @has_job_applications = 1 AND @idx_job_applications_contact_info_exists = 0,
    'ALTER TABLE job_applications ADD KEY idx_job_applications_contact_info (contact_info)',
    "SELECT 'Skip: idx_job_applications_contact_info exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add permissions for lead/application management and grant to admin role.
INSERT INTO permissions (permission_name, slug) VALUES
('Quan ly dau moi hoc vien', 'student_lead.manage'),
('Quan ly ho so ung tuyen giao vien', 'job_application.manage')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN ('student_lead.manage', 'job_application.manage')
WHERE r.role_name = 'admin';

-- Migrate assignments.lesson_id to assignments.schedule_id
SET @has_assignments := (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = 'assignments'
);

SET @has_assignments_lesson_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'assignments'
      AND column_name = 'lesson_id'
);

SET @has_assignments_schedule_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'assignments'
      AND column_name = 'schedule_id'
);

SET @sql := IF(
    @has_assignments = 1 AND @has_assignments_lesson_id = 1 AND @has_assignments_schedule_id = 0,
    "ALTER TABLE assignments ADD COLUMN schedule_id BIGINT UNSIGNED NULL AFTER id",
    "SELECT 'Skip: assignments.schedule_id exists or table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_assignments = 1 AND @has_assignments_lesson_id = 1 AND @has_assignments_schedule_id = 0,
    "UPDATE assignments a INNER JOIN lessons l ON l.id = a.lesson_id SET a.schedule_id = l.schedule_id",
    "SELECT 'Skip: assignments.schedule_id backfill not required' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fk_assignments_lesson := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'assignments'
      AND constraint_name = 'fk_assignments_lesson'
      AND constraint_type = 'FOREIGN KEY'
);

SET @sql := IF(
    @has_assignments = 1 AND @has_fk_assignments_lesson = 1,
    "ALTER TABLE assignments DROP FOREIGN KEY fk_assignments_lesson",
    "SELECT 'Skip: assignments.fk_assignments_lesson missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_assignments = 1 AND @has_assignments_lesson_id = 1,
    "ALTER TABLE assignments DROP COLUMN lesson_id",
    "SELECT 'Skip: assignments.lesson_id missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_fk_assignments_schedule := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE table_schema = DATABASE()
      AND table_name = 'assignments'
      AND constraint_name = 'fk_assignments_schedule'
      AND constraint_type = 'FOREIGN KEY'
);

SET @sql := IF(
    @has_assignments = 1 AND @has_fk_assignments_schedule = 0 AND @has_assignments_schedule_id = 1,
    "ALTER TABLE assignments ADD CONSTRAINT fk_assignments_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id)",
    "SELECT 'Skip: assignments.fk_assignments_schedule exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_assignments = 1 AND @has_assignments_schedule_id = 1,
    "ALTER TABLE assignments MODIFY COLUMN schedule_id BIGINT UNSIGNED NOT NULL",
    "SELECT 'Skip: assignments table missing' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


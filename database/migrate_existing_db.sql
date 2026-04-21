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
        full_name VARCHAR(150) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(150) DEFAULT NULL,
        age TINYINT UNSIGNED DEFAULT NULL,
        parent_name VARCHAR(150) DEFAULT NULL,
        parent_phone VARCHAR(20) DEFAULT NULL,
        school_name VARCHAR(180) DEFAULT NULL,
        target_program VARCHAR(180) DEFAULT NULL,
        target_score VARCHAR(50) DEFAULT NULL,
        desired_schedule VARCHAR(180) DEFAULT NULL,
        note TEXT,
        source VARCHAR(80) NOT NULL DEFAULT 'website',
        status ENUM('new', 'entry_tested', 'trial_completed', 'official', 'cancelled') NOT NULL DEFAULT 'new',
        admin_note TEXT,
        converted_user_id BIGINT UNSIGNED DEFAULT NULL,
        converted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_student_leads_user FOREIGN KEY (converted_user_id) REFERENCES users(id),
        KEY idx_student_leads_status_created (status, created_at),
        KEY idx_student_leads_phone (phone)
    ) ENGINE=InnoDB",
    "SELECT 'Skip: student_leads table exists' AS info"
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
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(150) DEFAULT NULL,
        applying_position VARCHAR(120) DEFAULT NULL,
        degree VARCHAR(150) DEFAULT NULL,
        experience_years INT NOT NULL DEFAULT 0,
        available_schedule VARCHAR(180) DEFAULT NULL,
        intro TEXT,
        source VARCHAR(80) NOT NULL DEFAULT 'website',
        status ENUM('new', 'interviewed', 'official', 'rejected') NOT NULL DEFAULT 'new',
        admin_note TEXT,
        converted_user_id BIGINT UNSIGNED DEFAULT NULL,
        converted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_job_applications_user FOREIGN KEY (converted_user_id) REFERENCES users(id),
        KEY idx_job_applications_status_created (status, created_at),
        KEY idx_job_applications_phone (phone)
    ) ENGINE=InnoDB",
    "SELECT 'Skip: job_applications table exists' AS info"
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

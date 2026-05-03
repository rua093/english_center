-- Phase 1: add created_at / updated_at for audit-friendly tables.

SET @table_name := '';
SET @created_after := '';
SET @updated_after := '';
SET @created_backfill := '';
SET @updated_backfill := '';

DROP PROCEDURE IF EXISTS ensure_audit_columns_phase1;

DELIMITER $$
CREATE PROCEDURE ensure_audit_columns_phase1(
    IN p_table_name VARCHAR(128),
    IN p_created_after VARCHAR(128),
    IN p_updated_after VARCHAR(128),
    IN p_created_backfill TEXT,
    IN p_updated_backfill TEXT
)
BEGIN
    DECLARE v_has_table INT DEFAULT 0;
    DECLARE v_has_created_at INT DEFAULT 0;
    DECLARE v_has_updated_at INT DEFAULT 0;

    SELECT COUNT(*)
    INTO v_has_table
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = p_table_name;

    IF v_has_table = 0 THEN
        SELECT CONCAT('Skip: table ', p_table_name, ' missing') AS info;
    ELSE
        SELECT COUNT(*)
        INTO v_has_created_at
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND column_name = 'created_at';

        SELECT COUNT(*)
        INTO v_has_updated_at
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND column_name = 'updated_at';

        IF v_has_created_at = 0 THEN
            SET @sql := CONCAT(
                'ALTER TABLE ', p_table_name,
                ' ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER ', p_created_after
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

            IF p_created_backfill IS NOT NULL AND p_created_backfill <> '' THEN
                SET @sql := CONCAT('UPDATE ', p_table_name, ' SET created_at = ', p_created_backfill);
                PREPARE stmt FROM @sql;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            END IF;
        ELSE
            SELECT CONCAT('Skip: ', p_table_name, '.created_at exists') AS info;
        END IF;

        IF v_has_updated_at = 0 THEN
            SET @sql := CONCAT(
                'ALTER TABLE ', p_table_name,
                ' ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER ', p_updated_after
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

            IF p_updated_backfill IS NOT NULL AND p_updated_backfill <> '' THEN
                SET @sql := CONCAT('UPDATE ', p_table_name, ' SET updated_at = ', p_updated_backfill);
                PREPARE stmt FROM @sql;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            END IF;
        ELSE
            SELECT CONCAT('Skip: ', p_table_name, '.updated_at exists') AS info;
        END IF;
    END IF;
END$$
DELIMITER ;

CALL ensure_audit_columns_phase1('roles', 'description', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('teacher_profiles', 'intro_video_url', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('teacher_certificates', 'image_url', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('courses', 'total_sessions', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('course_roadmaps', 'outline_content', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('promotions', 'end_date', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('rooms', 'room_name', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('classes', 'status', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('class_students', 'enrollment_date', 'created_at', 'COALESCE(TIMESTAMP(enrollment_date, ''00:00:00''), CURRENT_TIMESTAMP)', 'created_at');
CALL ensure_audit_columns_phase1('lessons', 'schedule_id', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('schedules', 'end_time', 'created_at', NULL, NULL);
CALL ensure_audit_columns_phase1('attendance', 'note', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('exams', 'teacher_comment', 'created_at', 'TIMESTAMP(exam_date, ''00:00:00'')', 'created_at');
CALL ensure_audit_columns_phase1('student_profiles', 'entry_test_id', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('staff_profiles', 'position', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('job_applications', 'created_at', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('assignments', 'file_url', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('submissions', 'teacher_comment', 'created_at', 'COALESCE(submitted_at, CURRENT_TIMESTAMP)', 'COALESCE(submitted_at, created_at)');
CALL ensure_audit_columns_phase1('tuition_fees', 'status', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('payment_transactions', 'created_at', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('extracurricular_activities', 'status', 'created_at', 'COALESCE(TIMESTAMP(start_date, ''00:00:00''), CURRENT_TIMESTAMP)', 'created_at');
CALL ensure_audit_columns_phase1('student_portfolios', 'created_at', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('notifications', 'created_at', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('materials', 'file_path', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('feedbacks', 'created_at', 'created_at', NULL, 'created_at');
CALL ensure_audit_columns_phase1('approvals', 'created_at', 'created_at', NULL, 'created_at');

DROP PROCEDURE IF EXISTS ensure_audit_columns_phase1;

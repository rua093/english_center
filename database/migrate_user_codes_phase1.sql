USE english_center_db;

SET @has_teacher_profiles_teacher_code := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'teacher_profiles'
      AND column_name = 'teacher_code'
);
SET @sql := IF(
    @has_teacher_profiles_teacher_code = 0,
    "ALTER TABLE teacher_profiles ADD COLUMN teacher_code VARCHAR(30) NULL AFTER user_id",
    "SELECT 'Skip: teacher_profiles.teacher_code already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE teacher_profiles
SET teacher_code = CONCAT('GV', LPAD(user_id, 5, '0'))
WHERE teacher_code IS NULL OR teacher_code = '';

SET @teacher_profiles_teacher_code_nullable := (
    SELECT IS_NULLABLE
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'teacher_profiles'
      AND column_name = 'teacher_code'
    LIMIT 1
);
SET @sql := IF(
    COALESCE(@teacher_profiles_teacher_code_nullable, '') = 'YES',
    "ALTER TABLE teacher_profiles MODIFY COLUMN teacher_code VARCHAR(30) NOT NULL",
    "SELECT 'Skip: teacher_profiles.teacher_code already NOT NULL' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_teacher_profiles_teacher_code_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'teacher_profiles'
      AND index_name = 'teacher_code'
);
SET @sql := IF(
    @has_teacher_profiles_teacher_code_idx = 0,
    "ALTER TABLE teacher_profiles ADD UNIQUE KEY teacher_code (teacher_code)",
    "SELECT 'Skip: teacher_profiles.teacher_code unique already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_student_profiles_student_code := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'student_code'
);
SET @sql := IF(
    @has_student_profiles_student_code = 0,
    "ALTER TABLE student_profiles ADD COLUMN student_code VARCHAR(30) NULL AFTER user_id",
    "SELECT 'Skip: student_profiles.student_code already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE student_profiles
SET student_code = CONCAT('HV', LPAD(user_id, 5, '0'))
WHERE student_code IS NULL OR student_code = '';

SET @student_profiles_student_code_nullable := (
    SELECT IS_NULLABLE
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'student_code'
    LIMIT 1
);
SET @sql := IF(
    COALESCE(@student_profiles_student_code_nullable, '') = 'YES',
    "ALTER TABLE student_profiles MODIFY COLUMN student_code VARCHAR(30) NOT NULL",
    "SELECT 'Skip: student_profiles.student_code already NOT NULL' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_student_profiles_student_code_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND index_name = 'student_code'
);
SET @sql := IF(
    @has_student_profiles_student_code_idx = 0,
    "ALTER TABLE student_profiles ADD UNIQUE KEY student_code (student_code)",
    "SELECT 'Skip: student_profiles.student_code unique already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

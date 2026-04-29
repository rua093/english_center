USE english_center_db;

SET @has_courses_deleted_at := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'courses'
      AND column_name = 'deleted_at'
);
SET @sql := IF(
    @has_courses_deleted_at = 0,
    "ALTER TABLE courses ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at",
    "SELECT 'Skip: courses.deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_courses_deleted_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'courses'
      AND index_name = 'idx_courses_deleted_at'
);
SET @sql := IF(
    @has_courses_deleted_idx = 0,
    "ALTER TABLE courses ADD INDEX idx_courses_deleted_at (deleted_at)",
    "SELECT 'Skip: idx_courses_deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_promotions_deleted_at := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND column_name = 'deleted_at'
);
SET @sql := IF(
    @has_promotions_deleted_at = 0,
    "ALTER TABLE promotions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at",
    "SELECT 'Skip: promotions.deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_promotions_deleted_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'promotions'
      AND index_name = 'idx_promotions_deleted_at'
);
SET @sql := IF(
    @has_promotions_deleted_idx = 0,
    "ALTER TABLE promotions ADD INDEX idx_promotions_deleted_at (deleted_at)",
    "SELECT 'Skip: idx_promotions_deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_rooms_deleted_at := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'rooms'
      AND column_name = 'deleted_at'
);
SET @sql := IF(
    @has_rooms_deleted_at = 0,
    "ALTER TABLE rooms ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at",
    "SELECT 'Skip: rooms.deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_rooms_deleted_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'rooms'
      AND index_name = 'idx_rooms_deleted_at'
);
SET @sql := IF(
    @has_rooms_deleted_idx = 0,
    "ALTER TABLE rooms ADD INDEX idx_rooms_deleted_at (deleted_at)",
    "SELECT 'Skip: idx_rooms_deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_activities_deleted_at := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'extracurricular_activities'
      AND column_name = 'deleted_at'
);
SET @sql := IF(
    @has_activities_deleted_at = 0,
    "ALTER TABLE extracurricular_activities ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at",
    "SELECT 'Skip: extracurricular_activities.deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_activities_deleted_idx := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'extracurricular_activities'
      AND index_name = 'idx_extracurricular_activities_deleted_at'
);
SET @sql := IF(
    @has_activities_deleted_idx = 0,
    "ALTER TABLE extracurricular_activities ADD INDEX idx_extracurricular_activities_deleted_at (deleted_at)",
    "SELECT 'Skip: idx_extracurricular_activities_deleted_at already exists' AS info"
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

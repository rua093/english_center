USE english_center_db;

CREATE TABLE IF NOT EXISTS parents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    father_name VARCHAR(150) DEFAULT NULL,
    father_phone VARCHAR(20) DEFAULT NULL,
    father_id_card VARCHAR(30) DEFAULT NULL,
    mother_name VARCHAR(150) DEFAULT NULL,
    mother_phone VARCHAR(20) DEFAULT NULL,
    mother_id_card VARCHAR(30) DEFAULT NULL,
    social_links TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @has_parent_id := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'parent_id'
);
SET @sql := IF(
    @has_parent_id = 0,
    'ALTER TABLE student_profiles ADD COLUMN parent_id BIGINT UNSIGNED DEFAULT NULL AFTER user_id',
    'SELECT "Skip: student_profiles.parent_id exists" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DELIMITER $$

DROP PROCEDURE IF EXISTS migrate_student_profile_parents$$
CREATE PROCEDURE migrate_student_profile_parents()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_profile_id BIGINT UNSIGNED;
    DECLARE v_parent_name VARCHAR(150);
    DECLARE v_parent_phone VARCHAR(20);
    DECLARE v_created_at TIMESTAMP;
    DECLARE v_updated_at TIMESTAMP;

    DECLARE cur CURSOR FOR
        SELECT sp.id,
               sp.parent_name,
               sp.parent_phone,
               sp.created_at,
               sp.updated_at
        FROM student_profiles sp
        WHERE sp.parent_id IS NULL
          AND (
              COALESCE(sp.parent_name, '') <> ''
              OR COALESCE(sp.parent_phone, '') <> ''
          );

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    parent_loop: LOOP
        FETCH cur INTO v_profile_id, v_parent_name, v_parent_phone, v_created_at, v_updated_at;
        IF done = 1 THEN
            LEAVE parent_loop;
        END IF;

        INSERT INTO parents (
            father_name,
            father_phone,
            father_id_card,
            mother_name,
            mother_phone,
            mother_id_card,
            social_links,
            created_at,
            updated_at
        ) VALUES (
            NULLIF(TRIM(v_parent_name), ''),
            NULLIF(TRIM(v_parent_phone), ''),
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            v_created_at,
            v_updated_at
        );

        UPDATE student_profiles
        SET parent_id = LAST_INSERT_ID()
        WHERE id = v_profile_id;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

CALL migrate_student_profile_parents();
DROP PROCEDURE IF EXISTS migrate_student_profile_parents;

SET @has_parent_id_index := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND index_name = 'idx_student_profiles_parent_id'
);
SET @sql := IF(
    @has_parent_id_index = 0,
    'ALTER TABLE student_profiles ADD KEY idx_student_profiles_parent_id (parent_id)',
    'SELECT "Skip: index idx_student_profiles_parent_id exists" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_parent_fk := (
    SELECT COUNT(*)
    FROM information_schema.referential_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND constraint_name = 'fk_student_profiles_parent'
);
SET @sql := IF(
    @has_parent_fk = 0,
    'ALTER TABLE student_profiles ADD CONSTRAINT fk_student_profiles_parent FOREIGN KEY (parent_id) REFERENCES parents(id)',
    'SELECT "Skip: fk_student_profiles_parent exists" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_parent_name := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'parent_name'
);
SET @sql := IF(
    @has_parent_name > 0,
    'ALTER TABLE student_profiles DROP COLUMN parent_name',
    'SELECT "Skip: student_profiles.parent_name missing" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_parent_phone := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'student_profiles'
      AND column_name = 'parent_phone'
);
SET @sql := IF(
    @has_parent_phone > 0,
    'ALTER TABLE student_profiles DROP COLUMN parent_phone',
    'SELECT "Skip: student_profiles.parent_phone missing" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Safe migration: drop legacy columns from student_leads
-- Drops `desired_schedule` and `age` if they exist
SET @s = (
  SELECT IF(COUNT(*) > 0,
    'ALTER TABLE student_leads DROP COLUMN desired_schedule;',
    'SELECT 1;'
  )
  FROM information_schema.COLUMNS
  WHERE table_schema = DATABASE() AND table_name = 'student_leads' AND column_name = 'desired_schedule'
);
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @s = (
  SELECT IF(COUNT(*) > 0,
    'ALTER TABLE student_leads DROP COLUMN age;',
    'SELECT 1;'
  )
  FROM information_schema.COLUMNS
  WHERE table_schema = DATABASE() AND table_name = 'student_leads' AND column_name = 'age'
);
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'migration_complete' AS status;

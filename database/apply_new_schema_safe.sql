-- Safe migration: check information_schema and run conditional ALTERs via prepared statements
-- student_leads: add columns if missing
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='student_name';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN student_name varchar(255) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='gender';
SET @s = IF(@c=0, "ALTER TABLE student_leads ADD COLUMN gender ENUM('Nam','Nữ','Khác') DEFAULT NULL", 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='dob';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN dob DATE DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='interests';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN interests TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='school_info';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN school_info varchar(255) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='personality';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN personality varchar(100) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='parent_contact';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN parent_contact TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='referral_source';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN referral_source varchar(255) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='current_level';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN current_level TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='study_time';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN study_time varchar(255) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='parent_expectation';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN parent_expectation TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='status';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN status varchar(50) DEFAULT "new"', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='admin_note';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN admin_note TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='created_at';
SET @s = IF(@c=0, 'ALTER TABLE student_leads ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill core mappings for student_leads (conditional via prepared statements)
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='full_name';
SET @s = IF(@c>0, 'UPDATE student_leads SET student_name = full_name WHERE student_name IS NULL AND full_name IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='parent_name';
SET @s = IF(@c>0, 'UPDATE student_leads SET parent_contact = CONCAT(IFNULL(parent_name, ""), " | ", IFNULL(parent_phone, "")) WHERE parent_contact IS NULL AND (parent_name IS NOT NULL OR parent_phone IS NOT NULL)', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='school_name';
SET @s = IF(@c>0, 'UPDATE student_leads SET school_info = school_name WHERE school_info IS NULL AND school_name IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='target_program';
SET @s = IF(@c>0, 'UPDATE student_leads SET current_level = target_program WHERE current_level IS NULL AND target_program IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop old columns from student_leads if they exist
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='full_name';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN full_name', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='parent_name';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN parent_name', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='parent_phone';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN parent_phone', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='school_name';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN school_name', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='target_program';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN target_program', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='student_leads' AND COLUMN_NAME='target_score';
SET @s = IF(@c>0, 'ALTER TABLE student_leads DROP COLUMN target_score', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- job_applications: add columns if missing
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='contact_info';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN contact_info TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='position_applied';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN position_applied varchar(255) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='education_history';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN education_history TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='work_experience';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN work_experience TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='skills_set';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN skills_set TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='personal_intro';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN personal_intro TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='start_date_available';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN start_date_available DATE DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='salary_expectation';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN salary_expectation varchar(100) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='cv_file_url';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN cv_file_url varchar(500) DEFAULT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='status';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN status varchar(50) DEFAULT "new"', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='hr_note';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN hr_note TEXT', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='created_at';
SET @s = IF(@c=0, 'ALTER TABLE job_applications ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill core mappings for job_applications (conditional via prepared statements)
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='applying_position';
SET @s = IF(@c>0, 'UPDATE job_applications SET position_applied = applying_position WHERE position_applied IS NULL AND applying_position IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='intro';
SET @s = IF(@c>0, 'UPDATE job_applications SET personal_intro = intro WHERE personal_intro IS NULL AND intro IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='experience_years';
SET @s = IF(@c>0, 'UPDATE job_applications SET work_experience = CONCAT("Years: ", experience_years) WHERE work_experience IS NULL AND experience_years IS NOT NULL', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Drop old columns from job_applications if they exist
SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='applying_position';
SET @s = IF(@c>0, 'ALTER TABLE job_applications DROP COLUMN applying_position', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='intro';
SET @s = IF(@c>0, 'ALTER TABLE job_applications DROP COLUMN intro', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='experience_years';
SET @s = IF(@c>0, 'ALTER TABLE job_applications DROP COLUMN experience_years', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='english_center_db' AND TABLE_NAME='job_applications' AND COLUMN_NAME='available_schedule';
SET @s = IF(@c>0, 'ALTER TABLE job_applications DROP COLUMN available_schedule', 'SELECT "skip"');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Done
SELECT 'migration_complete' as status;

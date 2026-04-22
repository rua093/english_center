-- Add columns to student_leads
ALTER TABLE student_leads
  ADD COLUMN student_name varchar(255) DEFAULT NULL,
  ADD COLUMN gender ENUM('Nam','Nữ','Khác') DEFAULT NULL,
  ADD COLUMN dob DATE DEFAULT NULL,
  ADD COLUMN interests TEXT,
  ADD COLUMN school_info varchar(255) DEFAULT NULL,
  ADD COLUMN personality varchar(100) DEFAULT NULL,
  ADD COLUMN parent_contact TEXT,
  ADD COLUMN referral_source varchar(255) DEFAULT NULL,
  ADD COLUMN current_level TEXT,
  ADD COLUMN study_time varchar(255) DEFAULT NULL,
  ADD COLUMN parent_expectation TEXT,
  ADD COLUMN status varchar(50) DEFAULT 'new',
  ADD COLUMN admin_note TEXT,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Backfill student_leads
UPDATE student_leads SET student_name = full_name WHERE student_name IS NULL AND full_name IS NOT NULL;
UPDATE student_leads SET parent_contact = CONCAT('{"parent_name":"', REPLACE(IFNULL(parent_name,''),'"','\\"'), '","parent_phone":"', IFNULL(parent_phone,''),'"}') WHERE parent_contact IS NULL AND (parent_name IS NOT NULL OR parent_phone IS NOT NULL);
UPDATE student_leads SET school_info = school_name WHERE school_info IS NULL AND school_name IS NOT NULL;
UPDATE student_leads SET current_level = target_program WHERE current_level IS NULL AND target_program IS NOT NULL;

-- Drop old columns from student_leads
ALTER TABLE student_leads
  DROP COLUMN IF EXISTS full_name,
  DROP COLUMN IF EXISTS parent_name,
  DROP COLUMN IF EXISTS parent_phone,
  DROP COLUMN IF EXISTS school_name,
  DROP COLUMN IF EXISTS target_program,
  DROP COLUMN IF EXISTS target_score,
  DROP COLUMN IF EXISTS desired_schedule;

-- Add columns to job_applications
ALTER TABLE job_applications
  ADD COLUMN contact_info TEXT,
  ADD COLUMN position_applied varchar(255) DEFAULT NULL,
  ADD COLUMN education_history TEXT,
  ADD COLUMN work_experience TEXT,
  ADD COLUMN skills_set TEXT,
  ADD COLUMN personal_intro TEXT,
  ADD COLUMN start_date_available DATE DEFAULT NULL,
  ADD COLUMN salary_expectation varchar(100) DEFAULT NULL,
  ADD COLUMN cv_file_url varchar(500) DEFAULT NULL,
  ADD COLUMN status varchar(50) DEFAULT 'new',
  ADD COLUMN hr_note TEXT,
  ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Backfill job_applications
UPDATE job_applications SET position_applied = applying_position WHERE position_applied IS NULL AND applying_position IS NOT NULL;
UPDATE job_applications SET personal_intro = intro WHERE personal_intro IS NULL AND intro IS NOT NULL;
UPDATE job_applications SET work_experience = CONCAT('Years: ', experience_years) WHERE work_experience IS NULL AND experience_years IS NOT NULL;

-- Drop old columns from job_applications
ALTER TABLE job_applications
  DROP COLUMN IF EXISTS applying_position,
  DROP COLUMN IF EXISTS intro,
  DROP COLUMN IF EXISTS experience_years,
  DROP COLUMN IF EXISTS available_schedule;

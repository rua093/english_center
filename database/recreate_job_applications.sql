-- Recreate job_applications table per new schema
DROP TABLE IF EXISTS job_applications;

CREATE TABLE job_applications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  position_applied VARCHAR(255) DEFAULT NULL,
  work_mode VARCHAR(50) DEFAULT NULL,
  highest_degree VARCHAR(255) DEFAULT NULL,
  experience_years INT DEFAULT NULL,
  education_detail TEXT DEFAULT NULL,
  work_history TEXT DEFAULT NULL,
  skills_set TEXT DEFAULT NULL,
  bio_summary TEXT DEFAULT NULL,
  start_date DATE DEFAULT NULL,
  salary_expectation VARCHAR(100) DEFAULT NULL,
  cv_file_url VARCHAR(500) DEFAULT NULL,
  status ENUM('PENDING','INTERVIEWING','PASSED','REJECTED') NOT NULL DEFAULT 'PENDING',
  hr_note TEXT DEFAULT NULL,
  converted_user_id BIGINT UNSIGNED DEFAULT NULL,
  converted_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_converted_user_id (converted_user_id),
  UNIQUE KEY ux_job_applications_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'migration_complete' AS status;

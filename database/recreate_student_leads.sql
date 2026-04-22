USE english_center_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS student_leads;

CREATE TABLE student_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(150) NOT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    dob DATE DEFAULT NULL,
    interests TEXT,
    personality TEXT,
    parent_name VARCHAR(150) DEFAULT NULL,
    parent_phone VARCHAR(20) DEFAULT NULL,
    school_name VARCHAR(180) DEFAULT NULL,
    current_grade VARCHAR(120) DEFAULT NULL,
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
    KEY idx_student_leads_parent_phone (parent_phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data
INSERT INTO student_leads (student_name, gender, dob, interests, personality, parent_name, parent_phone, school_name, current_grade, referral_source, current_level, study_time, parent_expectation, status, admin_note)
VALUES
('Pham Thi Hoa', 'female', '2015-08-12', 'Reading, Singing', 'Outgoing', 'Nguyen Van Hoa', '0911222333', 'Truong Tieu hoc A', 'Grade 4', 'Facebook', 'Giao tiep Level 1', 'Evenings', 'Muon hoc tieng Anh can ban', 'new', NULL),
('Le Minh Duc', 'male', NULL, 'Football, Games', 'Curious', 'Le Thi B', '0977001122', 'Truong THCS B', 'Grade 7', 'Referral', 'Giao tiep Level 2', 'Weekends', 'Muon them ky nang nghe noi', 'entry_tested', 'Called and scheduled trial'),
('Tran Thi Lan', 'female', '2012-03-05', 'Drawing', 'Calm', 'Tran Van C', '0909988776', 'Truong Tieu hoc C', 'Grade 6', 'Walk-in', 'Giao tiep Level 1', NULL, 'Tim lop phu hop voi lich hoc', 'trial_completed', NULL);

CREATE DATABASE IF NOT EXISTS english_center_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE english_center_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS approvals;
DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS materials;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS student_portfolios;
DROP TABLE IF EXISTS activity_registrations;
DROP TABLE IF EXISTS extracurricular_activities;
DROP TABLE IF EXISTS bank_accounts;
DROP TABLE IF EXISTS payment_transactions;
DROP TABLE IF EXISTS tuition_fees;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS assignments;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS class_students;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS course_packages;
DROP TABLE IF EXISTS course_roadmaps;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS staff_profiles;
DROP TABLE IF EXISTS job_applications;
DROP TABLE IF EXISTS student_leads;
DROP TABLE IF EXISTS student_profiles;
DROP TABLE IF EXISTS teacher_certificates;
DROP TABLE IF EXISTS teacher_profiles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE teacher_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    degree VARCHAR(150) DEFAULT NULL,
    experience_years INT DEFAULT 0,
    bio TEXT,
    intro_video_url VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_teacher_profiles_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE teacher_certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id BIGINT UNSIGNED NOT NULL,
    certificate_name VARCHAR(120) NOT NULL,
    score VARCHAR(30) DEFAULT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_teacher_cert_teacher FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id)
) ENGINE=InnoDB;

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(180) NOT NULL,
    description TEXT,
    base_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_sessions INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE course_roadmaps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    `order` INT NOT NULL,
    topic_title VARCHAR(200) NOT NULL,
    outline_content TEXT,
    CONSTRAINT fk_roadmap_course FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;

CREATE TABLE promotions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    promo_type ENUM('DURATION', 'SOCIAL', 'EVENT', 'GROUP') NOT NULL,
    discount_value DECIMAL(5,2) NOT NULL DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    CONSTRAINT ck_promotions_discount_value CHECK (discount_value >= 0 AND discount_value <= 100),
    CONSTRAINT ck_promotions_date_range CHECK (start_date IS NULL OR end_date IS NULL OR start_date <= end_date),
    CONSTRAINT fk_promotions_course FOREIGN KEY (course_id) REFERENCES courses(id),
    KEY idx_promotions_scope_dates (course_id, start_date, end_date),
    KEY idx_promotions_promo_type (promo_type)
) ENGINE=InnoDB;

CREATE TABLE rooms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    class_name VARCHAR(150) NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'active', 'graduated', 'cancelled') NOT NULL DEFAULT 'upcoming',
    CONSTRAINT fk_classes_course FOREIGN KEY (course_id) REFERENCES courses(id),
    CONSTRAINT fk_classes_teacher_user FOREIGN KEY (teacher_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE class_students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    learning_status ENUM('trial', 'official') NOT NULL DEFAULT 'official',
    enrollment_date DATE,
    CONSTRAINT fk_class_students_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_class_students_student FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY uq_class_student (class_id, student_id)
) ENGINE=InnoDB;

CREATE TABLE lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    roadmap_id BIGINT UNSIGNED DEFAULT NULL,
    actual_title VARCHAR(200) NOT NULL,
    actual_content TEXT,
    schedule_id BIGINT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_lessons_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_lessons_roadmap FOREIGN KEY (roadmap_id) REFERENCES course_roadmaps(id),
    KEY idx_lessons_class_schedule (class_id, schedule_id)
) ENGINE=InnoDB;

CREATE TABLE schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    room_id BIGINT UNSIGNED DEFAULT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    study_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    CONSTRAINT ck_schedules_time_range CHECK (start_time < end_time),
    CONSTRAINT fk_schedules_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_schedules_room FOREIGN KEY (room_id) REFERENCES rooms(id),
    CONSTRAINT fk_schedules_teacher FOREIGN KEY (teacher_id) REFERENCES users(id),
    KEY idx_schedules_class_date_time (class_id, study_date, start_time, end_time),
    KEY idx_schedules_teacher_date_time (teacher_id, study_date, start_time, end_time),
    KEY idx_schedules_room_date_time (room_id, study_date, start_time, end_time)
) ENGINE=InnoDB;

ALTER TABLE lessons
    ADD CONSTRAINT fk_lessons_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE SET NULL;

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

CREATE TABLE attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    schedule_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_attendance_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id),
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY uq_attendance_student_schedule (schedule_id, student_id)
) ENGINE=InnoDB;

CREATE TABLE exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED DEFAULT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    exam_name VARCHAR(150) NOT NULL,
    exam_type ENUM('entry', 'periodic', 'final') NOT NULL,
    exam_date DATE NOT NULL,
    score_listening DECIMAL(5,2) DEFAULT NULL,
    score_speaking DECIMAL(5,2) DEFAULT NULL,
    score_reading DECIMAL(5,2) DEFAULT NULL,
    score_writing DECIMAL(5,2) DEFAULT NULL,
    result VARCHAR(50) DEFAULT NULL,
    teacher_comment TEXT,
    level_suggested VARCHAR(120) DEFAULT NULL,
    CONSTRAINT fk_exams_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_exams_student FOREIGN KEY (student_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE student_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    parent_name VARCHAR(150) DEFAULT NULL,
    parent_phone VARCHAR(20) DEFAULT NULL,
    school_name VARCHAR(180) DEFAULT NULL,
    target_score VARCHAR(50) DEFAULT NULL,
    entry_test_id BIGINT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_student_profiles_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_student_profiles_entry_test FOREIGN KEY (entry_test_id) REFERENCES exams(id)
) ENGINE=InnoDB;

CREATE TABLE staff_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    position VARCHAR(100) NOT NULL,
    approval_limit DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_staff_profiles_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- Sample data for student_leads (parent_contact split into parent_name and parent_phone, school_info split)
INSERT INTO student_leads (student_name, gender, dob, interests, personality, parent_name, parent_phone, school_name, current_grade, referral_source, current_level, study_time, parent_expectation, status, admin_note)
VALUES
('Pham Thi Hoa', 'female', '2015-08-12', 'Reading, Singing', 'Outgoing', 'Nguyen Van Hoa', '0911222333', 'Truong Tieu hoc A', 'Grade 4', 'Facebook', 'Giao tiep Level 1', 'Evenings', 'Muon hoc tieng Anh can ban', 'new', NULL),
('Le Minh Duc', 'male', NULL, 'Football, Games', 'Curious', 'Le Thi B', '0977001122', 'Truong THCS B', 'Grade 7', 'Referral', 'Giao tiep Level 2', 'Weekends', 'Muon them ky nang nghe noi', 'entry_tested', 'Called and scheduled trial'),
('Tran Thi Lan', 'female', '2012-03-05', 'Drawing', 'Calm', 'Tran Van C', '0909988776', 'Truong Tieu hoc C', 'Grade 6', 'Walk-in', 'Giao tiep Level 1', NULL, 'Tim lop phu hop voi lich hoc', 'trial_completed', NULL);

CREATE TABLE job_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_applications_user FOREIGN KEY (converted_user_id) REFERENCES users(id),
    KEY idx_job_applications_converted_user (converted_user_id),
    UNIQUE KEY ux_job_applications_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for job_applications
INSERT INTO job_applications (full_name, email, phone, address, position_applied, work_mode, highest_degree, experience_years, education_detail, work_history, skills_set, bio_summary, start_date, salary_expectation, cv_file_url, status, hr_note)
VALUES
('Nguyen Van A', 'nguyenvana@example.com', '0987654321', '123 Le Loi, District 1', 'IELTS Teacher', 'Full-time', 'Master of TESOL', 4, 'MA TESOL - University X', '2 years at Center Y; 2 years freelance', 'IELTS, Speaking, Curriculum Design', 'Passionate teacher with focus on communicative skills.', '2026-05-01', '1500$', NULL, 'PENDING', 'Initial application'),
('Tran Thi B', 'tranthib@example.com', '0912345678', '45 Tran Hung Dao, District 3', 'Speaking Coach', 'Part-time', 'Bachelor of English', 2, 'BA English - University Z', '2 years tutoring experience', 'Speaking, Pronunciation', 'Energetic coach specialized in speaking fluency.', '2026-06-15', '800$', NULL, 'PENDING', NULL),
('Le Hoang C', 'lehoangc@example.com', '0901122334', '78 Nguyen Trai, District 5', 'Curriculum Designer', 'Contract', 'PhD Applied Linguistics', 6, 'PhD Applied Linguistics - University Q', 'Led curriculum projects for institutions A and B', 'Curriculum, Assessment, Training', 'Experienced curriculum designer and trainer.', NULL, NULL, NULL, 'PENDING', 'Senior candidate');

CREATE TABLE assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    deadline DATETIME NOT NULL,
    file_url VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_assignments_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id)
) ENGINE=InnoDB;

CREATE TABLE submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    file_url VARCHAR(255) DEFAULT NULL,
    submitted_at DATETIME DEFAULT NULL,
    score DECIMAL(5,2) DEFAULT NULL,
    teacher_comment TEXT,
    CONSTRAINT fk_submissions_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id),
    CONSTRAINT fk_submissions_student FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE KEY uq_submissions_assignment_student (assignment_id, student_id)
) ENGINE=InnoDB;

CREATE TABLE tuition_fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    package_id BIGINT UNSIGNED DEFAULT NULL,
    base_amount DECIMAL(12,2) NOT NULL,
    discount_type VARCHAR(100) DEFAULT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_plan ENUM('full', 'monthly') NOT NULL DEFAULT 'full',
    status ENUM('paid', 'debt') NOT NULL DEFAULT 'debt',
    CONSTRAINT fk_tuition_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_tuition_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_tuition_package FOREIGN KEY (package_id) REFERENCES promotions(id)
) ENGINE=InnoDB;

CREATE TABLE payment_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_fee_id BIGINT UNSIGNED NOT NULL,
    transaction_no VARCHAR(120) NOT NULL,
    payment_method VARCHAR(80) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    transaction_status ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending',
    raw_response JSON DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_tx_tuition FOREIGN KEY (tuition_fee_id) REFERENCES tuition_fees(id)
) ENGINE=InnoDB;

DROP TRIGGER IF EXISTS trg_class_students_auto_tuition;
-- Tuition is now created explicitly via course-registration workflow.

CREATE TABLE bank_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(120) NOT NULL,
    bin VARCHAR(10) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_holder VARCHAR(120) NOT NULL,
    qr_code_static_url VARCHAR(255) DEFAULT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE extracurricular_activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    content TEXT,
    location VARCHAR(180) DEFAULT NULL,
    image_thumbnail VARCHAR(255) DEFAULT NULL,
    fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    start_date DATE,
    status ENUM('upcoming', 'ongoing', 'finished') NOT NULL DEFAULT 'upcoming'
) ENGINE=InnoDB;

CREATE TABLE activity_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    payment_status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid',
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_reg_activity FOREIGN KEY (activity_id) REFERENCES extracurricular_activities(id),
    CONSTRAINT fk_activity_reg_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE student_portfolios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    type ENUM('progress_video', 'activity_photo', 'feedback') NOT NULL,
    media_url VARCHAR(255) NOT NULL,
    description TEXT,
    is_public_web TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_portfolio_student FOREIGN KEY (student_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    CONSTRAINT fk_materials_course FOREIGN KEY (course_id) REFERENCES courses(id)
) ENGINE=InnoDB;

CREATE TABLE feedbacks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    content TEXT,
    status ENUM('pending', 'reviewed', 'closed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedbacks_sender FOREIGN KEY (sender_id) REFERENCES users(id),
    CONSTRAINT fk_feedbacks_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_feedbacks_teacher FOREIGN KEY (teacher_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE approvals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requester_id BIGINT UNSIGNED NOT NULL,
    approver_id BIGINT UNSIGNED DEFAULT NULL,
    type ENUM('tuition_discount', 'tuition_delete', 'finance_adjust', 'teacher_leave', 'schedule_change') NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_approvals_requester FOREIGN KEY (requester_id) REFERENCES users(id),
    CONSTRAINT fk_approvals_approver FOREIGN KEY (approver_id) REFERENCES users(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

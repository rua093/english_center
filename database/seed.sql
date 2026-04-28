USE english_center_db;

-- Demo password for local environment only: 123456 (bcrypt hashed)
INSERT IGNORE INTO roles (role_name, description) VALUES
('admin', 'Quan tri he thong'),
('staff', 'Giao vu va tu van'),
('teacher', 'Giao vien'),
('student', 'Hoc vien');

INSERT IGNORE INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('admin@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'System Admin', (SELECT id FROM roles WHERE role_name = 'admin' LIMIT 1), '0900000001', 'admin@ec.local', 'active'),
('staff@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Academic Staff', (SELECT id FROM roles WHERE role_name = 'staff' LIMIT 1), '0900000002', 'staff@ec.local', 'active'),
('teacher@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Teacher Demo', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000003', 'teacher@ec.local', 'active'),
('student@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Nguyen Van Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000004', 'student@ec.local', 'active');

INSERT INTO teacher_profiles (user_id, degree, experience_years, bio, intro_video_url)
VALUES (3, 'Bachelor of English Language', 6, 'Teacher demo phu trach IELTS, giao tiep va phat am. Tap trung xay dung phan xa, tu vung hoc thuat va ky nang viet luan.', 'https://example.com/intro-teacher.mp4');

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
VALUES
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher@ec.local' LIMIT 1), 'IELTS Academic', '8.0 Overall', 'https://example.com/cert-ielts-academic.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher@ec.local' LIMIT 1), 'TESOL', 'Merit', 'https://example.com/cert-tesol.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher@ec.local' LIMIT 1), 'Cambridge Speaking Workshop', 'Completed', 'https://example.com/cert-speaking-workshop.png');

INSERT INTO courses (course_name, description, base_price, total_sessions) VALUES
('IELTS Foundation', 'Luyen tap 4 ky nang va chien luoc lam bai.', 5800000, 24);

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content) VALUES
(1, 1, 'Introduction & Placement Alignment', 'Can bang muc tieu hoc tap va danh gia dau ky.'),
(1, 2, 'Listening Foundations', 'Nghe y chinh, nghe chi tiet, dictation.');

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date) VALUES
(1, 'Goi 4 tuan', 'EVENT', 0.00, '2026-01-01', '2026-12-31'),
(1, 'Goi 12 tuan', 'DURATION', 3.00, NULL, NULL),
(NULL, 'Uu dai gioi thieu ban hoc', 'GROUP', 5.00, NULL, NULL);

INSERT INTO rooms (room_name) VALUES ('Phong 101');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
VALUES (1, 'IELTS-K20-Toi-2-4', 3, '2026-04-01', '2026-07-01', 'active');

INSERT INTO class_students (class_id, student_id, learning_status, enrollment_date)
VALUES (1, 4, 'official', '2026-04-01');

INSERT INTO lessons (class_id, roadmap_id, actual_title, actual_content, schedule_id) VALUES
(1, 1, 'Orientation Session', 'On dinh muc tieu dau vao va phuong phap hoc.', NULL),
(1, 2, 'Listening Skill Set 1', 'Luyen de nghe section 1 va section 2.', NULL);

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time) VALUES
(1, 1, 3, '2026-04-14', '19:00:00', '21:00:00'),
(1, 1, 3, '2026-04-16', '19:00:00', '21:00:00');

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time)
VALUES (1, 1, 3, CURDATE(), '19:00:00', '21:00:00');

UPDATE lessons
SET schedule_id = (
	SELECT s.id
	FROM schedules s
	WHERE s.class_id = 1 AND s.study_date = '2026-04-14'
	ORDER BY s.id ASC
	LIMIT 1
)
WHERE class_id = 1 AND actual_title = 'Listening Skill Set 1';

INSERT INTO attendance (schedule_id, student_id, status, note) VALUES
(1, 4, 'present', 'Di hoc day du');

INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment, level_suggested)
VALUES (NULL, 4, 'Entry Test', 'entry', '2026-03-28', '5.0', 'Can tang cuong speaking va vocab.', 'IELTS Foundation');

INSERT INTO student_profiles (user_id, parent_name, parent_phone, school_name, target_score, entry_test_id)
VALUES (4, 'Tran Thi Parent', '0909999999', 'THPT Demo', 'IELTS 6.5', 1);

INSERT INTO student_leads (
	student_name,
	gender,
	dob,
	interests,
	personality,
	parent_name,
	parent_phone,
	school_name,
	current_grade,
	referral_source,
	current_level,
	study_time,
	parent_expectation,
	status,
	admin_note,
	converted_user_id,
	converted_at
) VALUES
('Tran Gia Han', 'female', '2010-07-18', 'IELTS Foundation, speaking', 'Can than, tiep thu nhanh', 'Tran Thi Huong', '0911111122', 'THPT Nguyen Hue', NULL, 'website', 'Muc tieu IELTS 6.0', 'Toi T2-T4-T6', 'Can tu van lo trinh 6 thang, uu tien lop si so nho.', 'new', 'Da tiep nhan tu form web.', NULL, NULL),
('Pham Minh Duc', 'male', '2009-10-02', 'IELTS Intensive 6.5+', 'Tu giac, co nen ngu phap tot', 'Pham Van B', '0922222233', 'THPT Tran Phu', NULL, 'website', 'Muc tieu IELTS 6.5', 'Sang T3-T5', 'Gia dinh dong y nhap hoc chinh thuc sau hoc thu.', 'official', 'Da test dau vao va hoc thu dat yeu cau.', 4, '2026-03-30 10:00:00'),
('Le Ngoc Anh', 'female', '2011-03-21', 'Giao tiep Level 1', 'Hoi rut re, can tang phan xa', 'Le Thi C', '0933333444', 'THCS Chu Van An', NULL, 'website', 'Giao tiep co ban', 'Chieu T7-CN', 'Phu huynh muon hoc thu truoc khi dang ky chinh thuc.', 'entry_tested', 'Da hoan thanh test dau vao, cho xep lop hoc thu.', NULL, NULL),
('Do Khanh Linh', 'female', '2008-12-09', 'IELTS Intensive 6.5+', 'Nang dong, chu dong dat cau hoi', 'Do Van D', '0961112233', 'THPT Le Quy Don', NULL, 'facebook', 'Muc tieu IELTS 7.0', 'Toi T3-T5-T7', 'Da hoc thu 2 buoi, dang cho xac nhan nhap hoc.', 'trial_completed', 'Da hoan thanh hoc thu, sale dang follow.', NULL, NULL),
('Bui Tuan Kiet', 'male', '2012-05-14', 'Giao tiep Level 1', 'Can ho tro them tu vung nen', 'Bui Thi E', '0972223344', 'THCS Nguyen Du', NULL, 'website', 'Phat am co ban', 'Sang T7-CN', 'Phu huynh tam hoan vi trung lich hoc chinh.', 'cancelled', 'Khach tam dung, hen lien he lai vao thang sau.', NULL, NULL);

INSERT INTO job_applications (
	full_name,
	email,
	phone,
	address,
	position_applied,
	work_mode,
	highest_degree,
	experience_years,
	education_detail,
	work_history,
	skills_set,
	bio_summary,
	start_date,
	salary_expectation,
	cv_file_url,
	status,
	hr_note,
	converted_user_id,
	converted_at
) VALUES
('Nguyen Thi Ha', 'ha.teacher@example.com', '0934567890', NULL, 'IELTS Teacher', 'Full-time', 'Master of TESOL', 5, 'Master of TESOL', '5 years teaching IELTS classes', 'IELTS, lesson planning, mentoring', 'Da tung day tai trung tam quoc te va co lop IELTS 6.5+.', '2026-04-20', '18-22 trieu', '/assets/uploads/cv-nguyen-thi-ha.pdf', 'PENDING', 'Moi tiep nhan ho so.', NULL, NULL),
('Le Quang Huy', 'huy.applicant@example.com', '0945678901', NULL, 'Speaking Coach', 'Part-time', 'CELTA', 3, 'CELTA', '3 years coaching speaking and communication', 'Speaking workshop, classroom activities', 'Tap trung phan xa speaking va lop giao tiep cho nguoi di lam.', '2026-04-25', '14-18 trieu', '/assets/uploads/cv-le-quang-huy.pdf', 'INTERVIEWING', 'Da phong van vong 1, cho ket qua cuoi.', NULL, NULL),
('Teacher Demo', 'teacher@ec.local', '0900000003', NULL, 'IELTS Teacher', 'Full-time', 'Bachelor of English Language', 6, 'Bachelor of English Language', '6 years IELTS teaching experience', 'IELTS Writing, IELTS Speaking', 'Da trung tuyen va da tao tai khoan giao vien.', NULL, '22-26 trieu', '/assets/uploads/cv-teacher-demo.pdf', 'PASSED', 'Da phong van va onboard.', 3, '2026-03-25 09:15:00'),
('Pham Thu Trang', 'trang.pham@example.com', '0956789012', NULL, 'Junior English Teacher', 'Full-time', 'BA English Linguistics', 2, 'BA English Linguistics', '2 years assistant and junior teacher', 'Young learners, classroom management', 'Co kinh nghiem tro giang va dung lop thieu nhi.', '2026-05-01', '12-15 trieu', '/assets/uploads/cv-pham-thu-trang.pdf', 'PENDING', 'Ho so moi tu kenh linkedin.', NULL, NULL),
('Vo Thanh Son', 'son.vo@example.com', '0987001122', NULL, 'Speaking Coach', 'Part-time', 'CELTA', 4, 'CELTA', '4 years part-time speaking coach', 'Corporate communication, speaking drills', 'Phu hop lop giao tiep nguoi di lam cuoi tuan.', NULL, '13-16 trieu', '/assets/uploads/cv-vo-thanh-son.pdf', 'REJECTED', 'Khong phu hop khung gio trung tam dang can.', NULL, NULL);

INSERT INTO assignments (lesson_id, title, description, deadline, file_url) VALUES
(1, 'Write a self-introduction', 'Viet doan van 180-220 tu gioi thieu ban than.', '2026-04-15 23:59:00', '/assets/uploads/assignment-1.pdf');

INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment) VALUES
(1, 4, '/assets/uploads/submission-student-1.docx', '2026-04-12 20:30:00', 7.5, 'Bai viet on, can sua menh de quan he.');

INSERT INTO tuition_fees (student_id, class_id, package_id, base_amount, discount_type, discount_amount, total_amount, amount_paid, payment_plan, status)
VALUES (4, 1, 2, 5800000, 'DURATION', 3, 5626000, 2800000, 'monthly', 'debt');

INSERT INTO payment_transactions (tuition_fee_id, transaction_no, payment_method, amount, transaction_status, raw_response)
VALUES (1, 'TXN-EC-0001', 'bank_transfer', 2800000, 'success', JSON_OBJECT('bank', 'Vietcombank', 'message', 'Thanh cong'));

INSERT INTO bank_accounts (bank_name, bin, account_number, account_holder, qr_code_static_url, is_default)
VALUES ('Vietcombank', '970436', '0123456789', 'ENGLISH CENTER', 'https://example.com/vietqr.png', 1);

INSERT INTO extracurricular_activities (title, description, content, location, image_thumbnail, fee, start_date, status)
VALUES ('English Camp 2026', 'Hoat dong ngoai khoa ket noi hoc vien.', '<p>Thuc hanh giao tiep voi tinh huong thuc te.</p>', 'Co so 1 - San trung tam', '/assets/uploads/camp-thumb.jpg', 200000, '2026-05-10', 'upcoming');

INSERT INTO activity_registrations (activity_id, user_id, payment_status)
VALUES (1, 4, 'unpaid');

INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web)
VALUES (4, 'progress_video', 'https://example.com/student-progress.mp4', 'Tien bo speaking sau 8 tuan.', 1);

INSERT IGNORE INTO permissions (permission_name, slug) VALUES
('Xem dashboard quan tri', 'admin.dashboard.view'),
('Xem nguoi dung', 'admin.user.view'),
('Tao nguoi dung', 'admin.user.create'),
('Cap nhat nguoi dung', 'admin.user.update'),
('Xoa nguoi dung', 'admin.user.delete'),
('Xem phan quyen vai tro', 'admin.role_permission.view'),
('Cap nhat phan quyen vai tro', 'admin.role_permission.update'),
('Xem lop hoc', 'academic.classes.view'),
('Tao lop hoc', 'academic.classes.create'),
('Cap nhat lop hoc', 'academic.classes.update'),
('Xoa lop hoc', 'academic.classes.delete'),
('Xem khoa hoc', 'academic.courses.view'),
('Tao khoa hoc', 'academic.courses.create'),
('Cap nhat khoa hoc', 'academic.courses.update'),
('Xoa khoa hoc', 'academic.courses.delete'),
('Xem roadmap khoa hoc', 'academic.roadmaps.view'),
('Tao roadmap khoa hoc', 'academic.roadmaps.create'),
('Cap nhat roadmap khoa hoc', 'academic.roadmaps.update'),
('Xoa roadmap khoa hoc', 'academic.roadmaps.delete'),
('Xem lich hoc', 'academic.schedules.view'),
('Tao lich hoc', 'academic.schedules.create'),
('Cap nhat lich hoc', 'academic.schedules.update'),
('Xoa lich hoc', 'academic.schedules.delete'),
('Xem bai tap', 'academic.assignments.view'),
('Tao bai tap', 'academic.assignments.create'),
('Cap nhat bai tap', 'academic.assignments.update'),
('Xoa bai tap', 'academic.assignments.delete'),
('Xem bai nop', 'academic.submissions.view'),
('Cham diem bai nop', 'academic.submissions.grade'),
('Xem phong hoc', 'academic.rooms.view'),
('Tao phong hoc', 'academic.rooms.create'),
('Cap nhat phong hoc', 'academic.rooms.update'),
('Xoa phong hoc', 'academic.rooms.delete'),
('Xem tai lieu', 'materials.view'),
('Tao tai lieu', 'materials.create'),
('Cap nhat tai lieu', 'materials.update'),
('Xoa tai lieu', 'materials.delete'),
('Xem hoc phi', 'finance.tuition.view'),
('Tao hoc phi', 'finance.tuition.create'),
('Cap nhat hoc phi', 'finance.tuition.update'),
('Xoa hoc phi', 'finance.tuition.delete'),
('Xem dang ky', 'finance.registration.view'),
('Tao dang ky', 'finance.registration.create'),
('Cap nhat dang ky', 'finance.registration.update'),
('Xem khuyen mai', 'finance.promotions.view'),
('Tao khuyen mai', 'finance.promotions.create'),
('Cap nhat khuyen mai', 'finance.promotions.update'),
('Xoa khuyen mai', 'finance.promotions.delete'),
('Yeu cau chinh sua tai chinh', 'finance.adjust.request'),
('Xem giao dich thanh toan', 'finance.payments.view'),
('Tao giao dich thanh toan', 'finance.payments.create'),
('Cap nhat giao dich thanh toan', 'finance.payments.update'),
('Xoa giao dich thanh toan', 'finance.payments.delete'),
('Xem danh gia', 'feedback.view'),
('Cap nhat danh gia', 'feedback.update'),
('Xoa danh gia', 'feedback.delete'),
('Xem phe duyet', 'approval.view'),
('Tao phe duyet', 'approval.create'),
('Cap nhat phe duyet', 'approval.update'),
('Xoa phe duyet', 'approval.delete'),
('Tao yeu cau phe duyet', 'approval.request'),
('Xem hoat dong', 'activity.view'),
('Tao hoat dong', 'activity.create'),
('Cap nhat hoat dong', 'activity.update'),
('Xoa hoat dong', 'activity.delete'),
('Xem tai khoan ngan hang', 'bank.view'),
('Tao tai khoan ngan hang', 'bank.create'),
('Cap nhat tai khoan ngan hang', 'bank.update'),
('Xoa tai khoan ngan hang', 'bank.delete'),
('Xem dau moi hoc vien', 'student_lead.view'),
('Cap nhat dau moi hoc vien', 'student_lead.update'),
('Xoa dau moi hoc vien', 'student_lead.delete'),
('Xem ho so ung tuyen giao vien', 'job_application.view'),
('Cap nhat ho so ung tuyen giao vien', 'job_application.update'),
('Xoa ho so ung tuyen giao vien', 'job_application.delete'),
('Xem portfolio hoc vien', 'academic.portfolios.view'),
('Tao portfolio hoc vien', 'academic.portfolios.create'),
('Cap nhat portfolio hoc vien', 'academic.portfolios.update'),
('Xoa portfolio hoc vien', 'academic.portfolios.delete'),
('Nop bai tap tu portal', 'student.assignment.submit'),
('Cap nhat hoc phi tu portal', 'student.tuition.update'),
('Xem thong bao', 'notifications.view'),
('Tao thong bao', 'notifications.create'),
('Cap nhat thong bao', 'notifications.update'),
('Xoa thong bao', 'notifications.delete');

-- ADMIN: full access (safety net)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON 1 = 1
WHERE r.role_name = 'admin';

-- STAFF
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'admin.dashboard.view',
	'academic.classes.view', 'academic.classes.create', 'academic.classes.update',
	'academic.courses.view', 'academic.courses.create', 'academic.courses.update',
	'academic.roadmaps.view', 'academic.roadmaps.create', 'academic.roadmaps.update',
	'academic.schedules.view', 'academic.schedules.create', 'academic.schedules.update',
	'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update',
	'academic.submissions.view',
	'academic.rooms.view', 'academic.rooms.create', 'academic.rooms.update',
	'materials.view', 'materials.create', 'materials.update',
	'finance.tuition.view', 'finance.tuition.create', 'finance.tuition.update',
	'finance.registration.view', 'finance.registration.create', 'finance.registration.update',
	'finance.promotions.view',
	'finance.adjust.request',
	'finance.payments.view', 'finance.payments.update',
	'feedback.view', 'feedback.update',
	'approval.view', 'approval.update', 'approval.request',
	'activity.view', 'activity.create', 'activity.update',
	'bank.view', 'bank.create', 'bank.update',
	'student_lead.view', 'student_lead.update',
	'job_application.view', 'job_application.update',
	'academic.portfolios.view',
	'notifications.view', 'notifications.create', 'notifications.update'
)
WHERE r.role_name = 'staff';

-- TEACHER
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'admin.dashboard.view',
	'academic.schedules.view',
	'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update',
	'academic.submissions.view', 'academic.submissions.grade',
	'materials.view', 'materials.create', 'materials.update', 'materials.delete',
	'approval.request'
)
WHERE r.role_name = 'teacher';

-- STUDENT
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'student.assignment.submit',
	'student.tuition.update',
	'materials.view',
	'activity.view'
)
WHERE r.role_name = 'student';

INSERT INTO notifications (user_id, title, message, is_read) VALUES
(4, 'Nho nop bai tap', 'Ban co 1 bai tap den han vao 23:59 ngay 15/04.', 0),
(4, 'Lich hoc toi nay', 'Lop IELTS-K20 bat dau luc 19:00 tai Phong 101.', 0);

INSERT INTO materials (course_id, title, description, file_path)
SELECT src.course_id, src.title, src.description, src.file_path
FROM (
	SELECT 1 AS course_id, 'Listening Practice Set 01' AS title, 'Bo bai nghe co dap an cho hoc vien moi bat dau.' AS description, '/assets/uploads/material-listening-1.pdf' AS file_path
) AS src
WHERE NOT EXISTS (
	SELECT 1
	FROM materials m
	WHERE m.course_id = src.course_id
	  AND m.title = src.title
	  AND m.file_path = src.file_path
);

INSERT INTO feedbacks (sender_id, rating, content, is_public_web)
VALUES (4, 5, 'Giao vien day de hieu, co dong luc hoc.', 1);

INSERT INTO approvals (requester_id, approver_id, type, content, status)
VALUES (3, 1, 'schedule_change', 'Xin doi lich day ngay 18/04 sang 19/04.', 'pending');

INSERT INTO extracurricular_activities (title, description, content, location, image_thumbnail, fee, start_date, status)
VALUES 
('Conversation Club', 'Hoi hop toan tieng Anh vui ve va ty mat.', '<p>Luyen phan xa noi va tu duy bang tieng Anh.</p>', 'Phong sinh hoat B2', '/assets/uploads/activity-conversation.jpg', 0, '2026-04-20', 'upcoming'),
('Movie Night', 'Xem phim tieng Anh co phu de.', '<p>Luyen nghe qua boi canh phim thuc te.</p>', 'Hoi truong tang 3', '/assets/uploads/activity-movie.jpg', 50000, '2026-04-25', 'upcoming');

INSERT INTO activity_registrations (activity_id, user_id, payment_status, registration_date)
VALUES 
(1, 4, 'unpaid', '2026-04-10 10:00:00'),
(2, 4, 'paid', '2026-04-12 14:30:00');

INSERT INTO bank_accounts (bank_name, bin, account_number, account_holder, qr_code_static_url, is_default)
VALUES 
('VIETCOMBANK', '970436', '1234567890123', 'English Center Co., Ltd', 'https://example.com/vietqr-main.png', 1),
('TECHCOMBANK', '970407', '9876543210987', 'English Center Co., Ltd', 'https://example.com/vietqr-secondary.png', 0);

INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web)
VALUES 
(4, 'progress_video', '/assets/uploads/portfolio-1-progress.mp4', 'Video tien bo ngu phap thang 4.', 1),
(4, 'activity_photo', '/assets/uploads/portfolio-1-activity.jpg', 'Anh hoat dong tai Conversation Club.', 1);

-- Additional diversified demo data (excluding roles and permissions)
INSERT INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('staff.finance@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Thi Finance', (SELECT id FROM roles WHERE role_name = 'staff' LIMIT 1), '0900000010', 'staff.finance@ec.local', 'active'),
('teacher2@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Tran Minh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000011', 'teacher2@ec.local', 'active'),
('teacher3@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Phan Anh Teacher', (SELECT id FROM roles WHERE role_name = 'teacher' LIMIT 1), '0900000012', 'teacher3@ec.local', 'active'),
('student2@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Le Thu Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000013', 'student2@ec.local', 'active'),
('student3@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Do Gia Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000014', 'student3@ec.local', 'active'),
('student4@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Vu Nam Student', (SELECT id FROM roles WHERE role_name = 'student' LIMIT 1), '0900000015', 'student4@ec.local', 'inactive');

INSERT INTO staff_profiles (user_id, position, approval_limit) VALUES
((SELECT id FROM users WHERE username = 'staff@ec.local' LIMIT 1), 'Academic Coordinator', 3000000),
((SELECT id FROM users WHERE username = 'staff.finance@ec.local' LIMIT 1), 'Finance Officer', 8000000);

INSERT INTO teacher_profiles (user_id, degree, experience_years, bio, intro_video_url) VALUES
((SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), 'Master of TESOL', 9, 'Teacher chuyen Business English va speaking workshop.', 'https://example.com/intro-teacher-2.mp4'),
((SELECT id FROM users WHERE username = 'teacher3@ec.local' LIMIT 1), 'CELTA Certificate', 4, 'Teacher phu trach TOEIC va lop speaking co ban.', 'https://example.com/intro-teacher-3.mp4');

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url) VALUES
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher2@ec.local' LIMIT 1), 'TESOL', 'A', 'https://example.com/cert-tesol.png'),
((SELECT tp.id FROM teacher_profiles tp INNER JOIN users u ON u.id = tp.user_id WHERE u.username = 'teacher3@ec.local' LIMIT 1), 'TOEIC', '985', 'https://example.com/cert-toeic-985.png');

INSERT INTO courses (course_name, description, base_price, total_sessions) VALUES
('Business English Intensive', 'Luyen giao tiep cong so, email, presentation va meeting.', 6200000, 30),
('TOEIC Sprint B1-B2', 'Tang toc tu vung va ky nang lam bai TOEIC.', 4200000, 20),
('Kids Speaking Starter', 'Lop noi co ban cho hoc vien tieu hoc.', 3500000, 18);

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content) VALUES
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 1, 'Business Introductions & Networking', 'Mo rong mau cau gioi thieu, social small talk va networking.'),
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 2, 'Email Writing & Follow-up', 'Viet email chuyen nghiep va phan hoi khach hang.'),
((SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 1, 'TOEIC Listening Strategies', 'Ky thuat nghe nhanh Part 1-2 va ghi chu y chinh.'),
((SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 2, 'TOEIC Reading Speed Drills', 'Tang toc do doc va loc keyword Part 5-7.'),
((SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 1, 'Phonics & Basic Pronunciation', 'Luyen am co ban bang tro choi va flashcards.'),
((SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 2, 'Story-based Speaking', 'Ke chuyen ngan de ren phan xa dat cau don gian.');

INSERT INTO promotions (course_id, name, promo_type, discount_value, start_date, end_date) VALUES
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 'Goi 5 tuan', 'SOCIAL', 0.00, NULL, NULL),
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 'Goi 10 tuan', 'DURATION', 3.00, NULL, NULL),
((SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'Goi 4 tuan', 'EVENT', 0.00, '2026-01-01', '2026-12-31'),
((SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'Goi 8 tuan', 'DURATION', 3.00, NULL, NULL),
((SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'Goi 6 tuan', 'SOCIAL', 0.00, NULL, NULL),
((SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'Goi 12 tuan', 'GROUP', 5.00, NULL, NULL);

INSERT INTO rooms (room_name) VALUES
('Phong 102'),
('Online Zoom 01'),
('Phong Lab A');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status) VALUES
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 'BUS-K22-Toi-3-5', (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-05-05', '2026-08-20', 'upcoming'),
((SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'TOEIC-K11-Sang-2-4-6', (SELECT id FROM users WHERE username = 'teacher3@ec.local' LIMIT 1), '2026-03-01', '2026-06-30', 'active'),
((SELECT id FROM courses WHERE course_name = 'IELTS Foundation' ORDER BY id ASC LIMIT 1), 'IELTS-K19-Toi-3-5', (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1), '2025-11-01', '2026-02-28', 'graduated'),
((SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'KIDS-K03-Cuoi-Tuan', (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-04-06', '2026-08-06', 'active'),
((SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 'BUS-K20-Toi-2-4', (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-01-10', '2026-04-10', 'cancelled');

INSERT INTO class_students (class_id, student_id, learning_status, enrollment_date) VALUES
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'trial', '2026-05-03'),
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'official', '2026-05-03'),
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'official', '2026-03-01'),
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), 'official', '2026-03-01'),
((SELECT id FROM classes WHERE class_name = 'IELTS-K19-Toi-3-5' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'official', '2025-11-01'),
((SELECT id FROM classes WHERE class_name = 'KIDS-K03-Cuoi-Tuan' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'official', '2026-04-06');

INSERT INTO lessons (class_id, roadmap_id, actual_title, actual_content, schedule_id) VALUES
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'Business English Intensive' AND cr.`order` = 1 ORDER BY cr.id DESC LIMIT 1), 'Business Pitch Warm-up', 'Tap gioi thieu doanh nghiep trong 60 giay.', NULL),
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'Business English Intensive' AND cr.`order` = 2 ORDER BY cr.id DESC LIMIT 1), 'Meeting Simulation', 'Thuc hanh role-play hop nhom voi tinh huong thuc te.', NULL),
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'TOEIC Sprint B1-B2' AND cr.`order` = 1 ORDER BY cr.id DESC LIMIT 1), 'TOEIC Listening Part 2 Drill', 'Luyen nghe hoi dap nhanh va bo bay distractor.', NULL),
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'TOEIC Sprint B1-B2' AND cr.`order` = 2 ORDER BY cr.id DESC LIMIT 1), 'TOEIC Reading Time Challenge', 'Rang buoc thoi gian de toi uu Part 7.', NULL),
((SELECT id FROM classes WHERE class_name = 'KIDS-K03-Cuoi-Tuan' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'Kids Speaking Starter' AND cr.`order` = 1 ORDER BY cr.id DESC LIMIT 1), 'Kids Ice-breaker', 'Hoc vien tu gioi thieu bang tu vung co ban.', NULL),
((SELECT id FROM classes WHERE class_name = 'KIDS-K03-Cuoi-Tuan' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'Kids Speaking Starter' AND cr.`order` = 2 ORDER BY cr.id DESC LIMIT 1), 'Kids Story Circle', 'Ke chuyen ngan theo tranh va tu khoa.', NULL),
((SELECT id FROM classes WHERE class_name = 'IELTS-K19-Toi-3-5' LIMIT 1), (SELECT cr.id FROM course_roadmaps cr INNER JOIN courses c ON c.id = cr.course_id WHERE c.course_name = 'IELTS Foundation' AND cr.`order` = 2 ORDER BY cr.id ASC LIMIT 1), 'Alumni Mock Test Review', 'Tong ket bai mock test va ke hoach tu hoc.', NULL);

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time) VALUES
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT id FROM rooms WHERE room_name = 'Phong 102' LIMIT 1), (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-05-07', '19:00:00', '21:00:00'),
((SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT id FROM rooms WHERE room_name = 'Online Zoom 01' LIMIT 1), (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-05-09', '19:00:00', '21:00:00'),
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT id FROM rooms WHERE room_name = 'Phong Lab A' LIMIT 1), (SELECT id FROM users WHERE username = 'teacher3@ec.local' LIMIT 1), '2026-04-18', '08:00:00', '10:00:00'),
((SELECT id FROM classes WHERE class_name = 'KIDS-K03-Cuoi-Tuan' LIMIT 1), (SELECT id FROM rooms WHERE room_name = 'Phong 101' LIMIT 1), (SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), '2026-04-20', '09:00:00', '11:00:00'),
((SELECT id FROM classes WHERE class_name = 'IELTS-K19-Toi-3-5' LIMIT 1), (SELECT id FROM rooms WHERE room_name = 'Phong 102' LIMIT 1), (SELECT id FROM users WHERE username = 'teacher@ec.local' LIMIT 1), '2026-02-22', '19:00:00', '21:00:00');

UPDATE lessons
SET schedule_id = (
	SELECT s.id
	FROM schedules s
	INNER JOIN classes c ON c.id = s.class_id
	WHERE c.class_name = 'TOEIC-K11-Sang-2-4-6' AND s.study_date = '2026-04-18'
	ORDER BY s.id ASC
	LIMIT 1
)
WHERE actual_title = 'TOEIC Listening Part 2 Drill';

UPDATE lessons
SET schedule_id = (
	SELECT s.id
	FROM schedules s
	INNER JOIN classes c ON c.id = s.class_id
	WHERE c.class_name = 'KIDS-K03-Cuoi-Tuan' AND s.study_date = '2026-04-20'
	ORDER BY s.id ASC
	LIMIT 1
)
WHERE actual_title = 'Kids Ice-breaker';

UPDATE lessons
SET schedule_id = (
	SELECT s.id
	FROM schedules s
	INNER JOIN classes c ON c.id = s.class_id
	WHERE c.class_name = 'IELTS-K19-Toi-3-5' AND s.study_date = '2026-02-22'
	ORDER BY s.id ASC
	LIMIT 1
)
WHERE actual_title = 'Alumni Mock Test Review';

INSERT INTO attendance (schedule_id, student_id, status, note) VALUES
((SELECT s.id FROM schedules s INNER JOIN classes c ON c.id = s.class_id WHERE c.class_name = 'TOEIC-K11-Sang-2-4-6' AND s.study_date = '2026-04-18' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'present', 'Lam bai tap day du truoc gio hoc.'),
((SELECT s.id FROM schedules s INNER JOIN classes c ON c.id = s.class_id WHERE c.class_name = 'TOEIC-K11-Sang-2-4-6' AND s.study_date = '2026-04-18' LIMIT 1), (SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), 'late', 'Den muon 15 phut.'),
((SELECT s.id FROM schedules s INNER JOIN classes c ON c.id = s.class_id WHERE c.class_name = 'KIDS-K03-Cuoi-Tuan' AND s.study_date = '2026-04-20' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'absent', 'Bao nghi vi ban viec gia dinh.'),
((SELECT s.id FROM schedules s INNER JOIN classes c ON c.id = s.class_id WHERE c.class_name = 'BUS-K22-Toi-3-5' AND s.study_date = '2026-05-07' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'present', 'Tich cuc tham gia thao luan nhom.');

INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment, level_suggested) VALUES
((SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'TOEIC Midterm Mock 01', 'periodic', '2026-04-10', '680', 'Can tap trung vao Part 7 de tang diem.', 'TOEIC 750+'),
((SELECT id FROM classes WHERE class_name = 'IELTS-K19-Toi-3-5' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'IELTS Graduation Test', 'final', '2026-02-26', '6.5', 'Tien bo ro o writing task 2.', 'IELTS 7.0'),
(NULL, (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'Entry Test Student2', 'entry', '2026-02-20', '4.5', 'Nen hoc lai grammar co ban.', 'TOEIC Sprint B1-B2'),
(NULL, (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'Entry Test Student3', 'entry', '2026-03-02', '5.5', 'Phat am kha tot, can bo sung tu vung hoc thuat.', 'Business English Intensive'),
(NULL, (SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), 'Entry Test Student4', 'entry', '2026-04-01', '3.5', 'Can hoc phat am va mau cau giao tiep co ban.', 'Kids Speaking Starter');

INSERT INTO student_profiles (user_id, parent_name, parent_phone, school_name, target_score, entry_test_id) VALUES
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'Pham Thi Lan', '0911111111', 'Dai hoc Kinh te', 'TOEIC 750', (SELECT id FROM exams WHERE exam_name = 'Entry Test Student2' LIMIT 1)),
((SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'Do Van Minh', '0922222222', 'Cong ty ABC', 'Business English B2', (SELECT id FROM exams WHERE exam_name = 'Entry Test Student3' LIMIT 1)),
((SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), 'Vu Thi Hoa', '0933333333', 'THCS Nguyen Hue', 'TOEIC 550', (SELECT id FROM exams WHERE exam_name = 'Entry Test Student4' LIMIT 1));

INSERT INTO assignments (lesson_id, title, description, deadline, file_url) VALUES
((SELECT id FROM lessons WHERE actual_title = 'Business Pitch Warm-up' LIMIT 1), 'Business Pitch Outline', 'Tao slide pitch 3 phut voi 5 bullet chinh.', '2026-05-12 23:59:00', '/assets/uploads/assignment-business-pitch.pdf'),
((SELECT id FROM lessons WHERE actual_title = 'TOEIC Listening Part 2 Drill' LIMIT 1), 'TOEIC Part 2 Worksheet', 'Hoan thanh 40 cau nghe va ghi chu keyword.', '2026-04-19 22:00:00', '/assets/uploads/assignment-toeic-part2.pdf'),
((SELECT id FROM lessons WHERE actual_title = 'Kids Ice-breaker' LIMIT 1), 'Kids Vocabulary Flashcards', 'Thiet ke 12 flashcards va quay video doc to.', '2026-04-23 20:00:00', '/assets/uploads/assignment-kids-flashcards.pdf'),
((SELECT id FROM lessons WHERE actual_title = 'Meeting Simulation' LIMIT 1), 'Email Follow-up Draft', 'Viet email follow-up sau buoi hop voi khach hang.', '2026-05-15 23:00:00', '/assets/uploads/assignment-email-followup.pdf'),
((SELECT id FROM lessons WHERE actual_title = 'TOEIC Reading Time Challenge' LIMIT 1), 'TOEIC Reading Speed Log', 'Ghi log toc do doc trong 7 ngay lien tiep.', '2026-04-22 21:30:00', '/assets/uploads/assignment-toeic-reading-log.pdf');

INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment) VALUES
((SELECT id FROM assignments WHERE title = 'Business Pitch Outline' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), '/assets/uploads/submission-business-pitch-student3.pptx', '2026-05-10 20:15:00', NULL, NULL),
((SELECT id FROM assignments WHERE title = 'TOEIC Part 2 Worksheet' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), '/assets/uploads/submission-toeic-part2-student2.pdf', '2026-04-18 18:05:00', 8.0, 'Tien bo tot, can giu nhip lam bai on dinh.'),
((SELECT id FROM assignments WHERE title = 'Kids Vocabulary Flashcards' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), '/assets/uploads/submission-kids-flashcards-student3.mp4', '2026-04-22 19:40:00', NULL, 'Bai nop hop le, cho cham diem.'),
((SELECT id FROM assignments WHERE title = 'Email Follow-up Draft' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), '/assets/uploads/submission-email-followup-student2.docx', '2026-05-14 22:30:00', 7.0, 'Can chinh lai opening va closing format.'),
((SELECT id FROM assignments WHERE title = 'TOEIC Reading Speed Log' LIMIT 1), (SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), '/assets/uploads/submission-reading-log-student4.xlsx', '2026-04-21 21:00:00', 5.0, 'Can tap deu moi ngay de tang toc do doc.');

INSERT INTO tuition_fees (student_id, class_id, package_id, base_amount, discount_type, discount_amount, total_amount, amount_paid, payment_plan, status) VALUES
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), (SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT cp.id FROM promotions cp INNER JOIN courses c ON c.id = cp.course_id WHERE c.course_name = 'TOEIC Sprint B1-B2' AND cp.name = 'Goi 8 tuan' ORDER BY cp.id DESC LIMIT 1), 4200000, 'DURATION', 3, 4074000, 4074000, 'full', 'paid'),
((SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), (SELECT id FROM classes WHERE class_name = 'BUS-K22-Toi-3-5' LIMIT 1), (SELECT cp.id FROM promotions cp INNER JOIN courses c ON c.id = cp.course_id WHERE c.course_name = 'Business English Intensive' AND cp.name = 'Goi 10 tuan' ORDER BY cp.id DESC LIMIT 1), 6200000, 'DURATION', 3, 6014000, 2000000, 'monthly', 'debt'),
((SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), (SELECT id FROM classes WHERE class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), (SELECT cp.id FROM promotions cp INNER JOIN courses c ON c.id = cp.course_id WHERE c.course_name = 'TOEIC Sprint B1-B2' AND cp.name = 'Goi 4 tuan' ORDER BY cp.id DESC LIMIT 1), 4200000, NULL, 0, 4200000, 0, 'monthly', 'debt'),
((SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), (SELECT id FROM classes WHERE class_name = 'KIDS-K03-Cuoi-Tuan' LIMIT 1), (SELECT cp.id FROM promotions cp INNER JOIN courses c ON c.id = cp.course_id WHERE c.course_name = 'Kids Speaking Starter' AND cp.name = 'Goi 12 tuan' ORDER BY cp.id DESC LIMIT 1), 3500000, 'GROUP', 5, 3325000, 3325000, 'full', 'paid');

INSERT INTO payment_transactions (tuition_fee_id, transaction_no, payment_method, amount, transaction_status, raw_response) VALUES
((SELECT tf.id FROM tuition_fees tf INNER JOIN users u ON u.id = tf.student_id INNER JOIN classes c ON c.id = tf.class_id WHERE u.username = 'student2@ec.local' AND c.class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), 'TXN-EC-0101', 'bank_transfer', 4074000, 'success', JSON_OBJECT('bank', 'BIDV', 'message', 'Thanh cong')),
((SELECT tf.id FROM tuition_fees tf INNER JOIN users u ON u.id = tf.student_id INNER JOIN classes c ON c.id = tf.class_id WHERE u.username = 'student3@ec.local' AND c.class_name = 'BUS-K22-Toi-3-5' LIMIT 1), 'TXN-EC-0102', 'cash', 2000000, 'success', JSON_OBJECT('collector', 'staff.finance@ec.local', 'message', 'Thu dot 1')),
((SELECT tf.id FROM tuition_fees tf INNER JOIN users u ON u.id = tf.student_id INNER JOIN classes c ON c.id = tf.class_id WHERE u.username = 'student3@ec.local' AND c.class_name = 'BUS-K22-Toi-3-5' LIMIT 1), 'TXN-EC-0103', 'ewallet', 1500000, 'pending', JSON_OBJECT('provider', 'Momo', 'message', 'Dang doi doi soat')),
((SELECT tf.id FROM tuition_fees tf INNER JOIN users u ON u.id = tf.student_id INNER JOIN classes c ON c.id = tf.class_id WHERE u.username = 'student4@ec.local' AND c.class_name = 'TOEIC-K11-Sang-2-4-6' LIMIT 1), 'TXN-EC-0104', 'bank_transfer', 500000, 'failed', JSON_OBJECT('bank', 'Techcombank', 'message', 'Sai noi dung chuyen khoan'));

INSERT INTO bank_accounts (bank_name, bin, account_number, account_holder, qr_code_static_url, is_default)
VALUES ('MB BANK', '970422', '5454545454545', 'English Center Co., Ltd', 'https://example.com/vietqr-mbbank.png', 0);

INSERT INTO extracurricular_activities (title, description, content, location, image_thumbnail, fee, start_date, status) VALUES
('Public Speaking Bootcamp', 'Workshop thuc hanh thuyet trinh cho hoc vien trung cap.', '<p>Luyen ky nang tu tin va truc quan hoa bai noi.</p>', 'Phong Workshop A1', '/assets/uploads/activity-speaking-bootcamp.jpg', 150000, '2026-04-15', 'ongoing'),
('Volunteer English Day', 'Ngay tinh nguyen day tieng Anh cho hoc sinh dia phuong.', '<p>Ket hop hoat dong xa hoi va ren ky nang giao tiep.</p>', 'Truong THCS An Binh', '/assets/uploads/activity-volunteer.jpg', 0, '2026-03-22', 'finished'),
('Pronunciation Challenge', 'Mini challenge luyen phat am trong 14 ngay.', '<p>Moi ngay 10 phut shadowing va thu am.</p>', 'Phong Lab 2', '/assets/uploads/activity-pronunciation.jpg', 50000, '2026-05-02', 'upcoming');

INSERT INTO activity_registrations (activity_id, user_id, payment_status, registration_date) VALUES
((SELECT id FROM extracurricular_activities WHERE title = 'Public Speaking Bootcamp' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'paid', '2026-04-13 09:00:00'),
((SELECT id FROM extracurricular_activities WHERE title = 'Public Speaking Bootcamp' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'unpaid', '2026-04-14 11:20:00'),
((SELECT id FROM extracurricular_activities WHERE title = 'Volunteer English Day' LIMIT 1), (SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'paid', '2026-03-18 08:30:00'),
((SELECT id FROM extracurricular_activities WHERE title = 'Pronunciation Challenge' LIMIT 1), (SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'unpaid', '2026-04-28 19:45:00');

INSERT INTO notifications (user_id, title, message, is_read) VALUES
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'Nhac hoc phi dot 2', 'Ban can thanh toan dot 2 truoc ngay 25/04.', 0),
((SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), 'Co yeu cau nghi day', 'Yeu cau nghi day cua ban dang cho duyet.', 1),
((SELECT id FROM users WHERE username = 'staff.finance@ec.local' LIMIT 1), 'Don dieu chinh tai chinh', 'Co 1 don finance_adjust moi can xu ly.', 0);

INSERT INTO materials (course_id, title, description, file_path)
SELECT src.course_id, src.title, src.description, src.file_path
FROM (
		SELECT (SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1) AS course_id, 'Negotiation Roleplay Video' AS title, 'Video thuc hanh dam phan trong boi canh cong viec.' AS description, '/assets/uploads/material-business-negotiation.mp4' AS file_path
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'TOEIC Listening Part 2 Audio', 'File nghe luyen dang cau hoi dap ngan.', '/assets/uploads/material-toeic-part2.mp3'
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'Kids Color Flashcards', 'Bo the mau sac ho tro tu vung cho tre em.', '/assets/uploads/material-kids-flashcards.pdf'
) AS src
WHERE src.course_id IS NOT NULL
	AND NOT EXISTS (
		SELECT 1
		FROM materials m
		WHERE m.course_id = src.course_id
			AND m.title = src.title
			AND m.file_path = src.file_path
	);

INSERT INTO materials (course_id, title, description, file_path)
SELECT src.course_id, src.title, src.description, src.file_path
FROM (
		SELECT (SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1) AS course_id, 'Email Writing Templates' AS title, 'Mau email chuyen nghiep cho moi tinh huong cong viec.' AS description, '/assets/uploads/material-business-email-writing.pdf' AS file_path
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'TOEIC Vocabulary Pack', 'Danh sach tu vung TOEIC theo chu de va bai tap on luuyen.', '/assets/uploads/material-toeic-vocab-pack.pdf'
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'Kids Pronunciation Warmup', 'Hoat dong khop am va lop noi cho hoc vien nhi dong.', '/assets/uploads/material-kids-pronunciation.mp4'
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'Business English Intensive' ORDER BY id DESC LIMIT 1), 'Meeting Phrases Cheat Sheet', 'Cum tu dung nhanh khi hop va trao doi cong viec.', '/assets/uploads/material-meeting-phrases.pdf'
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'TOEIC Sprint B1-B2' ORDER BY id DESC LIMIT 1), 'Reading Strategy Notes', 'Ghi chu chien luoc doc hieu va tim y chinh.', '/assets/uploads/material-reading-strategy.pdf'
		UNION ALL
		SELECT (SELECT id FROM courses WHERE course_name = 'Kids Speaking Starter' ORDER BY id DESC LIMIT 1), 'Story Time Audio', 'File nghe ke chuyen ngan danh cho hoc vien nhi.', '/assets/uploads/material-story-time.mp3'
) AS src
WHERE src.course_id IS NOT NULL
	AND NOT EXISTS (
		SELECT 1
		FROM materials m
		WHERE m.course_id = src.course_id
			AND m.title = src.title
			AND m.file_path = src.file_path
	);

INSERT INTO feedbacks (sender_id, rating, content, is_public_web) VALUES
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 4, 'Lop hoc ro rang, can them bai tap speaking.', 0),
((SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 5, 'Giao vien tao dong luc va phan hoi nhanh.', 1),
((SELECT id FROM users WHERE username = 'student4@ec.local' LIMIT 1), 3, 'Can them video huong dan tu hoc tai nha.', 0);

INSERT INTO approvals (requester_id, approver_id, type, content, status) VALUES
((SELECT id FROM users WHERE username = 'staff.finance@ec.local' LIMIT 1), (SELECT id FROM users WHERE username = 'admin@ec.local' LIMIT 1), 'finance_adjust', 'De nghi dieu chinh cong no cho hoc vien student3 dot 1.', 'approved'),
((SELECT id FROM users WHERE username = 'teacher2@ec.local' LIMIT 1), (SELECT id FROM users WHERE username = 'admin@ec.local' LIMIT 1), 'teacher_leave', 'Xin nghi 1 buoi ngay 10/05 vi ly do suc khoe.', 'rejected'),
((SELECT id FROM users WHERE username = 'staff@ec.local' LIMIT 1), (SELECT id FROM users WHERE username = 'admin@ec.local' LIMIT 1), 'tuition_discount', 'Xin phe duyet giam 5 phan tram hoc phi cho hoc vien gioi.', 'pending'),
((SELECT id FROM users WHERE username = 'staff@ec.local' LIMIT 1), NULL, 'tuition_delete', 'Tao yeu cau xoa hoc phi tao nham cho hoc vien da chuyen lop.', 'pending');

INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web) VALUES
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'feedback', '/assets/uploads/portfolio-student2-feedback.txt', 'Ghi chu cam nhan sau 4 tuan hoc TOEIC.', 0),
((SELECT id FROM users WHERE username = 'student2@ec.local' LIMIT 1), 'progress_video', '/assets/uploads/portfolio-student2-progress.mp4', 'Video doi chieu phat am truoc va sau khoa hoc.', 1),
((SELECT id FROM users WHERE username = 'student3@ec.local' LIMIT 1), 'activity_photo', '/assets/uploads/portfolio-student3-activity.jpg', 'Anh tham gia Public Speaking Bootcamp.', 1);

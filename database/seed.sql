USE english_center_db;

-- Demo password for local environment only: 123456 (bcrypt hashed)
INSERT INTO roles (role_name, description) VALUES
('admin', 'Quan tri he thong'),
('staff', 'Giao vu va tu van'),
('teacher', 'Giao vien'),
('student', 'Hoc vien'),
('parent', 'Phu huynh');

INSERT INTO users (username, password, full_name, role_id, phone, email, status) VALUES
('admin@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'System Admin', 1, '0900000001', 'admin@ec.local', 'active'),
('staff@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Academic Staff', 2, '0900000002', 'staff@ec.local', 'active'),
('teacher@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Teacher Demo', 3, '0900000003', 'teacher@ec.local', 'active'),
('student@ec.local', '$2y$10$5luD5xfAGFeqHwRdPWq1ZezZW43r.qwE2wFcaXCanvh1O0DR8XYum', 'Nguyen Van Student', 4, '0900000004', 'student@ec.local', 'active');

INSERT INTO teacher_profiles (user_id, degree, experience_years, bio, intro_video_url)
VALUES (3, 'Bachelor of English Language', 6, 'Teacher chuyen luyen IELTS va giao tiep.', 'https://example.com/intro-teacher.mp4');

INSERT INTO teacher_certificates (teacher_id, certificate_name, score, image_url)
VALUES (1, 'IELTS', '8.0', 'https://example.com/cert-ielts.png');

INSERT INTO courses (course_name, description, base_price, total_sessions) VALUES
('IELTS Foundation', 'Luyen tap 4 ky nang va chien luoc lam bai.', 5800000, 24);

INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content) VALUES
(1, 1, 'Introduction & Placement Alignment', 'Can bang muc tieu hoc tap va danh gia dau ky.'),
(1, 2, 'Listening Foundations', 'Nghe y chinh, nghe chi tiet, dictation.');

INSERT INTO course_packages (course_id, package_name, number_of_weeks, discount_rate) VALUES
(1, 'Goi 4 tuan', 4, 0.00),
(1, 'Goi 12 tuan', 12, 3.00),
(1, 'Goi 24 tuan', 24, 5.00);

INSERT INTO rooms (room_name) VALUES ('Phong 101');

INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
VALUES (1, 'IELTS-K20-Toi-2-4', 3, '2026-04-01', '2026-07-01', 'active');

INSERT INTO class_students (class_id, student_id, learning_status, enrollment_date)
VALUES (1, 4, 'official', '2026-04-01');

INSERT INTO lessons (class_id, roadmap_id, actual_title, actual_content, lesson_date) VALUES
(1, 1, 'Orientation Session', 'On dinh muc tieu dau vao va phuong phap hoc.', '2026-04-02'),
(1, 2, 'Listening Skill Set 1', 'Luyen de nghe section 1 va section 2.', '2026-04-04');

INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time) VALUES
(1, 1, 3, '2026-04-14', '19:00:00', '21:00:00'),
(1, 1, 3, '2026-04-16', '19:00:00', '21:00:00');

INSERT INTO attendance (schedule_id, student_id, status, note) VALUES
(1, 4, 'present', 'Di hoc day du');

INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment, level_suggested)
VALUES (NULL, 4, 'Entry Test', 'entry', '2026-03-28', '5.0', 'Can tang cuong speaking va vocab.', 'IELTS Foundation');

INSERT INTO student_profiles (user_id, parent_name, parent_phone, school_name, target_score, entry_test_id)
VALUES (4, 'Tran Thi Parent', '0909999999', 'THPT Demo', 'IELTS 6.5', 1);

INSERT INTO assignments (lesson_id, title, description, deadline, file_url) VALUES
(1, 'Write a self-introduction', 'Viet doan van 180-220 tu gioi thieu ban than.', '2026-04-15 23:59:00', '/assets/uploads/assignment-1.pdf');

INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at, score, teacher_comment) VALUES
(1, 4, '/assets/uploads/submission-student-1.docx', '2026-04-12 20:30:00', 7.5, 'Bai viet on, can sua menh de quan he.');

INSERT INTO tuition_fees (student_id, class_id, package_id, base_amount, discount_type, discount_amount, total_amount, amount_paid, payment_plan, status)
VALUES (4, 1, 2, 5800000, 'package_discount', 174000, 5626000, 2800000, 'monthly', 'debt');

INSERT INTO payment_transactions (tuition_fee_id, transaction_no, payment_method, amount, transaction_status, raw_response)
VALUES (1, 'TXN-EC-0001', 'bank_transfer', 2800000, 'success', JSON_OBJECT('bank', 'Vietcombank', 'message', 'Thanh cong'));

INSERT INTO bank_accounts (bank_name, bin, account_number, account_holder, qr_code_static_url, is_default)
VALUES ('Vietcombank', '970436', '0123456789', 'ENGLISH CENTER', 'https://example.com/vietqr.png', 1);

INSERT INTO extracurricular_activities (title, description, content, image_thumbnail, fee, start_date, status)
VALUES ('English Camp 2026', 'Hoat dong ngoai khoa ket noi hoc vien.', '<p>Thuc hanh giao tiep voi tinh huong thuc te.</p>', '/assets/uploads/camp-thumb.jpg', 200000, '2026-05-10', 'upcoming');

INSERT INTO activity_registrations (activity_id, user_id, payment_status)
VALUES (1, 4, 'unpaid');

INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web)
VALUES (4, 'progress_video', 'https://example.com/student-progress.mp4', 'Tien bo speaking sau 8 tuan.', 1);

INSERT INTO permissions (permission_name, slug) VALUES
('Xem dashboard hoc vien', 'student.dashboard.view'),
('Xem bai tap hoc vien', 'student.assignment.view'),
('Xem hoc phi hoc vien', 'student.tuition.view'),
('Nop bai tap tu portal', 'student.assignment.submit'),
('Cap nhat hoc phi tu portal', 'student.tuition.update'),
('CRUD lop hoc', 'academic.classes.manage'),
('CRUD lich hoc', 'academic.schedules.manage'),
('CRUD bai tap', 'academic.assignments.manage'),
('Cham diem bai nop', 'academic.submissions.grade'),
('Quan ly tai lieu', 'materials.manage'),
('Xem dashboard quan tri', 'admin.dashboard.view'),
('Quan tri nguoi dung', 'admin.user.manage'),
('Xem lop hoc', 'academic.classes.view'),
('Tao lop hoc', 'academic.classes.create'),
('Cap nhat lop hoc', 'academic.classes.update'),
('Xoa lop hoc', 'academic.classes.delete'),
('Xem lich hoc', 'academic.schedules.view'),
('Tao lich hoc', 'academic.schedules.create'),
('Cap nhat lich hoc', 'academic.schedules.update'),
('Xoa lich hoc', 'academic.schedules.delete'),
('Xem bai tap', 'academic.assignments.view'),
('Tao bai tap', 'academic.assignments.create'),
('Cap nhat bai tap', 'academic.assignments.update'),
('Xoa bai tap', 'academic.assignments.delete'),
('Xem bai nop', 'academic.submissions.view'),
('Xem tai lieu', 'materials.view'),
('Tao tai lieu', 'materials.create'),
('Cap nhat tai lieu', 'materials.update'),
('Xoa tai lieu', 'materials.delete'),
('Xem hoc phi', 'finance.tuition.view'),
('Xem giao dich', 'finance.payment.view'),
('Xem danh gia', 'feedback.view'),
('Tao danh gia', 'feedback.create'),
('Cap nhat danh gia', 'feedback.update'),
('Xoa danh gia', 'feedback.delete'),
('Xem phe duyet', 'approval.view'),
('Cap nhat phe duyet', 'approval.update'),
('Xem hoat dong', 'activity.view'),
('Tao hoat dong', 'activity.create'),
('Cap nhat hoat dong', 'activity.update'),
('Xoa hoat dong', 'activity.delete'),
('Xem tai khoan ngan hang', 'bank.view'),
('Tao tai khoan ngan hang', 'bank.create'),
('Cap nhat tai khoan ngan hang', 'bank.update'),
('Xoa tai khoan ngan hang', 'bank.delete');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'student.dashboard.view',
	'student.assignment.view',
	'student.tuition.view',
	'student.assignment.submit',
	'student.tuition.update',
	'academic.classes.manage',
	'academic.schedules.manage',
	'academic.assignments.manage',
	'academic.submissions.grade',
	'materials.manage',
	'admin.dashboard.view',
	'admin.user.manage',
	'finance.tuition.manage',
	'finance.payment.manage',
	'feedback.manage',
	'approval.manage',
	'activity.manage',
	'bank.manage'
	,'academic.classes.view','academic.classes.create','academic.classes.update','academic.classes.delete'
	,'academic.schedules.view','academic.schedules.create','academic.schedules.update','academic.schedules.delete'
	,'academic.assignments.view','academic.assignments.create','academic.assignments.update','academic.assignments.delete'
	,'academic.submissions.view'
	,'materials.view','materials.create','materials.update','materials.delete'
	,'finance.tuition.view','finance.payment.view'
	,'feedback.view','feedback.create','feedback.update','feedback.delete'
	,'approval.view','approval.update'
	,'activity.view','activity.create','activity.update','activity.delete'
	,'bank.view','bank.create','bank.update','bank.delete'
)
WHERE r.role_name = 'admin';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'academic.classes.manage',
	'academic.schedules.manage',
	'academic.assignments.manage',
	'academic.submissions.grade',
	'academic.classes.view',
	'academic.classes.create',
	'academic.classes.update',
	'academic.schedules.view',
	'academic.schedules.create',
	'academic.schedules.update',
	'academic.assignments.view',
	'academic.assignments.create',
	'academic.assignments.update',
	'academic.submissions.view',
	'materials.manage',
	'materials.view',
	'materials.create',
	'materials.update',
	'admin.dashboard.view'
)
WHERE r.role_name = 'teacher';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'academic.classes.manage',
	'academic.schedules.manage',
	'academic.assignments.manage',
	'academic.submissions.grade',
	'materials.manage',
	'materials.view',
	'admin.dashboard.view',
	'finance.tuition.view',
	'finance.payment.view',
	'feedback.view',
	'feedback.create',
	'feedback.update',
	'feedback.delete',
	'approval.view',
	'approval.update',
	'activity.view',
	'activity.create',
	'activity.update',
	'activity.delete',
	'bank.view','bank.create','bank.update','bank.delete'
)
WHERE r.role_name = 'staff';

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
	'student.dashboard.view',
	'student.assignment.view',
	'student.tuition.view',
	'student.assignment.submit',
	'student.tuition.update'
)
WHERE r.role_name = 'student';

INSERT INTO notifications (user_id, title, message, is_read) VALUES
(4, 'Nho nop bai tap', 'Ban co 1 bai tap den han vao 23:59 ngay 15/04.', 0),
(4, 'Lich hoc toi nay', 'Lop IELTS-K20 bat dau luc 19:00 tai Phong 101.', 0);

INSERT INTO materials (course_id, title, file_path, type)
VALUES (1, 'Listening Practice Set 01', '/assets/uploads/material-listening-1.pdf', 'pdf');

INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
VALUES (4, 1, 3, 5, 'Giao vien day de hieu, co dong luc hoc.', 'reviewed');

INSERT INTO approvals (requester_id, approver_id, type, content, status)
VALUES (3, 1, 'schedule_change', 'Xin doi lich day ngay 18/04 sang 19/04.', 'pending');

INSERT INTO extracurricular_activities (title, description, content, image_thumbnail, fee, start_date, status)
VALUES 
('Conversation Club', 'Hoi hop toan tieng Anh vui ve va ty mat.', '<p>Luyen phan xa noi va tu duy bang tieng Anh.</p>', '/assets/uploads/activity-conversation.jpg', 0, '2026-04-20', 'upcoming'),
('Movie Night', 'Xem phim tieng Anh co phu de.', '<p>Luyen nghe qua boi canh phim thuc te.</p>', '/assets/uploads/activity-movie.jpg', 50000, '2026-04-25', 'upcoming');

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

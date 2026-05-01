USE english_center_db;

SET NAMES utf8mb4;


-- Removed guest role from roles

INSERT INTO permissions (permission_name, slug) VALUES
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
('Xem xuat Excel hoc vien', 'academic.exports.view'),
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
('Xoa thong bao', 'notifications.delete')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
WHERE r.role_name IN ('admin', 'staff', 'teacher', 'student');

SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON 1 = 1
WHERE r.role_name = 'admin';

-- =====================================================================
-- STAFF: operational access
-- =====================================================================
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'admin.user.view',
    -- Academic
    'academic.classes.view', 'academic.classes.create', 'academic.classes.update', 'academic.classes.delete',
    'academic.courses.view', 'academic.courses.create', 'academic.courses.update', 'academic.courses.delete',
    'academic.roadmaps.view', 'academic.roadmaps.create', 'academic.roadmaps.update', 'academic.roadmaps.delete',
    'academic.schedules.view', 'academic.schedules.create', 'academic.schedules.update', 'academic.schedules.delete',
    'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update', 'academic.assignments.delete',
    'academic.submissions.view', 'academic.submissions.grade',
    'academic.exports.view',
    'academic.rooms.view',
    -- Materials
    'materials.view', 'materials.create', 'materials.update', 'materials.delete',
    -- Finance
    'finance.tuition.view',
    'finance.registration.view', 'finance.registration.create', 'finance.registration.update',
    'finance.promotions.view',
    'finance.payments.view', 'finance.payments.update',
    -- Feedback
    'feedback.view', 'feedback.update', 'feedback.delete',
    -- Approval
    'approval.view', 'approval.update',
    -- Activity
    'activity.view', 'activity.create', 'activity.update', 'activity.delete',
    -- Leads & Applications
    'student_lead.view', 'student_lead.update', 'student_lead.delete',
    'job_application.view', 'job_application.update', 'job_application.delete',
    -- Portfolio
    'academic.portfolios.view', 'academic.portfolios.create', 'academic.portfolios.update', 'academic.portfolios.delete',
    -- Notifications
    'notifications.view', 'notifications.create', 'notifications.update', 'notifications.delete'
)
WHERE r.role_name = 'staff';

-- =====================================================================
-- TEACHER: teaching-focused access
-- =====================================================================
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'academic.schedules.view',
    'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update',
    'academic.submissions.view', 'academic.submissions.grade',
    'academic.exports.view',
    'materials.view', 'materials.create', 'materials.update', 'materials.delete',
    'approval.request'
)
WHERE r.role_name = 'teacher';

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

USE english_center_db;

SET NAMES utf8mb4;

INSERT INTO roles (role_name, description)
SELECT 'guest', 'Khach vang lai'
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE role_name = 'guest');

INSERT INTO permissions (permission_name, slug) VALUES
('Xem trang chu cong khai', 'public.home.view'),
('Xem dashboard hoc vien', 'student.dashboard.view'),
('Xem bai tap hoc vien', 'student.assignment.view'),
('Nop bai tap tu portal', 'student.assignment.submit'),
('Xem hoc phi hoc vien', 'student.tuition.view'),
('Cap nhat hoc phi tu portal', 'student.tuition.update'),
('Xem dashboard giao vien', 'teacher.dashboard.view'),
('Xem lich day giao vien', 'teacher.schedule.view'),
('Gui yeu cau nghi day', 'teacher.leave.request'),
('Xem dashboard quan tri', 'admin.dashboard.view'),
('Quan tri nguoi dung', 'admin.user.manage'),
('Quan tri role permission', 'admin.role_permission.manage'),
('Xem bao cao thong ke', 'reports.view'),
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
('Cham diem bai nop', 'academic.submissions.grade'),
('Xem tai lieu', 'materials.view'),
('Tao tai lieu', 'materials.create'),
('Cap nhat tai lieu', 'materials.update'),
('Xoa tai lieu', 'materials.delete'),
('Xem hoc phi', 'finance.tuition.view'),
('Xoa hoc phi', 'finance.tuition.delete'),
('Yeu cau chinh sua tai chinh', 'finance.adjust.request'),
('Xem giao dich thanh toan', 'finance.payment.view'),
('Xem danh gia', 'feedback.view'),
('Tao danh gia', 'feedback.create'),
('Cap nhat danh gia', 'feedback.update'),
('Xoa danh gia', 'feedback.delete'),
('Xem phe duyet', 'approval.view'),
('Cap nhat phe duyet', 'approval.update'),
('Tao yeu cau phe duyet', 'approval.request'),
('Xem hoat dong', 'activity.view'),
('Tao hoat dong', 'activity.create'),
('Cap nhat hoat dong', 'activity.update'),
('Xoa hoat dong', 'activity.delete'),
('Xem tai khoan ngan hang', 'bank.view'),
('Tao tai khoan ngan hang', 'bank.create'),
('Cap nhat tai khoan ngan hang', 'bank.update'),
('Xoa tai khoan ngan hang', 'bank.delete')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

DELETE rp
FROM role_permissions rp
INNER JOIN roles r ON r.id = rp.role_id
WHERE r.role_name IN ('admin', 'staff', 'teacher', 'student', 'guest');

-- ADMIN: full access
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'public.home.view',
    'student.dashboard.view', 'student.assignment.view', 'student.assignment.submit', 'student.tuition.view', 'student.tuition.update',
    'teacher.dashboard.view', 'teacher.schedule.view', 'teacher.leave.request',
    'admin.dashboard.view', 'admin.user.manage', 'admin.role_permission.manage', 'reports.view',
    'academic.classes.view', 'academic.classes.create', 'academic.classes.update', 'academic.classes.delete',
    'academic.schedules.view', 'academic.schedules.create', 'academic.schedules.update', 'academic.schedules.delete',
    'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update', 'academic.assignments.delete',
    'academic.submissions.view', 'academic.submissions.grade',
    'materials.view', 'materials.create', 'materials.update', 'materials.delete',
    'finance.tuition.view', 'finance.tuition.delete', 'finance.adjust.request', 'finance.payment.view',
    'feedback.view', 'feedback.create', 'feedback.update', 'feedback.delete',
    'approval.view', 'approval.update', 'approval.request',
    'activity.view', 'activity.create', 'activity.update', 'activity.delete',
    'bank.view', 'bank.create', 'bank.update', 'bank.delete'
)
WHERE r.role_name = 'admin';

-- STAFF: operational access, no direct financial delete, no approval final decision
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'public.home.view',
    'admin.dashboard.view', 'reports.view',
    'academic.classes.view', 'academic.classes.create', 'academic.classes.update',
    'academic.schedules.view', 'academic.schedules.create', 'academic.schedules.update',
    'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update',
    'academic.submissions.view',
    'materials.view', 'materials.create', 'materials.update',
    'finance.tuition.view', 'finance.adjust.request', 'finance.payment.view',
    'feedback.view', 'feedback.create', 'feedback.update',
    'approval.view', 'approval.request',
    'activity.view', 'activity.create', 'activity.update',
    'bank.view', 'bank.create', 'bank.update'
)
WHERE r.role_name = 'staff';

-- TEACHER: teaching-focused access
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'public.home.view',
    'teacher.dashboard.view', 'teacher.schedule.view', 'teacher.leave.request',
    'academic.assignments.view', 'academic.assignments.create', 'academic.assignments.update',
    'academic.submissions.view', 'academic.submissions.grade',
    'academic.schedules.view',
    'materials.view',
    'approval.request'
)
WHERE r.role_name = 'teacher';

-- STUDENT: portal usage
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN (
    'public.home.view',
    'student.dashboard.view',
    'student.assignment.view', 'student.assignment.submit',
    'student.tuition.view', 'student.tuition.update',
    'materials.view',
    'activity.view'
)
WHERE r.role_name = 'student';

-- GUEST: public only
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
INNER JOIN permissions p ON p.slug IN ('public.home.view')
WHERE r.role_name = 'guest';

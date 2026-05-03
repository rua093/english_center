-- Safe migration: convert legacy *.manage permissions into granular CRUD permissions.
-- This script is idempotent (can be executed multiple times).

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS tmp_permission_slug_migration;
CREATE TEMPORARY TABLE tmp_permission_slug_migration (
    old_slug VARCHAR(120) NOT NULL,
    new_slug VARCHAR(120) NOT NULL,
    new_name VARCHAR(180) NOT NULL,
    PRIMARY KEY (old_slug, new_slug)
) ENGINE=InnoDB;

INSERT INTO tmp_permission_slug_migration (old_slug, new_slug, new_name) VALUES
-- Academic: classes
('academic.classes.manage', 'academic.classes.view', 'Xem lop hoc'),
('academic.classes.manage', 'academic.classes.create', 'Tao lop hoc'),
('academic.classes.manage', 'academic.classes.update', 'Cap nhat lop hoc'),
('academic.classes.manage', 'academic.classes.delete', 'Xoa lop hoc'),

-- Academic: schedules
('academic.schedules.manage', 'academic.schedules.view', 'Xem lich hoc'),
('academic.schedules.manage', 'academic.schedules.create', 'Tao lich hoc'),
('academic.schedules.manage', 'academic.schedules.update', 'Cap nhat lich hoc'),
('academic.schedules.manage', 'academic.schedules.delete', 'Xoa lich hoc'),

-- Academic: assignments
('academic.assignments.manage', 'academic.assignments.view', 'Xem bai tap'),
('academic.assignments.manage', 'academic.assignments.create', 'Tao bai tap'),
('academic.assignments.manage', 'academic.assignments.update', 'Cap nhat bai tap'),
('academic.assignments.manage', 'academic.assignments.delete', 'Xoa bai tap'),

-- Materials
('materials.manage', 'materials.view', 'Xem tai lieu'),
('materials.manage', 'materials.create', 'Tao tai lieu'),
('materials.manage', 'materials.update', 'Cap nhat tai lieu'),
('materials.manage', 'materials.delete', 'Xoa tai lieu'),

-- Admin: users
('admin.user.manage', 'admin.user.view', 'Xem nguoi dung'),
('admin.user.manage', 'admin.user.create', 'Tao nguoi dung'),
('admin.user.manage', 'admin.user.update', 'Cap nhat nguoi dung'),
('admin.user.manage', 'admin.user.delete', 'Xoa nguoi dung'),

-- Admin: role permission matrix
('admin.role_permission.manage', 'admin.role_permission.view', 'Xem phan quyen vai tro'),
('admin.role_permission.manage', 'admin.role_permission.update', 'Cap nhat phan quyen vai tro'),

-- Finance: tuition
('finance.tuition.manage', 'finance.tuition.view', 'Xem hoc phi'),
('finance.tuition.manage', 'finance.tuition.create', 'Tao hoc phi'),
('finance.tuition.manage', 'finance.tuition.update', 'Cap nhat hoc phi'),
('finance.tuition.manage', 'finance.tuition.delete', 'Xoa hoc phi'),

-- Finance: payments (legacy singular slug -> canonical plural slug)
('finance.payment.manage', 'finance.payments.view', 'Xem giao dich thanh toan chi tiet'),
('finance.payment.manage', 'finance.payments.create', 'Tao giao dich thanh toan'),
('finance.payment.manage', 'finance.payments.update', 'Cap nhat giao dich thanh toan'),
('finance.payment.manage', 'finance.payments.delete', 'Xoa giao dich thanh toan'),
('finance.payment.view', 'finance.payments.view', 'Xem giao dich thanh toan chi tiet'),

-- Finance: promotions
('finance.promotions.manage', 'finance.promotions.view', 'Xem khuyen mai'),
('finance.promotions.manage', 'finance.promotions.create', 'Tao khuyen mai'),
('finance.promotions.manage', 'finance.promotions.update', 'Cap nhat khuyen mai'),
('finance.promotions.manage', 'finance.promotions.delete', 'Xoa khuyen mai'),

-- Student leads
('student_lead.manage', 'student_lead.view', 'Xem dau moi hoc vien'),
('student_lead.manage', 'student_lead.create', 'Tao dau moi hoc vien'),
('student_lead.manage', 'student_lead.update', 'Cap nhat dau moi hoc vien'),
('student_lead.manage', 'student_lead.delete', 'Xoa dau moi hoc vien'),

-- Job applications
('job_application.manage', 'job_application.view', 'Xem ho so ung tuyen giao vien'),
('job_application.manage', 'job_application.create', 'Tao ho so ung tuyen giao vien'),
('job_application.manage', 'job_application.update', 'Cap nhat ho so ung tuyen giao vien'),
('job_application.manage', 'job_application.delete', 'Xoa ho so ung tuyen giao vien'),

-- Approvals
('approval.manage', 'approval.view', 'Xem phe duyet'),
('approval.manage', 'approval.create', 'Tao phe duyet'),
('approval.manage', 'approval.update', 'Cap nhat phe duyet'),
('approval.manage', 'approval.delete', 'Xoa phe duyet'),

-- Activities
('activity.manage', 'activity.view', 'Xem hoat dong'),
('activity.manage', 'activity.create', 'Tao hoat dong'),
('activity.manage', 'activity.update', 'Cap nhat hoat dong'),
('activity.manage', 'activity.delete', 'Xoa hoat dong'),

-- Banks
('bank.manage', 'bank.view', 'Xem tai khoan ngan hang'),
('bank.manage', 'bank.create', 'Tao tai khoan ngan hang'),
('bank.manage', 'bank.update', 'Cap nhat tai khoan ngan hang'),
('bank.manage', 'bank.delete', 'Xoa tai khoan ngan hang');

-- 1) Ensure new granular permissions exist.
INSERT INTO permissions (permission_name, slug)
SELECT DISTINCT m.new_name, m.new_slug
FROM tmp_permission_slug_migration m
LEFT JOIN permissions p ON p.slug = m.new_slug
WHERE p.id IS NULL;

-- 2) Copy role grants from legacy permissions to new granular permissions.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT DISTINCT rp.role_id, p_new.id
FROM role_permissions rp
INNER JOIN permissions p_old ON p_old.id = rp.permission_id
INNER JOIN tmp_permission_slug_migration m ON m.old_slug = p_old.slug
INNER JOIN permissions p_new ON p_new.slug = m.new_slug;

-- 3) Remove legacy grants after successful copy.
DELETE rp
FROM role_permissions rp
INNER JOIN permissions p_old ON p_old.id = rp.permission_id
INNER JOIN tmp_permission_slug_migration m ON m.old_slug = p_old.slug;

-- 4) Remove legacy permission definitions only when no role uses them.
DELETE p
FROM permissions p
LEFT JOIN role_permissions rp ON rp.permission_id = p.id
WHERE p.slug IN (SELECT DISTINCT old_slug FROM tmp_permission_slug_migration)
  AND rp.permission_id IS NULL;

-- Optional verification result set.
SELECT
    old_slug,
    COUNT(DISTINCT new_slug) AS mapped_to_new_permissions
FROM tmp_permission_slug_migration
GROUP BY old_slug
ORDER BY old_slug ASC;

COMMIT;

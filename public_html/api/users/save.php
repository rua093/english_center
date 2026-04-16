<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('admin.user.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect(page_url('users-admin'));
}

$payload = [
	'id' => (int) ($_POST['id'] ?? 0),
	'username' => trim((string) ($_POST['username'] ?? '')),
	'full_name' => trim((string) ($_POST['full_name'] ?? '')),
	'role_id' => (int) ($_POST['role_id'] ?? 0),
	'phone' => trim((string) ($_POST['phone'] ?? '')),
	'email' => trim((string) ($_POST['email'] ?? '')),
	'status' => (string) ($_POST['status'] ?? 'active'),
	'password' => (string) ($_POST['password'] ?? ''),
	'staff_position' => trim((string) ($_POST['staff_position'] ?? '')),
	'staff_approval_limit' => (float) ($_POST['staff_approval_limit'] ?? 0),
	'teacher_degree' => trim((string) ($_POST['teacher_degree'] ?? '')),
	'teacher_experience_years' => (int) ($_POST['teacher_experience_years'] ?? 0),
	'teacher_bio' => trim((string) ($_POST['teacher_bio'] ?? '')),
	'teacher_intro_video_url' => trim((string) ($_POST['teacher_intro_video_url'] ?? '')),
	'student_parent_name' => trim((string) ($_POST['student_parent_name'] ?? '')),
	'student_parent_phone' => trim((string) ($_POST['student_parent_phone'] ?? '')),
	'student_school_name' => trim((string) ($_POST['student_school_name'] ?? '')),
	'student_target_score' => trim((string) ($_POST['student_target_score'] ?? '')),
	'student_entry_test_id' => (int) ($_POST['student_entry_test_id'] ?? 0),
];

if ($payload['username'] === '' || $payload['full_name'] === '' || $payload['role_id'] <= 0) {
	set_flash('error', 'Vui lòng nhập đầy đủ thông tin người dùng bắt buộc.');
	redirect(page_url('users-admin'));
}

if (!in_array($payload['status'], ['active', 'inactive'], true)) {
	set_flash('error', 'Vui lòng chọn trạng thái người dùng.');
	redirect(page_url('users-admin'));
}

$payload['staff_approval_limit'] = max(0, (float) $payload['staff_approval_limit']);
$payload['teacher_experience_years'] = max(0, (int) $payload['teacher_experience_years']);
$payload['student_entry_test_id'] = max(0, (int) $payload['student_entry_test_id']);

$adminModel = new AdminModel();
$role = $adminModel->findRoleById((int) $payload['role_id']);
if (!$role) {
	set_flash('error', 'Vai trò người dùng không hợp lệ.');
	redirect(page_url('users-admin'));
}

$roleName = (string) ($role['role_name'] ?? '');
if ($roleName === 'staff' && $payload['staff_position'] === '') {
	set_flash('error', 'Vai trò staff bắt buộc nhập chức vụ.');
	redirect(page_url('users-admin', ['edit' => (int) $payload['id']]));
}

try {
	$adminModel->saveUser($payload);
	set_flash('success', 'Đã lưu thông tin người dùng.');
} catch (Throwable $exception) {
	set_flash('error', 'Lưu người dùng thất bại: ' . $exception->getMessage());
}

redirect(page_url('users-admin'));

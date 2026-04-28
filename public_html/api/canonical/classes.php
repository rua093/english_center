<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_classes_save_action(): void
{
	api_require_post(page_url('classes-academic'));

	$classId = input_int($_POST, 'id');
	api_guard_permission($classId > 0 ? 'academic.classes.update' : 'academic.classes.create');

	$payload = $_POST;
	$statusMap = [
		'planned' => 'upcoming',
		'completed' => 'graduated',
	];
	$payload['status'] = $statusMap[(string) ($payload['status'] ?? '')] ?? (string) ($payload['status'] ?? 'upcoming');

	if (
		input_int($payload, 'course_id') <= 0 ||
		input_string($payload, 'class_name') === '' ||
		input_int($payload, 'teacher_id') <= 0
	) {
		set_flash('error', 'Vui lòng nhập đầy đủ khóa học, tên lớp và giáo viên.');
		redirect($classId > 0 ? page_url('classes-academic-edit', ['id' => $classId]) : page_url('classes-academic-edit'));
	}

	$academicModel = new AcademicModel();
	if ($classId > 0) {
		teacher_assert_class_scope($academicModel, $classId, page_url('classes-academic-edit', ['id' => $classId]));
	}

	$academicModel->saveClass($payload);
	set_flash('success', 'Đã lưu lớp học thành công.');

	redirect(page_url('classes-academic'));
}

function api_classes_edit_action(): void
{
	api_guard_permission('academic.classes.update');

	$classId = (int) ($_GET['id'] ?? 0);
	$academicModel = new AcademicModel();
	if ($classId > 0) {
		teacher_assert_class_scope($academicModel, $classId, page_url('classes-academic'));
	}

	redirect(page_url('classes-academic-edit', ['id' => $classId]));
}

function api_classes_delete_action(): void
{
	api_guard_permission('academic.classes.delete');
	api_require_post(page_url('classes-academic'));

	$classId = (int) ($_GET['id'] ?? 0);
	$academicModel = new AcademicModel();
	if ($classId > 0) {
		teacher_assert_class_scope($academicModel, $classId, page_url('classes-academic'));
	}

	$academicModel->deleteClass($classId);
	set_flash('success', 'Đã xóa lớp học.');
	redirect(page_url('classes-academic'));
}

function api_classes_student_profile_action(): void
{
	require_login();

	if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
		api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
	}

	$classId = (int) ($_GET['class_id'] ?? 0);
	$studentId = (int) ($_GET['student_id'] ?? 0);
	if ($classId <= 0 || $studentId <= 0) {
		api_error('Dữ liệu không hợp lệ.', ['code' => 'INVALID_PAYLOAD'], 422);
	}

	$academicModel = new AcademicModel();
	$class = $academicModel->findClass($classId);
	if (!is_array($class)) {
		api_error('Không tìm thấy lớp học.', ['code' => 'CLASS_NOT_FOUND'], 404);
	}

	if (!has_permission('academic.classes.view')) {
		$user = auth_user() ?? [];
		if ((string) ($user['role'] ?? '') === 'teacher') {
			teacher_assert_class_scope($academicModel, $classId, page_url('classes-academic'));
		} else {
			api_error('Không có quyền xem lớp học.', ['code' => 'FORBIDDEN'], 403);
		}
	}

	if (!$academicModel->isStudentEnrolledInClass($studentId, $classId)) {
		api_error('Học viên không thuộc lớp học.', ['code' => 'STUDENT_NOT_IN_CLASS'], 403);
	}

	$user = $academicModel->findActiveUser($studentId);
	if (!is_array($user)) {
		api_error('Không tìm thấy học viên.', ['code' => 'STUDENT_NOT_FOUND'], 404);
	}

	$roleName = strtolower((string) ($user['role_name'] ?? ''));
	if ($roleName !== 'student') {
		api_error('Tài khoản không phải học viên.', ['code' => 'NOT_STUDENT'], 422);
	}

	api_success('OK', [
		'user' => [
			'id' => (int) ($user['id'] ?? 0),
			'full_name' => (string) ($user['full_name'] ?? ''),
			'phone' => (string) ($user['phone'] ?? ''),
			'email' => (string) ($user['email'] ?? ''),
			'role_profile' => is_array($user['role_profile'] ?? null) ? $user['role_profile'] : [],
		],
	]);
}

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

	(new AcademicModel())->saveClass($payload);
	set_flash('success', 'Đã lưu lớp học thành công.');

	redirect(page_url('classes-academic'));
}

function api_classes_edit_action(): void
{
	api_guard_permission('academic.classes.update');
	redirect(page_url('classes-academic-edit', ['id' => (int) ($_GET['id'] ?? 0)]));
}

function api_classes_delete_action(): void
{
	api_guard_permission('academic.classes.delete');
	api_require_post(page_url('classes-academic'));

	(new AcademicModel())->deleteClass((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa lớp học.');
	redirect(page_url('classes-academic'));
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_schedules_save_action(): void
{
	api_require_post(page_url('schedules-academic'));

	$scheduleId = input_int($_POST, 'id');
	api_guard_permission($scheduleId > 0 ? 'academic.schedules.update' : 'academic.schedules.create');

	$payload = $_POST;
	$payload['room_id'] = (string) ($payload['room_id'] ?? '');

	if (
		input_int($payload, 'class_id') <= 0 ||
		input_int($payload, 'teacher_id') <= 0 ||
		input_string($payload, 'study_date') === '' ||
		input_string($payload, 'start_time') === '' ||
		input_string($payload, 'end_time') === ''
	) {
		set_flash('error', 'Vui lòng nhập đầy đủ lớp học, giáo viên, ngày học và giờ học.');
		redirect($scheduleId > 0 ? page_url('schedules-academic-edit', ['id' => $scheduleId]) : page_url('schedules-academic-edit'));
	}

	(new AcademicModel())->saveSchedule($payload);
	set_flash('success', 'Đã lưu lịch học thành công.');

	redirect(page_url('schedules-academic'));
}

function api_schedules_edit_action(): void
{
	api_guard_permission('academic.schedules.update');
	redirect(page_url('schedules-academic-edit', ['id' => (int) ($_GET['id'] ?? 0)]));
}

function api_schedules_delete_action(): void
{
	api_guard_permission('academic.schedules.delete');
	api_require_post(page_url('schedules-academic'));

	(new AcademicModel())->deleteSchedule((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa lịch học.');
	redirect(page_url('schedules-academic'));
}

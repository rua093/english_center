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
	$editPath = $scheduleId > 0
		? page_url('schedules-academic-edit', ['id' => $scheduleId])
		: page_url('schedules-academic-edit');

	if (
		input_int($payload, 'class_id') <= 0 ||
		input_int($payload, 'teacher_id') <= 0 ||
		input_string($payload, 'study_date') === '' ||
		input_string($payload, 'start_time') === '' ||
		input_string($payload, 'end_time') === ''
	) {
		set_flash('error', 'Vui lòng nhập đầy đủ lớp học, giáo viên, ngày học và giờ học.');
		redirect($editPath);
	}

	try {
		(new AcademicModel())->saveSchedule($payload);
	} catch (DomainException $exception) {
		if (api_expects_json()) {
			api_error($exception->getMessage(), ['code' => 'SCHEDULE_VALIDATION_FAILED'], 422);
		}

		set_flash('error', $exception->getMessage());
		redirect($editPath);
	}

	set_flash('success', 'Đã lưu lịch dạy thành công.');

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
	set_flash('success', 'Đã xóa lịch dạy.');
	redirect(page_url('schedules-academic'));
}

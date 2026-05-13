<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function schedules_assert_teacher_scope(AcademicModel $academicModel, int $classId, int $scheduleId = 0): array
{
	$user = auth_user() ?? [];
	$role = (string) ($user['role'] ?? '');
	if ($role !== 'teacher') {
		return ['is_teacher' => false, 'teacher_id' => 0];
	}

	$fail = static function (string $message, string $code): void {
		if (api_expects_json()) {
			api_error($message, ['code' => $code], 403);
		}

		set_flash('error', $message);
		redirect(page_url('schedules-academic'));
	};

	$teacherId = (int) ($user['id'] ?? 0);
	if ($teacherId <= 0) {
		$fail('Khong xac dinh duoc giao vien hien tai.', 'TEACHER_NOT_FOUND');
	}

	if ($scheduleId > 0) {
		$schedule = $academicModel->findSchedule($scheduleId);
		if (!is_array($schedule) || (int) ($schedule['teacher_id'] ?? 0) !== $teacherId) {
			$fail('Ban chi co the cap nhat lich day cua chinh minh.', 'SCHEDULE_ACCESS_DENIED');
		}
	}

	$classRow = $academicModel->findClass($classId);
	if (!is_array($classRow) || (int) ($classRow['teacher_id'] ?? 0) !== $teacherId) {
		$fail('Ban chi co the quan ly lop hoc minh dang day.', 'CLASS_ACCESS_DENIED');
	}

	return ['is_teacher' => true, 'teacher_id' => $teacherId];
}

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

	$academicModel = new AcademicModel();
	$teacherScope = schedules_assert_teacher_scope($academicModel, input_int($payload, 'class_id'), $scheduleId);
	if (($teacherScope['is_teacher'] ?? false) === true) {
		$payload['teacher_id'] = (string) ((int) ($teacherScope['teacher_id'] ?? 0));
	}

	try {
		$academicModel->saveSchedule($payload);
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

	$scheduleId = (int) ($_GET['id'] ?? 0);
	$academicModel = new AcademicModel();
	if ($scheduleId > 0) {
		$schedule = $academicModel->findSchedule($scheduleId);
		if (!is_array($schedule)) {
			set_flash('error', 'Không tìm thấy lịch dạy cần xóa.');
			redirect(page_url('schedules-academic'));
		}

		schedules_assert_teacher_scope($academicModel, (int) ($schedule['class_id'] ?? 0), $scheduleId);
	}

	try {
		$academicModel->deleteSchedule($scheduleId);
		set_flash('success', 'Đã xóa lịch dạy.');
	} catch (Throwable) {
		set_flash('error', 'Không thể xóa lịch dạy. Lịch này có thể đã được gắn với điểm danh hoặc bài tập.');
	}
	redirect(page_url('schedules-academic'));
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/page_actions.php';
require_once __DIR__ . '/../../models/AcademicModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

function assignments_manage_redirect_query(array $source): array
{
	$query = [];

	$courseId = input_int($source, 'course_id');
	if ($courseId > 0) {
		$query['course_id'] = $courseId;
	}

	$classId = input_int($source, 'class_id');
	if ($classId > 0) {
		$query['class_id'] = $classId;
	}

	$classPage = input_int($source, 'class_page');
	if ($classPage > 0) {
		$query['class_page'] = $classPage;
	}

	$classPerPage = input_int($source, 'class_per_page');
	if ($classPerPage > 0) {
		$query['class_per_page'] = $classPerPage;
	}

	$lessonId = input_int($source, 'lesson_id');
	if ($lessonId > 0) {
		$query['lesson_id'] = $lessonId;
	}

	$scheduleId = input_int($source, 'schedule_id');
	if ($scheduleId > 0) {
		$query['schedule_id'] = $scheduleId;
	}

	$focusScheduleId = input_int($source, 'focus_schedule_id');
	if ($focusScheduleId <= 0) {
		$focusScheduleId = $scheduleId;
	}
	if ($focusScheduleId > 0) {
		$query['focus_schedule_id'] = $focusScheduleId;
	}

	$weekStart = input_string($source, 'week_start');
	if ($weekStart !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart) === 1) {
		$query['week_start'] = $weekStart;
	}

	$weekRef = input_string($source, 'week_ref');
	if ($weekRef !== '' && preg_match('/^\d{4}-W\d{2}$/', $weekRef) === 1) {
		$query['week_ref'] = $weekRef;
	}

	return $query;
}

function assignments_manage_redirect_page(array $source, string $fallback): string
{
	$requestedPage = resolve_page_slug(input_string($source, 'redirect_page'));
	$allowedPages = ['assignments-academic', 'classrooms-academic'];

	if (in_array($requestedPage, $allowedPages, true)) {
		return $requestedPage;
	}

	return $fallback;
}

function api_assignments_save_action(): void
{
	api_require_post(page_url('assignments-academic'));

	$assignmentId = input_int($_POST, 'id');
	api_guard_permission($assignmentId > 0 ? 'academic.assignments.update' : 'academic.assignments.create');
	$redirectPage = assignments_manage_redirect_page($_POST, 'assignments-academic');
	$redirectQuery = assignments_manage_redirect_query($_POST);
	$listPath = page_url($redirectPage, $redirectQuery);

	$editPath = $listPath;
	if ($redirectPage === 'assignments-academic') {
		$editQuery = $redirectQuery;
		if ($assignmentId > 0) {
			$editQuery['id'] = $assignmentId;
		}
		$editPath = page_url('assignments-academic-edit', $editQuery);
	}

	$payload = $_POST;
	if (input_string($payload, 'deadline') === '' && input_string($payload, 'due_date') !== '') {
		$payload['deadline'] = (string) $payload['due_date'];
	}

	$academicModel = new AcademicModel();

	$uploadPath = input_string($payload, 'existing_file_url');
	$manualFileUrl = input_string($payload, 'file_url');
	if ($manualFileUrl !== '') {
		$uploadPath = $manualFileUrl;
	}

	if (!empty($_FILES['assignment_file']['name'])) {
		$fileUpload = store_uploaded_file($_FILES['assignment_file'], 'assignment');
		if ($fileUpload === null) {
			$uploadErrorCode = (int) ($_FILES['assignment_file']['error'] ?? UPLOAD_ERR_OK);
			$uploadMessage = 'Tải lên file bài tập thất bại.';
			if ($uploadErrorCode === UPLOAD_ERR_INI_SIZE || $uploadErrorCode === UPLOAD_ERR_FORM_SIZE) {
				$uploadMessage = 'File tải lên vượt quá giới hạn dung lượng cho phép.';
			}

			set_flash('error', $uploadMessage);
			redirect($editPath);
		}
		$uploadPath = $fileUpload;
	}

	$requiredErrors = validate_required_fields($payload, [
		'title' => 'Tiêu đề',
		'deadline' => 'Hạn nộp',
	]);

	$classId = input_int($payload, 'class_id');
	$lessonId = input_int($payload, 'lesson_id');
	if ($classId <= 0) {
		$requiredErrors['class_id'] = 'Lớp học';
	}
	if (input_int($payload, 'lesson_id') <= 0) {
		$requiredErrors['lesson_id'] = 'Buổi học';
	}

	if (!empty($requiredErrors)) {
		set_flash('error', 'Vui lòng chọn lớp, buổi học và nhập đầy đủ tiêu đề, hạn nộp.');
		redirect($editPath);
	}

	$lessonBelongsToClass = false;
	foreach ($academicModel->assignmentLookups() as $lessonRow) {
		if ((int) ($lessonRow['id'] ?? 0) !== $lessonId) {
			continue;
		}

		$lessonBelongsToClass = (int) ($lessonRow['class_id'] ?? 0) === $classId;
		break;
	}

	if (!$lessonBelongsToClass) {
		set_flash('error', 'Buổi học không thuộc lớp đã chọn. Vui lòng chọn lại.');
		redirect($editPath);
	}

	$payload['file_url'] = $uploadPath;
	$academicModel->saveAssignment($payload);

	set_flash('success', 'Đã lưu bài tập thành công.');
	redirect($listPath);
}

function api_assignments_delete_action(): void
{
	api_guard_permission('academic.assignments.delete');
	api_require_post(page_url('assignments-academic'));

	$assignmentId = (int) ($_GET['id'] ?? 0);
	if ($assignmentId > 0) {
		(new AcademicModel())->deleteAssignment($assignmentId);
		set_flash('success', 'Đã xóa bài tập.');
	}

	$redirectPage = assignments_manage_redirect_page($_GET, 'assignments-academic');
	redirect(page_url($redirectPage, assignments_manage_redirect_query($_GET)));
}

function api_assignments_submit_action(): void
{
	api_guard_permission('student.assignment.submit');
	api_require_post(page_url('dashboard-student'));

	$assignmentId = input_int($_POST, 'assignment_id');
	$fileUrl = input_string($_POST, 'file_url');
	$user = auth_user();

	if ($assignmentId > 0 && $user) {
		$uploadPath = $fileUrl;
		if (!empty($_FILES['submission_file']['name'])) {
			$fileUpload = store_uploaded_file($_FILES['submission_file'], sprintf('submission-%d-%d', (int) $user['id'], $assignmentId));
			if ($fileUpload === null) {
				set_flash('error', 'Tải lên bài làm thất bại. Vui lòng thử lại.');
				redirect(page_url('dashboard-student'));
			}
			$uploadPath = $fileUpload;
		}

		if ($uploadPath !== '') {
			(new UserModel())->submitAssignment((int) $user['id'], $assignmentId, $uploadPath);
			set_flash('success', 'Đã gửi bài tập thành công.');
		} else {
			set_flash('error', 'Vui lòng tải lên file hoặc nhập đường dẫn bài làm.');
		}
	}

	redirect(page_url('dashboard-student'));
}

function api_assignments_edit_action(): void
{
	api_guard_permission('academic.assignments.update');
	$assignmentId = (int) ($_GET['id'] ?? 0);
	redirect(page_url('assignments-academic-edit', ['id' => $assignmentId]));
}

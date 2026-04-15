<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/page_actions.php';
require_once __DIR__ . '/../../models/AcademicModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

function api_assignments_save_action(): void
{
	api_require_post(page_url('assignments-academic'));

	$assignmentId = input_int($_POST, 'id');
	api_guard_permission($assignmentId > 0 ? 'academic.assignments.update' : 'academic.assignments.create');

	$payload = $_POST;
	if (input_string($payload, 'deadline') === '' && input_string($payload, 'due_date') !== '') {
		$payload['deadline'] = (string) $payload['due_date'];
	}

	$uploadPath = input_string($payload, 'file_url');
	if (!empty($_FILES['assignment_file']['name'])) {
		$fileUpload = store_uploaded_file($_FILES['assignment_file'], 'assignment');
		if ($fileUpload === null) {
			set_flash('error', 'Tải lên file bài tập thất bại.');
			redirect(page_url('assignments-academic'));
		}
		$uploadPath = $fileUpload;
	}

	$requiredErrors = validate_required_fields($payload, [
		'title' => 'Tiêu đề',
		'deadline' => 'Hạn nộp',
	]);
	if (input_int($payload, 'lesson_id') <= 0) {
		$requiredErrors['lesson_id'] = 'Buổi học';
	}

	if (!empty($requiredErrors)) {
		set_flash('error', 'Vui lòng nhập đầy đủ buổi học, tiêu đề và hạn nộp.');
		redirect($assignmentId > 0 ? page_url('assignments-academic-edit', ['id' => $assignmentId]) : page_url('assignments-academic-edit'));
	}

	$payload['file_url'] = $uploadPath;
	(new AcademicModel())->saveAssignment($payload);

	set_flash('success', 'Đã lưu bài tập thành công.');
	redirect(page_url('assignments-academic'));
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

	redirect(page_url('assignments-academic'));
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

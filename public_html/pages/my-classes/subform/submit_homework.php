<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../models/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/../../../models/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/../../../models/tables/SubmissionsTableModel.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect(page_url('classes-my'));
}

$csrfToken = request_csrf_token();
if (!validate_csrf_token($csrfToken)) {
	set_flash('error', 'Yêu cầu không hợp lệ. Vui lòng thử lại.');
	redirect(page_url('classes-my'));
}

$user = auth_user();
if (!$user) {
	set_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
	redirect(page_url('login'));
}

if (!has_permission('student.assignment.submit') && !in_array((string) ($user['role'] ?? ''), ['student', 'admin'], true)) {
	set_flash('error', 'Bạn không có quyền nộp bài tập.');
	redirect(page_url('classes-my'));
}

$classStudentsTable = new ClassStudentsTableModel();
$assignmentsTable = new AssignmentsTableModel();
$submissionsTable = new SubmissionsTableModel();

$assignmentId = max(0, (int) ($_POST['assignment_id'] ?? 0));
$className = trim((string) ($_POST['class_name'] ?? ''));
$assignmentTitle = trim((string) ($_POST['assignment_title'] ?? ''));
$deadline = trim((string) ($_POST['assignment_deadline'] ?? ''));
$note = trim((string) ($_POST['note'] ?? ''));

if ($className === '' || $assignmentTitle === '') {
	set_flash('error', 'Vui lòng chọn lớp học và nhập tên bài tập.');
	redirect(page_url('classes-my'));
}

if (empty($_FILES['submission_file']['name'])) {
	set_flash('error', 'Vui lòng tải lên file bài làm.');
	redirect(page_url('classes-my'));
}

$studentClasses = $classStudentsTable->listMyClassesForStudent((int) $user['id']);
$classId = 0;
foreach ($studentClasses as $studentClass) {
	if (trim((string) ($studentClass['class_name'] ?? '')) === $className) {
		$classId = (int) ($studentClass['class_id'] ?? 0);
		break;
	}
}

if ($classId <= 0) {
	set_flash('error', 'Không tìm thấy lớp học tương ứng để nộp bài.');
	redirect(page_url('classes-my'));
}

	if ($assignmentId <= 0) {
		foreach ($assignmentsTable->listForStudentByClass((int) $user['id'], $classId) as $assignmentRow) {
			if (trim((string) ($assignmentRow['title'] ?? '')) === $assignmentTitle) {
				$assignmentId = (int) ($assignmentRow['id'] ?? 0);
				break;
			}
		}
	}

if ($assignmentId <= 0) {
	set_flash('error', 'Không tìm thấy bài tập tương ứng để nộp.');
	redirect(page_url('classes-my'));
}

$fileUpload = store_uploaded_file($_FILES['submission_file'], sprintf('submission-%d', (int) $user['id']), 'homeworks');
if ($fileUpload === null) {
	set_flash('error', 'Tải lên bài làm thất bại. Vui lòng thử lại.');
	redirect(page_url('classes-my'));
}

$submissionsTable->upsertStudentSubmission((int) $user['id'], $assignmentId, $fileUpload);

$successMessage = sprintf('Đã nộp bài "%s" cho lớp "%s" thành công.', $assignmentTitle, $className);
if ($deadline !== '') {
	$successMessage .= sprintf(' Deadline: %s.', $deadline);
}
if ($note !== '') {
	$successMessage .= ' Ghi chú đã được ghi nhận.';
}

set_flash('success', $successMessage);
redirect(page_url('classes-my'));

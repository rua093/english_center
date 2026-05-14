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
	set_flash('error', t('flash.invalid_request'));
	redirect(page_url('classes-my'));
}

$user = auth_user();
if (!$user) {
	set_flash('error', t('flash.login_required'));
	redirect(page_url('login'));
}

if (!has_permission('student.assignment.submit') && !in_array((string) ($user['role'] ?? ''), ['student', 'admin'], true)) {
	set_flash('error', t('student.assignment.no_permission'));
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

$respondError = static function (string $message) {
	if (api_expects_json()) {
		api_error($message, ['code' => 'VALIDATION_ERROR'], 400);
	}

	set_flash('error', $message);
	redirect(page_url('classes-my'));
};

if ($className === '' || $assignmentTitle === '') {
	$respondError(t('student.assignment.missing_class_assignment'));
}

if (empty($_FILES['submission_file']['name'])) {
	$respondError(t('student.assignment.missing_file'));
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
	$respondError(t('student.assignment.class_not_found'));
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
	$respondError(t('student.assignment.assignment_not_found'));
}

$fileUpload = store_uploaded_file($_FILES['submission_file'], sprintf('submission-%d', (int) $user['id']), 'homeworks');
if ($fileUpload === null) {
	$respondError(t('student.assignment.upload_failed'));
}

$submissionsTable->upsertStudentSubmission((int) $user['id'], $assignmentId, $fileUpload);

$successMessage = t('student.assignment.submit_success', ['assignment' => $assignmentTitle, 'class' => $className]);
if ($deadline !== '') {
	$successMessage .= sprintf(' Deadline: %s.', $deadline);
}
if ($note !== '') {
	$successMessage .= ' ' . t('student.assignment.note_recorded');
}

if (api_expects_json()) {
	api_success($successMessage, [
		'assignment_id' => $assignmentId,
		'class_name' => $className,
	]);
}

set_flash('success', $successMessage);
redirect(page_url('classes-my'));

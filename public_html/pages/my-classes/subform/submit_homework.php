<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

require_role(['student', 'admin']);

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

$fileUpload = store_uploaded_file($_FILES['submission_file'], sprintf('submission-%d', (int) $user['id']));
if ($fileUpload === null) {
	set_flash('error', 'Tải lên bài làm thất bại. Vui lòng thử lại.');
	redirect(page_url('classes-my'));
}

$successMessage = sprintf('Đã nộp bài "%s" cho lớp "%s" thành công.', $assignmentTitle, $className);
if ($deadline !== '') {
	$successMessage .= sprintf(' Deadline: %s.', $deadline);
}
if ($note !== '') {
	$successMessage .= ' Ghi chú đã được ghi nhận.';
}

set_flash('success', $successMessage);
redirect(page_url('classes-my'));

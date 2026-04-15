<?php
declare(strict_types=1);

require_permission('student.assignment.submit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=student-dashboard');
}

$assignmentId = (int) ($_POST['assignment_id'] ?? 0);
$fileUrl = trim((string) ($_POST['file_url'] ?? ''));
$user = auth_user();

if ($assignmentId > 0 && $user) {
$uploadPath = $fileUrl;
if (!empty($_FILES['submission_file']['name'])) {
$fileUpload = store_uploaded_file($_FILES['submission_file'], sprintf('submission-%d-%d', (int) $user['id'], $assignmentId));
if ($fileUpload === null) {
set_flash('error', 'Tải lên bài làm thất bại. Vui lòng thử lại.');
redirect('/?page=student-dashboard');
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

redirect('/?page=student-dashboard');

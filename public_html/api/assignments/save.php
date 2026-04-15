<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-assignments');
}

$assignmentId = (int) ($_POST['id'] ?? 0);
if ($assignmentId > 0) {
require_permission('academic.assignments.update');
} else {
require_permission('academic.assignments.create');
}

$payload = $_POST;
if (empty($payload['deadline']) && !empty($payload['due_date'])) {
$payload['deadline'] = (string) $payload['due_date'];
}

$uploadPath = trim((string) ($payload['file_url'] ?? ''));
if (!empty($_FILES['assignment_file']['name'])) {
$fileUpload = store_uploaded_file($_FILES['assignment_file'], 'assignment');
if ($fileUpload === null) {
set_flash('error', 'Tải lên file bài tập thất bại.');
redirect('/?page=academic-assignments');
}
$uploadPath = $fileUpload;
}

if (
    (int) ($payload['lesson_id'] ?? 0) <= 0 ||
    trim((string) ($payload['title'] ?? '')) === '' ||
    trim((string) ($payload['deadline'] ?? '')) === ''
) {
set_flash('error', 'Vui lòng nhập đầy đủ buổi học, tiêu đề và hạn nộp.');
redirect($assignmentId > 0 ? ('/?page=academic-assignment-edit&id=' . $assignmentId) : '/?page=academic-assignment-edit');
}

$payload['file_url'] = $uploadPath;
(new AcademicModel())->saveAssignment($payload);
set_flash('success', 'Đã lưu bài tập thành công.');

redirect('/?page=academic-assignments');

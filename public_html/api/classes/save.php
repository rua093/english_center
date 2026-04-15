<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-classes');
}

$classId = (int) ($_POST['id'] ?? 0);
if ($classId > 0) {
require_permission('academic.classes.update');
} else {
require_permission('academic.classes.create');
}

$payload = $_POST;
$statusMap = [
    'planned' => 'upcoming',
    'completed' => 'graduated',
];
$payload['status'] = $statusMap[(string) ($payload['status'] ?? '')] ?? (string) ($payload['status'] ?? 'upcoming');

if (
    (int) ($payload['course_id'] ?? 0) <= 0 ||
    trim((string) ($payload['class_name'] ?? '')) === '' ||
    (int) ($payload['teacher_id'] ?? 0) <= 0
) {
set_flash('error', 'Vui lòng nhập đầy đủ khóa học, tên lớp và giáo viên.');
redirect($classId > 0 ? ('/?page=academic-class-edit&id=' . $classId) : '/?page=academic-class-edit');
}

(new AcademicModel())->saveClass($payload);
set_flash('success', 'Đã lưu lớp học thành công.');

redirect('/?page=academic-classes');

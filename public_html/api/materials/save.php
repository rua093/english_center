<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-materials');
}

$materialId = (int) ($_POST['id'] ?? 0);
if ($materialId > 0) {
require_permission('materials.update');
} else {
require_permission('materials.create');
}

$payload = $_POST;
if (empty($payload['course_id']) && !empty($payload['class_id'])) {
$payload['course_id'] = $payload['class_id'];
}

$uploadPath = trim((string) ($payload['file_path'] ?? ''));
if (!empty($_FILES['material_file']['name'])) {
$fileUpload = store_uploaded_file($_FILES['material_file'], 'material');
if ($fileUpload === null) {
set_flash('error', 'Tải lên tài liệu thất bại.');
redirect('/?page=academic-materials');
}
$uploadPath = $fileUpload;
}

if (empty($payload['type'])) {
$extension = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));
$payload['type'] = match ($extension) {
'mp3' => 'mp3',
'mp4', 'mov', 'webm', 'avi' => 'video',
default => 'pdf',
};
}

if (
    (int) ($payload['course_id'] ?? 0) <= 0 ||
    trim((string) ($payload['title'] ?? '')) === '' ||
    !in_array((string) ($payload['type'] ?? ''), ['pdf', 'mp3', 'video'], true)
) {
set_flash('error', 'Vui lòng nhập đầy đủ khóa học, tiêu đề và kiểu tài liệu.');
redirect($materialId > 0 ? ('/?page=academic-material-edit&id=' . $materialId) : '/?page=academic-material-edit');
}

$payload['file_path'] = $uploadPath;
(new AcademicModel())->saveMaterial($payload);
set_flash('success', 'Đã lưu tài liệu thành công.');

redirect('/?page=academic-materials');

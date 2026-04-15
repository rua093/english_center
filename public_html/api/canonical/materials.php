<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_materials_save_action(): void
{
	api_require_post(page_url('materials-academic'));

	$materialId = input_int($_POST, 'id');
	api_guard_permission($materialId > 0 ? 'materials.update' : 'materials.create');

	$payload = $_POST;
	if (empty($payload['course_id']) && !empty($payload['class_id'])) {
		$payload['course_id'] = $payload['class_id'];
	}

	$uploadPath = input_string($payload, 'file_path');
	if (!empty($_FILES['material_file']['name'])) {
		$fileUpload = store_uploaded_file($_FILES['material_file'], 'material');
		if ($fileUpload === null) {
			set_flash('error', 'Tải lên tài liệu thất bại.');
			redirect(page_url('materials-academic'));
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
		input_int($payload, 'course_id') <= 0 ||
		input_string($payload, 'title') === '' ||
		!in_array((string) ($payload['type'] ?? ''), ['pdf', 'mp3', 'video'], true)
	) {
		set_flash('error', 'Vui lòng nhập đầy đủ khóa học, tiêu đề và kiểu tài liệu.');
		redirect($materialId > 0 ? page_url('materials-academic-edit', ['id' => $materialId]) : page_url('materials-academic-edit'));
	}

	$payload['file_path'] = $uploadPath;
	(new AcademicModel())->saveMaterial($payload);
	set_flash('success', 'Đã lưu tài liệu thành công.');

	redirect(page_url('materials-academic'));
}

function api_materials_edit_action(): void
{
	api_guard_permission('materials.update');
	redirect(page_url('materials-academic-edit', ['id' => (int) ($_GET['id'] ?? 0)]));
}

function api_materials_delete_action(): void
{
	api_guard_permission('materials.delete');
	api_require_post(page_url('materials-academic'));

	(new AcademicModel())->deleteMaterial((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa tài liệu.');
	redirect(page_url('materials-academic'));
}

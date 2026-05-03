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
	$editPath = $materialId > 0 ? page_url('materials-academic-edit', ['id' => $materialId]) : page_url('materials-academic-edit');

	$payload = $_POST;

	$uploadPath = input_string($payload, 'existing_file_path');
	$manualFilePath = input_string($payload, 'file_path');
	if ($manualFilePath !== '') {
		$uploadPath = $manualFilePath;
	}

	if (!empty($_FILES['material_file']['name'])) {
		$fileUpload = store_uploaded_file($_FILES['material_file'], 'material');
		if ($fileUpload === null) {
			$uploadErrorCode = (int) ($_FILES['material_file']['error'] ?? UPLOAD_ERR_OK);
			$uploadMessage = 'Tải lên tài liệu thất bại.';
			if ($uploadErrorCode === UPLOAD_ERR_INI_SIZE || $uploadErrorCode === UPLOAD_ERR_FORM_SIZE) {
				$uploadMessage = 'File tài liệu vượt quá giới hạn dung lượng cho phép.';
			}

			set_flash('error', $uploadMessage);
			redirect($editPath);
		}
		$uploadPath = $fileUpload;
	}

	if (
		$uploadPath === '' ||
		input_string($payload, 'title') === ''
	) {
		set_flash('error', 'Vui lòng nhập tiêu đề và tải lên file.');
		redirect($editPath);
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

	try {
		(new AcademicModel())->deleteMaterial((int) ($_GET['id'] ?? 0));
		set_flash('success', 'Đã xóa tài liệu.');
	} catch (Throwable) {
		set_flash('error', 'Không thể xóa tài liệu. Tài liệu này có thể đang được sử dụng hoặc dữ liệu không hợp lệ.');
	}
	redirect(page_url('materials-academic'));
}

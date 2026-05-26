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
	$academicModel = new AcademicModel();
	$existingMaterial = $materialId > 0 ? $academicModel->findMaterial($materialId) : null;

	$payload = $_POST;

	$uploadPath = input_string($payload, 'existing_file_path');
	$directUploadPath = input_string($payload, 'uploaded_file_url');
	if ($directUploadPath !== '') {
		if (!is_trusted_uploaded_file_url($directUploadPath)) {
			set_flash('error', 'Tài liệu tải lên chưa hợp lệ. Vui lòng thử lại.');
			redirect($editPath);
		}

		$uploadPath = normalize_public_file_url($directUploadPath);
	}

	$manualFilePath = input_string($payload, 'file_path');
	if ($manualFilePath !== '') {
		$uploadPath = $manualFilePath;
	}

	if (!empty($_FILES['material_file']['name'])) {
		if (app_uploaded_file_looks_like_video($_FILES['material_file'])) {
			set_flash('error', 'Video tài liệu chưa được tải lên. Vui lòng thử lại.');
			redirect($editPath);
		}

		$storedPath = store_uploaded_file_for_preset($_FILES['material_file'], 'material_file');
		if ($storedPath === null) {
			set_flash('error', 'Không thể tải tài liệu lên. Vui lòng thử lại.');
			redirect($editPath);
		}

		$uploadPath = $storedPath;
	}

	if (
		$uploadPath === '' ||
		input_string($payload, 'title') === ''
	) {
		set_flash('error', 'Vui lòng nhập tiêu đề và tải lên file.');
		redirect($editPath);
	}

	$payload['file_path'] = $uploadPath;
	$academicModel->saveMaterial($payload);
	if (is_array($existingMaterial)) {
		app_cleanup_replaced_uploaded_file((string) ($existingMaterial['file_path'] ?? ''), $uploadPath);
	}
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
		$academicModel = new AcademicModel();
		$material = $academicModel->findMaterial((int) ($_GET['id'] ?? 0));
		$academicModel->deleteMaterial((int) ($_GET['id'] ?? 0));
		if (is_array($material)) {
			app_delete_uploaded_file_by_url((string) ($material['file_path'] ?? ''));
		}
		set_flash('success', 'Đã xóa tài liệu.');
	} catch (Throwable) {
		set_flash('error', 'Không thể xóa tài liệu. Tài liệu này có thể đang được sử dụng hoặc dữ liệu không hợp lệ.');
	}
	redirect(page_url('materials-academic'));
}

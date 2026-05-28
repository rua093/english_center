<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_portfolios_save_action(): void
{
	api_guard_admin_or_staff();
	api_guard_login();
	api_require_post(page_url('portfolios-academic'));

	$user = auth_user();
	$academicModel = new AcademicModel();
	$portfolioId = input_int($_POST, 'id');
	api_guard_permission($portfolioId > 0 ? 'academic.portfolios.update' : 'academic.portfolios.create');
	$existing = null;

	if ($portfolioId > 0) {
		$existing = $academicModel->findPortfolio($portfolioId);
	}

	$uploadPath = trim((string) (($existing['media_url'] ?? '')));
	$directUploadPath = trim((string) ($_POST['uploaded_media_url'] ?? ''));
	if ($directUploadPath !== '') {
		if (!is_trusted_uploaded_file_url($directUploadPath)) {
			set_flash('error', 'File tải lên chưa hợp lệ. Vui lòng thử lại.');
			redirect(page_url('portfolios-academic'));
		}

		$uploadPath = normalize_public_file_url($directUploadPath);
	}
	if (!empty($_FILES['portfolio_file']['name'])) {
		if (app_uploaded_file_looks_like_video($_FILES['portfolio_file'])) {
			set_flash('error', 'Video hồ sơ tiến bộ chưa được tải lên. Vui lòng thử lại.');
			redirect(page_url('portfolios-academic'));
		}

		$storedPath = store_uploaded_file_for_preset($_FILES['portfolio_file'], 'portfolio_media');
		if ($storedPath === null) {
			set_flash('error', 'Không thể tải tệp hồ sơ tiến bộ lên. Vui lòng thử lại.');
			redirect(page_url('portfolios-academic'));
		}

		$uploadPath = $storedPath;
	}

	if ($uploadPath === '') {
		set_flash('error', 'Vui lòng tải lên tệp cho hồ sơ tiến bộ.');
		if ($portfolioId > 0) {
			redirect(page_url('portfolios-academic', ['edit' => $portfolioId]));
		}

		redirect(page_url('portfolios-academic'));
	}

	$_POST['media_url'] = $uploadPath;
	$academicModel->savePortfolio($_POST);
	app_uploaded_object_manifest_mark_attached($uploadPath, ['entity' => 'portfolio_media']);
	if (is_array($existing)) {
		app_cleanup_replaced_uploaded_file((string) ($existing['media_url'] ?? ''), $uploadPath);
	}
	set_flash('success', 'Đã lưu hồ sơ tiến bộ thành công.');

	redirect(page_url('portfolios-academic'));
}

function api_portfolios_edit_action(): void
{
	api_guard_admin_or_staff();
	api_guard_login();
	api_guard_permission('academic.portfolios.update');

	$portfolioId = (int) ($_GET['id'] ?? 0);

	redirect(page_url('portfolios-academic', ['edit' => $portfolioId]));
}

function api_portfolios_delete_action(): void
{
	api_guard_admin_or_staff();
	api_guard_login();
	api_guard_permission('academic.portfolios.delete');
	api_require_post(page_url('portfolios-academic'));

	$portfolioId = (int) ($_GET['id'] ?? 0);

	try {
		$academicModel = new AcademicModel();
		$existing = $academicModel->findPortfolio($portfolioId);
		$academicModel->deletePortfolio($portfolioId);
		if (is_array($existing)) {
			app_delete_uploaded_file_by_url((string) ($existing['media_url'] ?? ''));
		}
		set_flash('success', 'Đã xóa hồ sơ tiến bộ.');
	} catch (Throwable) {
		set_flash('error', 'Không thể xóa hồ sơ tiến bộ. Vui lòng thử lại.');
	}
	redirect(page_url('portfolios-academic'));
}

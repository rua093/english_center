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
	if (!empty($_FILES['portfolio_file']['name'])) {
		$fileUpload = store_uploaded_file($_FILES['portfolio_file'], 'portfolio');
		if ($fileUpload === null) {
			set_flash('error', 'Tải lên media portfolio thất bại.');
			redirect(page_url('portfolios-academic'));
		}
		$uploadPath = $fileUpload;
	}

	if ($uploadPath === '') {
		set_flash('error', 'Vui lòng tải lên media cho portfolio.');
		if ($portfolioId > 0) {
			redirect(page_url('portfolios-academic', ['edit' => $portfolioId]));
		}

		redirect(page_url('portfolios-academic'));
	}

	$_POST['media_url'] = $uploadPath;
	$academicModel->savePortfolio($_POST);
	set_flash('success', 'Đã lưu portfolio thành công.');

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

	(new AcademicModel())->deletePortfolio($portfolioId);
	set_flash('success', 'Đã xóa portfolio.');
	redirect(page_url('portfolios-academic'));
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_feedbacks_save_action(): void
{
	api_require_post(page_url('feedback'));
	require_login();

	$feedbackId = input_int($_POST, 'id');
	$redirectTo = safe_referer_path(input_string($_POST, 'redirect_to'));
	if ($redirectTo === '') {
		$redirectTo = $feedbackId > 0 ? page_url('feedbacks-manage') : page_url('feedback');
	}

	if ($feedbackId > 0) {
		api_guard_permission('feedback.update');
	}

	$rating = input_int($_POST, 'rating');
	if ($rating < 1 || $rating > 5) {
		set_flash('error', 'Vui lòng nhập đánh giá từ 1 đến 5.');
		redirect($redirectTo);
	}

	$user = auth_user() ?? [];
	$userId = (int) ($user['id'] ?? 0);
	$isPublicWeb = input_int($_POST, 'is_public_web') === 1 ? 1 : 0;
	if ($userId <= 0) {
		set_flash('error', 'Không thể xác định người gửi đánh giá.');
		redirect($redirectTo);
	}

	$_POST['sender_id'] = $userId;
	$_POST['is_public_web'] = $isPublicWeb;
	(new AcademicModel())->saveFeedback($_POST);
	set_flash('success', 'Đã lưu feedback thành công.');

	redirect($redirectTo);
}

function api_feedbacks_delete_action(): void
{
	api_guard_permission('feedback.delete');
	api_require_post(page_url('feedbacks-manage'));

	(new AcademicModel())->deleteFeedback((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa feedback.');
	redirect(page_url('feedbacks-manage'));
}

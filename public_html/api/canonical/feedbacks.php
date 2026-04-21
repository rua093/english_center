<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_feedbacks_save_action(): void
{
	api_require_post(page_url('feedbacks-manage'));

	$feedbackId = input_int($_POST, 'id');
	if ($feedbackId <= 0) {
		set_flash('error', 'Chức năng tạo mới đánh giá chỉ áp dụng cho cổng học viên.');
		redirect(page_url('feedbacks-manage'));
	}

	api_guard_permission('feedback.update');

	$rating = input_int($_POST, 'rating');
	if ($rating < 1 || $rating > 5) {
		set_flash('error', 'Vui lòng nhập đánh giá từ 1 đến 5.');
		redirect(page_url('feedbacks-manage'));
	}

	(new AcademicModel())->saveFeedback($_POST);
	set_flash('success', 'Đã lưu feedback thành công.');

	redirect(page_url('feedbacks-manage'));
}

function api_feedbacks_delete_action(): void
{
	api_guard_permission('feedback.delete');
	api_require_post(page_url('feedbacks-manage'));

	(new AcademicModel())->deleteFeedback((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa feedback.');
	redirect(page_url('feedbacks-manage'));
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/../../models/tables/ClassesTableModel.php';
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
	$classId = input_int($_POST, 'class_id');
	$teacherId = input_int($_POST, 'teacher_id');
	$classStudentsTable = new ClassStudentsTableModel();
	$classesTable = new ClassesTableModel();

	if ($classId <= 0 && $userId > 0) {
		$studentClasses = $classStudentsTable->listMyClassesForStudent($userId);
		if (!empty($studentClasses)) {
			$classId = (int) ($studentClasses[0]['class_id'] ?? 0);
		}
	}

	if ($classId <= 0) {
		$firstClass = $classesTable->listDetailedWithProgressPage(1, 1)[0] ?? null;
		if (is_array($firstClass)) {
			$classId = (int) ($firstClass['id'] ?? 0);
			$teacherId = (int) ($firstClass['teacher_id'] ?? 0);
		}
	}

	if ($teacherId <= 0 && $classId > 0) {
		$classRow = $classesTable->findById($classId);
		$teacherId = (int) ($classRow['teacher_id'] ?? 0);
	}

	if ($classId <= 0 || $teacherId <= 0) {
		set_flash('error', 'Không thể xác định lớp học và giáo viên để lưu đánh giá.');
		redirect($redirectTo);
	}

	$_POST['sender_id'] = $userId;
	$_POST['class_id'] = $classId;
	$_POST['teacher_id'] = $teacherId;
	$_POST['status'] = (string) ($_POST['status'] ?? 'pending');
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

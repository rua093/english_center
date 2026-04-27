<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/file_storage.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../models/AcademicModel.php';
require_once __DIR__ . '/../../models/tables/ExtracurricularActivitiesTableModel.php';

function api_activities_save_action(): void
{
	api_require_post(page_url('activities-manage'));

	$activityId = input_int($_POST, 'id');
	api_guard_permission($activityId > 0 ? 'activity.update' : 'activity.create');

	$payload = $_POST;
	$payload['id'] = $activityId;

	$thumbnailPath = trim((string) ($_POST['existing_image_thumbnail'] ?? ''));
	if (isset($_FILES['activity_thumbnail']) && is_array($_FILES['activity_thumbnail']) && (int) ($_FILES['activity_thumbnail']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
		$storedThumbnail = store_uploaded_file($_FILES['activity_thumbnail'], 'activity_thumb');
		if ($storedThumbnail === null) {
			set_flash('error', 'Không thể tải lên ảnh thumbnail. Vui lòng thử lại với tệp hợp lệ.');
			$query = $activityId > 0 ? ['edit' => $activityId] : [];
			redirect(page_url('activities-manage', $query));
		}

		$thumbnailPath = $storedThumbnail;
	}

	$payload['image_thumbnail'] = $thumbnailPath;

	(new AcademicModel())->saveActivity($payload);
	set_flash('success', 'Đã lưu hoạt động ngoại khóa thành công.');

	redirect(page_url('activities-manage'));
}

function api_activities_delete_action(): void
{
	api_guard_permission('activity.delete');
	api_require_post(page_url('activities-manage'));

	(new AcademicModel())->deleteActivity((int) ($_GET['id'] ?? 0));
	set_flash('success', 'Đã xóa hoạt động ngoại khóa.');
	redirect(page_url('activities-manage'));
}

function api_activities_remove_student_action(): void
{
	api_guard_permission('activity.update');
	api_require_post(page_url('activities-manage'));

	$activityId = input_int($_POST, 'activity_id');
	$studentId = input_int($_POST, 'student_id');
	$activityPage = max(0, input_int($_POST, 'activity_page'));
	$activityPerPage = max(0, input_int($_POST, 'activity_per_page'));

	$redirectQuery = [];
	if ($activityId > 0) {
		$redirectQuery['registrations_activity'] = $activityId;
	}
	if ($activityPage > 0) {
		$redirectQuery['activity_page'] = $activityPage;
	}
	if ($activityPerPage > 0) {
		$redirectQuery['activity_per_page'] = $activityPerPage;
	}

	if ($activityId <= 0 || $studentId <= 0) {
		set_flash('error', 'Du lieu hoc vien hoac hoat dong khong hop le.');
		redirect(page_url('activities-manage', $redirectQuery));
	}

	$removed = (new AcademicModel())->removeActivityRegistration($activityId, $studentId);
	if ($removed) {
		set_flash('success', 'Da xoa hoc vien khoi danh sach dang ky hoat dong.');
	} else {
		set_flash('error', 'Khong tim thay dang ky de xoa.');
	}

	redirect(page_url('activities-manage', $redirectQuery));
}

function api_activities_join_action(): void
{
	require_role(['student', 'admin']);
	api_require_post(page_url('activities-student'));

	$user = auth_user() ?? [];
	$userId = (int) ($user['id'] ?? 0);
	$activityId = input_int($_POST, 'activity_id');
	$redirectPath = $activityId > 0 ? page_url('activities-details', ['id' => $activityId]) : page_url('activities-student');

	if ($userId <= 0 || $activityId <= 0) {
		if (api_expects_json()) {
			api_error('Dữ liệu đăng ký không hợp lệ.', ['code' => 'INVALID_PAYLOAD'], 422);
		}

		set_flash('error', 'Dữ liệu đăng ký không hợp lệ.');
		redirect($redirectPath);
	}

	$model = new ExtracurricularActivitiesTableModel();
	$activity = $model->findById($activityId);
	if (!is_array($activity)) {
		if (api_expects_json()) {
			api_error('Không tìm thấy hoạt động ngoại khoá.', ['code' => 'ACTIVITY_NOT_FOUND'], 404);
		}

		set_flash('error', 'Không tìm thấy hoạt động ngoại khoá.');
		redirect(page_url('activities-student'));
	}

	$registration = $model->findStudentRegistration($activityId, $userId);
	if (is_array($registration)) {
		if (api_expects_json()) {
			api_success('Bạn đã đăng ký hoạt động này rồi.', [
				'activity_id' => $activityId,
				'registration_id' => (int) ($registration['id'] ?? 0),
				'payment_status' => (string) ($registration['payment_status'] ?? 'unpaid'),
			]);
		}

		set_flash('info', 'Bạn đã đăng ký hoạt động này rồi.');
		redirect($redirectPath);
	}

	$model->joinActivity($activityId, $userId);

	if (api_expects_json()) {
		api_success('Đăng ký hoạt động ngoại khoá thành công.', [
			'activity_id' => $activityId,
			'payment_status' => 'unpaid',
		]);
	}

	set_flash('success', 'Đăng ký hoạt động ngoại khoá thành công.');
	redirect($redirectPath);
}

function api_activities_pay_action(): void
{
	require_role(['student', 'admin']);
	api_require_post(page_url('activities-student'));

	/*
	$payment flow is intentionally commented out until the payment gateway is integrated.
	$user = auth_user() ?? [];
	$userId = (int) ($user['id'] ?? 0);
	$activityId = input_int($_POST, 'activity_id');
	$redirectPath = $activityId > 0 ? page_url('activities-details', ['id' => $activityId]) : page_url('activities-student');
	...
	$model->markActivityPaid($activityId, $userId);
	set_flash('success', 'Thanh toán hoạt động ngoại khoá thành công.');
	redirect($redirectPath);
	*/

	if (api_expects_json()) {
		api_error('Thanh toán hiện chưa được tích hợp.', ['code' => 'PAYMENT_NOT_IMPLEMENTED'], 501);
	}

	set_flash('info', 'Thanh toán hiện chưa được tích hợp.');
	redirect(page_url('activities-student'));
}

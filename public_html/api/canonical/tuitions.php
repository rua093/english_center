<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/page_actions.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function api_tuitions_is_admin(): bool
{
	$user = auth_user();
	return is_array($user) && (string) ($user['role'] ?? '') === 'admin';
}

function api_tuitions_can_manage_directly(): bool
{
	if (api_tuitions_is_admin()) {
		return true;
	}

	return has_any_permission([
		'finance.tuition.manage',
		'finance.tuition.create',
		'finance.tuition.update',
	]);
}

function api_tuitions_can_delete_directly(): bool
{
	if (api_tuitions_is_admin()) {
		return true;
	}

	return has_any_permission([
		'finance.tuition.manage',
		'finance.tuition.delete',
	]);
}

function api_tuitions_save_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.view');
	api_require_post(page_url('tuition-finance'));

	if (!api_tuitions_can_manage_directly()) {
		set_flash('error', 'Bạn không có quyền CRUD trực tiếp học phí. Vui lòng gửi yêu cầu phê duyệt.');
		redirect(page_url('tuition-finance'));
	}

	$id = input_int($_POST, 'id');
	$studentId = input_int($_POST, 'student_id');
	$classId = input_int($_POST, 'class_id');
	$totalAmount = input_float($_POST, 'total_amount');
	$amountPaid = input_float($_POST, 'amount_paid');
	$paymentPlan = input_string($_POST, 'payment_plan', 'full');
	$requestedStatus = input_string($_POST, 'status', '');
	$academicModel = new AcademicModel();

	if ($studentId <= 0 || $classId <= 0 || $totalAmount < 0 || $amountPaid < 0) {
		set_flash('error', 'Vui lòng nhập đầy đủ học viên, lớp học và số tiền hợp lệ.');
		$query = $id > 0 ? ['edit' => $id] : [];
		redirect(page_url('tuition-finance', $query));
	}

	if ($requestedStatus === 'paid' && $amountPaid < $totalAmount) {
		set_flash('error', 'Không thể chuyển trạng thái paid khi số tiền đã thu chưa đủ tổng học phí.');
		$query = $id > 0 ? ['edit' => $id] : [];
		redirect(page_url('tuition-finance', $query));
	}

	if (!$academicModel->isStudentEnrolledInClass($studentId, $classId)) {
		set_flash('error', 'Học viên không thuộc lớp đã chọn. Vui lòng chọn đúng học viên trong lớp.');
		$query = $id > 0 ? ['edit' => $id] : [];
		redirect(page_url('tuition-finance', $query));
	}

	$status = $amountPaid >= $totalAmount ? 'paid' : 'debt';

	$academicModel->saveTuitionFee([
		'id' => $id,
		'student_id' => $studentId,
		'class_id' => $classId,
		'total_amount' => $totalAmount,
		'amount_paid' => $amountPaid,
		'payment_plan' => $paymentPlan,
		'status' => $status,
	]);

	set_flash('success', $id > 0 ? 'Đã cập nhật học phí thành công.' : 'Đã tạo học phí thành công.');
	redirect(page_url('tuition-finance'));
}

function api_tuitions_delete_action(): void
{
	api_guard_admin_or_staff();
	api_require_post(page_url('tuition-finance'));

	$user = auth_user();
	$tuitionId = input_int($_POST, 'tuition_id');

	if (!api_tuitions_can_delete_directly() && $user && (string) $user['role'] === 'staff') {
		if ($tuitionId > 0) {
			$fee = (new AcademicModel())->findTuitionFee($tuitionId);
			if ($fee) {
				queue_approval_request(
					'tuition_delete',
					sprintf(
						'Yêu cầu xóa học phí #%d | Học viên: %s | Lớp: %s | Lý do: Thao tác xóa trực tiếp của Staff cần duyệt.',
						$tuitionId,
						(string) ($fee['student_name'] ?? 'N/A'),
						(string) ($fee['class_name'] ?? 'N/A')
					),
					[
						'tuition_id' => $tuitionId,
					]
				);
			}
		}

		set_flash('success', 'Yêu cầu xóa học phí đã được chuyển sang luồng phê duyệt.');
		redirect(page_url('tuition-finance'));
	}

	if (!api_tuitions_can_delete_directly()) {
		api_guard_permission('finance.tuition.delete');
	}

	if ($tuitionId > 0) {
		(new AcademicModel())->deleteTuitionFee($tuitionId);
		set_flash('success', 'Đã xóa học phí thành công.');
	}

	redirect(page_url('tuition-finance'));
}

function api_tuitions_update_action(): void
{
	api_guard_permission('student.tuition.update');
	api_require_post(page_url('dashboard-student'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$amount = input_float($_POST, 'amount');
	$user = auth_user();

	if ($tuitionId > 0 && $amount > 0 && $user) {
		$success = (new AcademicModel())->recordStudentWebPayment((int) $user['id'], $tuitionId, $amount, 'bank_transfer');
		if ($success) {
			set_flash('success', 'Đã ghi nhận thanh toán và tự động tạo biên lai thành công.');
		} else {
			set_flash('error', 'Không tìm thấy học phí hợp lệ để cập nhật thanh toán.');
		}
	}

	redirect(page_url('dashboard-student'));
}

function api_tuitions_request_adjust_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.view');
	api_require_post(page_url('tuition-finance'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$requestedPaid = input_float($_POST, 'requested_amount_paid');
	$reason = input_string($_POST, 'reason', 'Yêu cầu chỉnh sửa số tiền đã thu.');

	$academicModel = new AcademicModel();
	$fee = $academicModel->findTuitionFee($tuitionId);
	if ($fee) {
		$content = sprintf(
			'Yêu cầu chỉnh sửa tài chính học phí #%d | Học viên: %s | Lớp: %s | Số đã thu hiện tại: %s | Số đề xuất: %s | Lý do: %s',
			$tuitionId,
			(string) ($fee['student_name'] ?? 'N/A'),
			(string) ($fee['class_name'] ?? 'N/A'),
			format_money((float) ($fee['amount_paid'] ?? 0)),
			format_money($requestedPaid),
			$reason !== '' ? $reason : 'Không có ghi chú'
		);

		queue_approval_request('finance_adjust', $content, [
			'tuition_id' => $tuitionId,
			'requested_amount_paid' => $requestedPaid,
		]);

		set_flash('success', 'Đã gửi yêu cầu chỉnh sửa tài chính để Admin phê duyệt.');
	}

	redirect(page_url('tuition-finance'));
}

function api_tuitions_request_delete_action(): void
{
	api_guard_admin_or_staff();
	api_guard_permission('finance.tuition.view');
	api_require_post(page_url('tuition-finance'));

	$tuitionId = input_int($_POST, 'tuition_id');
	$reason = input_string($_POST, 'reason', 'Yêu cầu chỉnh sửa/xóa dữ liệu học phí.');
	$academicModel = new AcademicModel();
	$fee = $academicModel->findTuitionFee($tuitionId);

	if ($fee) {
		$content = sprintf(
			'Yêu cầu xóa học phí #%d | Học viên: %s | Lớp: %s | Lý do: %s',
			$tuitionId,
			(string) ($fee['student_name'] ?? 'N/A'),
			(string) ($fee['class_name'] ?? 'N/A'),
			$reason !== '' ? $reason : 'Không có ghi chú'
		);

		queue_approval_request('tuition_delete', $content, ['tuition_id' => $tuitionId]);
		set_flash('success', 'Đã gửi yêu cầu xóa học phí để Admin phê duyệt.');
	}

	redirect(page_url('tuition-finance'));
}

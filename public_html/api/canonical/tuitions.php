<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../core/page_actions.php';
require_once __DIR__ . '/../../models/AcademicModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

function api_tuitions_delete_action(): void
{
	api_guard_admin_or_staff();
	api_require_post(page_url('tuition-finance'));

	$user = auth_user();
	$tuitionId = input_int($_POST, 'tuition_id');

	if ($user && (string) $user['role'] === 'staff') {
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

	if (!$user || (string) $user['role'] !== 'admin') {
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
		(new UserModel())->updateTuitionPayment((int) $user['id'], $tuitionId, $amount);
		set_flash('success', 'Đã cập nhật học phí thành công.');
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

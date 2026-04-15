<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('finance.tuition.view');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=finance-tuition');
}

$tuitionId = (int) ($_POST['tuition_id'] ?? 0);
$requestedPaid = (float) ($_POST['requested_amount_paid'] ?? 0);
$reason = trim((string) ($_POST['reason'] ?? 'Yêu cầu chỉnh sửa số tiền đã thu.'));
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

redirect('/?page=finance-tuition');

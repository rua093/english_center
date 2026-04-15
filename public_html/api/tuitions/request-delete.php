<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('finance.tuition.view');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=finance-tuition');
}

$tuitionId = (int) ($_POST['tuition_id'] ?? 0);
$reason = trim((string) ($_POST['reason'] ?? 'Yêu cầu chỉnh sửa/xóa dữ liệu học phí.'));
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

redirect('/?page=finance-tuition');

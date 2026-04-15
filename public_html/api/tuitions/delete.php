<?php
declare(strict_types=1);

require_admin_or_staff();
$user = auth_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=finance-tuition');
}

if ($user && (string) $user['role'] === 'staff') {
$tuitionId = (int) ($_POST['tuition_id'] ?? 0);
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
redirect('/?page=finance-tuition');
}

if (!$user || (string) $user['role'] !== 'admin') {
require_permission('finance.tuition.delete');
}

$tuitionId = (int) ($_POST['tuition_id'] ?? 0);
if ($tuitionId > 0) {
(new AcademicModel())->deleteTuitionFee($tuitionId);
set_flash('success', 'Đã xóa học phí thành công.');
}

redirect('/?page=finance-tuition');

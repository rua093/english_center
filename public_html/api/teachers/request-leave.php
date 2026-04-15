<?php
declare(strict_types=1);

require_role(['teacher', 'admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect(page_url('dashboard-teacher'));
}

$scheduleId = (int) ($_POST['schedule_id'] ?? 0);
$newDate = trim((string) ($_POST['new_date'] ?? ''));
$reason = trim((string) ($_POST['reason'] ?? 'Xin nghỉ dạy theo lịch đã phân công.'));

if ($scheduleId > 0 && $newDate !== '') {
$user = auth_user();
$content = sprintf(
'Giáo viên %s xin nghỉ lịch #%d, đề xuất dời sang %s. Lý do: %s',
(string) ($user['full_name'] ?? 'N/A'),
$scheduleId,
$newDate,
$reason !== '' ? $reason : 'Không có ghi chú'
);
queue_approval_request('teacher_leave', $content, [
'schedule_id' => $scheduleId,
'new_date' => $newDate,
]);
set_flash('success', 'Yêu cầu nghỉ/dời lịch đã được gửi để phê duyệt.');
}

redirect(page_url('dashboard-teacher'));

<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-schedules');
}

$scheduleId = (int) ($_POST['id'] ?? 0);
if ($scheduleId > 0) {
require_permission('academic.schedules.update');
} else {
require_permission('academic.schedules.create');
}

$payload = $_POST;
$payload['room_id'] = (string) ($payload['room_id'] ?? '');

if (
    (int) ($payload['class_id'] ?? 0) <= 0 ||
    (int) ($payload['teacher_id'] ?? 0) <= 0 ||
    trim((string) ($payload['study_date'] ?? '')) === '' ||
    trim((string) ($payload['start_time'] ?? '')) === '' ||
    trim((string) ($payload['end_time'] ?? '')) === ''
) {
set_flash('error', 'Vui lòng nhập đầy đủ lớp học, giáo viên, ngày học và giờ học.');
redirect($scheduleId > 0 ? ('/?page=academic-schedule-edit&id=' . $scheduleId) : '/?page=academic-schedule-edit');
}

(new AcademicModel())->saveSchedule($payload);
set_flash('success', 'Đã lưu lịch học thành công.');

redirect('/?page=academic-schedules');

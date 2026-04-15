<?php
declare(strict_types=1);

require_permission('academic.schedules.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-schedules');
}

(new AcademicModel())->deleteSchedule((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa lịch học.');
redirect('/?page=academic-schedules');

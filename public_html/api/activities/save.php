<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-activities');
}

$activityId = (int) ($_POST['id'] ?? 0);
if ($activityId > 0) {
require_permission('activity.update');
} else {
require_permission('activity.create');
}
(new AcademicModel())->saveActivity($_POST);
set_flash('success', 'Đã lưu hoạt động ngoại khóa thành công.');

redirect('/?page=manage-activities');

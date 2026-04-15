<?php
declare(strict_types=1);

require_permission('activity.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-activities');
}

(new AcademicModel())->deleteActivity((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa hoạt động ngoại khóa.');
redirect('/?page=manage-activities');

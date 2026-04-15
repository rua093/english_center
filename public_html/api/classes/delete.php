<?php
declare(strict_types=1);

require_permission('academic.classes.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-classes');
}

(new AcademicModel())->deleteClass((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa lớp học.');
redirect('/?page=academic-classes');

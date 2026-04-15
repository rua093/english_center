<?php
declare(strict_types=1);

require_permission('academic.assignments.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-assignments');
}

(new AcademicModel())->deleteAssignment((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa bài tập.');
redirect('/?page=academic-assignments');

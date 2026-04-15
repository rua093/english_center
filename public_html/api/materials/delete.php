<?php
declare(strict_types=1);

require_permission('materials.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=academic-materials');
}

(new AcademicModel())->deleteMaterial((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa tài liệu.');
redirect('/?page=academic-materials');

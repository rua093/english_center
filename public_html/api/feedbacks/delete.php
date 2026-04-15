<?php
declare(strict_types=1);

require_permission('feedback.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-feedbacks');
}

(new AcademicModel())->deleteFeedback((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa feedback.');
redirect('/?page=manage-feedbacks');

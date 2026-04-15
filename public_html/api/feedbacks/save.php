<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-feedbacks');
}

$feedbackId = (int) ($_POST['id'] ?? 0);
if ($feedbackId > 0) {
require_permission('feedback.update');
} else {
require_permission('feedback.create');
}

if ($feedbackId <= 0 && (
    (int) ($_POST['student_id'] ?? $_POST['sender_id'] ?? 0) <= 0 ||
    (int) ($_POST['class_id'] ?? $_POST['course_id'] ?? 0) <= 0
)) {
set_flash('error', 'Vui lòng chọn học viên và lớp học.');
redirect('/?page=manage-feedbacks');
}

if (
    (int) ($_POST['rating'] ?? 0) < 1 ||
    (int) ($_POST['rating'] ?? 0) > 5
) {
set_flash('error', 'Vui lòng nhập đánh giá từ 1 đến 5.');
redirect('/?page=manage-feedbacks');
}

(new AcademicModel())->saveFeedback($_POST);
set_flash('success', 'Đã lưu feedback thành công.');

redirect('/?page=manage-feedbacks');

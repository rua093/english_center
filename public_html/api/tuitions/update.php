<?php
declare(strict_types=1);

require_permission('student.tuition.update');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=student-dashboard');
}

$tuitionId = (int) ($_POST['tuition_id'] ?? 0);
$amount = (float) ($_POST['amount'] ?? 0);
$user = auth_user();

if ($tuitionId > 0 && $amount > 0 && $user) {
(new UserModel())->updateTuitionPayment((int) $user['id'], $tuitionId, $amount);
set_flash('success', 'Đã cập nhật học phí thành công.');
}

redirect('/?page=student-dashboard');

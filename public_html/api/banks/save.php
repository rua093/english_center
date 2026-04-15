<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-bank');
}

$bankId = (int) ($_POST['id'] ?? 0);
if ($bankId > 0) {
require_permission('bank.update');
} else {
require_permission('bank.create');
}
(new AcademicModel())->saveBankAccount($_POST);
set_flash('success', 'Đã lưu tài khoản ngân hàng thành công.');

redirect('/?page=manage-bank');

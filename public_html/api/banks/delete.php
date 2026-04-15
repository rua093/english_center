<?php
declare(strict_types=1);

require_permission('bank.delete');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=manage-bank');
}

(new AcademicModel())->deleteBankAccount((int) ($_GET['id'] ?? 0));
set_flash('success', 'Đã xóa tài khoản ngân hàng.');
redirect('/?page=manage-bank');

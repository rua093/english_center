<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('admin.user.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=admin-users');
}

$userId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$currentUser = auth_user();
if ($currentUser && $userId === (int) $currentUser['id']) {
set_flash('error', 'Không thể tự xóa tài khoản đang đăng nhập.');
redirect('/?page=admin-users');
}

if ($userId > 0) {
(new AdminModel())->softDeleteUser($userId);
set_flash('success', 'Đã khóa/xóa mềm tài khoản người dùng.');
}

redirect('/?page=admin-users');

<?php
declare(strict_types=1);

require_admin_or_staff();
require_any_permission(['admin.user.delete']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect(page_url('users-admin'));
}

$userId = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$currentUser = auth_user();
if ($currentUser && $userId === (int) $currentUser['id']) {
set_flash('error', 'Không thể tự xóa tài khoản đang đăng nhập.');
redirect(page_url('users-admin'));
}

if ($userId > 0) {
(new AdminModel())->softDeleteUser($userId);
set_flash('success', 'Đã khóa hoặc xóa mềm tài khoản người dùng.');
}

redirect(page_url('users-admin'));

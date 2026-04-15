<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('admin.user.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect('/?page=admin-users');
}

$payload = [
'id' => (int) ($_POST['id'] ?? 0),
'username' => trim((string) ($_POST['username'] ?? '')),
'full_name' => trim((string) ($_POST['full_name'] ?? '')),
'role_id' => (int) ($_POST['role_id'] ?? 0),
'phone' => trim((string) ($_POST['phone'] ?? '')),
'email' => trim((string) ($_POST['email'] ?? '')),
'status' => (string) ($_POST['status'] ?? 'active'),
'password' => (string) ($_POST['password'] ?? ''),
];

if ($payload['username'] === '' || $payload['full_name'] === '' || $payload['role_id'] <= 0) {
set_flash('error', 'Vui lòng nhập đầy đủ thông tin người dùng bắt buộc.');
redirect('/?page=admin-users');
}

try {
(new AdminModel())->saveUser($payload);
set_flash('success', 'Đã lưu thông tin người dùng.');
} catch (Throwable $exception) {
set_flash('error', 'Lưu người dùng thất bại: ' . $exception->getMessage());
}

redirect('/?page=admin-users');

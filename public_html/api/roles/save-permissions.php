<?php
declare(strict_types=1);

require_admin_or_staff();
require_permission('admin.user.manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect(page_url('users-admin'));
}

$roleId = (int) ($_POST['role_id'] ?? 0);
$permissionIds = $_POST['permission_ids'] ?? [];
if ($roleId <= 0) {
set_flash('error', 'Vai trò không hợp lệ.');
redirect(page_url('users-admin'));
}

try {
(new AdminModel())->saveRolePermissions($roleId, is_array($permissionIds) ? $permissionIds : []);
if (is_logged_in()) {
$current = auth_user();
if ($current) {
$_SESSION['auth_permissions'] = fetch_permission_slugs((int) $current['id']);
}
}
set_flash('success', 'Đã cập nhật phân quyền theo vai trò.');
} catch (Throwable $exception) {
set_flash('error', 'Cập nhật phân quyền thất bại: ' . $exception->getMessage());
}

redirect(page_url('users-admin'));

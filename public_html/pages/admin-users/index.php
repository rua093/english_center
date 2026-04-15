<?php
require_admin_or_staff();
require_permission('admin.user.manage');

$adminModel = new AdminModel();
$users = $adminModel->listUsers();
$roles = $adminModel->listRoles();
$permissions = $adminModel->listPermissions();
$rolePermissionMap = $adminModel->rolePermissionMap();

$editingUser = null;
if (!empty($_GET['edit'])) {
    $editingUser = $adminModel->findUser((int) $_GET['edit']);
}

$module = 'users';
$adminTitle = 'Quản lý người dùng & phân quyền';

$success = get_flash('success');
$error = get_flash('error');
$editingUser = $editingUser ?? null;
?>

<div class="grid gap-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="mb-1 text-xs font-extrabold uppercase tracking-wide text-slate-500">Quản trị nhân sự</p>
            <h2>Người dùng và phân quyền</h2>
            <p>Quản lý tài khoản hệ thống và gán quyền theo vai trò.</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= $editingUser ? 'Cập nhật người dùng' : 'Tạo người dùng mới'; ?></h3>
        <form class="grid gap-3" method="post" action="/api/users/save">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingUser['id'] ?? 0); ?>">
            <label>
                Tên đăng nhập
                <input type="text" name="username" required value="<?= e((string) ($editingUser['username'] ?? '')); ?>">
            </label>
            <label>
                Họ và tên
                <input type="text" name="full_name" required value="<?= e((string) ($editingUser['full_name'] ?? '')); ?>">
            </label>
            <label>
                Vai trò
                <select name="role_id" required>
                    <option value="">-- Chọn vai trò --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role['id']; ?>" <?= (int) ($editingUser['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : ''; ?>>
                            <?= strtoupper((string) $role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Số điện thoại
                <input type="text" name="phone" value="<?= e((string) ($editingUser['phone'] ?? '')); ?>">
            </label>
            <label>
                Email
                <input type="email" name="email" value="<?= e((string) ($editingUser['email'] ?? '')); ?>">
            </label>
            <label>
                Trạng thái
                <select name="status">
                    <option value="active" <?= (($editingUser['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?= (($editingUser['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Khóa</option>
                </select>
            </label>
            <label>
                <?= $editingUser ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu'; ?>
                <input type="password" name="password" <?= $editingUser ? '' : 'required'; ?>>
            </label>
            <div class="inline-flex flex-wrap items-center gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu người dùng</button>
                <?php if ($editingUser): ?>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="/admin/users">Hủy chỉnh sửa</a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách tài khoản</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Họ tên</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có người dùng.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $item): ?>
                            <tr>
                                <td><?= (int) $item['id']; ?></td>
                                <td><?= e((string) $item['username']); ?></td>
                                <td><?= e((string) $item['full_name']); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e(strtoupper((string) $item['role_name'])); ?></span></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $item['status']); ?>"><?= e((string) $item['status']); ?></span></td>
                                <td><?= e((string) $item['created_at']); ?></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <a href="/admin/users?edit=<?= (int) $item['id']; ?>">Sửa</a>
                                        <form method="post" action="/api/users/delete" onsubmit="return confirm('Bạn chắc chắn muốn khóa tài khoản này?');">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                            <button class="<?= ui_btn_danger_classes('sm'); ?>" type="submit">Khóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Role & Permission Matrix</h3>
        <p>Chọn trực tiếp quyền cho từng vai trò. Guest sử dụng quyền công khai không cần đăng nhập.</p>
        <div class="grid gap-3">
            <?php foreach ($roles as $role): ?>
                <?php $assigned = array_map('intval', $rolePermissionMap[(int) $role['id']] ?? []); ?>
                <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" <?= ((string) $role['role_name'] === 'staff') ? 'open' : ''; ?>>
                    <summary><?= strtoupper((string) $role['role_name']); ?> - <?= e((string) ($role['description'] ?? '')); ?></summary>
                    <form method="post" action="/api/roles/save-permissions">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="role_id" value="<?= (int) $role['id']; ?>">
                        <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($permissions as $permission): ?>
                                <?php $permissionId = (int) $permission['id']; ?>
                                <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs font-medium text-slate-600">
                                    <input type="checkbox" name="permission_ids[]" value="<?= $permissionId; ?>" <?= in_array($permissionId, $assigned, true) ? 'checked' : ''; ?>>
                                    <span><?= e((string) $permission['slug']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Lưu quyền cho <?= strtoupper((string) $role['role_name']); ?></button>
                    </form>
                </details>
            <?php endforeach; ?>
        </div>
    </article>
</div>



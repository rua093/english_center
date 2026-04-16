<?php
require_admin_or_staff();
require_permission('admin.user.manage');

$adminModel = new AdminModel();
$roles = $adminModel->listRoles();
$permissions = $adminModel->listPermissions();
$rolePermissionMap = $adminModel->rolePermissionMap();

$usersPage = max(1, (int) ($_GET['users_page'] ?? 1));
$usersPerPage = 10;
$usersTotal = $adminModel->countUsers();
$usersTotalPages = max(1, (int) ceil($usersTotal / $usersPerPage));
if ($usersPage > $usersTotalPages) {
    $usersPage = $usersTotalPages;
}
$users = $adminModel->listUsersPage($usersPage, $usersPerPage);

$editingUser = null;
if (!empty($_GET['edit'])) {
    $editingUser = $adminModel->findUser((int) $_GET['edit']);
}

$module = 'users';
$adminTitle = 'Quản lý người dùng & phân quyền';

$success = get_flash('success');
$error = get_flash('error');
$editingUser = $editingUser ?? null;
$isCreateMode = $editingUser === null;
$editingRoleName = strtolower((string) ($editingUser['role_name'] ?? ''));
$editingRoleProfile = is_array($editingUser['role_profile'] ?? null) ? $editingUser['role_profile'] : [];
$roleIdToName = [];
foreach ($roles as $role) {
    $roleIdToName[(int) ($role['id'] ?? 0)] = strtolower((string) ($role['role_name'] ?? ''));
}

$isEditingStaff = $editingRoleName === 'staff';
$isEditingTeacher = $editingRoleName === 'teacher';
$isEditingStudent = $editingRoleName === 'student';
?>

<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= $editingUser ? 'Cập nhật người dùng' : 'Tạo người dùng mới'; ?></h3>
        <form id="admin-user-form" class="grid gap-3 md:grid-cols-2" method="post" action="/api/users/save" autocomplete="off">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingUser['id'] ?? 0); ?>">
            <label>
                Tên đăng nhập
                <input type="text" name="username" required autocomplete="off" value="<?= e((string) ($editingUser['username'] ?? '')); ?>">
            </label>
            <label>
                Họ và tên
                <input type="text" name="full_name" required autocomplete="off" value="<?= e((string) ($editingUser['full_name'] ?? '')); ?>">
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
                <input type="text" name="phone" autocomplete="off" value="<?= e((string) ($editingUser['phone'] ?? '')); ?>">
            </label>
            <label>
                Email
                <input type="email" name="email" autocomplete="off" value="<?= e((string) ($editingUser['email'] ?? '')); ?>">
            </label>
            <label>
                Trạng thái
                <select name="status" required>
                    <option value="" <?= $isCreateMode ? 'selected' : ''; ?>>-- Chọn trạng thái --</option>
                    <option value="active" <?= (($editingUser['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?= (($editingUser['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Khóa</option>
                </select>
            </label>
            <label>
                <?= $editingUser ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu'; ?>
                <input type="password" name="password" autocomplete="new-password" <?= $editingUser ? '' : 'required'; ?>>
            </label>
            <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <h4 class="mb-1 text-sm font-extrabold text-slate-800">Thông tin bổ sung theo vai trò</h4>
                <p class="mb-3 text-xs text-slate-500" data-role-profile-empty="1">Vai trò hiện tại không có trường thông tin bổ sung.</p>

                <div class="grid gap-3 <?= $isEditingStaff ? '' : 'hidden'; ?>" data-role-profile="staff">
                    <label>
                        Chức vụ
                        <input type="text" name="staff_position" value="<?= e((string) ($editingRoleProfile['staff_position'] ?? '')); ?>" <?= $isEditingStaff ? '' : 'disabled'; ?> placeholder="Ví dụ: Academic Coordinator">
                    </label>
                    <label>
                        Hạn mức duyệt (VNĐ)
                        <input type="number" step="1000" min="0" name="staff_approval_limit" value="<?= e((string) ($editingRoleProfile['staff_approval_limit'] ?? '0')); ?>" <?= $isEditingStaff ? '' : 'disabled'; ?>>
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2 <?= $isEditingTeacher ? '' : 'hidden'; ?>" data-role-profile="teacher">
                    <label>
                        Bằng cấp
                        <input type="text" name="teacher_degree" value="<?= e((string) ($editingRoleProfile['teacher_degree'] ?? '')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?> placeholder="Ví dụ: Master of TESOL">
                    </label>
                    <label>
                        Số năm kinh nghiệm
                        <input type="number" step="1" min="0" name="teacher_experience_years" value="<?= e((string) ($editingRoleProfile['teacher_experience_years'] ?? '0')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?>>
                    </label>
                    <label class="md:col-span-2">
                        Giới thiệu
                        <textarea name="teacher_bio" rows="3" <?= $isEditingTeacher ? '' : 'disabled'; ?>><?= e((string) ($editingRoleProfile['teacher_bio'] ?? '')); ?></textarea>
                    </label>
                    <label class="md:col-span-2">
                        URL video giới thiệu
                        <input type="text" name="teacher_intro_video_url" value="<?= e((string) ($editingRoleProfile['teacher_intro_video_url'] ?? '')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?> placeholder="https://...">
                    </label>
                </div>

                <div class="grid gap-3 md:grid-cols-2 <?= $isEditingStudent ? '' : 'hidden'; ?>" data-role-profile="student">
                    <label>
                        Tên phụ huynh
                        <input type="text" name="student_parent_name" value="<?= e((string) ($editingRoleProfile['student_parent_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                    </label>
                    <label>
                        Số điện thoại phụ huynh
                        <input type="text" name="student_parent_phone" value="<?= e((string) ($editingRoleProfile['student_parent_phone'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                    </label>
                    <label>
                        Trường học / đơn vị
                        <input type="text" name="student_school_name" value="<?= e((string) ($editingRoleProfile['student_school_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                    </label>
                    <label>
                        Mục tiêu điểm
                        <input type="text" name="student_target_score" value="<?= e((string) ($editingRoleProfile['student_target_score'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?> placeholder="Ví dụ: IELTS 6.5">
                    </label>
                    <label class="md:col-span-2">
                        Entry Test ID (nếu có)
                        <input type="number" step="1" min="0" name="student_entry_test_id" value="<?= e((string) ((int) ($editingRoleProfile['student_entry_test_id'] ?? 0))); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?> placeholder="0 để bỏ trống">
                    </label>
                </div>
            </div>
            <div class="inline-flex flex-wrap items-center gap-2 md:col-span-2">
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
            <table class="min-w-full border-collapse text-sm" data-force-row-detail="1">
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
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-skip-action-icon="1"
                                            data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $item['id'], 'users_page' => $usersPage])); ?>"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </span>
                                        </button>
                                        <a
                                            href="/admin/users?edit=<?= (int) $item['id']; ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="edit"
                                            data-skip-action-icon="1"
                                            title="Sửa"
                                            aria-label="Sửa"
                                        >
                                            <span class="admin-action-icon-label">Sửa</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                            </span>
                                        </a>
                                        <form method="post" action="/api/users/delete" onsubmit="return confirm('Bạn chắc chắn muốn khóa tài khoản này?');">
                                            <?= csrf_input(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                            <button
                                                class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                data-action-kind="lock"
                                                data-skip-action-icon="1"
                                                type="submit"
                                                title="Khóa"
                                                aria-label="Khóa"
                                            >
                                                <span class="admin-action-icon-label">Khóa</span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"></rect><path d="M8 11V8a4 4 0 0 1 8 0v3"></path></svg>
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($usersTotalPages > 1): ?>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                <span>Trang <?= (int) $usersPage; ?>/<?= (int) $usersTotalPages; ?> - Tổng <?= (int) $usersTotal; ?> tài khoản</span>
                <div class="inline-flex items-center gap-1">
                    <?php if ($usersPage > 1): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage - 1])); ?>">Trước</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Trước</span>
                    <?php endif; ?>

                    <?php if ($usersPage < $usersTotalPages): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage + 1])); ?>">Sau</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Sau</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Role & Permission Matrix</h3>
        <p>Chọn trực tiếp quyền cho từng vai trò. Guest sử dụng quyền công khai không cần đăng nhập.</p>
        <div class="grid gap-3">
            <?php foreach ($roles as $role): ?>
                <?php $assigned = array_map('intval', $rolePermissionMap[(int) $role['id']] ?? []); ?>
                <details class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <summary><?= strtoupper((string) $role['role_name']); ?> - <?= e((string) ($role['description'] ?? '')); ?></summary>
                    <form method="post" action="/api/roles/save-permissions">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="role_id" value="<?= (int) $role['id']; ?>">
                        <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($permissions as $permission): ?>
                                <?php $permissionId = (int) $permission['id']; ?>
                                <label class="flex items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs font-medium text-slate-600">
                                    <input type="checkbox" name="permission_ids[]" value="<?= $permissionId; ?>" <?= in_array($permissionId, $assigned, true) ? 'checked' : ''; ?>>
                                    <span>
                                        <strong class="block text-sm text-slate-800"><?= e((string) ($permission['permission_name'] ?? $permission['slug'] ?? '')); ?></strong>
                                        <small class="block text-[11px] font-semibold text-slate-500">slug: <?= e((string) ($permission['slug'] ?? '')); ?></small>
                                    </span>
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

<script>
    (function () {
        const form = document.getElementById('admin-user-form');
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const roleSelect = form.querySelector('select[name="role_id"]');
        if (!(roleSelect instanceof HTMLSelectElement)) {
            return;
        }

        const roleIdMap = <?= json_encode($roleIdToName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const sections = Array.from(form.querySelectorAll('[data-role-profile]'));
        const emptyHint = form.querySelector('[data-role-profile-empty="1"]');

        function resolveRoleName() {
            const roleId = String(Number(roleSelect.value || 0));
            return String(roleIdMap[roleId] || '').toLowerCase();
        }

        function toggleRoleProfileFields() {
            const activeRole = resolveRoleName();
            let hasActiveSection = false;

            sections.forEach(function (section) {
                if (!(section instanceof HTMLElement)) {
                    return;
                }

                const roleName = String(section.getAttribute('data-role-profile') || '').toLowerCase();
                const isActive = activeRole !== '' && roleName === activeRole;
                hasActiveSection = hasActiveSection || isActive;

                section.classList.toggle('hidden', !isActive);
                section.querySelectorAll('input, select, textarea').forEach(function (field) {
                    if (
                        field instanceof HTMLInputElement
                        || field instanceof HTMLSelectElement
                        || field instanceof HTMLTextAreaElement
                    ) {
                        field.disabled = !isActive;
                    }
                });
            });

            if (emptyHint instanceof HTMLElement) {
                emptyHint.classList.toggle('hidden', hasActiveSection);
            }
        }

        roleSelect.addEventListener('change', toggleRoleProfileFields);
        toggleRoleProfileFields();
    })();
</script>



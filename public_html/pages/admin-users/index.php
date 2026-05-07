<?php
require_admin_or_staff();
require_any_permission(['admin.user.view']);

$canUserCreate = has_permission('admin.user.create');
$canUserUpdate = has_permission('admin.user.update');
$canUserDelete = has_permission('admin.user.delete');
$canRolePermissionView = has_permission('admin.role_permission.view');
$canRolePermissionUpdate = has_permission('admin.role_permission.update');
$canSaveUser = $canUserCreate || $canUserUpdate;

$adminModel = new AdminModel();
$roles = $adminModel->listRoles();
$permissions = $adminModel->listPermissions();
$rolePermissionMap = $adminModel->rolePermissionMap();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$roleIdFilter = (int) ($_GET['role_id'] ?? 0);
$filters = [
    'status' => $statusFilter,
    'role_id' => $roleIdFilter > 0 ? $roleIdFilter : '',
];

$usersPage = max(1, (int) ($_GET['users_page'] ?? 1));
$usersPerPage = ui_pagination_resolve_per_page('users_per_page', 10);
$usersTotal = $adminModel->countUsers($searchQuery, $filters);
$usersTotalPages = max(1, (int) ceil($usersTotal / $usersPerPage));
if ($usersPage > $usersTotalPages) {
    $usersPage = $usersTotalPages;
}
$users = $adminModel->listUsersPage($usersPage, $usersPerPage, $searchQuery, $filters);
$usersPerPageOptions = ui_pagination_per_page_options();

$editingUser = null;
if (!empty($_GET['edit'])) {
    $editingUser = $adminModel->findUser((int) $_GET['edit']);
}
if ($editingUser !== null && !$canUserUpdate) {
    $editingUser = null;
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

$permissionActionLabels=['view'=>'Xem','create'=>'Tạo','update'=>'Cập nhật','delete'=>'Xóa','manage'=>'Quản lý','request'=>'Yêu cầu','grade'=>'Chấm điểm','submit'=>'Nộp bài','other'=>'Khác'];
$permissionActionOrder=['view','create','update','delete','submit','request','grade','manage','other'];
$permissionGroupLabels=[
    'admin'=>'Quản trị hệ thống','admin.user'=>'Người dùng','admin.role_permission'=>'Phân quyền vai trò',
    'academic.assignments'=>'Bài tập','academic.classes'=>'Lớp học','academic.courses'=>'Khóa học',
    'academic.exports'=>'Xuất Excel học viên',
    'academic.portfolios'=>'Hồ sơ tiến bộ học viên','academic.roadmaps'=>'Lộ trình','academic.rooms'=>'Phòng học',
    'academic.schedules'=>'Lịch học','academic.submissions'=>'Bài nộp & Chấm điểm',
    'activity'=>'Hoạt động ngoại khóa','approval'=>'Phê duyệt','feedback'=>'Đánh giá',
    'finance.adjust'=>'Điều chỉnh tài chính','finance.payments'=>'Giao dịch thanh toán',
    'finance.promotions'=>'Ưu đãi giảm giá','finance.registration'=>'Đăng ký khóa học','finance.tuition'=>'Học phí',
    'job_application'=>'Giáo viên ứng tuyển','materials'=>'Tài liệu học tập','notifications'=>'Thông báo',
    'student'=>'Cổng học viên','student.assignment'=>'Bài tập (Học viên)','student.tuition'=>'Học phí (Học viên)',
    'student_lead'=>'Học viên đăng ký',
];

function rbac_permission_effect_text(string $action, string $groupLabel, bool $enabled): string
{
    $groupLabel = trim($groupLabel);
    if ($groupLabel === '') {
        $groupLabel = 'nhóm quyền này';
    }

    return match ($action) {
        'view' => $enabled
            ? 'Cho phép xem và mở phần ' . $groupLabel . '.'
            : 'Ẩn menu và chặn truy cập xem ' . $groupLabel . '.',
        'create' => $enabled
            ? 'Cho phép tạo mới trong ' . $groupLabel . '.'
            : 'Không thể tạo mới trong ' . $groupLabel . '.',
        'update' => $enabled
            ? 'Cho phép cập nhật dữ liệu của ' . $groupLabel . '.'
            : 'Không thể cập nhật dữ liệu của ' . $groupLabel . '.',
        'delete' => $enabled
            ? 'Cho phép xóa dữ liệu của ' . $groupLabel . '.'
            : 'Không thể xóa dữ liệu của ' . $groupLabel . '.',
        'submit' => $enabled
            ? 'Cho phép nộp dữ liệu hoặc bài làm vào ' . $groupLabel . '.'
            : 'Không thể nộp dữ liệu hoặc bài làm vào ' . $groupLabel . '.',
        'request' => $enabled
            ? 'Cho phép gửi yêu cầu liên quan đến ' . $groupLabel . '.'
            : 'Không thể gửi yêu cầu liên quan đến ' . $groupLabel . '.',
        'grade' => $enabled
            ? 'Cho phép chấm điểm hoặc ghi nhận kết quả cho ' . $groupLabel . '.'
            : 'Không thể chấm điểm hoặc ghi nhận kết quả cho ' . $groupLabel . '.',
        'manage' => $enabled
            ? 'Cho phép quản lý toàn bộ ' . $groupLabel . '.'
            : 'Không thể quản lý toàn bộ ' . $groupLabel . '.',
        default => $enabled
            ? 'Cho phép thao tác với ' . $groupLabel . '.'
            : 'Không thể thao tác với ' . $groupLabel . '.',
    };
}

$rbacRoleDetails = [];
$permissionMatrixRows=[];
foreach($permissions as $permission){
    $slug=strtolower(trim((string)($permission['slug']??'')));
    if($slug===''||str_starts_with($slug,'bank')) continue;
    $parts=explode('.',$slug);$action='other';$grp=$slug;
    $last=strtolower((string)end($parts));
    if(count($parts)>1&&isset($permissionActionLabels[$last])){$action=$last;array_pop($parts);$grp=implode('.',$parts);}
    if(!isset($permissionMatrixRows[$grp])){$permissionMatrixRows[$grp]=['label'=>$permissionGroupLabels[$grp]??ucwords(str_replace(['.','_'],['/ ',' '],$grp)),'slug'=>$grp,'actions'=>[]];}
    $permissionMatrixRows[$grp]['actions'][$action]=$permission;
}
ksort($permissionMatrixRows);

foreach ($roles as $role) {
    $roleId = (int) ($role['id'] ?? 0);
    $assignedIds = array_map('intval', $rolePermissionMap[$roleId] ?? []);
    $detailRows = [];

    foreach ($permissionMatrixRows as $groupSlug => $groupData) {
        $actions = $groupData['actions'] ?? [];
        if (!is_array($actions) || empty($actions)) {
            continue;
        }

        $rowItems = [];
        $seenActionKeys = [];
        foreach ($permissionActionOrder as $actionKey) {
            if (!isset($actions[$actionKey]) || !is_array($actions[$actionKey])) {
                continue;
            }

            $permission = $actions[$actionKey];
            $permissionId = (int) ($permission['id'] ?? 0);
            $rowItems[] = [
                'permission_id' => $permissionId,
                'permission_name' => (string) ($permission['permission_name'] ?? ''),
                'slug' => (string) ($permission['slug'] ?? ''),
                'action' => $actionKey,
                'action_label' => (string) ($permissionActionLabels[$actionKey] ?? ucfirst($actionKey)),
                'checked' => in_array($permissionId, $assignedIds, true),
                'effect_on' => rbac_permission_effect_text($actionKey, (string) ($groupData['label'] ?? ''), true),
                'effect_off' => rbac_permission_effect_text($actionKey, (string) ($groupData['label'] ?? ''), false),
            ];
            $seenActionKeys[$actionKey] = true;
        }

        $otherActions = array_diff_key($actions, array_flip(array_merge($permissionActionOrder, array_keys($seenActionKeys))));
        if (!empty($otherActions)) {
            ksort($otherActions);
            foreach ($otherActions as $actionKey => $permission) {
                if (!is_array($permission)) {
                    continue;
                }

                $permissionId = (int) ($permission['id'] ?? 0);
                $slugParts = explode('.', (string) ($permission['slug'] ?? ''));
                $actionLabel = (string) (end($slugParts) ?: 'Khác');
                if ($actionLabel === '') {
                    $actionLabel = 'Khác';
                }

                $rowItems[] = [
                    'permission_id' => $permissionId,
                    'permission_name' => (string) ($permission['permission_name'] ?? ''),
                    'slug' => (string) ($permission['slug'] ?? ''),
                    'action' => (string) $actionKey,
                    'action_label' => $actionLabel,
                    'checked' => in_array($permissionId, $assignedIds, true),
                    'effect_on' => rbac_permission_effect_text((string) $actionKey, (string) ($groupData['label'] ?? ''), true),
                    'effect_off' => rbac_permission_effect_text((string) $actionKey, (string) ($groupData['label'] ?? ''), false),
                ];
            }
        }

        if (!empty($rowItems)) {
            $detailRows[] = [
                'group_label' => (string) ($groupData['label'] ?? $groupSlug),
                'group_slug' => (string) $groupSlug,
                'items' => $rowItems,
            ];
        }
    }

    $rbacRoleDetails[$roleId] = [
        'id' => $roleId,
        'name' => (string) ($role['role_name'] ?? ''),
        'description' => (string) ($role['description'] ?? ''),
        'assigned_count' => count($assignedIds),
        'total_count' => count(array_filter($detailRows, static function (array $row): bool {
            return !empty($row['items']);
        })),
        'rows' => $detailRows,
    ];
}

$rbacRoleDetailsJson = json_encode($rbacRoleDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($rbacRoleDetailsJson)) {
    $rbacRoleDetailsJson = '{}';
}

$isEditingStaff = $editingRoleName === 'staff';
$isEditingTeacher = $editingRoleName === 'teacher';
$isEditingStudent = $editingRoleName === 'student';
?>

<div class="flex flex-col gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách tài khoản</h3>
        <div
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="users-admin"
            data-ajax-page-param="users_page"
            data-ajax-search-param="search"
        >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex w-full flex-wrap items-center gap-3">
                <input
                    data-ajax-search="1"
                    type="search"
                    value="<?= e($searchQuery); ?>"
                    placeholder="Tìm theo tên, username, mã, email, SĐT, vai trò..."
                    autocomplete="off"
                    class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <select
                    name="role_id"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Tất cả vai trò</option>
                    <?php foreach ($roles as $role): ?>
                        <?php $roleId = (int) ($role['id'] ?? 0); ?>
                        <option value="<?= $roleId; ?>" <?= $roleIdFilter === $roleId ? 'selected' : ''; ?>><?= e((string) ($role['role_name'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select
                    name="status"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : ''; ?>>Ngưng hoạt động</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-force-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Mã</th>
                        <th>Họ tên</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có người dùng.</div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $item): ?>
                            <tr>
                                <td><?= (int) $item['id']; ?></td>
                                <td><?= e((string) $item['username']); ?></td>
                                <td><?= e((string) ($item['teacher_code'] ?? $item['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($item['full_name'] ?? '')); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e(strtoupper((string) $item['role_name'])); ?></span></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $item['status']); ?>"><?= e((string) $item['status']); ?></span></td>
                                <td><?= e(ui_format_datetime((string) ($item['created_at'] ?? ''))); ?></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <button
                                            type="button"
                                            class="admin-row-detail-button admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-admin-row-detail="1"
                                            data-skip-action-icon="1"
                                            data-detail-url="<?= e(page_url('users-admin', ['edit' => (int) $item['id'], 'users_page' => $usersPage, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>"
                                            title="Xem chi tiết"
                                            aria-label="Xem chi tiết"
                                        >
                                            <span class="admin-action-icon-label">Xem chi tiết</span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </span>
                                        </button>
                                        <?php if ($canUserUpdate): ?>
                                            <a
                                                href="<?= e(page_url('users-admin', ['edit' => (int) $item['id'], 'users_page' => $usersPage, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>"
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
                                        <?php endif; ?>
                                        <?php if ($canUserDelete): ?>
                                            <form method="post" action="/api/users/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản này?');">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                                <button
                                                    class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="Xóa"
                                                    aria-label="Xóa"
                                                >
                                                    <span class="admin-action-icon-label">Xóa</span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($usersTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium">Trang <?= (int) $usersPage; ?>/<?= (int) $usersTotalPages; ?> - Tổng <?= (int) $usersTotal; ?> tài khoản</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('users-admin')); ?>">
                                <input type="hidden" name="page" value="users-admin">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="role_id" value="<?= $roleIdFilter > 0 ? (int) $roleIdFilter : ''; ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="users-per-page">Số dòng</label>
                                <select id="users-per-page" name="users_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($usersPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $usersPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($usersPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage - 1, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($usersPage < $usersTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage + 1, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </article>

    <?php if ($canRolePermissionView || $canRolePermissionUpdate): ?>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-col gap-2 border-b border-slate-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Role &amp; Permission Matrix</h3>
                <p class="text-sm text-slate-500">Tích chọn quyền theo vai trò.</p>
            </div>
            <?php if ($canRolePermissionUpdate): ?>
                <div class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Thay đổi có hiệu lực ngay sau khi lưu</div>
            <?php endif; ?>
        </div>
        <style>
        .rbac-panel{overflow:hidden;border:1px solid #dbe4f0;border-radius:1rem;background:#fff;box-shadow:inset 0 1px 0 rgba(255,255,255,.8)}
        .rbac-scroll{overflow-x:auto}
        .rbac-tbl{border-collapse:separate;border-spacing:0;width:100%;min-width:940px;font-size:12px;table-layout:fixed}
        .rbac-tbl th,.rbac-tbl td{border-bottom:1px solid #dfe7f1;vertical-align:top;border-right:1px solid #edf2f7}
        .rbac-tbl th{background:#f8fafc;padding:12px 10px;font-weight:800;font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:#64748b;text-align:center}
        .rbac-tbl th:first-child{width:198px;text-align:left;padding-left:16px}
        .rbac-tbl th:not(:first-child){width:84px}
        .rbac-tbl td{padding:12px 10px;text-align:center;background:#fff}
        .rbac-tbl td:first-child{text-align:left;padding-left:16px;background:#fff;position:sticky;left:0;z-index:1}
        .rbac-tbl tr:nth-child(even) td{background:#fbfcfe}
        .rbac-tbl tr:nth-child(even) td:first-child{background:#fbfcfe}
        .rbac-row-label{font-weight:700;color:#0f172a;font-size:13px;line-height:1.3;white-space:normal;word-break:break-word}
        .rbac-grp-slug{font-size:10px;color:#94a3b8;margin-top:4px;line-height:1.25;white-space:normal;word-break:break-word}
        .rbac-cell{display:flex;align-items:center;justify-content:center;cursor:pointer;width:100%;min-height:42px}
        .rbac-cell input{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
        .rbac-box{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:8px;border:1.5px solid #dbe3ef;background:#fff;transition:background .12s,border-color .12s,transform .12s}
        .rbac-cell:hover .rbac-box{border-color:#93c5fd;transform:translateY(-1px)}
        .rbac-cell:has(input:checked) .rbac-box{background:#2563eb;border-color:#2563eb}
        .rbac-cell:has(input:disabled){opacity:.42;cursor:not-allowed;pointer-events:none}
        .rbac-chk{color:#fff;display:none}
        .rbac-cell:has(input:checked) .rbac-chk{display:block}
        .rbac-action-empty{display:inline-flex;align-items:center;justify-content:center;min-height:26px;color:#cbd5e1;font-size:18px;line-height:1}
        .rbac-role-card{margin-bottom:10px}
        .rbac-hdr{display:flex;align-items:center;gap:10px;padding:12px 14px;cursor:pointer;user-select:none;background:#f8fafc;border-bottom:1px solid #dbe4f0}
        .rbac-badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.06em}
        .rbac-empty{color:#cbd5e1;font-size:16px;line-height:1}
        .rbac-det[open] .rbac-chev{transform:rotate(180deg)}
        .rbac-chev{transition:transform .2s}
        .rbac-row-hidden{display:none}
        .rbac-th-pill{display:inline-flex;align-items:center;justify-content:center;min-height:26px;padding:0 10px;border-radius:999px;background:#eff6ff;color:#1d4ed8;box-shadow:inset 0 0 0 1px rgba(59,130,246,.12)}
        .rbac-detail-btn{display:inline-flex;align-items:center;gap:6px;border-radius:999px;border:1px solid #dbe4f0;background:#fff;padding:6px 10px;font-size:11px;font-weight:800;color:#334155;transition:background .12s,border-color .12s,color .12s,transform .12s}
        .rbac-detail-btn:hover{border-color:#93c5fd;background:#eff6ff;color:#1d4ed8;transform:translateY(-1px)}
        .rbac-modal-backdrop{position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.55);padding:16px}
        .rbac-modal-backdrop.is-open{display:flex}
        .rbac-modal{width:min(1120px,100%);max-height:min(86vh,900px);overflow:hidden;border-radius:1.5rem;background:#fff;box-shadow:0 30px 80px rgba(15,23,42,.28)}
        .rbac-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;border-bottom:1px solid #e2e8f0;padding:18px 20px;background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%)}
        .rbac-modal-title{font-size:18px;font-weight:900;color:#0f172a;line-height:1.25}
        .rbac-modal-sub{margin-top:3px;font-size:12px;color:#64748b}
        .rbac-modal-close{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:999px;border:1px solid #dbe4f0;background:#fff;color:#334155;transition:background .12s,border-color .12s,color .12s}
        .rbac-modal-close:hover{border-color:#93c5fd;background:#eff6ff;color:#1d4ed8}
        .rbac-modal-body{padding:18px 20px;overflow:auto;max-height:calc(86vh - 76px)}
        .rbac-stat{border:1px solid #e2e8f0;border-radius:1rem;background:#fff;padding:12px 14px}
        .rbac-stat-label{font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8}
        .rbac-stat-value{margin-top:4px;font-size:20px;font-weight:900;color:#0f172a}
        .rbac-stat-note{margin-top:3px;font-size:12px;color:#64748b;line-height:1.4}
        .rbac-modal-table{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
        .rbac-modal-table th,.rbac-modal-table td{border-bottom:1px solid #e8eef7;vertical-align:top;padding:12px 10px}
        .rbac-modal-table th{font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:#64748b;background:#f8fafc;font-weight:800;text-align:left}
        .rbac-modal-table th:first-child,.rbac-modal-table td:first-child{padding-left:14px}
        .rbac-modal-table td:last-child,.rbac-modal-table th:last-child{padding-right:14px}
        .rbac-pill{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;line-height:1.2}
        .rbac-pill.is-on{background:#dcfce7;color:#166534}
        .rbac-pill.is-off{background:#f1f5f9;color:#64748b}
        .rbac-pill.is-muted{background:#eff6ff;color:#1d4ed8}
        .rbac-modal-note{font-size:12px;color:#64748b;line-height:1.5}
        </style>
        <div class="flex flex-col gap-3 rbac-panel">
        <?php
        $badgeMap=['admin'=>'bg-red-100 text-red-700','staff'=>'bg-violet-100 text-violet-700','teacher'=>'bg-blue-100 text-blue-700','student'=>'bg-emerald-100 text-emerald-700'];
        $cols=['view','create','update','delete','submit','request','grade'];
        $colLabels=['Xem','Tạo','Cập nhật','Xóa','Nộp bài','Yêu cầu','Chấm điểm'];
        ?>
        <?php foreach ($roles as $role): ?>
        <?php $rName=strtolower((string)($role['role_name']??'')); $assigned=array_map('intval',$rolePermissionMap[(int)$role['id']]??[]); $dId='rbac-'.$rName; ?>
        <details class="rbac-det rbac-role-card rounded-xl border border-slate-200 bg-white overflow-hidden" id="<?= e($dId) ?>">
            <summary class="list-none"><div class="rbac-hdr">
                <span class="rbac-badge <?= e($badgeMap[$rName]??'bg-slate-100 text-slate-600') ?>"><?= strtoupper($rName) ?></span>
        
                <span class="ml-auto text-xs text-slate-400"><?= count($assigned) ?> quyền</span>
                <?php if ($canRolePermissionView): ?>
                    <button type="button" class="rbac-detail-btn" data-rbac-open-detail="1" data-role-id="<?= (int) $role['id']; ?>">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16h.01M12 8v4m0 8a8 8 0 100-16 8 8 0 000 16z"/></svg>
                        Mô tả quyền
                    </button>
                <?php endif; ?>
                <svg class="rbac-chev w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div></summary>
            <form method="post" action="/api/roles/save-permissions">
                <?= csrf_input() ?><input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
                <div class="p-3"><input type="search" placeholder="Tìm nhóm quyền..." autocomplete="off"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-200"
                    data-rbac-search="<?= e($dId) ?>"></div>
                <div class="rbac-scroll">
                    <table class="rbac-tbl" data-rbac-tbl="<?= e($dId) ?>">
                    <thead><tr><th class="text-left">Nhóm quyền</th>
                    <?php foreach ($colLabels as $cl): ?><th><span class="rbac-th-pill"><?= e($cl) ?></span></th><?php endforeach ?>
                    <th><span class="rbac-th-pill">Khác</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($permissionMatrixRows as $gSlug => $grp): ?>
                    <?php if (empty($grp['actions'])) continue; ?>
                    <tr class="rbac-row">
                        <td><div class="rbac-row-label"><?= e($grp['label']) ?></div><div class="rbac-grp-slug"><?= e($gSlug) ?></div></td>
                        <?php foreach ($cols as $ck): ?><td><?php $p=$grp['actions'][$ck]??null; if(is_array($p)):$pid=(int)($p['id']??0); ?>
                            <label class="rbac-cell" title="<?= e($p['permission_name']??$p['slug']??'') ?>">
                                <input type="checkbox" name="permission_ids[]" value="<?= $pid ?>" <?= in_array($pid,$assigned,true)?'checked':'' ?> <?= $canRolePermissionUpdate?'':'disabled' ?>>
                                <span class="rbac-box"><svg class="rbac-chk w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                            </label>
                        <?php else: ?><span class="rbac-action-empty" aria-hidden="true">&middot;</span><?php endif ?></td><?php endforeach ?>
                        <td><?php $others=array_diff_key($grp['actions'],array_flip($cols));
                            foreach($others as $op):if(!is_array($op))continue;$pid=(int)($op['id']??0);$osl=(string)($op['slug']??''); ?>
                            <label class="rbac-cell" title="<?= e($op['permission_name']??$osl) ?>">
                                <input type="checkbox" name="permission_ids[]" value="<?= $pid ?>" <?= in_array($pid,$assigned,true)?'checked':'' ?> <?= $canRolePermissionUpdate?'':'disabled' ?>>
                                <span class="rbac-box"><svg class="rbac-chk w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                            </label>
                            <?php endforeach;if(empty($others)):?><span class="rbac-action-empty" aria-hidden="true">&middot;</span><?php endif ?></td>
                    </tr>
                    <?php endforeach ?>
                    </tbody></table></div>
                <?php if ($canRolePermissionUpdate): ?>
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3">
                    <span class="text-xs text-slate-500">Thay đổi có hiệu lực ngay sau khi lưu.</span>
                    <button class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-all" type="submit">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Lưu <?= strtoupper($rName) ?>
                    </button>
                </div>
                <?php endif ?>
            </form>
        </details>
        <?php endforeach ?>
        </div>
    </article>
    <div id="rbac-detail-modal" class="rbac-modal-backdrop" aria-hidden="true">
        <div class="rbac-modal" role="dialog" aria-modal="true" aria-labelledby="rbac-detail-modal-title">
            <div class="rbac-modal-head">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-[0.2em] text-blue-600">Mô tả quyền</div>
                    <div id="rbac-detail-modal-title" class="rbac-modal-title">Vai trò và quyền hạn</div>
                    <div id="rbac-detail-modal-subtitle" class="rbac-modal-sub"></div>
                </div>
                <button type="button" class="rbac-modal-close" data-rbac-close-detail="1" aria-label="Đóng">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rbac-modal-body">
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rbac-stat">
                        <div class="rbac-stat-label">Quyền đang bật</div>
                        <div id="rbac-detail-enabled-count" class="rbac-stat-value">0</div>
                        <div class="rbac-stat-note">Số quyền vai trò này hiện có thể sử dụng.</div>
                    </div>
                    <div class="rbac-stat">
                        <div class="rbac-stat-label">Tổng quyền hiển thị</div>
                        <div id="rbac-detail-total-count" class="rbac-stat-value">0</div>
                        <div class="rbac-stat-note">Tổng số quyền đang được mô tả trong matrix.</div>
                    </div>
                    <div class="rbac-stat">
                        <div class="rbac-stat-label">Ý nghĩa chung</div>
                        <div class="rbac-stat-note">Bật quyền = cho phép thao tác. Tắt quyền = ẩn menu hoặc chặn hành động tương ứng.</div>
                    </div>
                </div>

                <p class="mt-4 rbac-modal-note">Bảng dưới đây mô tả từng quyền: nó làm gì, nếu bật thì có tác dụng ra sao, và nếu tắt thì hệ thống sẽ chặn hoặc ẩn gì.</p>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                    <table class="rbac-modal-table min-w-full">
                        <thead>
                            <tr>
                                <th>Nhóm quyền</th>
                                <th>Quyền</th>
                                <th>Hiện tại</th>
                                <th>Bật lên</th>
                                <th>Tắt đi</th>
                            </tr>
                        </thead>
                        <tbody id="rbac-detail-modal-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif ?>
</div>

<?php if ($canSaveUser): ?>
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
            <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" autocomplete="off" value="<?= e((string) ($editingUser['phone'] ?? '')); ?>">
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
                    <input type="text" name="staff_position" value="<?= e((string) ($editingRoleProfile['staff_position'] ?? '')); ?>" <?= $isEditingStaff ? '' : 'disabled'; ?> placeholder="Ví dụ: Điều phối học vụ">
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-2 <?= $isEditingTeacher ? '' : 'hidden'; ?>" data-role-profile="teacher">
                <label>
                    Bằng cấp
                    <input type="text" name="teacher_degree" value="<?= e((string) ($editingRoleProfile['teacher_degree'] ?? '')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?> placeholder="Ví dụ: Thạc sĩ TESOL">
                </label>
                <label>
                    Số năm kinh nghiệm
                    <input type="number" step="1" min="0" name="teacher_experience_years" value="<?= e((string) ($editingRoleProfile['teacher_experience_years'] ?? '0')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?>>
                </label>
                <label class="md:col-span-2">
                    Giới thiệu
                    <textarea name="teacher_bio" rows="3" <?= $isEditingTeacher ? '' : 'disabled'; ?>><?= e((string) ($editingRoleProfile['teacher_bio'] ?? '')); ?></textarea>
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-2 <?= $isEditingStudent ? '' : 'hidden'; ?>" data-role-profile="student">
                <label>
                    Tên phụ huynh
                    <input type="text" name="student_parent_name" value="<?= e((string) ($editingRoleProfile['student_parent_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    Số điện thoại phụ huynh
                    <input type="tel" inputmode="numeric" pattern="[0-9]*" name="student_parent_phone" value="<?= e((string) ($editingRoleProfile['student_parent_phone'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    Trường học / đơn vị
                    <input type="text" name="student_school_name" value="<?= e((string) ($editingRoleProfile['student_school_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    Mục tiêu điểm
                    <input type="text" name="student_target_score" value="<?= e((string) ($editingRoleProfile['student_target_score'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?> placeholder="Ví dụ: IELTS 6.5">
                </label>
            </div>
        </div>
        <div class="inline-flex flex-wrap items-center gap-2 md:col-span-2">
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu người dùng</button>
            <?php if ($editingUser): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('users-admin')); ?>">Hủy chỉnh sửa</a>
            <?php endif; ?>
        </div>
    </form>
</article>
<?php endif; ?>


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

    (function () {
        const roleData = <?= $rbacRoleDetailsJson; ?>;
        const modal = document.getElementById('rbac-detail-modal');
        const modalTitle = document.getElementById('rbac-detail-modal-title');
        const modalSubtitle = document.getElementById('rbac-detail-modal-subtitle');
        const modalEnabledCount = document.getElementById('rbac-detail-enabled-count');
        const modalTotalCount = document.getElementById('rbac-detail-total-count');
        const modalBody = document.getElementById('rbac-detail-modal-body');
        const closeButtons = Array.from(document.querySelectorAll('[data-rbac-close-detail="1"]'));
        const openButtons = Array.from(document.querySelectorAll('[data-rbac-open-detail="1"]'));

        if (!(modal instanceof HTMLElement) || !(modalTitle instanceof HTMLElement) || !(modalSubtitle instanceof HTMLElement) || !(modalEnabledCount instanceof HTMLElement) || !(modalTotalCount instanceof HTMLElement) || !(modalBody instanceof HTMLElement)) {
            return;
        }

        let lastFocusElement = null;

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        function renderRoleDetail(roleId) {
            const payload = roleData[String(roleId)] || roleData[roleId] || null;
            const rows = Array.isArray(payload && payload.rows) ? payload.rows : [];
            const roleName = String(payload && payload.name ? payload.name : '');
            const roleDescription = String(payload && payload.description ? payload.description : '');
            let enabledCount = 0;
            let totalCount = 0;
            const html = [];

            rows.forEach(function (row) {
                const items = Array.isArray(row && row.items) ? row.items : [];
                items.forEach(function (item) {
                    totalCount++;
                    if (item && item.checked) {
                        enabledCount++;
                    }

                    const isEnabled = !!(item && item.checked);
                    html.push(
                        '<tr>' +
                            '<td><div class="font-bold text-slate-900">' + escapeHtml(String(row && row.group_label ? row.group_label : '')) + '</div><div class="mt-1 text-[11px] text-slate-400">' + escapeHtml(String(row && row.group_slug ? row.group_slug : '')) + '</div></td>' +
                            '<td><div class="font-semibold text-slate-800">' + escapeHtml(String(item && item.action_label ? item.action_label : '')) + '</div><div class="mt-1 text-[11px] text-slate-400">' + escapeHtml(String(item && item.permission_name ? item.permission_name : item && item.slug ? item.slug : '')) + '</div></td>' +
                            '<td><span class="rbac-pill ' + (isEnabled ? 'is-on' : 'is-off') + '">' + (isEnabled ? 'Bật' : 'Tắt') + '</span></td>' +
                            '<td class="text-slate-600">' + escapeHtml(String(item && item.effect_on ? item.effect_on : '')) + '</td>' +
                            '<td class="text-slate-600">' + escapeHtml(String(item && item.effect_off ? item.effect_off : '')) + '</td>' +
                        '</tr>'
                    );
                });
            });

            modalTitle.textContent = roleName !== '' ? roleName.toUpperCase() + ' - Mô tả quyền' : 'Mô tả quyền';
            modalSubtitle.textContent = roleDescription !== '' ? roleDescription : 'Danh sách quyền và tác động khi bật hoặc tắt.';
            modalEnabledCount.textContent = String(enabledCount);
            modalTotalCount.textContent = String(totalCount);
            modalBody.innerHTML = html.length > 0 ? html.join('') : '<tr><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Không có quyền nào để mô tả.</td></tr>';
        }

        function openModal(roleId, triggerElement) {
            lastFocusElement = triggerElement instanceof HTMLElement ? triggerElement : null;
            renderRoleDetail(roleId);
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            const closeButton = modal.querySelector('[data-rbac-close-detail="1"]');
            if (closeButton instanceof HTMLElement) {
                closeButton.focus();
            }
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            if (lastFocusElement instanceof HTMLElement) {
                lastFocusElement.focus();
            }
        }

        openButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                openModal(button.getAttribute('data-role-id'), button);
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                closeModal();
            });
        });

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    })();
</script>



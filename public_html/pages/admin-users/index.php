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
$adminTitle = t('admin.users.title');
$statusLabelMap = [
    'active' => t('admin.users.status_active'),
    'inactive' => t('admin.users.status_inactive'),
];

$success = get_flash('success');
$error = get_flash('error');
$editingUser = $editingUser ?? null;
$isCreateMode = $editingUser === null;
$editingRoleName = strtolower((string) ($editingUser['role_name'] ?? ''));
$editingRoleProfile = is_array($editingUser['role_profile'] ?? null) ? $editingUser['role_profile'] : [];
$editingStudentEnrollments = is_array($editingRoleProfile['student_class_enrollments'] ?? null) ? $editingRoleProfile['student_class_enrollments'] : [];
$roleIdToName = [];
foreach ($roles as $role) {
    $roleIdToName[(int) ($role['id'] ?? 0)] = strtolower((string) ($role['role_name'] ?? ''));
}

$permissionActionLabels=[
    'view' => t('admin.rbac.action.view'),
    'create' => t('admin.rbac.action.create'),
    'update' => t('admin.rbac.action.update'),
    'delete' => t('admin.rbac.action.delete'),
    'manage' => t('admin.rbac.action.manage'),
    'request' => t('admin.rbac.action.request'),
    'grade' => t('admin.rbac.action.grade'),
    'submit' => t('admin.rbac.action.submit'),
    'other' => t('admin.rbac.action.other'),
];
$permissionActionOrder=['view','create','update','delete','submit','request','grade','manage','other'];
$permissionGroupLabels=[
    'admin' => t('admin.rbac.group.admin'),
    'admin.user' => t('admin.rbac.group.admin_user'),
    'admin.role_permission' => t('admin.rbac.group.admin_role_permission'),
    'academic.assignments' => t('admin.rbac.group.academic_assignments'),
    'academic.classes' => t('admin.rbac.group.academic_classes'),
    'academic.courses' => t('admin.rbac.group.academic_courses'),
    'academic.exports' => t('admin.rbac.group.academic_exports'),
    'academic.portfolios' => t('admin.rbac.group.academic_portfolios'),
    'academic.roadmaps' => t('admin.rbac.group.academic_roadmaps'),
    'academic.rooms' => t('admin.rbac.group.academic_rooms'),
    'academic.schedules' => t('admin.rbac.group.academic_schedules'),
    'academic.submissions' => t('admin.rbac.group.academic_submissions'),
    'activity' => t('admin.rbac.group.activity'),
    'approval' => t('admin.rbac.group.approval'),
    'feedback' => t('admin.rbac.group.feedback'),
    'finance.adjust' => t('admin.rbac.group.finance_adjust'),
    'finance.payments' => t('admin.rbac.group.finance_payments'),
    'finance.promotions' => t('admin.rbac.group.finance_promotions'),
    'finance.registration' => t('admin.rbac.group.finance_registration'),
    'finance.tuition' => t('admin.rbac.group.finance_tuition'),
    'job_application' => t('admin.rbac.group.job_application'),
    'materials' => t('admin.rbac.group.materials'),
    'notifications' => t('admin.rbac.group.notifications'),
    'student' => t('admin.rbac.group.student'),
    'student.assignment' => t('admin.rbac.group.student_assignment'),
    'student.tuition' => t('admin.rbac.group.student_tuition'),
    'student_lead' => t('admin.rbac.group.student_lead'),
];

function rbac_permission_effect_text(string $action, string $groupLabel, bool $enabled): string
{
    $groupLabel = trim($groupLabel);
    return match ($action) {
        'view' => $enabled
            ? t('admin.rbac.effect.view_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.view_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'create' => $enabled
            ? t('admin.rbac.effect.create_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.create_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'update' => $enabled
            ? t('admin.rbac.effect.update_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.update_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'delete' => $enabled
            ? t('admin.rbac.effect.delete_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.delete_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'submit' => $enabled
            ? t('admin.rbac.effect.submit_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.submit_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'request' => $enabled
            ? t('admin.rbac.effect.request_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.request_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'grade' => $enabled
            ? t('admin.rbac.effect.grade_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.grade_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        'manage' => $enabled
            ? t('admin.rbac.effect.manage_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.manage_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
        default => $enabled
            ? t('admin.rbac.effect.other_on', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')])
            : t('admin.rbac.effect.other_off', ['group' => $groupLabel ?: t('admin.rbac.group_fallback')]),
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
                $actionLabel = (string) (end($slugParts) ?: t('admin.rbac.action.other'));
                if ($actionLabel === '') {
                    $actionLabel = t('admin.rbac.action.other');
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
        <h3><?= e(t('admin.users.list_title')); ?></h3>
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
                    placeholder="<?= e(t('admin.users.search_placeholder')); ?>"
                    autocomplete="off"
                    class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                <select
                    name="role_id"
                    data-ajax-filter="1"
                    class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
                    <option value=""><?= e(t('admin.users.role_filter_all')); ?></option>
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
                    <option value=""><?= e(t('admin.users.status_filter_all')); ?></option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : ''; ?>><?= e(t('admin.users.status_active')); ?></option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : ''; ?>><?= e(t('admin.users.status_inactive')); ?></option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-force-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.users.table_id')); ?></th>
                        <th><?= e(t('admin.users.table_username')); ?></th>
                        <th><?= e(t('admin.users.table_code')); ?></th>
                        <th><?= e(t('admin.users.table_name')); ?></th>
                        <th><?= e(t('admin.users.table_role')); ?></th>
                        <th><?= e(t('admin.users.table_status')); ?></th>
                        <th><?= e(t('admin.users.table_created')); ?></th>
                        <th><?= e(t('admin.users.table_actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="8"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.users.empty')); ?></div></td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $item): ?>
                            <tr>
                                <td><?= (int) $item['id']; ?></td>
                                <td><?= e((string) $item['username']); ?></td>
                                <td><?= e((string) ($item['teacher_code'] ?? $item['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($item['full_name'] ?? '')); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize border-blue-200 bg-blue-50 text-blue-700"><?= e(strtoupper((string) $item['role_name'])); ?></span></td>
                                <?php $statusValue = (string) ($item['status'] ?? ''); ?>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e($statusValue); ?>"><?= e($statusLabelMap[$statusValue] ?? $statusValue); ?></span></td>
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
                                            title="<?= e(t('admin.common.view_detail')); ?>"
                                            aria-label="<?= e(t('admin.common.view_detail')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.common.view_detail')); ?></span>
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
                                                title="<?= e(t('admin.common.edit')); ?>"
                                                aria-label="<?= e(t('admin.common.edit')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.edit')); ?></span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canUserDelete): ?>
                                            <form method="post" action="/api/users/delete" onsubmit="return confirm(<?= json_encode(t('admin.users.delete_confirm'), JSON_UNESCAPED_UNICODE); ?>);">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                                <button
                                                    class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                    data-action-kind="delete"
                                                    data-skip-action-icon="1"
                                                    type="submit"
                                                    title="<?= e(t('admin.common.delete')); ?>"
                                                    aria-label="<?= e(t('admin.common.delete')); ?>"
                                                >
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
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
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.users.page_info', ['current' => (int) $usersPage, 'total' => (int) $usersTotalPages, 'count' => (int) $usersTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('users-admin')); ?>">
                                <input type="hidden" name="page" value="users-admin">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="role_id" value="<?= $roleIdFilter > 0 ? (int) $roleIdFilter : ''; ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="users-per-page"><?= e(t('admin.users.rows_label')); ?></label>
                                <select id="users-per-page" name="users_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($usersPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $usersPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($usersPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage - 1, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($usersPage < $usersTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('users-admin', ['users_page' => $usersPage + 1, 'users_per_page' => $usersPerPage, 'search' => $searchQuery, 'status' => $statusFilter, 'role_id' => $roleIdFilter > 0 ? $roleIdFilter : null])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
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
                <h3 class="text-base font-extrabold text-slate-900"><?= e(t('admin.rbac.title')); ?></h3>
                <p class="text-sm text-slate-500"><?= e(t('admin.rbac.subtitle')); ?></p>
            </div>
            <?php if ($canRolePermissionUpdate): ?>
                <div class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"><?= e(t('admin.rbac.changes_apply')); ?></div>
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
        $colLabels=[
            t('admin.rbac.action.view'),
            t('admin.rbac.action.create'),
            t('admin.rbac.action.update'),
            t('admin.rbac.action.delete'),
            t('admin.rbac.action.submit'),
            t('admin.rbac.action.request'),
            t('admin.rbac.action.grade'),
        ];
        ?>
        <?php foreach ($roles as $role): ?>
        <?php $rName=strtolower((string)($role['role_name']??'')); $assigned=array_map('intval',$rolePermissionMap[(int)$role['id']]??[]); $dId='rbac-'.$rName; ?>
        <details class="rbac-det rbac-role-card rounded-xl border border-slate-200 bg-white overflow-hidden" id="<?= e($dId) ?>">
            <summary class="list-none"><div class="rbac-hdr">
                <span class="rbac-badge <?= e($badgeMap[$rName]??'bg-slate-100 text-slate-600') ?>"><?= strtoupper($rName) ?></span>
        
                <span class="ml-auto text-xs text-slate-400"><?= e(t('admin.rbac.role_badge_count', ['count' => count($assigned)])); ?></span>
                <?php if ($canRolePermissionView): ?>
                    <button type="button" class="rbac-detail-btn" data-rbac-open-detail="1" data-role-id="<?= (int) $role['id']; ?>">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16h.01M12 8v4m0 8a8 8 0 100-16 8 8 0 000 16z"/></svg>
                        <?= e(t('admin.rbac.detail_button')); ?>
                    </button>
                <?php endif; ?>
                <svg class="rbac-chev w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </div></summary>
            <form method="post" action="/api/roles/save-permissions">
                <?= csrf_input() ?><input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
                <div class="p-3"><input type="search" placeholder="<?= e(t('admin.rbac.search_placeholder')); ?>" autocomplete="off"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-200"
                    data-rbac-search="<?= e($dId) ?>"></div>
                <div class="rbac-scroll">
                    <table class="rbac-tbl" data-rbac-tbl="<?= e($dId) ?>">
                    <thead><tr><th class="text-left"><?= e(t('admin.rbac.table_group')); ?></th>
                    <?php foreach ($colLabels as $cl): ?><th><span class="rbac-th-pill"><?= e($cl) ?></span></th><?php endforeach ?>
                    <th><span class="rbac-th-pill"><?= e(t('admin.rbac.action.other')); ?></span></th></tr></thead>
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
                    <span class="text-xs text-slate-500"><?= e(t('admin.rbac.changes_apply')); ?></span>
                    <button class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition-all" type="submit">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <?= e(t('admin.rbac.save_role', ['role' => strtoupper($rName)])); ?>
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
                    <div class="text-[11px] font-black uppercase tracking-[0.2em] text-blue-600"><?= e(t('admin.rbac.modal_kicker')); ?></div>
                    <div id="rbac-detail-modal-title" class="rbac-modal-title"><?= e(t('admin.rbac.modal_title')); ?></div>
                    <div id="rbac-detail-modal-subtitle" class="rbac-modal-sub"></div>
                </div>
                <button type="button" class="rbac-modal-close" data-rbac-close-detail="1" aria-label="<?= e(t('admin.rbac.close')); ?>">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rbac-modal-body">
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rbac-stat">
                        <div class="rbac-stat-label"><?= e(t('admin.rbac.stats_enabled')); ?></div>
                        <div id="rbac-detail-enabled-count" class="rbac-stat-value">0</div>
                        <div class="rbac-stat-note"><?= e(t('admin.rbac.stats_enabled_note')); ?></div>
                    </div>
                    <div class="rbac-stat">
                        <div class="rbac-stat-label"><?= e(t('admin.rbac.stats_total')); ?></div>
                        <div id="rbac-detail-total-count" class="rbac-stat-value">0</div>
                        <div class="rbac-stat-note"><?= e(t('admin.rbac.stats_total_note')); ?></div>
                    </div>
                    <div class="rbac-stat">
                        <div class="rbac-stat-label"><?= e(t('admin.rbac.stats_meaning')); ?></div>
                        <div class="rbac-stat-note"><?= e(t('admin.rbac.stats_meaning_note')); ?></div>
                    </div>
                </div>

                <p class="mt-4 rbac-modal-note"><?= e(t('admin.rbac.modal_table_note')); ?></p>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                    <table class="rbac-modal-table min-w-full">
                        <thead>
                            <tr>
                                <th><?= e(t('admin.rbac.table_group')); ?></th>
                                <th><?= e(t('admin.rbac.table_permission')); ?></th>
                                <th><?= e(t('admin.rbac.table_current')); ?></th>
                                <th><?= e(t('admin.rbac.table_on')); ?></th>
                                <th><?= e(t('admin.rbac.table_off')); ?></th>
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
    <h3><?= e($editingUser ? t('admin.users.update_title') : t('admin.users.create_title')); ?></h3>
    <form id="admin-user-form" class="grid gap-3 md:grid-cols-2" method="post" action="/api/users/save" autocomplete="off">
        <?= csrf_input(); ?>
        <input type="hidden" name="id" value="<?= (int) ($editingUser['id'] ?? 0); ?>">
        <label>
            <?= e(t('admin.users.username')); ?>
            <input type="text" name="username" required autocomplete="off" value="<?= e((string) ($editingUser['username'] ?? '')); ?>">
        </label>
        <label>
            <?= e(t('admin.users.full_name')); ?>
            <input type="text" name="full_name" required autocomplete="off" value="<?= e((string) ($editingUser['full_name'] ?? '')); ?>">
        </label>
        <label>
            <?= e(t('admin.users.role')); ?>
            <select name="role_id" required>
                <option value=""><?= e(t('admin.users.role_placeholder')); ?></option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= (int) $role['id']; ?>" <?= (int) ($editingUser['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : ''; ?>>
                        <?= strtoupper((string) $role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <?= e(t('admin.users.phone')); ?>
            <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" autocomplete="off" value="<?= e((string) ($editingUser['phone'] ?? '')); ?>">
        </label>
        <label>
            <?= e(t('admin.users.email')); ?>
            <input type="email" name="email" autocomplete="off" value="<?= e((string) ($editingUser['email'] ?? '')); ?>">
        </label>
        <label>
            <?= e(t('admin.users.status')); ?>
            <select name="status" required>
                <option value="" <?= $isCreateMode ? 'selected' : ''; ?>><?= e(t('admin.users.status_placeholder')); ?></option>
                <option value="active" <?= (($editingUser['status'] ?? '') === 'active') ? 'selected' : ''; ?>><?= e(t('admin.users.status_active')); ?></option>
                <option value="inactive" <?= (($editingUser['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>><?= e(t('admin.users.status_inactive')); ?></option>
            </select>
        </label>
        <label>
            <?= e($editingUser ? t('admin.users.password_new_hint') : t('admin.users.password')); ?>
            <input type="password" name="password" autocomplete="new-password" <?= $editingUser ? '' : 'required'; ?>>
        </label>
        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <h4 class="mb-1 text-sm font-extrabold text-slate-800"><?= e(t('admin.users.extra_info_title')); ?></h4>
            <p class="mb-3 text-xs text-slate-500" data-role-profile-empty="1"><?= e(t('admin.users.extra_info_empty')); ?></p>
            <div class="grid gap-3 <?= $isEditingStaff ? '' : 'hidden'; ?>" data-role-profile="staff">
                <label>
                    <?= e(t('admin.users.staff_position')); ?>
                    <input type="text" name="staff_position" value="<?= e((string) ($editingRoleProfile['staff_position'] ?? '')); ?>" <?= $isEditingStaff ? '' : 'disabled'; ?> placeholder="<?= e(t('admin.users.staff_position_placeholder')); ?>">
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-2 <?= $isEditingTeacher ? '' : 'hidden'; ?>" data-role-profile="teacher">
                <label>
                    <?= e(t('admin.users.teacher_degree')); ?>
                    <input type="text" name="teacher_degree" value="<?= e((string) ($editingRoleProfile['teacher_degree'] ?? '')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?> placeholder="<?= e(t('admin.users.teacher_degree_placeholder')); ?>">
                </label>
                <label>
                    <?= e(t('admin.users.teacher_experience')); ?>
                    <input type="number" step="1" min="0" name="teacher_experience_years" value="<?= e((string) ($editingRoleProfile['teacher_experience_years'] ?? '0')); ?>" <?= $isEditingTeacher ? '' : 'disabled'; ?>>
                </label>
                <label class="md:col-span-2">
                    <?= e(t('admin.users.teacher_bio')); ?>
                    <textarea name="teacher_bio" rows="3" <?= $isEditingTeacher ? '' : 'disabled'; ?>><?= e((string) ($editingRoleProfile['teacher_bio'] ?? '')); ?></textarea>
                </label>
            </div>
            <div class="grid gap-3 md:grid-cols-2 <?= $isEditingStudent ? '' : 'hidden'; ?>" data-role-profile="student">
                <label>
                    <?= e(t('admin.users.student_father_name')); ?>
                    <input type="text" name="student_father_name" value="<?= e((string) ($editingRoleProfile['student_father_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_father_phone')); ?>
                    <input type="tel" inputmode="numeric" pattern="[0-9]*" name="student_father_phone" value="<?= e((string) ($editingRoleProfile['student_father_phone'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_father_id_card')); ?>
                    <input type="text" name="student_father_id_card" value="<?= e((string) ($editingRoleProfile['student_father_id_card'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_mother_name')); ?>
                    <input type="text" name="student_mother_name" value="<?= e((string) ($editingRoleProfile['student_mother_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_mother_phone')); ?>
                    <input type="tel" inputmode="numeric" pattern="[0-9]*" name="student_mother_phone" value="<?= e((string) ($editingRoleProfile['student_mother_phone'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_mother_id_card')); ?>
                    <input type="text" name="student_mother_id_card" value="<?= e((string) ($editingRoleProfile['student_mother_id_card'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_school_name')); ?>
                    <input type="text" name="student_school_name" value="<?= e((string) ($editingRoleProfile['student_school_name'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?>>
                </label>
                <label>
                    <?= e(t('admin.users.student_target_score')); ?>
                    <input type="text" name="student_target_score" value="<?= e((string) ($editingRoleProfile['student_target_score'] ?? '')); ?>" <?= $isEditingStudent ? '' : 'disabled'; ?> placeholder="<?= e(t('admin.users.student_target_score_placeholder')); ?>">
                </label>
                <label class="md:col-span-2">
                    <?= e(t('admin.users.student_parent_social_links')); ?>
                    <textarea name="student_parent_social_links" rows="3" <?= $isEditingStudent ? '' : 'disabled'; ?> placeholder='<?= e(t('admin.users.student_parent_social_links_placeholder')); ?>'><?= e((string) ($editingRoleProfile['student_parent_social_links'] ?? '')); ?></textarea>
                </label>
                <div class="md:col-span-2 rounded-xl border border-slate-200 bg-white p-4">
                    <h5 class="text-sm font-extrabold text-slate-800"><?= e(t('admin.users.student_enrollments_title')); ?></h5>
                    <p class="mt-1 text-xs text-slate-500"><?= e(t('admin.users.student_enrollments_copy')); ?></p>
                    <?php if (empty($editingStudentEnrollments)): ?>
                        <div class="mt-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-500"><?= e(t('admin.users.student_enrollments_empty')); ?></div>
                    <?php else: ?>
                        <div class="mt-3 overflow-hidden rounded-lg border border-slate-200">
                            <table class="min-w-full border-collapse text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-bold text-slate-600"><?= e(t('admin.users.student_enrollments_table_class')); ?></th>
                                        <th class="px-3 py-2 text-left font-bold text-slate-600"><?= e(t('admin.users.student_enrollments_table_course')); ?></th>
                                        <th class="px-3 py-2 text-left font-bold text-slate-600"><?= e(t('admin.users.student_enrollments_table_date')); ?></th>
                                        <th class="px-3 py-2 text-left font-bold text-slate-600"><?= e(t('admin.users.student_enrollments_table_status')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($editingStudentEnrollments as $enrollment): ?>
                                        <tr class="border-t border-slate-200">
                                            <td class="px-3 py-2 text-slate-700"><?= e((string) ($enrollment['class_name'] ?? '')); ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= e((string) ($enrollment['course_name'] ?? '')); ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= e(ui_format_date((string) ($enrollment['enrollment_date'] ?? ''))); ?></td>
                                            <td class="px-3 py-2 text-slate-600"><?= e((string) ($enrollment['class_status'] ?? '')); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="inline-flex flex-wrap items-center gap-2 md:col-span-2">
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.users.save')); ?></button>
            <?php if ($editingUser): ?>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('users-admin')); ?>"><?= e(t('admin.users.cancel_edit')); ?></a>
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
        const rbacI18n = <?php echo json_encode([
            'status_on' => t('admin.rbac.status_on'),
            'status_off' => t('admin.rbac.status_off'),
            'modal_title' => t('admin.rbac.modal_title'),
            'modal_title_suffix' => t('admin.rbac.modal_title_suffix'),
            'modal_subtitle_default' => t('admin.rbac.modal_subtitle_default'),
            'empty' => t('admin.rbac.empty'),
        ], JSON_UNESCAPED_UNICODE); ?>;
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
                            '<td><span class="rbac-pill ' + (isEnabled ? 'is-on' : 'is-off') + '">' + (isEnabled ? rbacI18n.status_on : rbacI18n.status_off) + '</span></td>' +
                            '<td class="text-slate-600">' + escapeHtml(String(item && item.effect_on ? item.effect_on : '')) + '</td>' +
                            '<td class="text-slate-600">' + escapeHtml(String(item && item.effect_off ? item.effect_off : '')) + '</td>' +
                        '</tr>'
                    );
                });
            });

            modalTitle.textContent = roleName !== ''
                ? roleName.toUpperCase() + rbacI18n.modal_title_suffix
                : rbacI18n.modal_title;
            modalSubtitle.textContent = roleDescription !== '' ? roleDescription : rbacI18n.modal_subtitle_default;
            modalEnabledCount.textContent = String(enabledCount);
            modalTotalCount.textContent = String(totalCount);
            modalBody.innerHTML = html.length > 0
                ? html.join('')
                : '<tr><td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">' + rbacI18n.empty + '</td></tr>';
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



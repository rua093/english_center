<?php
require_admin_or_staff();
require_any_permission(['approval.view']);

$academicModel = new AcademicModel();
$approvalPage = max(1, (int) ($_GET['approval_page'] ?? 1));
$approvalPerPage = ui_pagination_resolve_per_page('approval_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$statusFilter = strtolower(trim((string) ($_GET['status'] ?? '')));
$typeFilter = strtolower(trim((string) ($_GET['type'] ?? '')));
$approvalFilters = ['status' => $statusFilter, 'type' => $typeFilter];
$approvalTotal = $academicModel->countApprovals($searchQuery, $approvalFilters);
$approvalTotalPages = max(1, (int) ceil($approvalTotal / $approvalPerPage));
if ($approvalPage > $approvalTotalPages) {
    $approvalPage = $approvalTotalPages;
}
$approvals = $academicModel->listApprovalsPage($approvalPage, $approvalPerPage, $searchQuery, $approvalFilters);
$approvalPerPageOptions = ui_pagination_per_page_options();

$editingApproval = null;
if (!empty($_GET['edit'])) {
    $editingApproval = $academicModel->findApproval((int) $_GET['edit']);
}

$module = 'approvals';
$adminTitle = 'Hệ thống phê duyệt';

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$staffEditableTypes = ['schedule_change', 'teacher_leave'];

$canCreateApproval = $isAdmin;
$canUpdateApproval = $isAdmin || has_any_permission(['approval.update']);
$canDeleteApproval = $isAdmin;

$approvalTypeOptions = [
    'schedule_change'  => 'Thay đổi lịch học',
    'teacher_leave'    => 'Giáo viên xin nghỉ',
    'finance_adjust'   => 'Điều chỉnh tài chính',
    'tuition_discount' => 'Miễn giảm học phí',
    'tuition_delete'   => 'Hủy hóa đơn học phí',
    'other'            => 'Lý do khác',
];
$approvalType = 'schedule_change';
$approvalContentValue = '';
if (is_array($editingApproval)) {
    $approvalType = (string) ($editingApproval['type'] ?? 'schedule_change');
    $approvalContentValue = (string) ($editingApproval['content'] ?? '');

    $decodedEditingContent = json_decode($approvalContentValue, true);
    if (is_array($decodedEditingContent)) {
        $decodedAction = strtolower(trim((string) ($decodedEditingContent['action'] ?? '')));
        if ($decodedAction !== '') {
            $approvalType = $decodedAction;
        }

        if (array_key_exists('message', $decodedEditingContent)) {
            $approvalContentValue = (string) ($decodedEditingContent['message'] ?? '');
        }
    }
}

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateApproval || ($canUpdateApproval && $editingApproval && in_array((string) $approvalType, $staffEditableTypes, true))): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= $editingApproval ? 'Sửa phiếu phê duyệt' : 'Tạo phiếu phê duyệt'; ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/approvals/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingApproval['id'] ?? 0); ?>">

                <label>
                    Loại yêu cầu
                    <select name="type" <?= $editingApproval ? 'disabled' : ''; ?>>
                        <?php foreach ($approvalTypeOptions as $typeValue => $typeLabel): ?>
                            <option value="<?= e((string) $typeValue); ?>" <?= $approvalType === (string) $typeValue ? 'selected' : ''; ?>><?= e((string) $typeLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($editingApproval): ?>
                        <input type="hidden" name="type" value="<?= e($approvalType); ?>">
                    <?php endif; ?>
                </label>

                <?php if ($editingApproval): ?>
                    <label>
                        Trạng thái
                        <select name="status">
                            <option value="pending" <?= (($editingApproval['status'] ?? 'pending') === 'pending') ? 'selected' : ''; ?>>pending</option>
                            <option value="approved" <?= (($editingApproval['status'] ?? '') === 'approved') ? 'selected' : ''; ?>>approved</option>
                            <option value="rejected" <?= (($editingApproval['status'] ?? '') === 'rejected') ? 'selected' : ''; ?>>rejected</option>
                        </select>
                    </label>
                <?php endif; ?>

                <label class="md:col-span-2">
                    Nội dung
                    <textarea name="content" rows="3" required><?= e($approvalContentValue); ?></textarea>
                </label>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= $editingApproval ? 'Cập nhật phiếu' : 'Tạo phiếu'; ?></button>
                    <?php if ($editingApproval): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('approvals-manage')); ?>">Hủy chỉnh sửa</a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="approvals-manage"
        data-ajax-page-param="approval_page"
        data-ajax-search-param="search"
    >
        <h3>Danh sách phiếu phê duyệt</h3>
        <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
            <label class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                </span>
                <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="Tìm loại phiếu, nội dung, người tạo..." autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
            </label>
            <select name="status" data-ajax-filter="1" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : ''; ?>>Từ chối</option>
            </select>
            <select name="type" data-ajax-filter="1" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <option value="">Tất cả loại yêu cầu</option>
                <?php foreach ($approvalTypeOptions as $typeValue => $typeLabel): ?>
                    <option value="<?= e((string) $typeValue); ?>" <?= $typeFilter === (string) $typeValue ? 'selected' : ''; ?>><?= e((string) $typeLabel); ?></option>
                <?php endforeach; ?>
            </select>
            <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500">Hiển thị <?= (int) count($approvals); ?> / <?= (int) $approvalTotal; ?> dòng</span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th>Loại</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Người tạo</th>
                        <th>Người duyệt</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($approvals)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có yêu cầu phê duyệt.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($approvals as $app): ?>
                            <?php
                                $rawContent = (string) ($app['content'] ?? '');
                                $decoded = json_decode($rawContent, true);
                                $displayType = (string) ($app['content_type'] ?? '');
                                $displayContent = $rawContent;
                                if (is_array($decoded)) {
                                    $displayType = (string) ($decoded['action'] ?? $displayType);
                                    $displayContent = (string) ($decoded['message'] ?? $displayContent);
                                }
                            ?>
                            <tr>
                                <td><?= e((string) ($approvalTypeOptions[$displayType] ?? $displayType)); ?></td>
                                <td><?= e($displayContent); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $app['status']); ?>"><?= e((string) $app['status']); ?></span></td>
                                <td><?= e((string) ($app['requester_name'] ?? '-')); ?></td>
                                <td><?= $app['approver_name'] ? e((string) $app['approver_name']) : '-'; ?></td>
                                <td><?= e(ui_format_datetime((string) ($app['created_at'] ?? ''))); ?></td>
                                <td>
                                    <?php
                                    $approvalRowType = strtolower(trim((string) $displayType));
                                    $canEditThisApproval = $isAdmin || ($canUpdateApproval && in_array($approvalRowType, $staffEditableTypes, true));
                                    ?>
                                    <?php if ($canEditThisApproval || $canDeleteApproval): ?>
                                        <div class="inline-flex flex-wrap items-center gap-2">
                                            <?php if ($canEditThisApproval): ?>
                                                <a
                                                    href="<?= e(page_url('approvals-manage', ['edit' => (int) $app['id'], 'approval_page' => $approvalPage, 'approval_per_page' => $approvalPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'status' => $statusFilter !== '' ? $statusFilter : null, 'type' => $typeFilter !== '' ? $typeFilter : null])); ?>"
                                                    class="admin-action-icon-btn"
                                                    data-action-kind="edit"
                                                    data-skip-action-icon="1"
                                                    title="Sửa phiếu"
                                                    aria-label="Sửa phiếu"
                                                >
                                                    <span class="admin-action-icon-label">Sửa</span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                                    </span>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($canDeleteApproval): ?>
                                                <form method="post" action="/api/approvals/delete" onsubmit="return confirm('Bạn chắc chắn muốn xóa phiếu phê duyệt này?');">
                                                    <?= csrf_input(); ?>
                                                    <input type="hidden" name="id" value="<?= (int) $app['id']; ?>">
                                                    <button
                                                        class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn"
                                                        data-action-kind="delete"
                                                        data-skip-action-icon="1"
                                                        type="submit"
                                                        title="Xóa phiếu"
                                                        aria-label="Xóa phiếu"
                                                    >
                                                        <span class="admin-action-icon-label">Xóa</span>
                                                        <span class="admin-action-icon-glyph" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                        </span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-slate-500">Chỉ có quyền xem</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($approvalTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium">Trang <?= (int) $approvalPage; ?>/<?= (int) $approvalTotalPages; ?> - Tổng <?= (int) $approvalTotal; ?> yêu cầu</span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('approvals-manage')); ?>">
                                <input type="hidden" name="page" value="approvals-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="status" value="<?= e($statusFilter); ?>">
                                <input type="hidden" name="type" value="<?= e($typeFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="approval-per-page">Số dòng</label>
                                <select id="approval-per-page" name="approval_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($approvalPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $approvalPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($approvalPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('approvals-manage', ['approval_page' => $approvalPage - 1, 'approval_per_page' => $approvalPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'status' => $statusFilter !== '' ? $statusFilter : null, 'type' => $typeFilter !== '' ? $typeFilter : null])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($approvalPage < $approvalTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('approvals-manage', ['approval_page' => $approvalPage + 1, 'approval_per_page' => $approvalPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'status' => $statusFilter !== '' ? $statusFilter : null, 'type' => $typeFilter !== '' ? $typeFilter : null])); ?>">Sau</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Sau</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>




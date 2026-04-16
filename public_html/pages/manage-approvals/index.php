<?php
require_admin_or_staff();
require_permission('approval.view');

$academicModel = new AcademicModel();
$approvalPage = max(1, (int) ($_GET['approval_page'] ?? 1));
$approvalPerPage = 10;
$approvalTotal = $academicModel->countApprovals();
$approvalTotalPages = max(1, (int) ceil($approvalTotal / $approvalPerPage));
if ($approvalPage > $approvalTotalPages) {
    $approvalPage = $approvalTotalPages;
}
$approvals = $academicModel->listApprovalsPage($approvalPage, $approvalPerPage);

$editingApproval = null;
if (!empty($_GET['edit'])) {
    $editingApproval = $academicModel->findApproval((int) $_GET['edit']);
}

$module = 'approvals';
$adminTitle = 'Hệ thống phê duyệt';

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');

$canCreateApproval = $isAdmin || has_any_permission(['approval.manage', 'approval.request']);
$canUpdateApproval = $isAdmin || has_any_permission(['approval.manage', 'approval.update']);
$canDeleteApproval = $isAdmin || has_any_permission(['approval.manage', 'approval.delete']);

$approvalTypeOptions = [
    'schedule_change' => 'schedule_change',
    'teacher_leave' => 'teacher_leave',
    'finance_adjust' => 'finance_adjust',
    'tuition_discount' => 'tuition_discount',
    'tuition_delete' => 'tuition_delete',
    'other' => 'other (khác)',
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

    <?php if ($canCreateApproval || $canUpdateApproval): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách phiếu phê duyệt</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm">
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
                <tbody>
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
                                <td><?= e($displayType); ?></td>
                                <td><?= e($displayContent); ?></td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) $app['status']); ?>"><?= e((string) $app['status']); ?></span></td>
                                <td><?= e((string) ($app['requester_name'] ?? '-')); ?></td>
                                <td><?= $app['approver_name'] ? e((string) $app['approver_name']) : '-'; ?></td>
                                <td><?= e((string) ($app['created_at'] ?? '')); ?></td>
                                <td>
                                    <?php if ($canUpdateApproval || $canDeleteApproval): ?>
                                        <div class="inline-flex flex-wrap items-center gap-2">
                                            <?php if ($canUpdateApproval): ?>
                                                <a
                                                    href="<?= e(page_url('approvals-manage', ['edit' => (int) $app['id'], 'approval_page' => $approvalPage])); ?>"
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
        </div>

        <?php if ($approvalTotalPages > 1): ?>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                <span>Trang <?= (int) $approvalPage; ?>/<?= (int) $approvalTotalPages; ?> - Tổng <?= (int) $approvalTotal; ?> yêu cầu</span>
                <div class="inline-flex items-center gap-1">
                    <?php if ($approvalPage > 1): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('approvals-manage', ['approval_page' => $approvalPage - 1])); ?>">Trước</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Trước</span>
                    <?php endif; ?>

                    <?php if ($approvalPage < $approvalTotalPages): ?>
                        <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('approvals-manage', ['approval_page' => $approvalPage + 1])); ?>">Sau</a>
                    <?php else: ?>
                        <span class="rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400">Sau</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </article>
</div>




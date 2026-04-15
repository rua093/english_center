<?php
require_admin_or_staff();
require_permission('approval.view');

$academicModel = new AcademicModel();
$approvals = $academicModel->listApprovals();

$module = 'approvals';
$adminTitle = 'Hệ thống phê duyệt';
?>
<section class="py-10 md:py-14">
    <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <?php $canUpdateApproval = has_permission('approval.update'); ?>
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1>Quản lý phê duyệt</h1>
                <p>Xử lý các yêu cầu phê duyệt.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                            <th>Thao tác</th>
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
                                        <?php if ($canUpdateApproval): ?>
                                            <form class="grid min-w-[220px] gap-1.5" method="post" action="/api/approvals/approve">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $app['id']; ?>">
                                                <select name="status">
                                                    <option value="pending" <?= $app['status'] === 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                    <option value="approved" <?= $app['status'] === 'approved' ? 'selected' : ''; ?>>Đã phê duyệt</option>
                                                    <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : ''; ?>>Đã từ chối</option>
                                                </select>
                                                <input type="text" name="decision_note" placeholder="Ghi chú duyệt (tùy chọn)">
                                                <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Cập nhật</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-sm text-slate-500">Chỉ có quyền xem</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>




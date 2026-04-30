<?php
require_admin_or_staff();
require_any_permission(['feedback.view']);

$academicModel = new AcademicModel();

$feedbackPage = max(1, (int) ($_GET['feedback_page'] ?? 1));
$feedbackPerPage = ui_pagination_resolve_per_page('feedback_per_page', 10);
$feedbackTotal = $academicModel->countFeedbacks();
$feedbackTotalPages = max(1, (int) ceil($feedbackTotal / $feedbackPerPage));
if ($feedbackPage > $feedbackTotalPages) {
    $feedbackPage = $feedbackTotalPages;
}
$feedbacks = $academicModel->listFeedbacksPage($feedbackPage, $feedbackPerPage);
$feedbackPerPageOptions = ui_pagination_per_page_options();

$module = 'feedbacks';
$adminTitle = 'Quản lý phản hồi';

$success = get_flash('success');
$error = get_flash('error');
?>
<div class="grid gap-4">
    <?php
    $canUpdateFeedback = has_permission('feedback.update');
    $canDeleteFeedback = has_permission('feedback.delete');
    
    $editingFeedback = null;
    if (!empty($_GET['edit'])) {
        $editingFeedback = $academicModel->findFeedback((int) $_GET['edit']);
    }
    ?>

    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canUpdateFeedback && $editingFeedback): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Sửa đánh giá</h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/feedbacks/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) $editingFeedback['id']; ?>">
                
                <label>
                    Đánh giá (1-5 sao)
                    <input type="number" min="1" max="5" name="rating" required value="<?= (int) ($editingFeedback['rating'] ?? 5); ?>">
                </label>
                
                <label>
                    Hiển thị ngoài website
                    <select name="is_public_web">
                        <option value="0" <?= ((int) ($editingFeedback['is_public_web'] ?? 0) === 0) ? 'selected' : ''; ?>>0 - Không</option>
                        <option value="1" <?= ((int) ($editingFeedback['is_public_web'] ?? 0) === 1) ? 'selected' : ''; ?>>1 - Có</option>
                    </select>
                </label>

                <label class="md:col-span-2">
                    Nhận xét (không bắt buộc)
                    <textarea name="comment" rows="3"><?= e((string) ($editingFeedback['comment'] ?? '')); ?></textarea>
                </label>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Cập nhật đánh giá</button>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('feedbacks-manage')); ?>">Hủy</a>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Danh sách đánh giá</h3>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1">
                <thead>
                    <tr>
                        <th>Mã HV</th>
                        <th>Học viên</th>
                        <th>Đánh giá</th>
                        <th>Nhận xét</th>
                        <th>Hiển thị web</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Chưa có đánh giá nào.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td><?= e((string) ($fb['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($fb['full_name'] ?? 'Học viên')); ?></td>
                                <td><?= (int) $fb['rating']; ?>/5</td>
                                <td>
                                    <?php $fullComment = (string) ($fb['comment'] ?? ''); ?>
                                    <span data-full-value="<?= e($fullComment); ?>"><?= e((string) substr($fullComment, 0, 50)); ?></span>
                                </td>
                                <td><span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold <?= (int) ($fb['is_public_web'] ?? 0) === 1 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600'; ?>"><?= (int) ($fb['is_public_web'] ?? 0); ?></span></td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateFeedback): ?>
                                            <a
                                                href="<?= e(page_url('feedbacks-manage', ['edit' => (int) $fb['id'], 'feedback_page' => $feedbackPage, 'feedback_per_page' => $feedbackPerPage])); ?>"
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
                                        
                                        <?php if ($canDeleteFeedback): ?>
                                            <form class="inline-block" method="post" action="/api/feedbacks/delete?id=<?= (int) $fb['id']; ?>" onsubmit="return confirm('Có chắc không?')">
                                                <?= csrf_input(); ?>
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
                                        
                                        <?php if (!$canUpdateFeedback && !$canDeleteFeedback): ?>
                                            <span class="text-sm text-slate-500">Không có quyền</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($feedbackTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="font-medium">Trang <?= (int) $feedbackPage; ?>/<?= (int) $feedbackTotalPages; ?> - Tổng <?= (int) $feedbackTotal; ?> đánh giá</span>
                        <div class="inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('feedbacks-manage')); ?>">
                                <input type="hidden" name="page" value="feedbacks-manage">
                                <label class="text-[11px] font-semibold text-slate-500" for="feedback-per-page">Số dòng</label>
                                <select id="feedback-per-page" name="feedback_per_page" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700" onchange="this.form.submit()">
                                    <?php foreach ($feedbackPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $feedbackPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($feedbackPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage - 1, 'feedback_per_page' => $feedbackPerPage])); ?>">Trước</a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400">Trước</span>
                            <?php endif; ?>

                            <?php if ($feedbackPage < $feedbackTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage + 1, 'feedback_per_page' => $feedbackPerPage])); ?>">Sau</a>
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

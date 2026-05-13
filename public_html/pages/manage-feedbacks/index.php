<?php
require_admin_or_staff();
require_any_permission(['feedback.view']);

$academicModel = new AcademicModel();
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$publicWebFilter = trim((string) ($_GET['is_public_web'] ?? ''));
if ($publicWebFilter !== '0' && $publicWebFilter !== '1') {
    $publicWebFilter = '';
}
$ratingFilter = trim((string) ($_GET['rating'] ?? ''));
if (!in_array($ratingFilter, ['1', '2', '3', '4', '5'], true)) {
    $ratingFilter = '';
}
$feedbackFilters = [
    'is_public_web' => $publicWebFilter,
    'rating' => $ratingFilter,
];

$feedbackPage = max(1, (int) ($_GET['feedback_page'] ?? 1));
$feedbackPerPage = ui_pagination_resolve_per_page('feedback_per_page', 10);
$feedbackTotal = $academicModel->countFeedbacks($searchQuery, $feedbackFilters);
$feedbackTotalPages = max(1, (int) ceil($feedbackTotal / $feedbackPerPage));
if ($feedbackPage > $feedbackTotalPages) {
    $feedbackPage = $feedbackTotalPages;
}
$feedbacks = $academicModel->listFeedbacksPage($feedbackPage, $feedbackPerPage, $searchQuery, $feedbackFilters);
$feedbackPerPageOptions = ui_pagination_per_page_options();

$module = 'feedbacks';
$adminTitle = t('admin.feedbacks.title');

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
            <h3><?= e(t('admin.feedbacks.edit')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/feedbacks/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) $editingFeedback['id']; ?>">
                
                <label>
                    <?= e(t('admin.feedbacks.rating')); ?>
                    <input type="number" min="1" max="5" name="rating" required value="<?= (int) ($editingFeedback['rating'] ?? 5); ?>">
                </label>
                
                <label>
                    <?= e(t('admin.feedbacks.public_label')); ?>
                    <select name="is_public_web">
                        <option value="0" <?= ((int) ($editingFeedback['is_public_web'] ?? 0) === 0) ? 'selected' : ''; ?>><?= e(t('admin.feedbacks.public_no')); ?></option>
                        <option value="1" <?= ((int) ($editingFeedback['is_public_web'] ?? 0) === 1) ? 'selected' : ''; ?>><?= e(t('admin.feedbacks.public_yes')); ?></option>
                    </select>
                </label>

                <label class="md:col-span-2">
                    <?= e(t('admin.feedbacks.comment')); ?>
                    <textarea name="comment" rows="3"><?= e((string) ($editingFeedback['comment'] ?? '')); ?></textarea>
                </label>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.feedbacks.update')); ?></button>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage, 'feedback_per_page' => $feedbackPerPage, 'search' => $searchQuery, 'is_public_web' => $publicWebFilter, 'rating' => $ratingFilter])); ?>"><?= e(t('admin.common.cancel')); ?></a>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="feedbacks-manage"
        data-ajax-page-param="feedback_page"
        data-ajax-search-param="search"
    >
        <h3><?= e(t('admin.feedbacks.list')); ?></h3>
        <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
                <label class="relative block w-full md:max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input
                        type="search"
                        value="<?= e($searchQuery); ?>"
                        data-ajax-search="1"
                        placeholder="<?= e(t('admin.feedbacks.search_placeholder')); ?>"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </label>
                <select name="is_public_web" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value=""><?= e(t('admin.feedbacks.public_all')); ?></option>
                    <option value="1" <?= $publicWebFilter === '1' ? 'selected' : ''; ?>><?= e(t('admin.feedbacks.public_yes')); ?></option>
                    <option value="0" <?= $publicWebFilter === '0' ? 'selected' : ''; ?>><?= e(t('admin.feedbacks.public_no')); ?></option>
                </select>
                <select name="rating" data-ajax-filter="1" class="h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value=""><?= e(t('admin.feedbacks.rating_all')); ?></option>
                    <?php for ($ratingOption = 1; $ratingOption <= 5; $ratingOption++): ?>
                        <option value="<?= $ratingOption; ?>" <?= $ratingFilter === (string) $ratingOption ? 'selected' : ''; ?>><?= e(t('admin.feedbacks.rating_value', ['count' => $ratingOption])); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-enable-row-detail="1" data-disable-global-filter="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.feedbacks.table_student_code')); ?></th>
                        <th><?= e(t('admin.feedbacks.table_student')); ?></th>
                        <th><?= e(t('admin.feedbacks.table_rating')); ?></th>
                        <th><?= e(t('admin.feedbacks.table_comment')); ?></th>
                        <th><?= e(t('admin.feedbacks.table_public')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($feedbacks)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.feedbacks.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $fb): ?>
                            <tr>
                                <td><?= e((string) ($fb['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($fb['full_name'] ?? ($fb['student_name'] ?? ''))); ?></td>
                                <td><?= e(t('admin.feedbacks.rating_fraction', ['rating' => (int) $fb['rating'], 'total' => 5])); ?></td>
                                <td>
                                    <?php $fullComment = (string) ($fb['comment'] ?? ''); ?>
                                    <span data-full-value="<?= e($fullComment); ?>"><?= e((string) substr($fullComment, 0, 50)); ?></span>
                                </td>
                                <td>
                                    <?php $isPublicWeb = (int) ($fb['is_public_web'] ?? 0) === 1; ?>
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize <?= $isPublicWeb ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600'; ?>">
                                        <?= $isPublicWeb ? e(t('admin.feedbacks.public_label_yes')) : e(t('admin.feedbacks.public_label_no')); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canUpdateFeedback): ?>
                                            <button
                                                type="button"
                                                class="admin-row-detail-button admin-action-icon-btn"
                                                data-action-kind="detail"
                                                data-admin-row-detail="1"
                                                data-detail-url="<?= e(page_url('feedbacks-manage', ['edit' => (int) $fb['id'], 'feedback_page' => $feedbackPage, 'feedback_per_page' => $feedbackPerPage, 'search' => $searchQuery, 'is_public_web' => $publicWebFilter, 'rating' => $ratingFilter])); ?>"
                                                data-skip-action-icon="1"
                                                title="<?= e(t('admin.common.view_detail')); ?>"
                                                aria-label="<?= e(t('admin.common.view_detail')); ?>"
                                            >
                                                <span class="admin-action-icon-label"><?= e(t('admin.common.view_detail')); ?></span>
                                                <span class="admin-action-icon-glyph" aria-hidden="true">
                                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M2 12s3.5-6.5 10-6.5S22 12 22 12s-3.5 6.5-10 6.5S2 12 2 12z"></path></svg>
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canUpdateFeedback): ?>
                                            <a
                                                href="<?= e(page_url('feedbacks-manage', ['edit' => (int) $fb['id'], 'feedback_page' => $feedbackPage, 'feedback_per_page' => $feedbackPerPage, 'search' => $searchQuery, 'is_public_web' => $publicWebFilter, 'rating' => $ratingFilter])); ?>"
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
                                        
                                        <?php if ($canDeleteFeedback): ?>
                                            <form class="inline-block" method="post" action="/api/feedbacks/delete?id=<?= (int) $fb['id']; ?>" onsubmit="return confirm('<?= e(t('admin.feedbacks.delete_confirm')); ?>')">
                                                <?= csrf_input(); ?>
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
                                        
                                        <?php if (!$canUpdateFeedback && !$canDeleteFeedback): ?>
                                            <span class="text-sm text-slate-500"><?= e(t('admin.common.view_only')); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($feedbackTotal > 0): ?>
                <div class="border-t border-slate-200 bg-slate-50/80 px-3 py-2" data-ajax-pagination="1">
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                        <span class="min-w-0 flex-1 font-medium" data-ajax-row-info="1"><?= e(t('admin.feedbacks.page_info', ['current' => (int) $feedbackPage, 'total' => (int) $feedbackTotalPages, 'count' => (int) $feedbackTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('feedbacks-manage')); ?>">
                                <input type="hidden" name="page" value="feedbacks-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="is_public_web" value="<?= e($publicWebFilter); ?>">
                                <input type="hidden" name="rating" value="<?= e($ratingFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="feedback-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="feedback-per-page" name="feedback_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($feedbackPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $feedbackPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($feedbackPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage - 1, 'feedback_per_page' => $feedbackPerPage, 'search' => $searchQuery, 'is_public_web' => $publicWebFilter, 'rating' => $ratingFilter])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($feedbackPage < $feedbackTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('feedbacks-manage', ['feedback_page' => $feedbackPage + 1, 'feedback_per_page' => $feedbackPerPage, 'search' => $searchQuery, 'is_public_web' => $publicWebFilter, 'rating' => $ratingFilter])); ?>"><?= e(t('admin.common.next')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>

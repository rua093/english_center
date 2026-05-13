<?php
require_admin_or_staff();
require_any_permission(['academic.portfolios.view']);
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();

$portfolioPage = max(1, (int) ($_GET['portfolio_page'] ?? 1));
$portfolioPerPage = ui_pagination_resolve_per_page('portfolio_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$typeFilter = trim((string) ($_GET['type'] ?? ''));
$publicFilter = trim((string) ($_GET['is_public_web'] ?? ''));
$portfolioFilters = ['type' => $typeFilter, 'is_public_web' => $publicFilter];
$portfolioTotal = $academicModel->countPortfolios($searchQuery, $portfolioFilters);
$portfolioTotalPages = max(1, (int) ceil($portfolioTotal / $portfolioPerPage));
if ($portfolioPage > $portfolioTotalPages) {
    $portfolioPage = $portfolioTotalPages;
}

$portfolios = $academicModel->listPortfoliosPage($portfolioPage, $portfolioPerPage, $searchQuery, $portfolioFilters);
$portfolioPerPageOptions = ui_pagination_per_page_options();
$students = $academicModel->studentLookups();

$editingPortfolio = null;
if (!empty($_GET['edit'])) {
    $editingPortfolio = $academicModel->findPortfolio((int) $_GET['edit']);
}

$module = 'portfolios';
$adminTitle = t('admin.portfolios.title');

$viewer = auth_user();
$viewerRole = (string) ($viewer['role'] ?? '');
$viewerId = (int) ($viewer['id'] ?? 0);

$success = get_flash('success');
$error = get_flash('error');

$portfolioTypeLabels = [
    'progress_video' => t('admin.portfolios.type_progress_video'),
    'activity_photo' => t('admin.portfolios.type_activity_photo'),
    'feedback' => t('admin.portfolios.type_feedback'),
];

$selectedType = (string) ($editingPortfolio['type'] ?? 'progress_video');
$selectedDescription = trim((string) ($editingPortfolio['description'] ?? ''));
$selectedIsPublicWeb = (int) ($editingPortfolio['is_public_web'] ?? 0);
$editingPortfolioMediaPath = normalize_public_file_url((string) ($editingPortfolio['media_url'] ?? ''));
?>

<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e($editingPortfolio ? t('admin.portfolios.edit') : t('admin.portfolios.add')); ?></h3>
        <form class="grid gap-3" method="post" action="/api/portfolios/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input type="hidden" name="id" value="<?= (int) ($editingPortfolio['id'] ?? 0); ?>">

            <label>
                <?= e(t('admin.portfolios.student')); ?>
                <select name="student_id" required>
                    <?php if (empty($students)): ?>
                        <option value=""><?= e(t('admin.portfolios.no_students')); ?></option>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <option value="<?= (int) ($student['id'] ?? 0); ?>" <?= (int) ($editingPortfolio['student_id'] ?? 0) === (int) ($student['id'] ?? 0) ? 'selected' : ''; ?>><?= e(student_dropdown_label($student)); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </label>

            <label>
                <?= e(t('admin.portfolios.content_type')); ?>
                <select name="type" required>
                    <?php foreach ($portfolioTypeLabels as $typeValue => $typeLabel): ?>
                        <option value="<?= e($typeValue); ?>" <?= $selectedType === $typeValue ? 'selected' : ''; ?>><?= e($typeLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div>
                <label for="portfolio-description"><?= e(t('admin.assignment_edit.description')); ?></label>
                <?= render_bbcode_editor('description', $selectedDescription, ['id' => 'portfolio-description', 'rows' => 4, 'placeholder' => t('admin.portfolios.description_placeholder')]); ?>
            </div>

            <label>
                <?= e(t('admin.portfolios.upload_media')); ?>
                <input type="file" name="portfolio_file" accept=".jpg,.jpeg,.png,.mp4,.mov,.webm" <?= $editingPortfolio ? '' : 'required'; ?>>
            </label>

            <?php if ($editingPortfolioMediaPath !== ''): ?>
                <p class="text-xs text-slate-500">
                    <?= e(t('admin.portfolios.current_file')); ?>:
                    <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingPortfolioMediaPath); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.portfolios.open_file')); ?></a>.
                    <?= e(t('admin.assignment_edit.replace_hint')); ?>
                </p>
            <?php endif; ?>

            <label>
                <?= e(t('admin.portfolios.public_visibility')); ?>
                <select name="is_public_web">
                    <option value="1" <?= $selectedIsPublicWeb === 1 ? 'selected' : ''; ?>><?= e(t('common.yes')); ?></option>
                    <option value="0" <?= $selectedIsPublicWeb === 0 ? 'selected' : ''; ?>><?= e(t('common.no')); ?></option>
                </select>
            </label>

            <div class="inline-flex flex-wrap items-center gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e($editingPortfolio ? t('admin.portfolios.update') : t('admin.portfolios.save')); ?></button>
                <?php if ($editingPortfolio): ?>
                    <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage, 'portfolio_per_page' => $portfolioPerPage])); ?>"><?= e(t('admin.common.cancel_edit')); ?></a>
                <?php endif; ?>
            </div>
        </form>
    </article>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="portfolios-academic"
        data-ajax-page-param="portfolio_page"
        data-ajax-search-param="search"
    >
        <h3><?= e(t('admin.portfolios.list')); ?></h3>
        <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
            <label class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                </span>
                <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="<?= e(t('admin.portfolios.search_placeholder')); ?>" autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
            </label>
            <select name="type" data-ajax-filter="1" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <option value=""><?= e(t('admin.portfolios.all_content_types')); ?></option>
                <?php foreach ($portfolioTypeLabels as $typeValue => $typeLabel): ?>
                    <option value="<?= e($typeValue); ?>" <?= $typeFilter === $typeValue ? 'selected' : ''; ?>><?= e($typeLabel); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="is_public_web" data-ajax-filter="1" class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <option value=""><?= e(t('admin.portfolios.all_visibility')); ?></option>
                <option value="1" <?= $publicFilter === '1' ? 'selected' : ''; ?>><?= e(t('admin.portfolios.public')); ?></option>
                <option value="0" <?= $publicFilter === '0' ? 'selected' : ''; ?>><?= e(t('admin.portfolios.internal')); ?></option>
            </select>
            <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.portfolios.showing_rows', ['shown' => (int) count($portfolios), 'total' => (int) $portfolioTotal])); ?></span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.portfolios.student_code')); ?></th>
                        <th><?= e(t('admin.portfolios.student')); ?></th>
                        <th><?= e(t('admin.portfolios.type')); ?></th>
                        <th>Media</th>
                        <th><?= e(t('admin.assignment_edit.description')); ?></th>
                        <th><?= e(t('admin.portfolios.visibility')); ?></th>
                        <th><?= e(t('admin.portfolios.created_at')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($portfolios)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.portfolios.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($portfolios as $portfolio): ?>
                            <?php
                            $portfolioId = (int) ($portfolio['id'] ?? 0);
                            $portfolioStudentId = (int) ($portfolio['student_id'] ?? 0);
                            $portfolioType = (string) ($portfolio['type'] ?? '');
                            $portfolioTypeLabel = $portfolioTypeLabels[$portfolioType] ?? str_replace('_', ' ', $portfolioType);
                            $portfolioDescription = trim((string) ($portfolio['description'] ?? ''));

                            $portfolioMediaRaw = trim((string) ($portfolio['media_url'] ?? ''));
                            $portfolioMediaUrl = normalize_public_file_url($portfolioMediaRaw);
                            $isImage = $portfolioMediaUrl !== '' && preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $portfolioMediaUrl) === 1;
                            $isVideo = $portfolioMediaUrl !== '' && preg_match('/\.(mp4|mov|webm)(\?.*)?$/i', $portfolioMediaUrl) === 1;

                            $createdAtRaw = trim((string) ($portfolio['created_at'] ?? ''));
                            $createdAtTimestamp = $createdAtRaw !== '' ? strtotime($createdAtRaw) : false;
                            $createdAtText = $createdAtTimestamp ? date('d/m/Y H:i', $createdAtTimestamp) : '-';

                            $canManagePortfolio = true;
                            ?>
                            <tr>
                                <td><?= e((string) ($portfolio['student_code'] ?? '-')); ?></td>
                                <td><?= e((string) ($portfolio['full_name'] ?? t('admin.portfolios.student'))); ?></td>
                                <td>
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= e($portfolioTypeLabel); ?></span>
                                </td>
                                <td>
                                    <?php if ($portfolioMediaUrl === ''): ?>
                                        <span class="text-xs text-slate-400">-</span>
                                    <?php elseif ($isImage): ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(t('admin.portfolios.view_image')); ?>" class="inline-block">
                                            <img src="<?= e($portfolioMediaUrl); ?>" alt="<?= e(t('admin.portfolios.preview')); ?>" class="h-10 w-14 rounded-md border border-slate-200 object-cover">
                                        </a>
                                    <?php elseif ($isVideo): ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline"><?= e(t('admin.portfolios.view_video')); ?></a>
                                    <?php else: ?>
                                        <a href="<?= e($portfolioMediaUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline"><?= e(t('admin.portfolios.open_link')); ?></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($portfolioDescription === ''): ?>
                                        <span class="text-slate-400">-</span>
                                    <?php else: ?>
                                        <div class="bbcode-content"><?= bbcode_to_html($portfolioDescription); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) ($portfolio['is_public_web'] ?? 0) === 1): ?>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700"><?= e(t('admin.portfolios.public')); ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600"><?= e(t('admin.portfolios.internal')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($createdAtText); ?></td>
                                <td>
                                    <?php if ($canManagePortfolio): ?>
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <a
                                                href="<?= e(page_url('portfolios-academic', ['edit' => $portfolioId, 'portfolio_page' => $portfolioPage, 'portfolio_per_page' => $portfolioPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'type' => $typeFilter !== '' ? $typeFilter : null, 'is_public_web' => $publicFilter !== '' ? $publicFilter : null])); ?>"
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
                                            <form class="inline-block" method="post" action="/api/portfolios/delete?id=<?= $portfolioId; ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.portfolios.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
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
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($portfolioTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.portfolios.page_info', ['current' => (int) $portfolioPage, 'total' => (int) $portfolioTotalPages, 'count' => (int) $portfolioTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('portfolios-academic')); ?>">
                                <input type="hidden" name="page" value="portfolios-academic">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="type" value="<?= e($typeFilter); ?>">
                                <input type="hidden" name="is_public_web" value="<?= e($publicFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="portfolio-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="portfolio-per-page" name="portfolio_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($portfolioPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $portfolioPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($portfolioPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage - 1, 'portfolio_per_page' => $portfolioPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'type' => $typeFilter !== '' ? $typeFilter : null, 'is_public_web' => $publicFilter !== '' ? $publicFilter : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($portfolioPage < $portfolioTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('portfolios-academic', ['portfolio_page' => $portfolioPage + 1, 'portfolio_per_page' => $portfolioPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'type' => $typeFilter !== '' ? $typeFilter : null, 'is_public_web' => $publicFilter !== '' ? $publicFilter : null])); ?>"><?= e(t('admin.common.next')); ?></a>
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

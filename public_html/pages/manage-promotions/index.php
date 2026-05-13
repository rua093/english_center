<?php
require_admin_or_staff();
require_any_permission(['finance.promotions.view']);

$academicModel = new AcademicModel();
$lookups = $academicModel->registrationLookups();
$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];

$promotionPage = max(1, (int) ($_GET['promotion_page'] ?? 1));
$promotionPerPage = ui_pagination_resolve_per_page('promotion_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$promoTypeFilter = strtoupper(trim((string) ($_GET['promo_type'] ?? '')));
$promotionFilters = ['promo_type' => $promoTypeFilter];
$promotionTotal = $academicModel->countPromotions($searchQuery, $promotionFilters);
$promotionTotalPages = max(1, (int) ceil($promotionTotal / $promotionPerPage));
if ($promotionPage > $promotionTotalPages) {
    $promotionPage = $promotionTotalPages;
}
$promotions = $academicModel->listPromotionsPage($promotionPage, $promotionPerPage, $searchQuery, $promotionFilters);
$promotionPerPageOptions = ui_pagination_per_page_options();

$editingPromotion = null;
if (!empty($_GET['edit'])) {
    $editingPromotion = $academicModel->findPromotion((int) $_GET['edit']);
}

$module = 'promotions';
$adminTitle = t('admin.promotions.title');

$viewer = auth_user();
$isAdmin = (($viewer['role'] ?? '') === 'admin');
$canManagePromotion = $isAdmin;
$canDeletePromotion = $isAdmin;
$usesPromotionSchema = $academicModel->usesPromotionSchema();

$success = get_flash('success');
$error = get_flash('error');

$promoTypeOptions = [
    'DURATION' => t('admin.promotions.type_duration'),
    'SOCIAL'   => t('admin.promotions.type_social'),
    'EVENT'    => t('admin.promotions.type_event'),
    'GROUP'    => t('admin.promotions.type_group'),
];
$selectedCourseId = max(0, (int) ($editingPromotion['course_id'] ?? 0));
$selectedName = trim((string) ($editingPromotion['name'] ?? ''));
$selectedPromoType = strtoupper(trim((string) ($editingPromotion['promo_type'] ?? 'DURATION')));
if (!isset($promoTypeOptions[$selectedPromoType])) {
    $selectedPromoType = 'DURATION';
}
$selectedDiscountValue = (float) ($editingPromotion['discount_value'] ?? 0);
$selectedStartDate = trim((string) ($editingPromotion['start_date'] ?? ''));
$selectedEndDate = trim((string) ($editingPromotion['end_date'] ?? ''));
$selectedQuantityLimit = $editingPromotion['quantity_limit'] ?? null;
$selectedQuantityRemaining = $editingPromotion['quantity_remaining'] ?? null;

$today = date('Y-m-d');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if (!$usesPromotionSchema): ?>
        <div class="rounded-xl border-l-4 border-amber-500 bg-amber-50 p-3 text-sm text-amber-800">
            <?= e(t('admin.promotions.legacy_schema_note')); ?>
        </div>
    <?php endif; ?>

    <?php if ($canManagePromotion): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e($editingPromotion ? t('admin.promotions.edit') : t('admin.promotions.add')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/promotions/save">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingPromotion['id'] ?? 0); ?>">

                <label>
                    <?= e(t('admin.promotions.scope')); ?>
                    <select name="course_id" <?= $usesPromotionSchema ? '' : 'required'; ?>>
                        <?php if ($usesPromotionSchema): ?>
                            <option value="0" <?= $selectedCourseId === 0 ? 'selected' : ''; ?>><?= e(t('admin.promotions.scope_all')); ?></option>
                        <?php else: ?>
                            <option value="" <?= $selectedCourseId <= 0 ? 'selected' : ''; ?>><?= e(t('admin.promotions.choose_course')); ?></option>
                        <?php endif; ?>
                        <?php foreach ($courses as $course): ?>
                            <?php $courseId = (int) ($course['id'] ?? 0); ?>
                            <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>>
                                <?= e((string) ($course['course_name'] ?? t('admin.promotions.course_fallback', ['id' => $courseId]))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!$usesPromotionSchema): ?>
                        <small class="mt-1 block text-xs text-slate-500"><?= e(t('admin.promotions.scope_hint')); ?></small>
                    <?php endif; ?>
                </label>

                <label>
                    <?= e(t('admin.promotions.name')); ?>
                    <input type="text" name="name" value="<?= e($selectedName); ?>" required>
                </label>

                <label>
                    <?= e(t('admin.promotions.type')); ?>
                    <select name="promo_type" required>
                        <?php foreach ($promoTypeOptions as $promoTypeValue => $promoTypeLabel): ?>
                            <option value="<?= e($promoTypeValue); ?>" <?= $selectedPromoType === $promoTypeValue ? 'selected' : ''; ?>><?= e($promoTypeLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    <?= e(t('admin.promotions.discount')); ?>
                    <input type="number" step="0.01" min="0" max="100" name="discount_value" value="<?= e(rtrim(rtrim(number_format($selectedDiscountValue, 2, '.', ''), '0'), '.')); ?>" required>
                </label>

                <label>
                    <?= e(t('admin.promotions.start_date')); ?>
                    <input type="date" name="start_date" value="<?= e($selectedStartDate); ?>">
                </label>

                <label>
                    <?= e(t('admin.promotions.end_date')); ?>
                    <input type="date" name="end_date" value="<?= e($selectedEndDate); ?>">
                </label>

                <label>
                    <?= e(t('admin.promotions.quantity_limit')); ?>
                    <input
                        type="number"
                        min="1"
                        step="1"
                        name="quantity_limit"
                        value="<?= $selectedQuantityLimit !== null ? e((string) (int) $selectedQuantityLimit) : ''; ?>"
                        placeholder="<?= e(t('admin.promotions.quantity_placeholder')); ?>"
                    >
                    <small class="mt-1 block text-xs text-slate-500"><?= e(t('admin.promotions.quantity_hint')); ?></small>
                </label>

                <?php if ($editingPromotion && $selectedQuantityLimit !== null): ?>
                    <label>
                        <?= e(t('admin.promotions.quantity_remaining_label')); ?>
                        <input type="text" value="<?= e((string) max(0, (int) $selectedQuantityRemaining)); ?>" readonly>
                    </label>
                <?php endif; ?>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e($editingPromotion ? t('admin.promotions.update') : t('admin.promotions.create')); ?></button>
                    <?php if ($editingPromotion): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('promotions-manage')); ?>"><?= e(t('admin.common.cancel')); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="promotions-manage"
        data-ajax-page-param="promotion_page"
        data-ajax-search-param="search"
    >
        <h3><?= e(t('admin.promotions.list')); ?></h3>
        <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
            <label class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                </span>
                <input
                    data-ajax-search="1"
                    type="search"
                    value="<?= e($searchQuery); ?>"
                    placeholder="<?= e(t('admin.promotions.search_placeholder')); ?>"
                    autocomplete="off"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
            </label>
            <select
                name="promo_type"
                data-ajax-filter="1"
                class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
            >
                <option value=""><?= e(t('admin.promotions.type_all')); ?></option>
                <?php foreach ($promoTypeOptions as $promoTypeValue => $promoTypeLabel): ?>
                    <option value="<?= e($promoTypeValue); ?>" <?= $promoTypeFilter === $promoTypeValue ? 'selected' : ''; ?>><?= e($promoTypeLabel); ?></option>
                <?php endforeach; ?>
            </select>
            <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.promotions.showing_rows', ['shown' => (int) count($promotions), 'total' => (int) $promotionTotal])); ?></span>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.promotions.table_name')); ?></th>
                        <th><?= e(t('admin.promotions.table_scope')); ?></th>
                        <th><?= e(t('admin.promotions.table_type')); ?></th>
                        <th><?= e(t('admin.promotions.table_discount')); ?></th>
                        <th><?= e(t('admin.promotions.table_effective')); ?></th>
                        <th><?= e(t('admin.promotions.table_quantity')); ?></th>
                        <th><?= e(t('admin.promotions.table_status')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($promotions)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.promotions.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $promotion): ?>
                            <?php
                            $promotionId = (int) ($promotion['id'] ?? 0);
                            $courseId = max(0, (int) ($promotion['course_id'] ?? 0));
                            $courseName = trim((string) ($promotion['course_name'] ?? ''));
                            $scopeLabel = $courseId > 0
                                ? ($courseName !== '' ? $courseName : t('admin.promotions.course_fallback', ['id' => $courseId]))
                                : t('admin.promotions.scope_all');

                            $promoType = strtoupper(trim((string) ($promotion['promo_type'] ?? 'DURATION')));
                            $promoTypeLabel = (string) ($promoTypeOptions[$promoType] ?? $promoType);

                            $discountValue = max(0, min(100, (float) ($promotion['discount_value'] ?? 0)));
                            $discountText = rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.');

                            $startDate = trim((string) ($promotion['start_date'] ?? ''));
                            $endDate = trim((string) ($promotion['end_date'] ?? ''));
                            $quantityLimit = $promotion['quantity_limit'] ?? null;
                            $quantityRemaining = $promotion['quantity_remaining'] ?? null;

                            $effectiveText = ui_format_date_range($startDate, $endDate);

                            $isActive = ($startDate === '' || $startDate <= $today)
                                && ($endDate === '' || $endDate >= $today);
                            ?>
                            <tr>
                                <td><?= e((string) ($promotion['name'] ?? t('admin.promotions.fallback', ['id' => $promotionId]))); ?></td>
                                <td><?= e($scopeLabel); ?></td>
                                <td><?= e($promoTypeLabel); ?></td>
                                <td><?= e($discountText); ?>%</td>
                                <td><?= e($effectiveText); ?></td>
                                <td>
                                    <?php if ($quantityLimit === null): ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-600"><?= e(t('admin.promotions.quantity_unlimited')); ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                            <?= e(t('admin.promotions.quantity_remaining', ['remaining' => (int) max(0, (int) $quantityRemaining), 'limit' => (int) $quantityLimit])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700"><?= e(t('admin.promotions.status_active')); ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600"><?= e(t('admin.promotions.status_inactive')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <?php if ($canManagePromotion): ?>
                                            <a
                                                href="<?= e(page_url('promotions-manage', ['edit' => $promotionId, 'promotion_page' => $promotionPage, 'promotion_per_page' => $promotionPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'promo_type' => $promoTypeFilter !== '' ? $promoTypeFilter : null])); ?>"
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
                                        <?php if ($canDeletePromotion): ?>
                                            <form class="inline-block" method="post" action="/api/promotions/delete?id=<?= $promotionId; ?>&promotion_page=<?= (int) $promotionPage; ?>&promotion_per_page=<?= (int) $promotionPerPage; ?>&search=<?= urlencode($searchQuery); ?>&promo_type=<?= urlencode($promoTypeFilter); ?>" onsubmit="return confirm('<?= e(t('admin.promotions.delete_confirm')); ?>')">
                                                <?= csrf_input(); ?>
                                                <button class="<?= ui_btn_danger_classes('sm'); ?> admin-action-icon-btn" data-action-kind="delete" data-skip-action-icon="1" type="submit" title="<?= e(t('admin.common.delete')); ?>" aria-label="<?= e(t('admin.common.delete')); ?>">
                                                    <span class="admin-action-icon-label"><?= e(t('admin.common.delete')); ?></span>
                                                    <span class="admin-action-icon-glyph" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path></svg>
                                                    </span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($promotionTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.promotions.page_info', ['current' => (int) $promotionPage, 'total' => (int) $promotionTotalPages, 'count' => (int) $promotionTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('promotions-manage')); ?>">
                                <input type="hidden" name="page" value="promotions-manage">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <input type="hidden" name="promo_type" value="<?= e($promoTypeFilter); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="promotion-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="promotion-per-page" name="promotion_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($promotionPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $promotionPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <?php if ($promotionPage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('promotions-manage', ['promotion_page' => $promotionPage - 1, 'promotion_per_page' => $promotionPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'promo_type' => $promoTypeFilter !== '' ? $promoTypeFilter : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($promotionPage < $promotionTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('promotions-manage', ['promotion_page' => $promotionPage + 1, 'promotion_per_page' => $promotionPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null, 'promo_type' => $promoTypeFilter !== '' ? $promoTypeFilter : null])); ?>"><?= e(t('admin.common.next')); ?></a>
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

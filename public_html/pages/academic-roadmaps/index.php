<?php
require_permission('academic.roadmaps.view');

$academicModel = new AcademicModel();
$courseOptions = $academicModel->classLookups()['courses'] ?? [];
$selectedCourseId = max(0, (int) ($_GET['course_id'] ?? 0));
$roadmapPage = max(1, (int) ($_GET['roadmap_page'] ?? 1));
$roadmapPerPage = ui_pagination_resolve_per_page('roadmap_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$roadmapPerPageOptions = ui_pagination_per_page_options();

$editingRoadmap = null;
if (!empty($_GET['edit'])) {
    $editingRoadmap = $academicModel->findRoadmap((int) $_GET['edit']);
    if (is_array($editingRoadmap) && $selectedCourseId <= 0) {
        $selectedCourseId = (int) ($editingRoadmap['course_id'] ?? 0);
    }
}

$courseMap = [];
foreach ($courseOptions as $courseRow) {
    $courseId = (int) ($courseRow['id'] ?? 0);
    if ($courseId > 0) {
        $courseMap[$courseId] = $courseRow;
    }
}

if (is_array($editingRoadmap) && $selectedCourseId > 0 && (int) ($editingRoadmap['course_id'] ?? 0) !== $selectedCourseId) {
    $editingRoadmap = null;
}

$selectedCourse = $selectedCourseId > 0 ? ($courseMap[$selectedCourseId] ?? null) : null;
$roadmapTotal = $selectedCourseId > 0 ? $academicModel->countRoadmapsByCourse($selectedCourseId, $searchQuery) : 0;
$roadmapTotalPages = max(1, (int) ceil($roadmapTotal / $roadmapPerPage));
if ($roadmapPage > $roadmapTotalPages) {
    $roadmapPage = $roadmapTotalPages;
}
$roadmaps = $selectedCourseId > 0 ? $academicModel->listRoadmapsByCoursePage($selectedCourseId, $roadmapPage, $roadmapPerPage, $searchQuery) : [];

$module = 'roadmaps';
$adminTitle = t('admin.roadmaps.title');

$success = get_flash('success');
$error = get_flash('error');

$canCreateRoadmap = has_permission('academic.roadmaps.create');
$canUpdateRoadmap = has_permission('academic.roadmaps.update');
$canDeleteRoadmap = has_permission('academic.roadmaps.delete');

$selectedOrder = max(1, (int) ($editingRoadmap['order'] ?? 1));
$selectedTopicTitle = trim((string) ($editingRoadmap['topic_title'] ?? ''));
$selectedOutlineContent = trim((string) ($editingRoadmap['outline_content'] ?? ''));
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e(t('admin.roadmaps.filter_title')); ?></h3>
        <form class="grid gap-3 md:grid-cols-3" method="get" action="<?= e(page_url('roadmaps-academic')); ?>">
            <input type="hidden" name="page" value="roadmaps-academic">
            <label>
                <?= e(t('admin.class_edit.course')); ?>
                <select name="course_id">
                    <option value=""><?= e(t('admin.class_edit.choose_course')); ?></option>
                    <?php foreach ($courseOptions as $course): ?>
                        <?php $courseId = (int) ($course['id'] ?? 0); ?>
                        <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>><?= e((string) ($course['course_name'] ?? t('admin.courses.course_fallback', ['id' => $courseId]))); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="flex items-end gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.roadmaps.filter')); ?></button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('roadmaps-academic')); ?>"><?= e(t('admin.submissions.reset')); ?></a>
            </div>

            <div class="flex items-end justify-start md:justify-end">
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('courses-academic')); ?>"><?= e(t('admin.roadmaps.course_catalog')); ?></a>
            </div>
        </form>
        <?php if (is_array($selectedCourse)): ?>
            <p class="mt-3 text-sm text-slate-600"><?= e(t('admin.roadmaps.managing_course_prefix')); ?> <strong><?= e((string) ($selectedCourse['course_name'] ?? '')); ?></strong>.</p>
        <?php endif; ?>
    </article>

    <?php if (!is_array($selectedCourse)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            <?= e(t('admin.roadmaps.choose_course_hint')); ?>
        </article>
    <?php else: ?>
        <?php if ($canCreateRoadmap || $canUpdateRoadmap): ?>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3><?= e($editingRoadmap ? t('admin.roadmaps.edit') : t('admin.roadmaps.add')); ?></h3>
                <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/roadmaps/save">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="<?= (int) ($editingRoadmap['id'] ?? 0); ?>">
                    <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                    <input type="hidden" name="roadmap_page" value="<?= (int) $roadmapPage; ?>">
                    <input type="hidden" name="roadmap_per_page" value="<?= (int) $roadmapPerPage; ?>">

                    <label>
                        <?= e(t('admin.roadmaps.order')); ?>
                        <input type="number" min="1" step="1" name="order" required value="<?= e((string) $selectedOrder); ?>">
                    </label>

                    <label>
                        <?= e(t('admin.roadmaps.topic')); ?>
                        <input type="text" name="topic_title" required value="<?= e($selectedTopicTitle); ?>">
                    </label>

                    <div class="md:col-span-2">
                        <label for="roadmap-outline-content"><?= e(t('admin.roadmaps.outline')); ?></label>
                        <?= render_bbcode_editor('outline_content', $selectedOutlineContent, ['id' => 'roadmap-outline-content', 'rows' => 4, 'placeholder' => t('admin.roadmaps.outline_placeholder')]); ?>
                    </div>

                    <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e($editingRoadmap ? t('admin.roadmaps.update') : t('admin.roadmaps.create')); ?></button>
                        <?php if ($editingRoadmap): ?>
                            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage, 'roadmap_per_page' => $roadmapPerPage])); ?>"><?= e(t('admin.common.cancel_edit')); ?></a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>
        <?php endif; ?>

        <article
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="roadmaps-academic"
            data-ajax-page-param="roadmap_page"
            data-ajax-search-param="search"
        >
            <h3><?= e(t('admin.roadmaps.list')); ?></h3>
            <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
                <label class="relative w-full max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="<?= e(t('admin.roadmaps.search_placeholder')); ?>" autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </label>
                <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.roadmaps.showing_rows', ['shown' => (int) count($roadmaps), 'total' => (int) $roadmapTotal])); ?></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                    <thead>
                        <tr>
                            <th><?= e(t('admin.roadmaps.order_short')); ?></th>
                            <th><?= e(t('admin.roadmaps.topic')); ?></th>
                            <th><?= e(t('admin.roadmaps.outline')); ?></th>
                            <th><?= e(t('admin.roadmaps.used')); ?></th>
                            <th><?= e(t('admin.common.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody data-ajax-tbody="1">
                        <?php if (empty($roadmaps)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.roadmaps.empty')); ?></div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roadmaps as $roadmap): ?>
                                <?php
                                $roadmapId = (int) ($roadmap['id'] ?? 0);
                                $lessonCount = max(0, (int) ($roadmap['lesson_count'] ?? 0));
                                $outlineText = trim((string) ($roadmap['outline_content'] ?? ''));
                                ?>
                                <tr>
                                    <td>#<?= (int) ($roadmap['order'] ?? 0); ?></td>
                                    <td>
                                        <div class="font-semibold text-slate-800"><?= e((string) ($roadmap['topic_title'] ?? t('admin.roadmaps.topic_fallback', ['id' => $roadmapId]))); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($outlineText === ''): ?>
                                            <span class="text-slate-400">-</span>
                                        <?php else: ?>
                                            <div class="bbcode-content"><?= bbcode_to_html($outlineText); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"><?= e(t('admin.courses.lesson_count', ['count' => $lessonCount])); ?></span>
                                    </td>
                                    <td>
                                        <span class="inline-flex flex-wrap items-center gap-2">
                                            <?php if ($canUpdateRoadmap): ?>
                                                <a
                                                    href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'edit' => $roadmapId, 'roadmap_page' => $roadmapPage, 'roadmap_per_page' => $roadmapPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"
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

                                            <?php if ($canDeleteRoadmap): ?>
                                                <form class="inline-block" method="post" action="/api/roadmaps/delete?id=<?= $roadmapId; ?>&course_id=<?= (int) $selectedCourseId; ?>&roadmap_page=<?= (int) $roadmapPage; ?>&roadmap_per_page=<?= (int) $roadmapPerPage; ?>&search=<?= urlencode($searchQuery); ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.roadmaps.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
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
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($roadmapTotal > 0): ?>
                    <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                            <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.roadmaps.page_info', ['current' => (int) $roadmapPage, 'total' => (int) $roadmapTotalPages, 'count' => (int) $roadmapTotal])); ?></span>
                            <div class="ml-auto inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('roadmaps-academic')); ?>">
                                    <input type="hidden" name="page" value="roadmaps-academic">
                                    <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                    <label class="text-[11px] font-semibold text-slate-500" for="roadmap-per-page"><?= e(t('admin.common.rows')); ?></label>
                                    <select id="roadmap-per-page" name="roadmap_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                        <?php foreach ($roadmapPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $roadmapPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <?php if ($roadmapPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage - 1, 'roadmap_per_page' => $roadmapPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                                <?php endif; ?>

                                <?php if ($roadmapPage < $roadmapTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('roadmaps-academic', ['course_id' => $selectedCourseId, 'roadmap_page' => $roadmapPage + 1, 'roadmap_per_page' => $roadmapPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.next')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.next')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>
</div>

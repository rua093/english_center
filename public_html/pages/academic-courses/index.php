<?php
require_permission('academic.courses.view');

$academicModel = new AcademicModel();
$coursePage = max(1, (int) ($_GET['course_page'] ?? 1));
$coursePerPage = ui_pagination_resolve_per_page('course_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$courseTotal = $academicModel->countCourses($searchQuery);
$courseTotalPages = max(1, (int) ceil($courseTotal / $coursePerPage));
if ($coursePage > $courseTotalPages) {
    $coursePage = $courseTotalPages;
}

$courses = $academicModel->listCoursesPage($coursePage, $coursePerPage, $searchQuery);
$coursePerPageOptions = ui_pagination_per_page_options();

$editingCourse = null;
if (!empty($_GET['edit'])) {
    $editingCourse = $academicModel->findCourse((int) $_GET['edit']);
}

$module = 'courses';
$adminTitle = t('admin.courses.title');

$success = get_flash('success');
$error = get_flash('error');

$canCreateCourse = has_permission('academic.courses.create');
$canUpdateCourse = has_permission('academic.courses.update');
$canDeleteCourse = has_permission('academic.courses.delete');

$selectedCourseName = trim((string) ($editingCourse['course_name'] ?? ''));
$selectedDescription = trim((string) ($editingCourse['description'] ?? ''));
$selectedBasePrice = (float) ($editingCourse['base_price'] ?? 0);
$selectedTotalSessions = max(0, (int) ($editingCourse['total_sessions'] ?? 0));
$selectedThumbnailUrl = normalize_public_file_url((string) ($editingCourse['image_thumbnail'] ?? ''));
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <?php if ($canCreateCourse || $canUpdateCourse): ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e($editingCourse ? t('admin.courses.edit') : t('admin.courses.add')); ?></h3>
            <form class="grid gap-3 md:grid-cols-2" method="post" action="/api/courses/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingCourse['id'] ?? 0); ?>">
                <input type="hidden" name="course_page" value="<?= (int) $coursePage; ?>">
                <input type="hidden" name="course_per_page" value="<?= (int) $coursePerPage; ?>">
                <input type="hidden" name="existing_image_thumbnail" value="<?= e((string) ($editingCourse['image_thumbnail'] ?? '')); ?>">

                <label>
                    <?= e(t('admin.courses.course_name')); ?>
                    <input type="text" name="course_name" required value="<?= e($selectedCourseName); ?>">
                </label>

                <label>
                    <?= e(t('admin.courses.total_sessions')); ?>
                    <input type="number" min="0" step="1" name="total_sessions" value="<?= e((string) $selectedTotalSessions); ?>">
                </label>

                <label>
                    <?= e(t('admin.courses.base_price')); ?>
                    <input type="number" min="0" step="0.01" name="base_price" value="<?= e(number_format($selectedBasePrice, 2, '.', '')); ?>">
                </label>

                <div class="md:col-span-2">
                    <label for="course-description"><?= e(t('admin.courses.description')); ?></label>
                    <?= render_bbcode_editor('description', $selectedDescription, ['id' => 'course-description', 'rows' => 4, 'placeholder' => t('admin.courses.description_placeholder')]); ?>
                </div>

                <label class="md:col-span-2">
                    <?= e(t('admin.courses.thumbnail')); ?>
                    <input type="file" name="course_thumbnail" accept=".jpg,.jpeg,.png,.gif,.webp">
                </label>
                <?php if ($selectedThumbnailUrl !== ''): ?>
                    <div class="md:col-span-2 text-xs text-slate-500">
                        <?= e(t('admin.courses.current_image')); ?>:
                        <a class="font-semibold text-blue-700 hover:underline" href="<?= e($selectedThumbnailUrl); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.courses.open_image')); ?></a>.
                        <?= e(t('admin.courses.replace_image_hint')); ?>
                    </div>
                <?php endif; ?>

                <div class="md:col-span-2 inline-flex flex-wrap items-center gap-2">
                    <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e($editingCourse ? t('admin.courses.update') : t('admin.courses.create')); ?></button>
                    <?php if ($editingCourse): ?>
                        <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('courses-academic', ['course_page' => $coursePage, 'course_per_page' => $coursePerPage])); ?>"><?= e(t('admin.common.cancel_edit')); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </article>
    <?php endif; ?>

    <article
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        data-ajax-table-root="1"
        data-ajax-page-key="page"
        data-ajax-page-value="courses-academic"
        data-ajax-page-param="course_page"
        data-ajax-search-param="search"
    >
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h3><?= e(t('admin.courses.list')); ?></h3>
            <a class="<?= ui_btn_secondary_classes('sm'); ?>" href="<?= e(page_url('roadmaps-academic')); ?>"><?= e(t('admin.courses.manage_roadmaps')); ?></a>
        </div>

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
                    placeholder="<?= e(t('admin.courses.search_placeholder')); ?>"
                    autocomplete="off"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                >
            </label>
            <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.courses.showing_rows', ['shown' => (int) count($courses), 'total' => (int) $courseTotal])); ?></span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr>
                        <th><?= e(t('admin.courses.course_name')); ?></th>
                        <th><?= e(t('admin.courses.total_lessons')); ?></th>
                        <th><?= e(t('admin.courses.base_price')); ?></th>
                        <th><?= e(t('admin.courses.roadmap')); ?></th>
                        <th><?= e(t('admin.classes.list')); ?></th>
                        <th><?= e(t('admin.common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.courses.empty')); ?></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <?php
                            $courseId = (int) ($course['id'] ?? 0);
                            $courseName = trim((string) ($course['course_name'] ?? t('admin.courses.course_fallback', ['id' => $courseId])));
                            $basePriceText = number_format((float) ($course['base_price'] ?? 0), 0, ',', '.');
                            $roadmapCount = max(0, (int) ($course['roadmap_count'] ?? 0));
                            $classCount = max(0, (int) ($course['class_count'] ?? 0));
                            ?>
                            <tr>
                                <td>
                                    <?php $courseThumbUrl = normalize_public_file_url((string) ($course['image_thumbnail'] ?? '')); ?>
                                    <?php if ($courseThumbUrl !== ''): ?>
                                        <a class="mb-2 inline-flex" href="<?= e($courseThumbUrl); ?>" target="_blank" rel="noopener noreferrer">
                                            <img class="h-12 w-16 rounded-md border border-slate-200 object-cover" src="<?= e($courseThumbUrl); ?>" alt="<?= e(t('admin.courses.thumbnail_alt')); ?>">
                                        </a>
                                    <?php endif; ?>
                                    <div class="font-semibold text-slate-800"><?= e($courseName); ?></div>
                                    <?php if (!empty($course['description'])): ?>
                                        <div class="mt-1 text-xs text-slate-500 bbcode-content"><?= bbcode_to_html((string) $course['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(t('admin.courses.lesson_count', ['count' => (int) ($course['total_sessions'] ?? 0)])); ?></td>
                                <td><?= e($basePriceText); ?> <?= e(t('admin.courses.currency_vnd')); ?></td>
                                <td>
                                    <a class="text-xs font-semibold text-blue-700 hover:underline" href="<?= e(page_url('roadmaps-academic', ['course_id' => $courseId])); ?>">
                                        <?= e(t('admin.courses.topic_count', ['count' => (int) $roadmapCount])); ?>
                                    </a>
                                </td>
                                <td><?= e(t('admin.courses.class_count', ['count' => (int) $classCount])); ?></td>
                                <td>
                                    <span class="inline-flex flex-wrap items-center gap-2">
                                        <a
                                            href="<?= e(page_url('roadmaps-academic', ['course_id' => $courseId])); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.courses.roadmap')); ?>"
                                            aria-label="<?= e(t('admin.courses.roadmap')); ?>"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.courses.roadmap')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M3 12h18"></path><path d="M3 18h18"></path><circle cx="7" cy="6" r="1.5"></circle><circle cx="12" cy="12" r="1.5"></circle><circle cx="17" cy="18" r="1.5"></circle></svg>
                                            </span>
                                        </a>
                                        <?php if ($canUpdateCourse): ?>
                                            <a
                                                href="<?= e(page_url('courses-academic', ['edit' => $courseId, 'course_page' => $coursePage, 'course_per_page' => $coursePerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"
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

                                        <?php if ($canDeleteCourse): ?>
                                            <form class="inline-block" method="post" action="/api/courses/delete?id=<?= $courseId; ?>&course_page=<?= (int) $coursePage; ?>&course_per_page=<?= (int) $coursePerPage; ?>&search=<?= urlencode($searchQuery); ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.courses.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
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
            <?php if ($courseTotal > 0): ?>
                <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.courses.page_info', ['current' => (int) $coursePage, 'total' => (int) $courseTotalPages, 'count' => (int) $courseTotal])); ?></span>
                        <div class="ml-auto inline-flex items-center gap-1.5">
                            <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('courses-academic')); ?>">
                                <input type="hidden" name="page" value="courses-academic">
                                <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                <label class="text-[11px] font-semibold text-slate-500" for="course-per-page"><?= e(t('admin.common.rows')); ?></label>
                                <select id="course-per-page" name="course_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                    <?php foreach ($coursePerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $coursePerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <?php if ($coursePage > 1): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('courses-academic', ['course_page' => $coursePage - 1, 'course_per_page' => $coursePerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                            <?php else: ?>
                                <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                            <?php endif; ?>

                            <?php if ($coursePage < $courseTotalPages): ?>
                                <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('courses-academic', ['course_page' => $coursePage + 1, 'course_per_page' => $coursePerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.next')); ?></a>
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

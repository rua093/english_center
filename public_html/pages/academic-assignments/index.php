<?php
require_permission('academic.assignments.view');
require_once __DIR__ . '/../../core/file_storage.php';

$academicModel = new AcademicModel();
$assignmentPage = max(1, (int) ($_GET['assignment_page'] ?? 1));
$assignmentPerPage = ui_pagination_resolve_per_page('assignment_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$assignmentTotal = $academicModel->countAssignments($searchQuery);
$assignmentTotalPages = max(1, (int) ceil($assignmentTotal / $assignmentPerPage));
if ($assignmentPage > $assignmentTotalPages) {
    $assignmentPage = $assignmentTotalPages;
}
$assignments = $academicModel->listAssignmentsPage($assignmentPage, $assignmentPerPage, $searchQuery);
$assignmentPerPageOptions = ui_pagination_per_page_options();
$lessons = $academicModel->assignmentLookups();

$editingAssignment = null;
if (!empty($_GET['edit'])) {
    $editingAssignment = $academicModel->findAssignment((int) $_GET['edit']);
}

$editingAssignmentFileUrl = normalize_public_file_url((string) ($editingAssignment['file_url'] ?? ''));

$assignmentClasses = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($classId <= 0 || isset($assignmentClasses[$classId])) {
        continue;
    }

    $assignmentClasses[$classId] = [
        'id' => $classId,
        'class_name' => (string) ($lesson['class_name'] ?? t('admin.assignment_edit.class_fallback', ['id' => $classId])),
    ];
}

$selectedAssignmentClassId = 0;
$selectedAssignmentScheduleId = 0;
if (is_array($editingAssignment)) {
    $selectedAssignmentScheduleId = (int) ($editingAssignment['schedule_id'] ?? 0);
    foreach ($lessons as $lesson) {
        if ((int) ($lesson['id'] ?? 0) !== $selectedAssignmentScheduleId) {
            continue;
        }

        $selectedAssignmentClassId = (int) ($lesson['class_id'] ?? 0);
        break;
    }
} else {
    $requestedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
    $requestedScheduleId = max(0, (int) ($_GET['schedule_id'] ?? 0));

    if ($requestedClassId > 0 && isset($assignmentClasses[$requestedClassId])) {
        $selectedAssignmentClassId = $requestedClassId;
    }

    if ($requestedScheduleId > 0) {
        foreach ($lessons as $lesson) {
            if ((int) ($lesson['id'] ?? 0) !== $requestedScheduleId) {
                continue;
            }

            $lessonClassId = (int) ($lesson['class_id'] ?? 0);
            if ($selectedAssignmentClassId > 0 && $lessonClassId !== $selectedAssignmentClassId) {
                break;
            }

            $selectedAssignmentClassId = $lessonClassId;
            $selectedAssignmentScheduleId = $requestedScheduleId;
            break;
        }
    }
}

$module = 'assignments';
$adminTitle = t('admin.assignments.title');

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');
$canDeleteAssignment = has_permission('academic.assignments.delete');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($canCreateAssignment || $canUpdateAssignment): ?>
        <article class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e($editingAssignment ? t('admin.assignments.edit') : t('admin.assignments.add')); ?></h3>
            <form class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingAssignment['id'] ?? 0); ?>">
                <input type="hidden" name="existing_file_url" value="<?= e((string) ($editingAssignment['file_url'] ?? '')); ?>">
                <label>
                    <?= e(t('admin.assignment_edit.class')); ?>
                    <select id="assignment-class-select" name="class_id" required>
                        <option value=""><?= e(t('admin.assignment_edit.choose_class')); ?></option>
                        <?php foreach ($assignmentClasses as $assignmentClass): ?>
                            <option value="<?= (int) $assignmentClass['id']; ?>" <?= $selectedAssignmentClassId === (int) $assignmentClass['id'] ? 'selected' : ''; ?>><?= e((string) $assignmentClass['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.assignment_edit.lesson')); ?>
                    <select id="assignment-lesson-select" name="schedule_id" required>
                        <option value=""><?= e(t('admin.assignment_edit.choose_lesson')); ?></option>
                        <?php foreach ($lessons as $lesson): ?>
                            <?php
                            $title = trim((string) ($lesson['actual_title'] ?? ''));
                            if ($title === '') {
                                $title = t('admin.assignment_edit.lesson_fallback', [
                                    'date' => ui_format_date((string) ($lesson['study_date'] ?? ''), (string) ($lesson['study_date'] ?? '')),
                                    'time' => (string) ($lesson['start_time'] ?? ''),
                                ]);
                            }
                            ?>
                            <option data-class-id="<?= (int) ($lesson['class_id'] ?? 0); ?>" value="<?= (int) $lesson['id']; ?>" <?= $selectedAssignmentScheduleId === (int) $lesson['id'] ? 'selected' : ''; ?>><?= e($title); ?> - <?= e((string) $lesson['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.assignment_edit.assignment_title')); ?>
                    <input type="text" name="title" required value="<?= e((string) ($editingAssignment['title'] ?? '')); ?>">
                </label>
                <label>
                    <?= e(t('admin.assignment_edit.description')); ?>
                    <textarea name="description" rows="4"><?= e((string) ($editingAssignment['description'] ?? '')); ?></textarea>
                </label>
                <label>
                    <?= e(t('admin.assignment_edit.deadline')); ?>
                    <input type="datetime-local" name="deadline" required value="<?= !empty($editingAssignment['deadline']) ? e(date('Y-m-d\TH:i', strtotime((string) $editingAssignment['deadline']))) : ''; ?>">
                </label>
                <label>
                    <?= e(t('admin.assignment_edit.upload_file')); ?>
                    <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
                </label>
                <?php if ($editingAssignmentFileUrl !== ''): ?>
                    <p class="text-xs text-slate-500"><?= e(t('admin.assignment_edit.current_file')); ?>: <a class="font-semibold text-blue-700 hover:underline" href="<?= e($editingAssignmentFileUrl); ?>" target="_blank" rel="noopener noreferrer"><?= e(t('admin.assignment_edit.open_file')); ?></a>. <?= e(t('admin.assignment_edit.replace_hint')); ?></p>
                <?php endif; ?>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.assignment_edit.save')); ?></button>
            </form>
        </article>
        <?php endif; ?>

        <article
            class="order-1 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="assignments-academic"
            data-ajax-page-param="assignment_page"
            data-ajax-search-param="search"
        >
            <h3><?= e(t('admin.assignments.list')); ?></h3>
            <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
                <label class="relative w-full max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="<?= e(t('admin.assignments.search_placeholder')); ?>" autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </label>
                <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.assignments.showing_rows', ['shown' => (int) count($assignments), 'total' => (int) $assignmentTotal])); ?></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr><th><?= e(t('admin.assignments.table_assignment')); ?></th><th><?= e(t('admin.assignment_edit.class')); ?></th><th><?= e(t('admin.assignment_edit.deadline')); ?></th><th><?= e(t('admin.common.actions')); ?></th></tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($assignments)): ?>
                        <tr><td colspan="4"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.assignments.empty')); ?></div></td></tr>
                    <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <?php $assignmentFileUrl = normalize_public_file_url((string) ($assignment['file_url'] ?? '')); ?>
                        <tr>
                            <td><?= e((string) $assignment['title']); ?></td>
                            <td><?= e((string) $assignment['class_name']); ?></td>
                            <td><?= e(ui_format_datetime((string) ($assignment['deadline'] ?? ''))); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($assignmentFileUrl !== ''): ?>
                                        <a
                                            href="<?= e($assignmentFileUrl); ?>"
                                            class="admin-action-icon-btn"
                                            data-action-kind="detail"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.assignment_edit.open_file')); ?>"
                                            aria-label="<?= e(t('admin.assignment_edit.open_file')); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.assignment_edit.open_file')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path></svg>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span
                                            class="admin-action-icon-btn"
                                            data-skip-action-icon="1"
                                            title="<?= e(t('admin.assignments.no_file')); ?>"
                                            aria-label="<?= e(t('admin.assignments.no_file')); ?>"
                                            aria-disabled="true"
                                            tabindex="-1"
                                            style="opacity: 0.35; pointer-events: none;"
                                        >
                                            <span class="admin-action-icon-label"><?= e(t('admin.assignments.no_file')); ?></span>
                                            <span class="admin-action-icon-glyph" aria-hidden="true">
                                                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M16 13H8"></path><path d="M16 17H8"></path><path d="M10 9H8"></path></svg>
                                            </span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($canUpdateAssignment): ?>
                                        <a
                                            href="<?= e(page_url('assignments-academic-edit', ['id' => (int) $assignment['id'], 'assignment_page' => $assignmentPage, 'assignment_per_page' => $assignmentPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"
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
                                    <?php if ($canDeleteAssignment): ?>
                                        <form class="inline-block" method="post" action="/api/assignments/delete?id=<?= (int) $assignment['id']; ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.assignments.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
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
                <?php if ($assignmentTotal > 0): ?>
                    <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                            <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.assignments.page_info', ['current' => (int) $assignmentPage, 'total' => (int) $assignmentTotalPages, 'count' => (int) $assignmentTotal])); ?></span>
                            <div class="ml-auto inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('assignments-academic')); ?>">
                                    <input type="hidden" name="page" value="assignments-academic">
                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                    <label class="text-[11px] font-semibold text-slate-500" for="assignment-per-page"><?= e(t('admin.common.rows')); ?></label>
                                    <select id="assignment-per-page" name="assignment_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                        <?php foreach ($assignmentPerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $assignmentPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($assignmentPage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('assignments-academic', ['assignment_page' => $assignmentPage - 1, 'assignment_per_page' => $assignmentPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                                <?php endif; ?>

                                <?php if ($assignmentPage < $assignmentTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('assignments-academic', ['assignment_page' => $assignmentPage + 1, 'assignment_per_page' => $assignmentPerPage, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.next')); ?></a>
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

<script>
(function () {
    const classSelect = document.getElementById('assignment-class-select');
    const lessonSelect = document.getElementById('assignment-lesson-select');
    if (!classSelect || !lessonSelect) {
        return;
    }

    const lessonOptions = Array.from(lessonSelect.querySelectorAll('option[data-class-id]')).map(function (option) {
        return {
            value: String(option.value || ''),
            classId: String(option.getAttribute('data-class-id') || ''),
            label: String(option.textContent || ''),
        };
    });

    const initialLessonValue = String(lessonSelect.value || '');

    function renderLessonOptions(preferredValue) {
        const selectedClassId = String(classSelect.value || '');
        lessonSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = <?= json_encode(t('admin.assignment_edit.choose_lesson'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        lessonSelect.appendChild(placeholder);

        const matchingLessons = selectedClassId === '' ? [] : lessonOptions.filter(function (lesson) {
            return lesson.classId === selectedClassId;
        });

        matchingLessons.forEach(function (lesson) {
            const option = document.createElement('option');
            option.value = lesson.value;
            option.textContent = lesson.label;
            lessonSelect.appendChild(option);
        });

        if (lessonSelect.tomselect) {
            lessonSelect.tomselect.sync();
        }

        if (selectedClassId === '' || matchingLessons.length === 0) {
            lessonSelect.value = '';
            lessonSelect.disabled = true;
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue('');
                lessonSelect.tomselect.disable();
            }
            return;
        }

        lessonSelect.disabled = false;
        if (lessonSelect.tomselect) {
            lessonSelect.tomselect.enable();
        }

        const safePreferredValue = String(preferredValue || '');
        if (safePreferredValue !== '' && matchingLessons.some(function (lesson) { return lesson.value === safePreferredValue; })) {
            lessonSelect.value = safePreferredValue;
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue(safePreferredValue);
            }
        } else {
            lessonSelect.value = '';
            if (lessonSelect.tomselect) {
                lessonSelect.tomselect.setValue('');
            }
        }
    }

    classSelect.addEventListener('change', function () {
        renderLessonOptions('');
    });

    renderLessonOptions(initialLessonValue);
})();
</script>





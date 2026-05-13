<?php
require_permission('academic.submissions.view');

$academicModel = new AcademicModel();
$selectedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
$selectedScheduleId = max(0, (int) ($_GET['schedule_id'] ?? 0));
$selectedAssignmentId = max(0, (int) ($_GET['assignment_id'] ?? 0));
$gradeStatus = trim((string) ($_GET['grade_status'] ?? 'all'));
if (!in_array($gradeStatus, ['pending', 'graded', 'all', 'missing'], true)) {
    $gradeStatus = 'all';
}

$lessons = $academicModel->assignmentLookups();
$assignments = $academicModel->listAssignments();

$classOptions = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($classId <= 0 || isset($classOptions[$classId])) {
        continue;
    }

    $classOptions[$classId] = [
        'id' => $classId,
        'class_name' => (string) ($lesson['class_name'] ?? t('admin.assignment_edit.class_fallback', ['id' => $classId])),
    ];
}

if ($selectedClassId > 0 && !isset($classOptions[$selectedClassId])) {
    $selectedClassId = 0;
    $selectedScheduleId = 0;
    $selectedAssignmentId = 0;
}

$scheduleOptions = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($selectedClassId > 0 && $classId !== $selectedClassId) {
        continue;
    }
    $scheduleOptions[] = $lesson;
}

$isValidSchedule = false;
foreach ($scheduleOptions as $lesson) {
    if ((int) ($lesson['id'] ?? 0) === $selectedScheduleId) {
        $isValidSchedule = true;
        break;
    }
}
if ($selectedScheduleId > 0 && !$isValidSchedule) {
    $selectedScheduleId = 0;
    $selectedAssignmentId = 0;
}

$assignmentOptions = [];
if ($selectedScheduleId > 0) {
    foreach ($assignments as $assignment) {
        if ((int) ($assignment['schedule_id'] ?? 0) !== $selectedScheduleId) {
            continue;
        }
        $assignmentOptions[] = $assignment;
    }
}

$isValidAssignment = false;
foreach ($assignmentOptions as $assignment) {
    if ((int) ($assignment['id'] ?? 0) === $selectedAssignmentId) {
        $isValidAssignment = true;
        break;
    }
}
if ($selectedAssignmentId > 0 && !$isValidAssignment) {
    $selectedAssignmentId = 0;
}

$statusLabels = [
    'pending' => t('admin.submissions.status_pending'),
    'graded' => t('admin.submissions.status_graded'),
    'missing' => t('admin.submissions.status_missing'),
    'all' => t('admin.submissions.status_all'),
];

$module = 'submissions';
$adminTitle = t('admin.submissions.title');

$success = get_flash('success');
$error = get_flash('error');

$canGradeSubmission = has_permission('academic.submissions.grade');

$lessonsJson = json_encode(array_values($lessons), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($lessonsJson)) {
    $lessonsJson = '[]';
}

$assignmentsJson = json_encode(array_values($assignments), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($assignmentsJson)) {
    $assignmentsJson = '[]';
}
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="space-y-1">
                <h3 class="text-xl font-semibold text-slate-900"><?= e(t('admin.submissions.filter_title')); ?></h3>
                <p class="text-sm text-slate-600"><?= e(t('admin.submissions.filter_description')); ?></p>
            </div>
            <button id="reset-grading-filter" type="button" class="inline-flex h-10 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"><?= e(t('admin.submissions.reset')); ?></button>
        </div>

        <div class="mt-5 grid gap-3 lg:grid-cols-2 2xl:grid-cols-4">
            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700"><?= e(t('admin.assignment_edit.class')); ?></span>
                <select id="grading-class-select" name="class_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                    <option value="0"><?= e(t('admin.assignment_edit.choose_class')); ?></option>
                    <?php foreach ($classOptions as $classOption): ?>
                        <option value="<?= (int) $classOption['id']; ?>" <?= $selectedClassId === (int) $classOption['id'] ? 'selected' : ''; ?>><?= e((string) $classOption['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700"><?= e(t('admin.assignment_edit.lesson')); ?></span>
                <select id="grading-lesson-select" name="schedule_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" <?= $selectedClassId > 0 ? '' : 'disabled'; ?>>
                    <option value="0"><?= e(t('admin.submissions.choose_lesson_short')); ?></option>
                    <?php foreach ($scheduleOptions as $lesson): ?>
                        <?php
                        $scheduleId = (int) ($lesson['id'] ?? 0);
                        $lessonTitle = (string) ($lesson['actual_title'] ?? t('admin.submissions.lesson_fallback', ['id' => $scheduleId]));
                        $lessonDate = trim((string) ($lesson['study_date'] ?? ''));
                        if ($lessonDate !== '') {
                            $lessonTitle .= ' (' . $lessonDate . ')';
                        }
                        ?>
                        <option value="<?= $scheduleId; ?>" <?= $selectedScheduleId === $scheduleId ? 'selected' : ''; ?>><?= e($lessonTitle); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700"><?= e(t('admin.assignments.table_assignment')); ?></span>
                <select id="grading-assignment-select" name="assignment_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" <?= $selectedScheduleId > 0 ? '' : 'disabled'; ?>>
                    <option value="0"><?= e(t('admin.submissions.choose_assignment')); ?></option>
                    <?php foreach ($assignmentOptions as $assignment): ?>
                        <?php
                        $assignmentId = (int) ($assignment['id'] ?? 0);
                        $assignmentTitle = (string) ($assignment['title'] ?? t('admin.submissions.assignment_fallback', ['id' => $assignmentId]));
                        $deadlineText = trim((string) ($assignment['deadline'] ?? ''));
                        if ($deadlineText !== '') {
                            $assignmentTitle .= ' - ' . t('admin.submissions.deadline_prefix') . ': ' . $deadlineText;
                        }
                        ?>
                        <option value="<?= $assignmentId; ?>" <?= $selectedAssignmentId === $assignmentId ? 'selected' : ''; ?>><?= e($assignmentTitle); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700"><?= e(t('admin.submissions.grade_status')); ?></span>
                <select id="grading-status-select" name="grade_status" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue); ?>" <?= $gradeStatus === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700"><?= e(t('admin.assignment_edit.class')); ?>: <span id="selected-class-label" class="ml-1"><?= e(t('admin.submissions.not_selected')); ?></span></span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700"><?= e(t('admin.assignment_edit.lesson')); ?>: <span id="selected-lesson-label" class="ml-1"><?= e(t('admin.submissions.not_selected')); ?></span></span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700"><?= e(t('admin.assignments.table_assignment')); ?>: <span id="selected-assignment-label" class="ml-1"><?= e(t('admin.submissions.not_selected')); ?></span></span>
        </div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3><?= e(t('admin.submissions.grading_list')); ?></h3>
            <div class="inline-flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-700"><?= e(t('admin.submissions.total_students')); ?>: <span id="summary-total" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 font-semibold text-blue-700"><?= e(t('admin.submissions.submitted')); ?>: <span id="summary-submitted" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 font-semibold text-amber-700"><?= e(t('admin.submissions.not_submitted')); ?>: <span id="summary-missing" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700"><?= e(t('admin.submissions.graded')); ?>: <span id="summary-graded" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 font-semibold text-rose-700"><?= e(t('admin.submissions.late')); ?>: <span id="summary-late" class="ml-1">0</span></span>
            </div>
        </div>

        <form id="batch-grade-form" class="mt-3 grid gap-3" method="post" action="/api/submissions/grade">
            <?= csrf_input(); ?>
            <input id="context-class-id" type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="context-lesson-id" type="hidden" name="schedule_id" value="<?= (int) $selectedScheduleId; ?>">
            <input id="context-assignment-id" type="hidden" name="assignment_id" value="<?= (int) $selectedAssignmentId; ?>">
            <input id="context-grade-status" type="hidden" name="grade_status" value="<?= e($gradeStatus); ?>">
            <input type="hidden" name="submission_page" value="1">
            <input type="hidden" name="submission_per_page" value="10">

            <?php if ($canGradeSubmission): ?>
                <div class="flex flex-wrap items-center gap-2">
                    <button id="select-all-submitted" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"><?= e(t('admin.submissions.select_all_submitted')); ?></button>
                    <button id="clear-selected-submitted" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"><?= e(t('admin.submissions.clear_selection')); ?></button>
                    <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit"><?= e(t('admin.submissions.save_selected')); ?></button>
                </div>
            <?php else: ?>
                <span class="inline-flex w-fit items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700"><?= e(t('admin.common.view_only')); ?></span>
            <?php endif; ?>

            <div id="grading-state-message" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-600"><?= e(t('admin.submissions.choose_all_to_load')); ?></div>
            <div id="grading-list" class="grid gap-3"></div>
        </form>
    </article>
</div>

<script>
(function () {
    const classSelect = document.getElementById('grading-class-select');
    const lessonSelect = document.getElementById('grading-lesson-select');
    const assignmentSelect = document.getElementById('grading-assignment-select');
    const statusSelect = document.getElementById('grading-status-select');
    const resetButton = document.getElementById('reset-grading-filter');
    const stateMessage = document.getElementById('grading-state-message');
    const gradingList = document.getElementById('grading-list');
    const batchForm = document.getElementById('batch-grade-form');
    const selectAllButton = document.getElementById('select-all-submitted');
    const clearSelectedButton = document.getElementById('clear-selected-submitted');

    if (!classSelect || !lessonSelect || !assignmentSelect || !statusSelect || !stateMessage || !gradingList || !batchForm) {
        return;
    }

    const contextClassId = document.getElementById('context-class-id');
    const contextLessonId = document.getElementById('context-lesson-id');
    const contextAssignmentId = document.getElementById('context-assignment-id');
    const contextGradeStatus = document.getElementById('context-grade-status');

    const selectedClassLabel = document.getElementById('selected-class-label');
    const selectedLessonLabel = document.getElementById('selected-lesson-label');
    const selectedAssignmentLabel = document.getElementById('selected-assignment-label');

    const summaryTotal = document.getElementById('summary-total');
    const summarySubmitted = document.getElementById('summary-submitted');
    const summaryMissing = document.getElementById('summary-missing');
    const summaryGraded = document.getElementById('summary-graded');
    const summaryLate = document.getElementById('summary-late');

    const lessons = <?= $lessonsJson; ?>;
    const assignments = <?= $assignmentsJson; ?>;
    const canGrade = <?= $canGradeSubmission ? 'true' : 'false'; ?>;
    const i18n = <?= json_encode([
        'graded' => t('admin.submissions.graded'),
        'pending' => t('admin.submissions.pending'),
        'notSubmitted' => t('admin.submissions.not_submitted'),
        'lessonFallback' => t('admin.submissions.lesson_fallback_js'),
        'assignmentFallback' => t('admin.submissions.assignment_fallback_js'),
        'deadlinePrefix' => t('admin.submissions.deadline_prefix'),
        'chooseLesson' => t('admin.assignment_edit.choose_lesson'),
        'chooseAssignment' => t('admin.submissions.choose_assignment'),
        'notSelected' => t('admin.submissions.not_selected'),
        'submittedAt' => t('admin.submissions.submitted_at'),
        'noDeadline' => t('admin.submissions.no_deadline'),
        'lateSubmission' => t('admin.submissions.late_submission'),
        'onTimeSubmission' => t('admin.submissions.on_time_submission'),
        'batchSelect' => t('admin.submissions.batch_select'),
        'score' => t('admin.submissions.score'),
        'scorePlaceholder' => t('admin.submissions.score_placeholder'),
        'comment' => t('admin.submissions.comment'),
        'commentPlaceholder' => t('admin.submissions.comment_placeholder'),
        'cannotGradeMissing' => t('admin.submissions.cannot_grade_missing'),
        'noSubmittedFile' => t('admin.submissions.no_submitted_file'),
        'openSubmittedFile' => t('admin.submissions.open_submitted_file'),
        'chooseClassToStart' => t('admin.submissions.choose_class_to_start'),
        'chooseLessonToContinue' => t('admin.submissions.choose_lesson_to_continue'),
        'chooseAssignmentToLoad' => t('admin.submissions.choose_assignment_to_load'),
        'loadingRoster' => t('admin.submissions.loading_roster'),
        'emptyRoster' => t('admin.submissions.empty_roster'),
        'emptyFiltered' => t('admin.submissions.empty_filtered'),
        'loadError' => t('admin.submissions.load_error'),
        'selectAtLeastOne' => t('admin.submissions.select_at_least_one'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    const state = {
        classId: <?= (int) $selectedClassId; ?>,
        scheduleId: <?= (int) $selectedScheduleId; ?>,
        assignmentId: <?= (int) $selectedAssignmentId; ?>,
        gradeStatus: <?= json_encode($gradeStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        roster: [],
        lastLoadedKey: '',
        loading: false,
        errorMessage: '',
        drafts: {},
    };

    const statusBadgeMeta = {
        graded: {
            label: i18n.graded,
            classes: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        },
        pending: {
            label: i18n.pending,
            classes: 'border-blue-200 bg-blue-50 text-blue-700',
        },
        missing: {
            label: i18n.notSubmitted,
            classes: 'border-amber-200 bg-amber-50 text-amber-700',
        },
    };

    function toInt(value) {
        const parsed = parseInt(String(value || '0'), 10);
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function normalizeText(value) {
        return String(value || '').trim();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    function updateHiddenContext() {
        if (contextClassId) {
            contextClassId.value = String(state.classId);
        }
        if (contextLessonId) {
            contextLessonId.value = String(state.scheduleId);
        }
        if (contextAssignmentId) {
            contextAssignmentId.value = String(state.assignmentId);
        }
        if (contextGradeStatus) {
            contextGradeStatus.value = state.gradeStatus;
        }
    }

    function syncUrlWithoutReload() {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', 'submissions-academic');

        if (state.classId > 0) {
            currentUrl.searchParams.set('class_id', String(state.classId));
        } else {
            currentUrl.searchParams.delete('class_id');
        }

        if (state.scheduleId > 0) {
            currentUrl.searchParams.set('schedule_id', String(state.scheduleId));
        } else {
            currentUrl.searchParams.delete('schedule_id');
        }

        if (state.assignmentId > 0) {
            currentUrl.searchParams.set('assignment_id', String(state.assignmentId));
        } else {
            currentUrl.searchParams.delete('assignment_id');
        }

        if (state.gradeStatus !== '') {
            currentUrl.searchParams.set('grade_status', state.gradeStatus);
        } else {
            currentUrl.searchParams.delete('grade_status');
        }

        window.history.replaceState({}, '', currentUrl.toString());
    }

    function lessonsByClass(classId) {
        return lessons.filter((lesson) => toInt(lesson.class_id) === classId);
    }

    function assignmentsBySchedule(scheduleId) {
        return assignments.filter((assignment) => toInt(assignment.schedule_id) === scheduleId);
    }

    function buildLessonLabel(lesson) {
        const title = normalizeText(lesson.actual_title || String(i18n.lessonFallback || '').replace(':id', String(toInt(lesson.id))));
        const lessonDate = normalizeText(lesson.lesson_date);
        return lessonDate === '' ? title : (title + ' (' + lessonDate + ')');
    }

    function buildAssignmentLabel(assignment) {
        const title = normalizeText(assignment.title || String(i18n.assignmentFallback || '').replace(':id', String(toInt(assignment.id))));
        const deadline = normalizeText(assignment.deadline);
        return deadline === '' ? title : (title + ' - ' + i18n.deadlinePrefix + ': ' + deadline);
    }

    function setSelectOptions(selectElement, options, placeholder, selectedValue, labelResolver) {
        const resolvedOptions = Array.isArray(options) ? options : [];
        selectElement.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '0';
        placeholderOption.textContent = placeholder;
        selectElement.appendChild(placeholderOption);

        resolvedOptions.forEach((option) => {
            const optionId = toInt(option.id);
            const optionElement = document.createElement('option');
            optionElement.value = String(optionId);
            optionElement.textContent = labelResolver(option);
            if (optionId === selectedValue) {
                optionElement.selected = true;
            }
            selectElement.appendChild(optionElement);
        });
    }

    function updateFilterSelects() {
        const currentClassId = toInt(classSelect.value);
        if (currentClassId !== state.classId) {
            state.classId = currentClassId;
        }

        const availableLessons = lessonsByClass(state.classId);
        if (!availableLessons.some((lesson) => toInt(lesson.id) === state.scheduleId)) {
            state.scheduleId = 0;
        }

        setSelectOptions(
            lessonSelect,
            availableLessons,
            i18n.chooseLesson,
            state.scheduleId,
            buildLessonLabel
        );
        lessonSelect.disabled = state.classId <= 0;

        const availableAssignments = assignmentsBySchedule(state.scheduleId);
        if (!availableAssignments.some((assignment) => toInt(assignment.id) === state.assignmentId)) {
            state.assignmentId = 0;
        }

        setSelectOptions(
            assignmentSelect,
            availableAssignments,
            i18n.chooseAssignment,
            state.assignmentId,
            buildAssignmentLabel
        );
        assignmentSelect.disabled = state.scheduleId <= 0;
    }

    function selectedText(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        if (!option) {
            return i18n.notSelected;
        }

        if (toInt(option.value) <= 0) {
            return i18n.notSelected;
        }

        return normalizeText(option.textContent || i18n.notSelected);
    }

    function updateSelectedLabels() {
        if (selectedClassLabel) {
            selectedClassLabel.textContent = selectedText(classSelect);
        }
        if (selectedLessonLabel) {
            selectedLessonLabel.textContent = selectedText(lessonSelect);
        }
        if (selectedAssignmentLabel) {
            selectedAssignmentLabel.textContent = selectedText(assignmentSelect);
        }
    }

    function setStateMessage(text, mode) {
        stateMessage.textContent = text;

        const classes = {
            info: 'rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-600',
            error: 'rounded-xl border border-rose-200 bg-rose-50 p-4 text-center text-sm text-rose-700',
        };

        stateMessage.className = classes[mode] || classes.info;
        stateMessage.style.display = '';
    }

    function hideStateMessage() {
        stateMessage.style.display = 'none';
    }

    function submissionIdOfRow(row) {
        return toInt(row.submission_id);
    }

    function hasSubmitted(row) {
        return submissionIdOfRow(row) > 0;
    }

    function submissionDeadlineText(row) {
        return normalizeText(row.assignment_deadline);
    }

    function isLateSubmission(row) {
        if (!hasSubmitted(row)) {
            return false;
        }

        return toInt(row.is_late_submission) === 1;
    }

    function ensureDraftKey(submissionId) {
        return String(submissionId);
    }

    function captureDraftsFromDom() {
        const rowNodes = gradingList.querySelectorAll('[data-submission-id]');
        rowNodes.forEach((rowNode) => {
            const submissionId = toInt(rowNode.getAttribute('data-submission-id') || '0');
            if (submissionId <= 0) {
                return;
            }

            const scoreInput = rowNode.querySelector('input[data-field="score"]');
            const commentInput = rowNode.querySelector('textarea[data-field="comment"]');
            const selectInput = rowNode.querySelector('input[data-field="selected"]');

            state.drafts[ensureDraftKey(submissionId)] = {
                score: scoreInput instanceof HTMLInputElement ? scoreInput.value : '',
                comment: commentInput instanceof HTMLTextAreaElement ? commentInput.value : '',
                selected: selectInput instanceof HTMLInputElement ? selectInput.checked : false,
            };
        });
    }

    function draftForRow(row) {
        const submissionId = submissionIdOfRow(row);
        if (submissionId <= 0) {
            return {
                score: '',
                comment: '',
                selected: false,
            };
        }

        const key = ensureDraftKey(submissionId);
        const existingDraft = state.drafts[key] || {};
        const fallbackScore = normalizeText(row.score);
        const fallbackComment = String(row.teacher_comment || '');

        return {
            score: typeof existingDraft.score === 'string' ? existingDraft.score : fallbackScore,
            comment: typeof existingDraft.comment === 'string' ? existingDraft.comment : fallbackComment,
            selected: !!existingDraft.selected,
        };
    }

    function resolvedStatus(row) {
        if (!hasSubmitted(row)) {
            return 'missing';
        }

        const draft = draftForRow(row);
        return normalizeText(draft.score) === '' ? 'pending' : 'graded';
    }

    function shouldShowRow(row) {
        const status = resolvedStatus(row);
        if (state.gradeStatus === 'all') {
            return true;
        }
        if (state.gradeStatus === 'graded') {
            return status === 'graded';
        }
        if (state.gradeStatus === 'missing') {
            return status === 'missing';
        }
        return status === 'pending' || status === 'missing';
    }

    function computeSummary() {
        const summary = {
            total: 0,
            submitted: 0,
            missing: 0,
            graded: 0,
            pending: 0,
            late: 0,
        };

        state.roster.forEach((row) => {
            summary.total += 1;
            if (!hasSubmitted(row)) {
                summary.missing += 1;
                return;
            }

            summary.submitted += 1;
            if (isLateSubmission(row)) {
                summary.late += 1;
            }

            if (resolvedStatus(row) === 'graded') {
                summary.graded += 1;
            } else {
                summary.pending += 1;
            }
        });

        return summary;
    }

    function updateSummaryBadges() {
        const summary = computeSummary();
        if (summaryTotal) {
            summaryTotal.textContent = String(summary.total);
        }
        if (summarySubmitted) {
            summarySubmitted.textContent = String(summary.submitted);
        }
        if (summaryMissing) {
            summaryMissing.textContent = String(summary.missing);
        }
        if (summaryGraded) {
            summaryGraded.textContent = String(summary.graded);
        }
        if (summaryLate) {
            summaryLate.textContent = String(summary.late);
        }
    }

    function statusBadge(status) {
        const meta = statusBadgeMeta[status] || statusBadgeMeta.pending;
        return '<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold ' + meta.classes + '">' + meta.label + '</span>';
    }

    function renderRow(row) {
        const submissionId = submissionIdOfRow(row);
        const status = resolvedStatus(row);
        const draft = draftForRow(row);
        const scoreValue = normalizeText(draft.score);
        const commentValue = String(draft.comment || '');
        const submittedAt = normalizeText(row.submitted_at) || i18n.notSubmitted;
        const deadline = submissionDeadlineText(row);
        const lateStatusBadge = hasSubmitted(row)
            ? (deadline === ''
                ? '<span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-semibold text-slate-600">' + escapeHtml(i18n.noDeadline) + '</span>'
                : (isLateSubmission(row)
                    ? '<span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 font-semibold text-rose-700">' + escapeHtml(i18n.lateSubmission) + '</span>'
                    : '<span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">' + escapeHtml(i18n.onTimeSubmission) + '</span>'))
            : '';
        const fileUrl = normalizeText(row.file_url);

        const canBatchGradeThisRow = canGrade && submissionId > 0;

        let gradingMarkup = '';
        if (canBatchGradeThisRow) {
            gradingMarkup = '' +
                '<div class="mt-3 grid gap-3 lg:grid-cols-[170px_1fr]">' +
                    '<div class="grid gap-2">' +
                        '<label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs font-semibold text-slate-700">' +
                            '<input data-field="selected" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" type="checkbox" name="selected_submission_ids[]" value="' + submissionId + '" ' + (draft.selected ? 'checked' : '') + '>' +
                            escapeHtml(i18n.batchSelect) +
                        '</label>' +
                        '<label class="text-xs font-semibold text-slate-600">' +
                            escapeHtml(i18n.score) +
                            '<input data-field="score" class="mt-1" type="number" min="0" max="10" step="0.1" name="score[' + submissionId + ']" value="' + escapeHtml(scoreValue) + '" placeholder="' + escapeHtml(i18n.scorePlaceholder) + '">' +
                        '</label>' +
                    '</div>' +
                    '<label class="text-xs font-semibold text-slate-600">' +
                        escapeHtml(i18n.comment) +
                        '<textarea data-field="comment" class="mt-1 min-h-[92px]" rows="3" name="teacher_comment[' + submissionId + ']" placeholder="' + escapeHtml(i18n.commentPlaceholder) + '">' + escapeHtml(commentValue) + '</textarea>' +
                    '</label>' +
                '</div>';
        } else if (submissionId <= 0) {
            gradingMarkup = '<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">' + escapeHtml(i18n.cannotGradeMissing) + '</div>';
        } else {
            gradingMarkup = '' +
                '<div class="mt-3 grid gap-2 lg:grid-cols-[140px_1fr]">' +
                    '<div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">' + escapeHtml(i18n.score) + ': ' + (scoreValue === '' ? '-' : escapeHtml(scoreValue)) + '</div>' +
                    '<div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">' + escapeHtml(i18n.comment) + ': ' + (commentValue === '' ? '-' : escapeHtml(commentValue)) + '</div>' +
                '</div>';
        }

        return '' +
            '<div class="rounded-xl border border-slate-200 bg-white p-4" data-submission-id="' + submissionId + '">' +
                '<div class="grid gap-2 lg:grid-cols-[minmax(220px,1fr)_auto] lg:items-start">' +
                    '<div class="space-y-2">' +
                        '<div class="text-lg font-semibold text-slate-900">' + escapeHtml(row.full_name || '') + '</div>' +
                        '<div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">' +
                            '<span>' + escapeHtml(i18n.submittedAt) + ': ' + escapeHtml(submittedAt) + '</span>' +
                            (deadline === '' ? '' : '<span>' + escapeHtml(i18n.deadlinePrefix) + ': ' + escapeHtml(deadline) + '</span>') +
                            lateStatusBadge +
                            (fileUrl === ''
                                ? '<span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-semibold text-slate-500">' + escapeHtml(i18n.noSubmittedFile) + '</span>'
                                : '<a class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 font-semibold text-blue-700 hover:bg-blue-100" href="' + escapeHtml(fileUrl) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(i18n.openSubmittedFile) + '</a>') +
                        '</div>' +
                    '</div>' +
                    '<div>' + statusBadge(status) + '</div>' +
                '</div>' +
                gradingMarkup +
            '</div>';
    }

    function renderRoster() {
        updateHiddenContext();
        updateSelectedLabels();
        updateSummaryBadges();

        gradingList.innerHTML = '';

        if (state.classId <= 0) {
            setStateMessage(i18n.chooseClassToStart, 'info');
            return;
        }

        if (state.scheduleId <= 0) {
            setStateMessage(i18n.chooseLessonToContinue, 'info');
            return;
        }

        if (state.assignmentId <= 0) {
            setStateMessage(i18n.chooseAssignmentToLoad, 'info');
            return;
        }

        if (state.loading) {
            setStateMessage(i18n.loadingRoster, 'info');
            return;
        }

        if (state.errorMessage !== '') {
            setStateMessage(state.errorMessage, 'error');
            return;
        }

        if (state.roster.length === 0) {
            setStateMessage(i18n.emptyRoster, 'info');
            return;
        }

        const visibleRows = state.roster.filter((row) => shouldShowRow(row));
        if (visibleRows.length === 0) {
            setStateMessage(i18n.emptyFiltered, 'info');
            return;
        }

        hideStateMessage();
        gradingList.innerHTML = visibleRows.map((row) => renderRow(row)).join('');
    }

    async function loadRoster() {
        updateHiddenContext();
        syncUrlWithoutReload();

        if (state.classId <= 0 || state.scheduleId <= 0 || state.assignmentId <= 0) {
            state.loading = false;
            state.errorMessage = '';
            state.lastLoadedKey = '';
            state.roster = [];
            renderRoster();
            return;
        }

        captureDraftsFromDom();

        const requestKey = String(state.classId) + ':' + String(state.assignmentId);
        if (state.lastLoadedKey === requestKey && !state.loading) {
            renderRoster();
            return;
        }

        state.loading = true;
        state.errorMessage = '';
        renderRoster();

        try {
            const endpoint = '/api/submissions/roster?class_id=' + encodeURIComponent(String(state.classId))
                + '&assignment_id=' + encodeURIComponent(String(state.assignmentId))
                + '&format=json';
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();
            if (!response.ok || !payload || payload.status !== 'success') {
                throw new Error(payload && payload.message ? payload.message : i18n.loadError);
            }

            state.roster = Array.isArray(payload.data && payload.data.rows) ? payload.data.rows : [];
            state.lastLoadedKey = requestKey;
            state.loading = false;
            state.errorMessage = '';
            renderRoster();
        } catch (error) {
            state.loading = false;
            state.roster = [];
            state.lastLoadedKey = '';
            state.errorMessage = error instanceof Error ? error.message : i18n.loadError;
            renderRoster();
        }
    }

    function syncStateFromControls() {
        state.classId = toInt(classSelect.value);
        state.scheduleId = toInt(lessonSelect.value);
        state.assignmentId = toInt(assignmentSelect.value);
        state.gradeStatus = normalizeText(statusSelect.value) || 'all';
        updateHiddenContext();
        syncUrlWithoutReload();
    }

    classSelect.addEventListener('change', () => {
        captureDraftsFromDom();
        state.classId = toInt(classSelect.value);
        state.scheduleId = 0;
        state.assignmentId = 0;
        state.lastLoadedKey = '';
        state.roster = [];
        state.errorMessage = '';
        updateFilterSelects();
        syncStateFromControls();
        renderRoster();
    });

    lessonSelect.addEventListener('change', () => {
        captureDraftsFromDom();
        state.scheduleId = toInt(lessonSelect.value);
        state.assignmentId = 0;
        state.lastLoadedKey = '';
        state.roster = [];
        state.errorMessage = '';
        updateFilterSelects();
        syncStateFromControls();
        renderRoster();
    });

    assignmentSelect.addEventListener('change', () => {
        captureDraftsFromDom();
        state.assignmentId = toInt(assignmentSelect.value);
        state.lastLoadedKey = '';
        state.roster = [];
        state.errorMessage = '';
        syncStateFromControls();
        loadRoster();
    });

    statusSelect.addEventListener('change', () => {
        captureDraftsFromDom();
        state.gradeStatus = normalizeText(statusSelect.value) || 'all';
        syncStateFromControls();
        renderRoster();
    });

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            captureDraftsFromDom();
            state.classId = 0;
            state.scheduleId = 0;
            state.assignmentId = 0;
            state.gradeStatus = 'all';
            state.lastLoadedKey = '';
            state.roster = [];
            state.errorMessage = '';
            state.drafts = {};

            classSelect.value = '0';
            statusSelect.value = 'all';
            updateFilterSelects();
            syncStateFromControls();
            renderRoster();
        });
    }

    if (selectAllButton) {
        selectAllButton.addEventListener('click', () => {
            const checkboxes = gradingList.querySelectorAll('input[data-field="selected"]');
            checkboxes.forEach((checkbox) => {
                if (checkbox instanceof HTMLInputElement && !checkbox.disabled) {
                    checkbox.checked = true;
                }
            });
            captureDraftsFromDom();
        });
    }

    if (clearSelectedButton) {
        clearSelectedButton.addEventListener('click', () => {
            const checkboxes = gradingList.querySelectorAll('input[data-field="selected"]');
            checkboxes.forEach((checkbox) => {
                if (checkbox instanceof HTMLInputElement) {
                    checkbox.checked = false;
                }
            });
            captureDraftsFromDom();
        });
    }

    batchForm.addEventListener('submit', (event) => {
        if (!canGrade) {
            return;
        }

        captureDraftsFromDom();
        syncStateFromControls();

        const selectedRows = batchForm.querySelectorAll('input[name="selected_submission_ids[]"]:checked');
        if (selectedRows.length === 0) {
            event.preventDefault();
            setStateMessage(i18n.selectAtLeastOne, 'error');
        }
    });

    updateFilterSelects();
    statusSelect.value = state.gradeStatus;
    syncStateFromControls();
    loadRoster();
})();
</script>





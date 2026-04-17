<?php
require_permission('academic.submissions.view');

$academicModel = new AcademicModel();
$selectedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
$selectedLessonId = max(0, (int) ($_GET['lesson_id'] ?? 0));
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
        'class_name' => (string) ($lesson['class_name'] ?? ('Lớp #' . $classId)),
    ];
}

if ($selectedClassId > 0 && !isset($classOptions[$selectedClassId])) {
    $selectedClassId = 0;
    $selectedLessonId = 0;
    $selectedAssignmentId = 0;
}

$lessonOptions = [];
foreach ($lessons as $lesson) {
    $classId = (int) ($lesson['class_id'] ?? 0);
    if ($selectedClassId > 0 && $classId !== $selectedClassId) {
        continue;
    }
    $lessonOptions[] = $lesson;
}

$isValidLesson = false;
foreach ($lessonOptions as $lesson) {
    if ((int) ($lesson['id'] ?? 0) === $selectedLessonId) {
        $isValidLesson = true;
        break;
    }
}
if ($selectedLessonId > 0 && !$isValidLesson) {
    $selectedLessonId = 0;
    $selectedAssignmentId = 0;
}

$assignmentOptions = [];
if ($selectedLessonId > 0) {
    foreach ($assignments as $assignment) {
        if ((int) ($assignment['lesson_id'] ?? 0) !== $selectedLessonId) {
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
    'pending' => 'Cần xử lý',
    'graded' => 'Đã chấm',
    'missing' => 'Chưa nộp',
    'all' => 'Tất cả',
];

$module = 'submissions';
$adminTitle = 'Học vụ - Bài nộp';

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
                <h3 class="text-xl font-semibold text-slate-900">Bộ lọc chấm điểm</h3>
                <p class="text-sm text-slate-600">Giao diện gọn, cập nhật dữ liệu ngay khi đổi bộ lọc, không tải lại toàn trang.</p>
            </div>
            <button id="reset-grading-filter" type="button" class="inline-flex h-10 items-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đặt lại</button>
        </div>

        <div class="mt-5 grid gap-3 lg:grid-cols-2 2xl:grid-cols-4">
            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700">Lớp học</span>
                <select id="grading-class-select" name="class_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                    <option value="0">-- Chọn lớp --</option>
                    <?php foreach ($classOptions as $classOption): ?>
                        <option value="<?= (int) $classOption['id']; ?>" <?= $selectedClassId === (int) $classOption['id'] ? 'selected' : ''; ?>><?= e((string) $classOption['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700">Buổi học</span>
                <select id="grading-lesson-select" name="lesson_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" <?= $selectedClassId > 0 ? '' : 'disabled'; ?>>
                    <option value="0">-- Chọn buổi --</option>
                    <?php foreach ($lessonOptions as $lesson): ?>
                        <?php
                        $lessonId = (int) ($lesson['id'] ?? 0);
                        $lessonTitle = (string) ($lesson['actual_title'] ?? ('Buổi #' . $lessonId));
                        $lessonDate = trim((string) ($lesson['lesson_date'] ?? ''));
                        if ($lessonDate !== '') {
                            $lessonTitle .= ' (' . $lessonDate . ')';
                        }
                        ?>
                        <option value="<?= $lessonId; ?>" <?= $selectedLessonId === $lessonId ? 'selected' : ''; ?>><?= e($lessonTitle); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700">Bài tập</span>
                <select id="grading-assignment-select" name="assignment_id" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" <?= $selectedLessonId > 0 ? '' : 'disabled'; ?>>
                    <option value="0">-- Chọn bài tập --</option>
                    <?php foreach ($assignmentOptions as $assignment): ?>
                        <?php
                        $assignmentId = (int) ($assignment['id'] ?? 0);
                        $assignmentTitle = (string) ($assignment['title'] ?? ('Bài tập #' . $assignmentId));
                        $deadlineText = trim((string) ($assignment['deadline'] ?? ''));
                        if ($deadlineText !== '') {
                            $assignmentTitle .= ' - Hạn: ' . $deadlineText;
                        }
                        ?>
                        <option value="<?= $assignmentId; ?>" <?= $selectedAssignmentId === $assignmentId ? 'selected' : ''; ?>><?= e($assignmentTitle); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <span class="block text-sm font-semibold text-slate-700">Trạng thái chấm</span>
                <select id="grading-status-select" name="grade_status" class="mt-2 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-base font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                        <option value="<?= e($statusValue); ?>" <?= $gradeStatus === $statusValue ? 'selected' : ''; ?>><?= e($statusLabel); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700">Lớp: <span id="selected-class-label" class="ml-1">Chưa chọn</span></span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700">Buổi: <span id="selected-lesson-label" class="ml-1">Chưa chọn</span></span>
            <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 font-semibold text-slate-700">Bài tập: <span id="selected-assignment-label" class="ml-1">Chưa chọn</span></span>
        </div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3>Danh sách chấm điểm</h3>
            <div class="inline-flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-semibold text-slate-700">Tổng học viên: <span id="summary-total" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 font-semibold text-blue-700">Đã nộp: <span id="summary-submitted" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 font-semibold text-amber-700">Chưa nộp: <span id="summary-missing" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700">Đã chấm: <span id="summary-graded" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 font-semibold text-rose-700">Nộp trễ: <span id="summary-late" class="ml-1">0</span></span>
            </div>
        </div>

        <form id="batch-grade-form" class="mt-3 grid gap-3" method="post" action="/api/submissions/grade">
            <?= csrf_input(); ?>
            <input id="context-class-id" type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="context-lesson-id" type="hidden" name="lesson_id" value="<?= (int) $selectedLessonId; ?>">
            <input id="context-assignment-id" type="hidden" name="assignment_id" value="<?= (int) $selectedAssignmentId; ?>">
            <input id="context-grade-status" type="hidden" name="grade_status" value="<?= e($gradeStatus); ?>">
            <input type="hidden" name="submission_page" value="1">
            <input type="hidden" name="submission_per_page" value="10">

            <?php if ($canGradeSubmission): ?>
                <div class="flex flex-wrap items-center gap-2">
                    <button id="select-all-submitted" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Chọn tất cả đã nộp</button>
                    <button id="clear-selected-submitted" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Bỏ chọn</button>
                    <button class="<?= ui_btn_primary_classes('sm'); ?>" type="submit">Lưu các dòng đã chọn</button>
                </div>
            <?php else: ?>
                <span class="inline-flex w-fit items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">Bạn chỉ có quyền xem.</span>
            <?php endif; ?>

            <div id="grading-state-message" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-600">Chọn lớp, buổi học và bài tập để tải danh sách chấm điểm.</div>
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

    const state = {
        classId: <?= (int) $selectedClassId; ?>,
        lessonId: <?= (int) $selectedLessonId; ?>,
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
            label: 'Đã chấm',
            classes: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        },
        pending: {
            label: 'Chưa chấm',
            classes: 'border-blue-200 bg-blue-50 text-blue-700',
        },
        missing: {
            label: 'Chưa nộp',
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
            contextLessonId.value = String(state.lessonId);
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

        if (state.lessonId > 0) {
            currentUrl.searchParams.set('lesson_id', String(state.lessonId));
        } else {
            currentUrl.searchParams.delete('lesson_id');
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

    function assignmentsByLesson(lessonId) {
        return assignments.filter((assignment) => toInt(assignment.lesson_id) === lessonId);
    }

    function buildLessonLabel(lesson) {
        const title = normalizeText(lesson.actual_title || ('Buổi #' + toInt(lesson.id)));
        const lessonDate = normalizeText(lesson.lesson_date);
        return lessonDate === '' ? title : (title + ' (' + lessonDate + ')');
    }

    function buildAssignmentLabel(assignment) {
        const title = normalizeText(assignment.title || ('Bài tập #' + toInt(assignment.id)));
        const deadline = normalizeText(assignment.deadline);
        return deadline === '' ? title : (title + ' - Hạn: ' + deadline);
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
        if (!availableLessons.some((lesson) => toInt(lesson.id) === state.lessonId)) {
            state.lessonId = 0;
        }

        setSelectOptions(
            lessonSelect,
            availableLessons,
            '-- Chọn buổi học --',
            state.lessonId,
            buildLessonLabel
        );
        lessonSelect.disabled = state.classId <= 0;

        const availableAssignments = assignmentsByLesson(state.lessonId);
        if (!availableAssignments.some((assignment) => toInt(assignment.id) === state.assignmentId)) {
            state.assignmentId = 0;
        }

        setSelectOptions(
            assignmentSelect,
            availableAssignments,
            '-- Chọn bài tập --',
            state.assignmentId,
            buildAssignmentLabel
        );
        assignmentSelect.disabled = state.lessonId <= 0;
    }

    function selectedText(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        if (!option) {
            return 'Chưa chọn';
        }

        if (toInt(option.value) <= 0) {
            return 'Chưa chọn';
        }

        return normalizeText(option.textContent || 'Chưa chọn');
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
        const submittedAt = normalizeText(row.submitted_at) || 'Chưa nộp';
        const deadline = submissionDeadlineText(row);
        const lateStatusBadge = hasSubmitted(row)
            ? (deadline === ''
                ? '<span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-semibold text-slate-600">Không có deadline</span>'
                : (isLateSubmission(row)
                    ? '<span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 font-semibold text-rose-700">Nộp trễ hạn</span>'
                    : '<span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">Nộp đúng hạn</span>'))
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
                            'Chọn lưu batch' +
                        '</label>' +
                        '<label class="text-xs font-semibold text-slate-600">' +
                            'Điểm' +
                            '<input data-field="score" class="mt-1" type="number" min="0" max="10" step="0.1" name="score[' + submissionId + ']" value="' + escapeHtml(scoreValue) + '" placeholder="Điểm (0-10)">' +
                        '</label>' +
                    '</div>' +
                    '<label class="text-xs font-semibold text-slate-600">' +
                        'Nhận xét' +
                        '<textarea data-field="comment" class="mt-1 min-h-[92px]" rows="3" name="teacher_comment[' + submissionId + ']" placeholder="Nhận xét cho học viên này">' + escapeHtml(commentValue) + '</textarea>' +
                    '</label>' +
                '</div>';
        } else if (submissionId <= 0) {
            gradingMarkup = '<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">Học viên chưa nộp bài. Hiện tại không thể chấm điểm.</div>';
        } else {
            gradingMarkup = '' +
                '<div class="mt-3 grid gap-2 lg:grid-cols-[140px_1fr]">' +
                    '<div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">Điểm: ' + (scoreValue === '' ? '-' : escapeHtml(scoreValue)) + '</div>' +
                    '<div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">Nhận xét: ' + (commentValue === '' ? '-' : escapeHtml(commentValue)) + '</div>' +
                '</div>';
        }

        return '' +
            '<div class="rounded-xl border border-slate-200 bg-white p-4" data-submission-id="' + submissionId + '">' +
                '<div class="grid gap-2 lg:grid-cols-[minmax(220px,1fr)_auto] lg:items-start">' +
                    '<div class="space-y-2">' +
                        '<div class="text-lg font-semibold text-slate-900">' + escapeHtml(row.student_name || '') + '</div>' +
                        '<div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">' +
                            '<span>Nộp lúc: ' + escapeHtml(submittedAt) + '</span>' +
                            (deadline === '' ? '' : '<span>Deadline: ' + escapeHtml(deadline) + '</span>') +
                            lateStatusBadge +
                            (fileUrl === ''
                                ? '<span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-semibold text-slate-500">Không có file nộp</span>'
                                : '<a class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2.5 py-1 font-semibold text-blue-700 hover:bg-blue-100" href="' + escapeHtml(fileUrl) + '" target="_blank" rel="noopener noreferrer">Mở file nộp</a>') +
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
            setStateMessage('Chọn lớp học để bắt đầu.', 'info');
            return;
        }

        if (state.lessonId <= 0) {
            setStateMessage('Chọn buổi học để tiếp tục.', 'info');
            return;
        }

        if (state.assignmentId <= 0) {
            setStateMessage('Chọn bài tập để tải danh sách học viên.', 'info');
            return;
        }

        if (state.loading) {
            setStateMessage('Đang tải danh sách học viên...', 'info');
            return;
        }

        if (state.errorMessage !== '') {
            setStateMessage(state.errorMessage, 'error');
            return;
        }

        if (state.roster.length === 0) {
            setStateMessage('Không có dữ liệu học viên cho bộ lọc hiện tại.', 'info');
            return;
        }

        const visibleRows = state.roster.filter((row) => shouldShowRow(row));
        if (visibleRows.length === 0) {
            setStateMessage('Không có học viên phù hợp với trạng thái đã chọn.', 'info');
            return;
        }

        hideStateMessage();
        gradingList.innerHTML = visibleRows.map((row) => renderRow(row)).join('');
    }

    async function loadRoster() {
        updateHiddenContext();
        syncUrlWithoutReload();

        if (state.classId <= 0 || state.lessonId <= 0 || state.assignmentId <= 0) {
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
                throw new Error(payload && payload.message ? payload.message : 'Không thể tải dữ liệu bài nộp.');
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
            state.errorMessage = error instanceof Error ? error.message : 'Không thể tải dữ liệu bài nộp.';
            renderRoster();
        }
    }

    function syncStateFromControls() {
        state.classId = toInt(classSelect.value);
        state.lessonId = toInt(lessonSelect.value);
        state.assignmentId = toInt(assignmentSelect.value);
        state.gradeStatus = normalizeText(statusSelect.value) || 'all';
        updateHiddenContext();
        syncUrlWithoutReload();
    }

    classSelect.addEventListener('change', () => {
        captureDraftsFromDom();
        state.classId = toInt(classSelect.value);
        state.lessonId = 0;
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
        state.lessonId = toInt(lessonSelect.value);
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
            state.lessonId = 0;
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
            setStateMessage('Hãy chọn ít nhất 1 học viên đã nộp để lưu chấm điểm hàng loạt.', 'error');
        }
    });

    updateFilterSelects();
    statusSelect.value = state.gradeStatus;
    syncStateFromControls();
    loadRoster();
})();
</script>





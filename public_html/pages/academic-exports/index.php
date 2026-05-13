<?php
declare(strict_types=1);

$authUser = auth_user() ?? [];
$authRole = strtolower((string) ($authUser['role'] ?? ''));

require_permission('academic.exports.view');

function export_class_status_label(?string $value): string
{
    $normalized = strtolower(trim((string) $value));

    return match ($normalized) {
        'active' => t('admin.class_edit.status_active'),
        'upcoming' => t('admin.class_edit.status_upcoming'),
        'graduated' => t('admin.class_edit.status_graduated'),
        'cancelled' => t('admin.class_edit.status_cancelled'),
        default => trim((string) $value),
    };
}

$academicModel = new AcademicModel();
$lookups = $academicModel->classroomLookups();
$classOptions = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];

if ($authRole === 'teacher') {
    $teacherUserId = (int) ($authUser['id'] ?? 0);
    $classOptions = array_values(array_filter($classOptions, static function (array $classRow) use ($teacherUserId): bool {
        return (int) ($classRow['teacher_id'] ?? 0) === $teacherUserId;
    }));
}

$classOptionsJson = json_encode(array_values($classOptions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($classOptionsJson)) {
    $classOptionsJson = '[]';
}

$module = 'exports';
$adminTitle = t('admin.exports.title');
$success = get_flash('success');
$error = get_flash('error');
$csrfToken = csrf_token();
?>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<div class="grid gap-5" id="student-export-app">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>



    <div class="grid gap-4 xl:grid-cols-[320px_minmax(0,1fr)]">
        <aside class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <div class="text-xs font-extrabold uppercase tracking-[0.24em] text-blue-700"><?= e(t('admin.exports.progress')); ?></div>
                <h3 class="mt-2 text-xl font-extrabold text-slate-900"><?= e(t('admin.exports.two_steps')); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.progress_hint')); ?></p>
            </div>

            <div class="space-y-3">
                <div id="step-card-1" class="rounded-2xl border border-blue-200 bg-blue-50/80 p-4 transition">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-sm font-extrabold text-white shadow-lg shadow-blue-500/30">1</div>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900"><?= e(t('admin.exports.choose_class')); ?></div>
                            <div class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.choose_class_hint')); ?></div>
                        </div>
                    </div>
                </div>

                <div id="step-card-2" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition">
                    <div class="flex items-start gap-3">
                        <div id="step-badge-2" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-300 text-sm font-extrabold text-white transition">2</div>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900"><?= e(t('admin.exports.choose_students_export')); ?></div>
                            <div class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.choose_students_export_hint')); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="grid gap-5">
            <article id="step-1-panel" class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm ring-2 ring-blue-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-[0.22em] text-blue-700"><?= e(t('admin.exports.step_1')); ?></div>
                        <h3 class="mt-2 text-xl font-extrabold text-slate-900"><?= e(t('admin.exports.choose_class')); ?></h3>
                        <p class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.load_students_hint')); ?></p>
                    </div>
                    <div id="step-1-status" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700"><?= e(t('admin.exports.waiting_class')); ?></div>
                </div>

                <form id="export-class-form" class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <label>
                        <?= e(t('admin.classrooms.class_info')); ?>
                        <select id="export-class-select" name="class_id">
                            <option value=""><?= e(t('admin.exports.choose_class_placeholder')); ?></option>
                            <?php foreach ($classOptions as $classOption): ?>
                                <?php
                                $classId = (int) ($classOption['id'] ?? 0);
                                $label = trim((string) ($classOption['class_name'] ?? str_replace(':id', (string) $classId, t('admin.assignment_edit.class_fallback'))));
                                $courseName = trim((string) ($classOption['course_name'] ?? ''));
                                if ($courseName !== '') {
                                    $label .= ' - ' . $courseName;
                                }
                                $statusText = export_class_status_label((string) ($classOption['status'] ?? ''));
                                if ($statusText !== '') {
                                    $label .= ' - ' . $statusText;
                                }
                                ?>
                                <option value="<?= $classId; ?>"><?= e($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="flex items-end gap-2">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.exports.show_students')); ?></button>
                        <button id="reset-export-flow" class="<?= ui_btn_secondary_classes(); ?>" type="button"><?= e(t('admin.exports.reset')); ?></button>
                    </div>
                </form>

                <div id="selected-class-summary" class="mt-4 hidden grid gap-3 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500"><?= e(t('admin.classrooms.class_info')); ?></div>
                        <div id="summary-class-name" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500"><?= e(t('admin.class_edit.course')); ?></div>
                        <div id="summary-course-name" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500"><?= e(t('admin.class_edit.status')); ?></div>
                        <div id="summary-class-status" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                </div>
            </article>

            <article id="step-2-panel" class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-[0.22em] text-cyan-700"><?= e(t('admin.exports.step_2')); ?></div>
                        <h3 class="mt-2 text-xl font-extrabold text-slate-900"><?= e(t('admin.exports.choose_students_xlsx')); ?></h3>
                        <p class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.student_select_hint')); ?></p>
                    </div>
                    <div id="student-count-badge" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-bold text-slate-600"><?= e(t('admin.exports.no_class_data')); ?></div>
                </div>

                <div id="step-2-placeholder" class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">
                    <?= e(t('admin.exports.finish_step_1')); ?>
                </div>

                <div id="step-2-content" class="mt-5 hidden">
                    <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-cyan-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900"><?= e(t('admin.exports.students_to_export')); ?></div>
                                <div class="mt-1 text-sm text-slate-600"><?= e(t('admin.exports.students_to_export_hint')); ?></div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button id="select-all-export-students" class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-bold text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-100" type="button"><?= e(t('admin.exports.select_all_students')); ?></button>
                                <button id="clear-export-students" class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100" type="button"><?= e(t('admin.exports.clear_all_students')); ?></button>
                                <button id="export-all-students" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-700" type="button"><?= e(t('admin.exports.export_all_students')); ?></button>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                            <label>
                                <?= e(t('admin.portfolios.student')); ?>
                                <select id="export-student-select" name="student_ids[]" multiple data-no-search="1"></select>
                            </label>

                            <div class="rounded-2xl border border-white bg-white/85 p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900"><?= e(t('admin.exports.workbook_three_sheets')); ?></div>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    <div>1. <?= e(t('admin.exports.sheet_summary')); ?></div>
                                    <div>2. <?= e(t('admin.exports.sheet_assignments')); ?></div>
                                    <div>3. <?= e(t('admin.exports.sheet_exams')); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button id="export-selected-students" class="<?= ui_btn_primary_classes(); ?>" type="button"><?= e(t('admin.exports.export_selected_students')); ?></button>
                            <div id="export-status" class="text-sm font-semibold text-slate-500"><?= e(t('admin.exports.no_export_task')); ?></div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>

<script>
    (function () {
        const classOptions = <?= $classOptionsJson; ?>;
        const csrfToken = <?= json_encode($csrfToken, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const exportApiStudents = '/api/exports/students';
        const exportApiReport = '/api/exports/student-report';
        const exportI18n = <?= json_encode([
            'active' => t('admin.class_edit.status_active'),
            'upcoming' => t('admin.class_edit.status_upcoming'),
            'graduated' => t('admin.class_edit.status_graduated'),
            'cancelled' => t('admin.class_edit.status_cancelled'),
            'removeStudent' => t('admin.exports.remove_student'),
            'studentSelectPlaceholder' => t('admin.exports.student_select_placeholder'),
            'studentsLoaded' => t('admin.exports.students_loaded'),
            'loadDataError' => t('admin.exports.load_data_error'),
            'loadingStudents' => t('admin.exports.loading_students'),
            'waitingClassData' => t('admin.exports.waiting_class_data'),
            'studentFallback' => t('admin.classrooms.student_fallback'),
            'studentsLoadedDone' => t('admin.exports.students_loaded_done'),
            'colStudentCode' => t('admin.portfolios.student_code'),
            'colFullName' => t('profile.full_name'),
            'colAttendanceRate' => t('admin.exports.col_attendance_rate'),
            'colAttendedSessions' => t('admin.exports.col_attended_sessions'),
            'colLate' => t('admin.attendance.late'),
            'colAbsent' => t('admin.attendance.absent'),
            'colSubmissionRate' => t('admin.exports.col_submission_rate'),
            'colSubmittedAssignments' => t('admin.exports.col_submitted_assignments'),
            'colOnTime' => t('admin.exports.col_on_time'),
            'colLateSubmission' => t('admin.exports.col_late_submission'),
            'colGradedAssignments' => t('admin.exports.col_graded_assignments'),
            'colAssignmentAverage' => t('admin.exports.col_assignment_average'),
            'colAssignmentTitle' => t('admin.assignments.table_assignment'),
            'colDeadline' => t('admin.assignment_edit.deadline'),
            'colSubmissionStatus' => t('admin.exports.col_submission_status'),
            'colSubmittedAt' => t('admin.submissions.submitted_at'),
            'colOnTimeLate' => t('admin.exports.col_on_time_late'),
            'colScore' => t('admin.submissions.score'),
            'colTeacherComment' => t('admin.submissions.comment'),
            'submittedLate' => t('admin.classrooms.late_submission_short'),
            'submittedOnTime' => t('admin.classrooms.on_time_submission_short'),
            'noAssignmentData' => t('admin.exports.no_assignment_data'),
            'colExamName' => t('admin.classrooms.exam_column_name'),
            'colType' => t('admin.portfolios.type'),
            'colExamDate' => t('admin.classrooms.exam_date'),
            'colListening' => t('my_classes.listening'),
            'colSpeaking' => t('my_classes.speaking'),
            'colReading' => t('my_classes.reading'),
            'colWriting' => t('my_classes.writing'),
            'colResult' => t('admin.classrooms.result_total'),
            'noExamData' => t('admin.exports.no_exam_data'),
            'chooseClassFirst' => t('admin.exports.choose_class_first'),
            'chooseAtLeastOneStudent' => t('admin.exports.choose_at_least_one_student'),
            'preparingWorkbook' => t('admin.exports.preparing_workbook'),
            'exportSuccess' => t('admin.exports.export_success'),
            'chooseValidClass' => t('admin.exports.choose_valid_class'),
            'loadStudentsError' => t('admin.exports.load_students_error'),
            'noClassData' => t('admin.exports.no_class_data'),
            'waitingClass' => t('admin.exports.waiting_class'),
            'noExportTask' => t('admin.exports.no_export_task'),
            'selectedAll' => t('admin.exports.selected_all'),
            'clearedAll' => t('admin.exports.cleared_all'),
            'exportError' => t('admin.exports.export_error'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const state = {
            classId: 0,
            classMeta: null,
            students: [],
            studentSelect: null,
            loadingStudents: false,
            exporting: false,
        };

        const classForm = document.getElementById('export-class-form');
        const classSelect = document.getElementById('export-class-select');
        const resetButton = document.getElementById('reset-export-flow');
        const selectedClassSummary = document.getElementById('selected-class-summary');
        const summaryClassName = document.getElementById('summary-class-name');
        const summaryCourseName = document.getElementById('summary-course-name');
        const summaryClassStatus = document.getElementById('summary-class-status');
        const step1Status = document.getElementById('step-1-status');
        const step2Panel = document.getElementById('step-2-panel');
        const step2Placeholder = document.getElementById('step-2-placeholder');
        const step2Content = document.getElementById('step-2-content');
        const studentCountBadge = document.getElementById('student-count-badge');
        const stepCard1 = document.getElementById('step-card-1');
        const stepCard2 = document.getElementById('step-card-2');
        const stepBadge2 = document.getElementById('step-badge-2');
        const studentSelectElement = document.getElementById('export-student-select');
        const exportStatus = document.getElementById('export-status');
        const selectAllButton = document.getElementById('select-all-export-students');
        const clearButton = document.getElementById('clear-export-students');
        const exportSelectedButton = document.getElementById('export-selected-students');
        const exportAllButton = document.getElementById('export-all-students');

        function statusLabel(rawStatus) {
            const normalized = String(rawStatus || '').trim().toLowerCase();
            const map = {
                active: exportI18n.active,
                upcoming: exportI18n.upcoming,
                graduated: exportI18n.graduated,
                cancelled: exportI18n.cancelled
            };
            return map[normalized] || String(rawStatus || '');
        }

        function findClassMetaById(classId) {
            return classOptions.find(function (item) {
                return Number(item.id || 0) === Number(classId || 0);
            }) || null;
        }

        function setStepHighlight(stepNumber) {
            if (stepNumber === 1) {
                stepCard1.className = 'rounded-2xl border border-blue-200 bg-blue-50/80 p-4 transition';
                stepCard2.className = 'rounded-2xl border border-slate-200 bg-slate-50 p-4 transition';
                stepBadge2.className = 'flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-300 text-sm font-extrabold text-white transition';
            } else {
                stepCard1.className = 'rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 transition';
                stepCard2.className = 'rounded-2xl border border-cyan-200 bg-cyan-50/80 p-4 transition ring-2 ring-cyan-100';
                stepBadge2.className = 'flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-600 text-sm font-extrabold text-white transition shadow-lg shadow-cyan-500/30';
            }
        }

        function renderClassSummary(classMeta) {
            if (!classMeta) {
                selectedClassSummary.classList.add('hidden');
                return;
            }

            summaryClassName.textContent = String(classMeta.class_name || '');
            summaryCourseName.textContent = String(classMeta.course_name || '');
            summaryClassStatus.textContent = statusLabel(classMeta.status || '');
            selectedClassSummary.classList.remove('hidden');
        }

        function ensureStudentSelect() {
            if (state.studentSelect) {
                return state.studentSelect;
            }

            state.studentSelect = new TomSelect(studentSelectElement, {
                plugins: {
                    remove_button: {
                        title: exportI18n.removeStudent
                    }
                },
                valueField: 'id',
                labelField: 'label',
                searchField: ['label'],
                options: [],
                items: [],
                placeholder: exportI18n.studentSelectPlaceholder,
                create: false,
                maxOptions: 500,
                closeAfterSelect: false,
                render: {
                    option: function (data, escape) {
                        return '<div class="py-1">' +
                            '<div class="font-semibold text-slate-900">' + escape(data.label || '') + '</div>' +
                            '</div>';
                    },
                    item: function (data, escape) {
                        return '<div>' + escape(data.label || '') + '</div>';
                    }
                }
            });

            return state.studentSelect;
        }

        function resetStudentSelectOptions(students) {
            const select = ensureStudentSelect();
            select.clear(true);
            select.clearOptions();
            select.addOptions(students.map(function (student) {
                return {
                    id: String(student.student_id || ''),
                    label: String(student.label || '')
                };
            }));
            select.refreshOptions(false);
        }

        function renderStudents(students) {
            state.students = students.slice();
            resetStudentSelectOptions(state.students);
            step2Placeholder.classList.add('hidden');
            step2Content.classList.remove('hidden');
            studentCountBadge.textContent = String(exportI18n.studentsLoaded || '').replace(':count', String(state.students.length));
        }

        function setStep1Status(text, tone) {
            const toneMap = {
                idle: 'inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700',
                success: 'inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-bold text-emerald-700',
                loading: 'inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-bold text-amber-700',
                error: 'inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-bold text-rose-700'
            };
            step1Status.className = toneMap[tone] || toneMap.idle;
            step1Status.textContent = text;
        }

        function setExportStatus(text, tone) {
            const toneMap = {
                idle: 'text-sm font-semibold text-slate-500',
                loading: 'text-sm font-semibold text-amber-700',
                success: 'text-sm font-semibold text-emerald-700',
                error: 'text-sm font-semibold text-rose-700'
            };
            exportStatus.className = toneMap[tone] || toneMap.idle;
            exportStatus.textContent = text;
        }

        async function requestJson(url, options) {
            const response = await fetch(url, Object.assign({
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }, options || {}));

            const payload = await response.json().catch(function () {
                return null;
            });

            if (!response.ok || !payload || payload.status !== 'success') {
                const message = payload && payload.message ? payload.message : exportI18n.loadDataError;
                throw new Error(message);
            }

            return payload.data || {};
        }

        async function loadStudentsForClass(classId) {
            state.loadingStudents = true;
            setStep1Status(exportI18n.loadingStudents, 'loading');
            setExportStatus(exportI18n.waitingClassData, 'idle');

            try {
                const data = await requestJson(exportApiStudents + '?class_id=' + encodeURIComponent(String(classId)));
                const classMeta = findClassMetaById(classId) || data.class || null;
                state.classId = classId;
                state.classMeta = classMeta;
                const students = Array.isArray(data.students) ? data.students.map(function (student) {
                    const studentId = Number(student.student_id || 0);
                    const studentName = String(student.student_name || student.full_name || String(exportI18n.studentFallback || '').replace(':id', String(studentId)));
                    const studentCode = String(student.student_code || '').trim();
                    return Object.assign({}, student, {
                        student_id: studentId,
                        label: studentCode !== '' ? (studentName + ' - ' + studentCode) : studentName
                    });
                }) : [];

                renderClassSummary(classMeta);
                renderStudents(students);
                setStep1Status(exportI18n.studentsLoadedDone, 'success');
                setStepHighlight(2);
                step2Panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                window.setTimeout(function () {
                    const select = ensureStudentSelect();
                    if (select && typeof select.focus === 'function') {
                        select.focus();
                    }
                }, 450);
            } finally {
                state.loadingStudents = false;
            }
        }

        function getSelectedStudentIds() {
            const select = ensureStudentSelect();
            return select.getValue().map(function (value) {
                return Number(value || 0);
            }).filter(function (value) {
                return value > 0;
            });
        }

        function buildWorkbook(reportData, selectedStudentIds, exportAll) {
            const workbook = XLSX.utils.book_new();
            const selectedLabelMap = new Map();
            state.students.forEach(function (student) {
                selectedLabelMap.set(Number(student.student_id || 0), {
                    name: String(student.student_name || student.full_name || ''),
                    code: String(student.student_code || '')
                });
            });

            const summaryRows = Array.isArray(reportData.summary_rows) ? reportData.summary_rows : [];
            const assignmentRows = Array.isArray(reportData.assignment_rows) ? reportData.assignment_rows : [];
            const examRows = Array.isArray(reportData.exam_rows) ? reportData.exam_rows : [];

            const summarySheetRows = summaryRows.map(function (row) {
                return {
                    [exportI18n.colStudentCode]: row.student_code || '',
                    [exportI18n.colFullName]: row.student_name || '',
                    [exportI18n.colAttendanceRate]: Number(row.attendance_rate || 0),
                    [exportI18n.colAttendedSessions]: String(row.attended_sessions || 0) + ' / ' + String(row.total_sessions || 0),
                    [exportI18n.colLate]: Number(row.late_sessions || 0),
                    [exportI18n.colAbsent]: Number(row.absent_sessions || 0),
                    [exportI18n.colSubmissionRate]: Number(row.submission_rate || 0),
                    [exportI18n.colSubmittedAssignments]: String(row.submitted_assignments || 0) + ' / ' + String(row.total_assignments || 0),
                    [exportI18n.colOnTime]: Number(row.on_time_assignments || 0),
                    [exportI18n.colLateSubmission]: Number(row.late_assignments || 0),
                    [exportI18n.colGradedAssignments]: Number(row.graded_assignment_count || 0),
                    [exportI18n.colAssignmentAverage]: row.assignment_average == null ? '' : Number(row.assignment_average)
                };
            });

            const assignmentSheetRows = assignmentRows.length > 0 ? assignmentRows.map(function (row) {
                return {
                    [exportI18n.colStudentCode]: row.student_code || '',
                    [exportI18n.colFullName]: row.full_name || '',
                    [exportI18n.colAssignmentTitle]: row.assignment_title || '',
                    [exportI18n.colDeadline]: row.assignment_deadline || '',
                    [exportI18n.colSubmissionStatus]: row.submission_status || '',
                    [exportI18n.colSubmittedAt]: row.submitted_at || '',
                    [exportI18n.colOnTimeLate]: Number(row.is_late_submission || 0) === 1 ? exportI18n.submittedLate : (row.submitted_at ? exportI18n.submittedOnTime : ''),
                    [exportI18n.colScore]: row.score == null || row.score === '' ? '' : Number(row.score),
                    [exportI18n.colTeacherComment]: row.teacher_comment || ''
                };
            }) : [{
                [exportI18n.colStudentCode]: '',
                [exportI18n.colFullName]: '',
                [exportI18n.colAssignmentTitle]: exportI18n.noAssignmentData,
                [exportI18n.colDeadline]: '',
                [exportI18n.colSubmissionStatus]: '',
                [exportI18n.colSubmittedAt]: '',
                [exportI18n.colOnTimeLate]: '',
                [exportI18n.colScore]: '',
                [exportI18n.colTeacherComment]: ''
            }];

            const examSheetRows = examRows.length > 0 ? examRows.map(function (row) {
                const meta = selectedLabelMap.get(Number(row.student_id || 0)) || {};
                return {
                    [exportI18n.colStudentCode]: meta.code || '',
                    [exportI18n.colFullName]: meta.name || '',
                    [exportI18n.colExamName]: row.exam_name || '',
                    [exportI18n.colType]: row.exam_type || '',
                    [exportI18n.colExamDate]: row.exam_date || '',
                    [exportI18n.colListening]: row.score_listening == null || row.score_listening === '' ? '' : Number(row.score_listening),
                    [exportI18n.colSpeaking]: row.score_speaking == null || row.score_speaking === '' ? '' : Number(row.score_speaking),
                    [exportI18n.colReading]: row.score_reading == null || row.score_reading === '' ? '' : Number(row.score_reading),
                    [exportI18n.colWriting]: row.score_writing == null || row.score_writing === '' ? '' : Number(row.score_writing),
                    [exportI18n.colResult]: row.result || '',
                    [exportI18n.colTeacherComment]: row.teacher_comment || ''
                };
            }) : [{
                [exportI18n.colStudentCode]: '',
                [exportI18n.colFullName]: '',
                [exportI18n.colExamName]: exportI18n.noExamData,
                [exportI18n.colType]: '',
                [exportI18n.colExamDate]: '',
                [exportI18n.colListening]: '',
                [exportI18n.colSpeaking]: '',
                [exportI18n.colReading]: '',
                [exportI18n.colWriting]: '',
                [exportI18n.colResult]: '',
                [exportI18n.colTeacherComment]: ''
            }];

            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(summarySheetRows), 'Tong quan');
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(assignmentSheetRows), 'Chi tiet bai tap');
            XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(examSheetRows), 'Bang diem kiem tra');

            const safeClassName = String((state.classMeta && state.classMeta.class_name) || 'lop-hoc')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^A-Za-z0-9_-]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '')
                .toLowerCase() || 'lop-hoc';

            const suffix = exportAll ? 'tat-ca-hoc-vien' : ('nhom-' + selectedStudentIds.length + '-hoc-vien');
            const fileName = 'bao-cao-' + safeClassName + '-' + suffix + '.xlsx';
            XLSX.writeFile(workbook, fileName, { compression: true });
        }

        async function exportReport(studentIds, exportAll) {
            if (state.classId <= 0) {
                throw new Error(exportI18n.chooseClassFirst);
            }

            if (!Array.isArray(studentIds) || studentIds.length === 0) {
                throw new Error(exportI18n.chooseAtLeastOneStudent);
            }

            state.exporting = true;
            setExportStatus(exportI18n.preparingWorkbook, 'loading');

            try {
                const formData = new FormData();
                formData.append('_csrf', csrfToken);
                formData.append('class_id', String(state.classId));
                formData.append('format', 'json');
                studentIds.forEach(function (studentId) {
                    formData.append('student_ids[]', String(studentId));
                });

                const reportData = await requestJson(exportApiReport, {
                    method: 'POST',
                    body: formData
                });

                buildWorkbook(reportData, studentIds, exportAll);
                setExportStatus(exportI18n.exportSuccess, 'success');
            } finally {
                state.exporting = false;
            }
        }

        if (classForm instanceof HTMLFormElement) {
            classForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const classId = Number(classSelect && classSelect.value ? classSelect.value : 0);
                if (classId <= 0) {
                    setStep1Status(exportI18n.chooseValidClass, 'error');
                    return;
                }

                loadStudentsForClass(classId).catch(function (error) {
                    setStep1Status(error.message || exportI18n.loadStudentsError, 'error');
                    setExportStatus(error.message || exportI18n.loadStudentsError, 'error');
                });
            });
        }

        if (resetButton instanceof HTMLButtonElement) {
            resetButton.addEventListener('click', function () {
                state.classId = 0;
                state.classMeta = null;
                state.students = [];
                if (classSelect instanceof HTMLSelectElement) {
                    classSelect.value = '';
                }
                if (state.studentSelect) {
                    state.studentSelect.clear(true);
                    state.studentSelect.clearOptions();
                }
                selectedClassSummary.classList.add('hidden');
                step2Content.classList.add('hidden');
                step2Placeholder.classList.remove('hidden');
                studentCountBadge.textContent = exportI18n.noClassData;
                setStep1Status(exportI18n.waitingClass, 'idle');
                setExportStatus(exportI18n.noExportTask, 'idle');
                setStepHighlight(1);
                document.getElementById('step-1-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        if (selectAllButton instanceof HTMLButtonElement) {
            selectAllButton.addEventListener('click', function () {
                const select = ensureStudentSelect();
                select.setValue(state.students.map(function (student) {
                    return String(student.student_id || '');
                }));
                setExportStatus(exportI18n.selectedAll, 'idle');
            });
        }

        if (clearButton instanceof HTMLButtonElement) {
            clearButton.addEventListener('click', function () {
                const select = ensureStudentSelect();
                select.clear(true);
                setExportStatus(exportI18n.clearedAll, 'idle');
            });
        }

        if (exportSelectedButton instanceof HTMLButtonElement) {
            exportSelectedButton.addEventListener('click', function () {
                exportReport(getSelectedStudentIds(), false).catch(function (error) {
                    setExportStatus(error.message || exportI18n.exportError, 'error');
                });
            });
        }

        if (exportAllButton instanceof HTMLButtonElement) {
            exportAllButton.addEventListener('click', function () {
                const allIds = state.students.map(function (student) {
                    return Number(student.student_id || 0);
                }).filter(function (studentId) {
                    return studentId > 0;
                });

                exportReport(allIds, true).catch(function (error) {
                    setExportStatus(error.message || exportI18n.exportError, 'error');
                });
            });
        }

        setStepHighlight(1);
        setExportStatus(exportI18n.noExportTask, 'idle');
    })();
</script>

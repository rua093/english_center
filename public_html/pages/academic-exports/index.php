<?php
declare(strict_types=1);

$authUser = auth_user() ?? [];
$authRole = strtolower((string) ($authUser['role'] ?? ''));

require_permission('academic.exports.view');

function export_class_status_label(?string $value): string
{
    $normalized = strtolower(trim((string) $value));

    return match ($normalized) {
        'active' => 'Đang học',
        'upcoming' => 'Sắp mở',
        'graduated' => 'Đã kết thúc',
        'cancelled' => 'Đã hủy',
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
$adminTitle = 'Học vụ - Xuất Excel học viên';
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
                <div class="text-xs font-extrabold uppercase tracking-[0.24em] text-blue-700">Tiến trình</div>
                <h3 class="mt-2 text-xl font-extrabold text-slate-900">2 bước thao tác</h3>
                <p class="mt-1 text-sm text-slate-600">Mỗi bước đều có chỉ dấu rõ ràng. Sau khi xong bước 1, màn hình sẽ tự cuộn đến bước 2.</p>
            </div>

            <div class="space-y-3">
                <div id="step-card-1" class="rounded-2xl border border-blue-200 bg-blue-50/80 p-4 transition">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-sm font-extrabold text-white shadow-lg shadow-blue-500/30">1</div>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">Chọn lớp học</div>
                            <div class="mt-1 text-sm text-slate-600">Bấm “Hiển thị học viên” để tải roster ngay trong trang.</div>
                        </div>
                    </div>
                </div>

                <div id="step-card-2" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition">
                    <div class="flex items-start gap-3">
                        <div id="step-badge-2" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-300 text-sm font-extrabold text-white transition">2</div>
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">Chọn học viên và xuất file</div>
                            <div class="mt-1 text-sm text-slate-600">Chọn một, nhiều, hoặc toàn bộ học viên rồi xuất workbook `.xlsx`.</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="grid gap-5">
            <article id="step-1-panel" class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm ring-2 ring-blue-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-[0.22em] text-blue-700">Bước 1</div>
                        <h3 class="mt-2 text-xl font-extrabold text-slate-900">Chọn lớp học</h3>
                        <p class="mt-1 text-sm text-slate-600">Hệ thống sẽ lấy danh sách học viên đúng theo lớp bạn chọn mà không tải lại trang.</p>
                    </div>
                    <div id="step-1-status" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700">Đang chờ chọn lớp</div>
                </div>

                <form id="export-class-form" class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                    <label>
                        Lớp học
                        <select id="export-class-select" name="class_id">
                            <option value="">-- Chọn lớp học để hiển thị học viên --</option>
                            <?php foreach ($classOptions as $classOption): ?>
                                <?php
                                $classId = (int) ($classOption['id'] ?? 0);
                                $label = trim((string) ($classOption['class_name'] ?? ('Lớp #' . $classId)));
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
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Hiển thị học viên</button>
                        <button id="reset-export-flow" class="<?= ui_btn_secondary_classes(); ?>" type="button">Đặt lại</button>
                    </div>
                </form>

                <div id="selected-class-summary" class="mt-4 hidden grid gap-3 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Lớp học</div>
                        <div id="summary-class-name" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Khóa học</div>
                        <div id="summary-course-name" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Trạng thái lớp</div>
                        <div id="summary-class-status" class="mt-1 text-base font-semibold text-slate-900"></div>
                    </div>
                </div>
            </article>

            <article id="step-2-panel" class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold uppercase tracking-[0.22em] text-cyan-700">Bước 2</div>
                        <h3 class="mt-2 text-xl font-extrabold text-slate-900">Chọn học viên và xuất `.xlsx`</h3>
                        <p class="mt-1 text-sm text-slate-600">Ô chọn học viên có placeholder rõ ràng, có tag đang chọn và nút xóa từng học viên nếu chọn nhầm.</p>
                    </div>
                    <div id="student-count-badge" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-bold text-slate-600">Chưa có dữ liệu lớp</div>
                </div>

                <div id="step-2-placeholder" class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-600">
                    Hoàn thành bước 1 để tải danh sách học viên vào đây.
                </div>

                <div id="step-2-content" class="mt-5 hidden">
                    <div class="rounded-[24px] border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-cyan-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">Danh sách học viên cần xuất</div>
                                <div class="mt-1 text-sm text-slate-600">Có thể chọn một học viên cụ thể, nhiều học viên, hoặc xuất toàn bộ học viên trong lớp.</div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button id="select-all-export-students" class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-4 py-2 text-sm font-bold text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-100" type="button">Chọn toàn bộ học viên</button>
                                <button id="clear-export-students" class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100" type="button">Bỏ chọn toàn bộ</button>
                                <button id="export-all-students" class="inline-flex items-center rounded-full border border-blue-200 bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-700" type="button">Xuất tất cả học viên trong lớp</button>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
                            <label>
                                Học viên
                                <select id="export-student-select" name="student_ids[]" multiple data-no-search="1"></select>
                            </label>

                            <div class="rounded-2xl border border-white bg-white/85 p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900">Workbook sẽ có 3 sheet</div>
                                <div class="mt-3 space-y-2 text-sm text-slate-700">
                                    <div>1. Tổng quan học viên</div>
                                    <div>2. Chi tiết bài tập</div>
                                    <div>3. Bảng điểm kiểm tra</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button id="export-selected-students" class="<?= ui_btn_primary_classes(); ?>" type="button">Xuất các học viên đã chọn</button>
                            <div id="export-status" class="text-sm font-semibold text-slate-500">Chưa có tác vụ xuất file.</div>
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
                active: 'Đang học',
                upcoming: 'Sắp mở',
                graduated: 'Đã kết thúc',
                cancelled: 'Đã hủy'
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
                        title: 'Bỏ học viên này'
                    }
                },
                valueField: 'id',
                labelField: 'label',
                searchField: ['label'],
                options: [],
                items: [],
                placeholder: 'Bấm vào đây để chọn một hoặc nhiều học viên...',
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
            studentCountBadge.textContent = 'Đã tải ' + state.students.length + ' học viên';
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
                const message = payload && payload.message ? payload.message : 'Không thể tải dữ liệu.';
                throw new Error(message);
            }

            return payload.data || {};
        }

        async function loadStudentsForClass(classId) {
            state.loadingStudents = true;
            setStep1Status('Đang tải học viên...', 'loading');
            setExportStatus('Đang chờ dữ liệu lớp...', 'idle');

            try {
                const data = await requestJson(exportApiStudents + '?class_id=' + encodeURIComponent(String(classId)));
                const classMeta = findClassMetaById(classId) || data.class || null;
                state.classId = classId;
                state.classMeta = classMeta;
                const students = Array.isArray(data.students) ? data.students.map(function (student) {
                    const studentId = Number(student.student_id || 0);
                    const studentName = String(student.student_name || student.full_name || ('Học viên #' + studentId));
                    const studentCode = String(student.student_code || '').trim();
                    return Object.assign({}, student, {
                        student_id: studentId,
                        label: studentCode !== '' ? (studentName + ' - ' + studentCode) : studentName
                    });
                }) : [];

                renderClassSummary(classMeta);
                renderStudents(students);
                setStep1Status('Đã tải xong danh sách học viên', 'success');
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
                    'Mã học viên': row.student_code || '',
                    'Họ tên': row.student_name || '',
                    'Tỷ lệ đi học đầy đủ': Number(row.attendance_rate || 0),
                    'Có mặt / Tổng buổi': String(row.attended_sessions || 0) + ' / ' + String(row.total_sessions || 0),
                    'Đi muộn': Number(row.late_sessions || 0),
                    'Vắng': Number(row.absent_sessions || 0),
                    'Tỷ lệ hoàn thành bài tập': Number(row.submission_rate || 0),
                    'Đã nộp / Tổng bài': String(row.submitted_assignments || 0) + ' / ' + String(row.total_assignments || 0),
                    'Nộp đúng hạn': Number(row.on_time_assignments || 0),
                    'Nộp trễ': Number(row.late_assignments || 0),
                    'Số bài đã chấm': Number(row.graded_assignment_count || 0),
                    'Điểm bài tập trung bình': row.assignment_average == null ? '' : Number(row.assignment_average)
                };
            });

            const assignmentSheetRows = assignmentRows.length > 0 ? assignmentRows.map(function (row) {
                return {
                    'Mã học viên': row.student_code || '',
                    'Họ tên': row.full_name || '',
                    'Tên bài tập': row.assignment_title || '',
                    'Hạn nộp': row.assignment_deadline || '',
                    'Trạng thái nộp': row.submission_status || '',
                    'Nộp lúc': row.submitted_at || '',
                    'Đúng hạn / Trễ': Number(row.is_late_submission || 0) === 1 ? 'Nộp trễ' : (row.submitted_at ? 'Đúng hạn' : ''),
                    'Điểm': row.score == null || row.score === '' ? '' : Number(row.score),
                    'Nhận xét giáo viên': row.teacher_comment || ''
                };
            }) : [{
                'Mã học viên': '',
                'Họ tên': '',
                'Tên bài tập': 'Chưa có dữ liệu bài tập hoặc bài nộp',
                'Hạn nộp': '',
                'Trạng thái nộp': '',
                'Nộp lúc': '',
                'Đúng hạn / Trễ': '',
                'Điểm': '',
                'Nhận xét giáo viên': ''
            }];

            const examSheetRows = examRows.length > 0 ? examRows.map(function (row) {
                const meta = selectedLabelMap.get(Number(row.student_id || 0)) || {};
                return {
                    'Mã học viên': meta.code || '',
                    'Họ tên': meta.name || '',
                    'Tên bài kiểm tra': row.exam_name || '',
                    'Loại': row.exam_type || '',
                    'Ngày thi': row.exam_date || '',
                    'Nghe': row.score_listening == null || row.score_listening === '' ? '' : Number(row.score_listening),
                    'Nói': row.score_speaking == null || row.score_speaking === '' ? '' : Number(row.score_speaking),
                    'Đọc': row.score_reading == null || row.score_reading === '' ? '' : Number(row.score_reading),
                    'Viết': row.score_writing == null || row.score_writing === '' ? '' : Number(row.score_writing),
                    'Kết quả': row.result || '',
                    'Nhận xét giáo viên': row.teacher_comment || ''
                };
            }) : [{
                'Mã học viên': '',
                'Họ tên': '',
                'Tên bài kiểm tra': 'Chưa có dữ liệu điểm kiểm tra',
                'Loại': '',
                'Ngày thi': '',
                'Nghe': '',
                'Nói': '',
                'Đọc': '',
                'Viết': '',
                'Kết quả': '',
                'Nhận xét giáo viên': ''
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
                throw new Error('Vui lòng chọn lớp học trước.');
            }

            if (!Array.isArray(studentIds) || studentIds.length === 0) {
                throw new Error('Vui lòng chọn ít nhất một học viên.');
            }

            state.exporting = true;
            setExportStatus('Đang chuẩn bị workbook `.xlsx`...', 'loading');

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
                setExportStatus('Đã tạo xong file `.xlsx` và bắt đầu tải xuống.', 'success');
            } finally {
                state.exporting = false;
            }
        }

        if (classForm instanceof HTMLFormElement) {
            classForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const classId = Number(classSelect && classSelect.value ? classSelect.value : 0);
                if (classId <= 0) {
                    setStep1Status('Vui lòng chọn một lớp học hợp lệ', 'error');
                    return;
                }

                loadStudentsForClass(classId).catch(function (error) {
                    setStep1Status(error.message || 'Không tải được danh sách học viên.', 'error');
                    setExportStatus(error.message || 'Không tải được danh sách học viên.', 'error');
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
                studentCountBadge.textContent = 'Chưa có dữ liệu lớp';
                setStep1Status('Đang chờ chọn lớp', 'idle');
                setExportStatus('Chưa có tác vụ xuất file.', 'idle');
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
                setExportStatus('Đã chọn toàn bộ học viên trong lớp.', 'idle');
            });
        }

        if (clearButton instanceof HTMLButtonElement) {
            clearButton.addEventListener('click', function () {
                const select = ensureStudentSelect();
                select.clear(true);
                setExportStatus('Đã bỏ chọn toàn bộ học viên.', 'idle');
            });
        }

        if (exportSelectedButton instanceof HTMLButtonElement) {
            exportSelectedButton.addEventListener('click', function () {
                exportReport(getSelectedStudentIds(), false).catch(function (error) {
                    setExportStatus(error.message || 'Không xuất được file Excel.', 'error');
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
                    setExportStatus(error.message || 'Không xuất được file Excel.', 'error');
                });
            });
        }

        setStepHighlight(1);
        setExportStatus('Chưa có tác vụ xuất file.', 'idle');
    })();
</script>

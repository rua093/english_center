<?php
require_permission('academic.classes.view');

$academicModel = new AcademicModel();
$lookups = $academicModel->classroomLookups();

$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];
$classRows = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];

$selectedCourseId = max(0, (int) ($_GET['course_id'] ?? 0));
$selectedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
$editingLessonId = max(0, (int) ($_GET['lesson_id'] ?? 0));
$prefillScheduleId = max(0, (int) ($_GET['schedule_id'] ?? 0));
$focusedScheduleId = max(0, (int) ($_GET['focus_schedule_id'] ?? 0));
if ($prefillScheduleId <= 0 && $focusedScheduleId > 0) {
    $prefillScheduleId = $focusedScheduleId;
}

$weekRefInput = trim((string) ($_GET['week_ref'] ?? ''));
$weekStartInput = trim((string) ($_GET['week_start'] ?? ''));
$weekStartDate = null;
if ($weekRefInput !== '' && preg_match('/^(\d{4})-W(\d{2})$/', $weekRefInput, $weekRefMatch) === 1) {
    $isoYear = (int) $weekRefMatch[1];
    $isoWeek = (int) $weekRefMatch[2];
    if ($isoWeek >= 1 && $isoWeek <= 53) {
        $weekStartDate = (new DateTimeImmutable('today'))->setISODate($isoYear, $isoWeek, 1);
    }
}

if ($weekStartInput !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStartInput) === 1) {
    $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $weekStartInput);
    if ($parsedDate instanceof DateTimeImmutable) {
        $weekStartDate = $parsedDate;
    }
}
if (!$weekStartDate instanceof DateTimeImmutable) {
    $weekStartDate = (new DateTimeImmutable('today'))->modify('monday this week');
}

$weekStartDate = $weekStartDate->modify('monday this week');
$weekEndDate = $weekStartDate->modify('+6 days');
$weekStartValue = $weekStartDate->format('Y-m-d');
$weekEndValue = $weekEndDate->format('Y-m-d');
$prevWeekStart = $weekStartDate->modify('-7 days')->format('Y-m-d');
$nextWeekStart = $weekStartDate->modify('+7 days')->format('Y-m-d');
$weekRefValue = $weekStartDate->format('o-\\WW');
$prevWeekRef = $weekStartDate->modify('-7 days')->format('o-\\WW');
$nextWeekRef = $weekStartDate->modify('+7 days')->format('o-\\WW');

$weekDayLabels = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật'];
$weekDays = [];
for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
    $currentDate = $weekStartDate->modify('+' . $dayOffset . ' days');
    $weekDays[] = [
        'label' => $weekDayLabels[$dayOffset],
        'value' => $currentDate->format('Y-m-d'),
        'display' => $currentDate->format('d/m'),
    ];
}

$classMap = [];
foreach ($classRows as $classRow) {
    $classId = (int) ($classRow['id'] ?? 0);
    if ($classId > 0) {
        $classMap[$classId] = $classRow;
    }
}

if ($selectedClassId > 0 && isset($classMap[$selectedClassId]) && $selectedCourseId <= 0) {
    $selectedCourseId = (int) ($classMap[$selectedClassId]['course_id'] ?? 0);
}

$filteredClasses = array_values(array_filter($classRows, static function (array $classRow) use ($selectedCourseId): bool {
    if ($selectedCourseId <= 0) {
        return false;
    }

    return (int) ($classRow['course_id'] ?? 0) === $selectedCourseId;
}));

if ($selectedClassId > 0 && isset($classMap[$selectedClassId])) {
    $selectedClassCourseId = (int) ($classMap[$selectedClassId]['course_id'] ?? 0);
    if ($selectedCourseId > 0 && $selectedClassCourseId !== $selectedCourseId) {
        $selectedClassId = 0;
        $editingLessonId = 0;
        $prefillScheduleId = 0;
        $focusedScheduleId = 0;
    }
}

$selectedClass = $selectedClassId > 0 ? ($classMap[$selectedClassId] ?? null) : null;
$lessons = [];
$schedules = [];
$roadmaps = [];
$editingLesson = null;
$unscheduledLessons = [];
$lessonByScheduleId = [];
$scheduleById = [];
$assignmentDefaultByLesson = [];
$lessonStatsById = [];
$classroomLessonsById = [];
$classroomScheduleOptions = [];
$classroomAssignmentsByLesson = [];
$weekSchedules = [];
$weekTimeSlots = [];
$weekScheduleGrid = [];

if ($selectedClassId > 0) {
    $lessons = $academicModel->listLessonsByClass($selectedClassId);
    $schedules = $academicModel->listSchedulesByClass($selectedClassId);
    $roadmaps = $academicModel->listRoadmapsByClass($selectedClassId);

    $lessonIdMap = [];
    foreach ($lessons as $lessonRow) {
        $lessonId = (int) ($lessonRow['id'] ?? 0);
        if ($lessonId <= 0) {
            continue;
        }

        $lessonIdMap[$lessonId] = true;
        $lessonStatsById[$lessonId] = [
            'assignment_count' => 0,
            'submission_count' => 0,
            'ungraded_count' => 0,
        ];
        $classroomLessonsById[$lessonId] = [
            'id' => $lessonId,
            'roadmap_id' => (int) ($lessonRow['roadmap_id'] ?? 0),
            'actual_title' => (string) ($lessonRow['actual_title'] ?? ''),
            'actual_content' => (string) ($lessonRow['actual_content'] ?? ''),
            'schedule_id' => (int) ($lessonRow['schedule_id'] ?? 0),
        ];
    }

    if ($editingLessonId > 0) {
        $editingLesson = $academicModel->findLesson($editingLessonId);
        if (!is_array($editingLesson) || (int) ($editingLesson['class_id'] ?? 0) !== $selectedClassId) {
            $editingLesson = null;
        }
    }

    foreach ($lessons as $lessonRow) {
        $scheduleId = (int) ($lessonRow['schedule_id'] ?? 0);
        if ($scheduleId > 0 && !isset($lessonByScheduleId[$scheduleId])) {
            $lessonByScheduleId[$scheduleId] = $lessonRow;
        }
        if ($scheduleId <= 0) {
            $unscheduledLessons[] = $lessonRow;
        }
    }

    foreach ($schedules as $scheduleRow) {
        $scheduleId = (int) ($scheduleRow['id'] ?? 0);
        if ($scheduleId > 0) {
            $scheduleById[$scheduleId] = $scheduleRow;
        }
    }

    if ($prefillScheduleId > 0 && !isset($scheduleById[$prefillScheduleId])) {
        $prefillScheduleId = 0;
    }

    if ($focusedScheduleId > 0 && !isset($scheduleById[$focusedScheduleId])) {
        $focusedScheduleId = 0;
    }

    $assignmentSubmissionStats = [];
    foreach ($academicModel->listAssignments() as $assignmentRow) {
        $lessonId = (int) ($assignmentRow['lesson_id'] ?? 0);
        $assignmentId = (int) ($assignmentRow['id'] ?? 0);
        if ($lessonId <= 0 || $assignmentId <= 0 || !isset($lessonIdMap[$lessonId])) {
            continue;
        }

        if (!isset($assignmentDefaultByLesson[$lessonId])) {
            $assignmentDefaultByLesson[$lessonId] = $assignmentId;
        }

        $lessonStatsById[$lessonId]['assignment_count']++;
        if (!isset($classroomAssignmentsByLesson[$lessonId])) {
            $classroomAssignmentsByLesson[$lessonId] = [];
        }

        $classroomAssignmentsByLesson[$lessonId][] = [
            'id' => $assignmentId,
            'title' => (string) ($assignmentRow['title'] ?? ('Bài tập #' . $assignmentId)),
            'deadline' => (string) ($assignmentRow['deadline'] ?? ''),
        ];

        $assignmentSubmissionStats[$assignmentId] = [
            'submitted' => 0,
            'ungraded' => 0,
        ];
    }

    foreach ($academicModel->listSubmissionsForGrading() as $submissionRow) {
        if ((int) ($submissionRow['class_id'] ?? 0) !== $selectedClassId) {
            continue;
        }

        $assignmentId = (int) ($submissionRow['assignment_id'] ?? 0);
        if ($assignmentId <= 0 || !isset($assignmentSubmissionStats[$assignmentId])) {
            continue;
        }

        $assignmentSubmissionStats[$assignmentId]['submitted']++;
        if (trim((string) ($submissionRow['score'] ?? '')) === '') {
            $assignmentSubmissionStats[$assignmentId]['ungraded']++;
        }
    }

    foreach ($classroomAssignmentsByLesson as $lessonId => $assignmentRows) {
        foreach ($assignmentRows as $assignmentRow) {
            $assignmentId = (int) ($assignmentRow['id'] ?? 0);
            $lessonStatsById[$lessonId]['submission_count'] += (int) ($assignmentSubmissionStats[$assignmentId]['submitted'] ?? 0);
            $lessonStatsById[$lessonId]['ungraded_count'] += (int) ($assignmentSubmissionStats[$assignmentId]['ungraded'] ?? 0);
        }
    }

    $weekSchedules = array_values(array_filter($schedules, static function (array $schedule) use ($weekStartValue, $weekEndValue): bool {
        $studyDate = (string) ($schedule['study_date'] ?? '');
        return $studyDate >= $weekStartValue && $studyDate <= $weekEndValue;
    }));

    $weekTimeSlotMap = [];
    foreach ($weekSchedules as $scheduleRow) {
        $startTime = (string) ($scheduleRow['start_time'] ?? '');
        $endTime = (string) ($scheduleRow['end_time'] ?? '');
        $studyDate = (string) ($scheduleRow['study_date'] ?? '');
        if ($startTime === '' || $endTime === '' || $studyDate === '') {
            continue;
        }

        $slotKey = $startTime . '|' . $endTime;
        if (!isset($weekTimeSlotMap[$slotKey])) {
            $weekTimeSlotMap[$slotKey] = [
                'key' => $slotKey,
                'start' => $startTime,
                'end' => $endTime,
                'label' => substr($startTime, 0, 5) . ' - ' . substr($endTime, 0, 5),
            ];
        }

        if (!isset($weekScheduleGrid[$studyDate])) {
            $weekScheduleGrid[$studyDate] = [];
        }
        if (!isset($weekScheduleGrid[$studyDate][$slotKey])) {
            $weekScheduleGrid[$studyDate][$slotKey] = [];
        }
        $weekScheduleGrid[$studyDate][$slotKey][] = $scheduleRow;
    }

    $weekTimeSlots = array_values($weekTimeSlotMap);
    usort($weekTimeSlots, static function (array $left, array $right): int {
        return strcmp(($left['start'] . '|' . $left['end']), ($right['start'] . '|' . $right['end']));
    });
}

$formatDate = static function (?string $date): string {
    $raw = trim((string) $date);
    if ($raw === '') {
        return '';
    }

    $timestamp = strtotime($raw);
    if ($timestamp === false) {
        return $raw;
    }

    return date('d/m/Y', $timestamp);
};

$formatTime = static function (?string $time): string {
    $raw = trim((string) $time);
    if ($raw === '') {
        return '';
    }

    return strlen($raw) >= 5 ? substr($raw, 0, 5) : $raw;
};

if ($selectedClassId > 0) {
    foreach ($schedules as $scheduleRow) {
        $scheduleId = (int) ($scheduleRow['id'] ?? 0);
        if ($scheduleId <= 0) {
            continue;
        }

        $assignedLessonId = (int) ($scheduleRow['assigned_lesson_id'] ?? 0);
        $assignedLessonTitle = trim((string) ($scheduleRow['assigned_lesson_title'] ?? ''));
        if ($assignedLessonId <= 0 && isset($lessonByScheduleId[$scheduleId])) {
            $assignedLessonId = (int) ($lessonByScheduleId[$scheduleId]['id'] ?? 0);
        }
        if ($assignedLessonTitle === '' && isset($lessonByScheduleId[$scheduleId])) {
            $assignedLessonTitle = trim((string) ($lessonByScheduleId[$scheduleId]['actual_title'] ?? ''));
        }

        $scheduleLabel = trim(
            $formatDate((string) ($scheduleRow['study_date'] ?? ''))
            . ' | '
            . $formatTime((string) ($scheduleRow['start_time'] ?? ''))
            . '-'
            . $formatTime((string) ($scheduleRow['end_time'] ?? ''))
            . ' | '
            . (string) ($scheduleRow['room_name'] ?? 'Online')
        );

        if ($assignedLessonId > 0 && $assignedLessonTitle !== '') {
            $scheduleLabel .= ' | Đã có giáo án: ' . $assignedLessonTitle;
        }

        $classroomScheduleOptions[] = [
            'id' => $scheduleId,
            'label' => $scheduleLabel,
            'assigned_lesson_id' => $assignedLessonId,
            'assigned_lesson_title' => $assignedLessonTitle,
        ];
    }
}

$success = get_flash('success');
$error = get_flash('error');

$canCreateLesson = has_permission('academic.classes.create');
$canUpdateLesson = has_permission('academic.classes.update');
$canViewAttendance = has_permission('academic.schedules.view');
$canManageAttendance = has_permission('academic.schedules.update');
$canCreateAssignment = has_permission('academic.assignments.create');
$canGradeSubmission = has_permission('academic.submissions.grade');
$canManageExams = $canGradeSubmission;

$classroomAssignmentsByLessonJson = json_encode($classroomAssignmentsByLesson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($classroomAssignmentsByLessonJson)) {
    $classroomAssignmentsByLessonJson = '{}';
}

$classroomLessonsByIdJson = json_encode($classroomLessonsById, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($classroomLessonsByIdJson)) {
    $classroomLessonsByIdJson = '{}';
}

$classroomScheduleOptionsJson = json_encode($classroomScheduleOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($classroomScheduleOptionsJson)) {
    $classroomScheduleOptionsJson = '[]';
}

$classroomAllClassesJson = json_encode($classRows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($classroomAllClassesJson)) {
    $classroomAllClassesJson = '[]';
}

$module = 'classrooms';
$adminTitle = 'Học vụ - Quản lý lớp học';
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Bộ lọc lớp học</h3>
        <form class="grid gap-3 md:grid-cols-3" method="get" action="<?= e(page_url('classrooms-academic')); ?>" data-classroom-filter="1">
            <input type="hidden" name="page" value="classrooms-academic">
            <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
            <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">
            <label>
                Khóa học
                <select id="classroom-filter-course" name="course_id">
                    <option value="0">-- Chọn khóa học --</option>
                    <?php foreach ($courses as $course): ?>
                        <?php $courseId = (int) ($course['id'] ?? 0); ?>
                        <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>><?= e((string) ($course['course_name'] ?? ('Khóa #' . $courseId))); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div id="classroom-filter-class-wrap" class="transition duration-200 <?= $selectedCourseId > 0 ? '' : 'hidden'; ?>">
                <label>
                    Lớp học
                    <select id="classroom-filter-class" name="class_id" <?= $selectedCourseId > 0 ? '' : 'disabled'; ?>>
                        <?php if ($selectedCourseId <= 0): ?>
                            <option value="0">-- Chọn khóa học trước --</option>
                        <?php else: ?>
                            <option value="0">-- Chọn lớp học --</option>
                            <?php foreach ($filteredClasses as $classRow): ?>
                                <?php $classId = (int) ($classRow['id'] ?? 0); ?>
                                <option value="<?= $classId; ?>" <?= $selectedClassId === $classId ? 'selected' : ''; ?>>
                                    <?= e((string) ($classRow['class_name'] ?? ('Lớp #' . $classId))); ?>
                                    <?= !empty($classRow['course_name']) ? ' | ' . e((string) $classRow['course_name']) : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>
            </div>
            <div class="flex items-end gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lọc dữ liệu</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('classrooms-academic')); ?>">Đặt lại</a>
            </div>
        </form>
        <?php if (is_array($selectedClass)): ?>
            <p class="mt-3 text-sm text-slate-600">
                Đang làm việc với lớp <strong><?= e((string) ($selectedClass['class_name'] ?? '')); ?></strong>
                <?= !empty($selectedClass['course_name']) ? ' | Khóa ' . e((string) $selectedClass['course_name']) : ''; ?>.
            </p>
        <?php endif; ?>
    </article>

    <?php if (!is_array($selectedClass)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            Chọn khóa học và lớp học để soạn giáo án buổi học và xếp vào thời khóa biểu tuần.
        </article>
    <?php else: ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3>Soạn giáo án buổi học</h3>
                    <p class="text-sm text-slate-600">Soạn hoặc cập nhật nội dung buổi học trực tiếp từ menu thao tác trên ô lịch hoặc danh sách giáo án chưa xếp lịch.</p>
                </div>
                <?php if ($canCreateLesson): ?>
                    <button id="classroom-open-lesson-create" class="<?= ui_btn_primary_classes(); ?>" type="button">Soạn giáo án mới</button>
                <?php else: ?>
                    <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Bạn chưa có quyền tạo giáo án mới.</span>
                <?php endif; ?>
            </div>
        </article>

        <div data-weekly-shell="1" data-week-start="<?= e($weekStartValue); ?>" data-week-ref="<?= e($weekRefValue); ?>">

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-weekly-card="1">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3>Thời khóa biểu tuần lớp <?= e((string) ($selectedClass['class_name'] ?? '')); ?></h3>
                    <p class="text-xs text-slate-500">Từ <?= e($weekStartDate->format('d/m/Y')); ?> đến <?= e($weekEndDate->format('d/m/Y')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <a data-week-nav-link="1" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classrooms-academic', ['course_id' => $selectedCourseId, 'class_id' => $selectedClassId, 'week_start' => $prevWeekStart, 'week_ref' => $prevWeekRef])); ?>">Tuần trước</a>
                    <span class="inline-flex h-8 items-center rounded-md border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700"><?= e($weekStartDate->format('d/m')); ?> - <?= e($weekEndDate->format('d/m')); ?></span>
                    <a data-week-nav-link="1" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('classrooms-academic', ['course_id' => $selectedCourseId, 'class_id' => $selectedClassId, 'week_start' => $nextWeekStart, 'week_ref' => $nextWeekRef])); ?>">Tuần sau</a>
                    <form data-week-nav-form="1" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2 py-1" method="get" action="<?= e(page_url('classrooms-academic')); ?>">
                        <input type="hidden" name="page" value="classrooms-academic">
                        <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                        <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
                        <label class="text-[11px] font-semibold text-slate-600" for="classroom-week-picker">Chọn tuần</label>
                        <input id="classroom-week-picker" type="week" name="week_ref" value="<?= e($weekRefValue); ?>" class="h-7 rounded-md border border-slate-300 bg-white px-2 text-xs font-semibold text-slate-700">
                        <button type="submit" class="inline-flex h-7 items-center rounded-md border border-slate-300 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Xem</button>
                    </form>
                    <span id="classroom-weekly-loading" class="hidden inline-flex h-8 items-center rounded-md border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700">Đang tải tuần...</span>
                </div>
            </div>

            <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">Xanh: Đã có giáo án buổi học</span>
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 font-semibold text-rose-700">Đỏ: Khung lịch chưa có giáo án</span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 font-semibold text-slate-600">Chuột phải hoặc phím Menu / Shift+F10 để mở menu thao tác nhanh</span>
            </div>

            <?php if (empty($weekTimeSlots)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Không có lịch học trong tuần này.</div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <div class="min-w-[980px]">
                        <table class="w-full border-collapse border border-slate-300 text-sm">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap border border-slate-300 bg-slate-100 px-2 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-700">Khung giờ</th>
                                    <?php foreach ($weekDays as $weekDay): ?>
                                        <th class="border border-slate-300 bg-slate-100 px-2 py-2 text-center">
                                            <div class="font-semibold"><?= e((string) $weekDay['label']); ?></div>
                                            <div class="text-[11px] font-medium text-slate-500"><?= e((string) $weekDay['display']); ?></div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($weekTimeSlots as $slot): ?>
                                    <tr>
                                        <td class="whitespace-nowrap border border-slate-300 bg-slate-50 px-2 py-2 text-xs font-semibold text-slate-700"><?= e((string) $slot['label']); ?></td>
                                        <?php foreach ($weekDays as $weekDay): ?>
                                            <?php $slotSchedules = $weekScheduleGrid[(string) $weekDay['value']][(string) $slot['key']] ?? []; ?>
                                            <td class="min-w-[160px] border border-slate-300 align-top px-1.5 py-1.5">
                                                <?php if (empty($slotSchedules)): ?>
                                                    <span class="text-xs text-slate-300">-</span>
                                                <?php else: ?>
                                                    <?php foreach ($slotSchedules as $slotSchedule): ?>
                                                        <?php
                                                        $scheduleId = (int) ($slotSchedule['id'] ?? 0);
                                                        $assignedLessonId = (int) ($slotSchedule['assigned_lesson_id'] ?? 0);
                                                        if ($assignedLessonId <= 0 && isset($lessonByScheduleId[$scheduleId])) {
                                                            $assignedLessonId = (int) ($lessonByScheduleId[$scheduleId]['id'] ?? 0);
                                                        }
                                                        $linkedLesson = $assignedLessonId > 0 ? ($lessonByScheduleId[$scheduleId] ?? null) : null;
                                                        $hasLesson = $assignedLessonId > 0;
                                                        $lessonTitle = $hasLesson
                                                            ? trim((string) ($linkedLesson['actual_title'] ?? ($slotSchedule['assigned_lesson_title'] ?? 'Buổi học')))
                                                            : 'Chưa soạn giáo án buổi học';
                                                        $roomName = trim((string) ($slotSchedule['room_name'] ?? 'Online'));
                                                        $timeLabel = $formatTime((string) ($slotSchedule['start_time'] ?? '')) . '-' . $formatTime((string) ($slotSchedule['end_time'] ?? ''));
                                                        $attendanceSummary = '';
                                                        if (is_array($linkedLesson)) {
                                                            $attendanceSummary = 'Có mặt ' . (int) ($linkedLesson['present_count'] ?? 0)
                                                                . ' | Muộn ' . (int) ($linkedLesson['late_count'] ?? 0)
                                                                . ' | Vắng ' . (int) ($linkedLesson['absent_count'] ?? 0);
                                                        }

                                                        $lessonStats = $hasLesson
                                                            ? ($lessonStatsById[$assignedLessonId] ?? ['assignment_count' => 0, 'submission_count' => 0, 'ungraded_count' => 0])
                                                            : ['assignment_count' => 0, 'submission_count' => 0, 'ungraded_count' => 0];
                                                        $assignmentCount = (int) ($lessonStats['assignment_count'] ?? 0);
                                                        $submissionCount = (int) ($lessonStats['submission_count'] ?? 0);
                                                        $ungradedCount = (int) ($lessonStats['ungraded_count'] ?? 0);

                                                        $defaultAssignmentId = $hasLesson ? (int) ($assignmentDefaultByLesson[$assignedLessonId] ?? 0) : 0;

                                                        $attendanceUrl = page_url('attendance-academic', [
                                                            'course_id' => $selectedCourseId,
                                                            'class_id' => $selectedClassId,
                                                            'schedule_id' => $scheduleId,
                                                        ]);

                                                        $assignmentQuery = ['class_id' => $selectedClassId];
                                                        if ($hasLesson) {
                                                            $assignmentQuery['lesson_id'] = $assignedLessonId;
                                                        }
                                                        $assignmentUrl = page_url('assignments-academic', $assignmentQuery);

                                                        $gradingQuery = ['class_id' => $selectedClassId];
                                                        if ($hasLesson) {
                                                            $gradingQuery['lesson_id'] = $assignedLessonId;
                                                            if ($defaultAssignmentId > 0) {
                                                                $gradingQuery['assignment_id'] = $defaultAssignmentId;
                                                            }
                                                        }
                                                        $gradingUrl = page_url('submissions-academic', $gradingQuery);

                                                        $chipClasses = $hasLesson
                                                            ? 'border-emerald-300 bg-emerald-50 text-emerald-800'
                                                            : 'border-rose-300 bg-rose-50 text-rose-700';

                                                        if ($focusedScheduleId > 0 && $focusedScheduleId === $scheduleId) {
                                                            $chipClasses .= ' ring-2 ring-blue-300';
                                                        }

                                                        $slotLabel = ($lessonTitle !== '' ? $lessonTitle : 'Buổi học')
                                                            . ' | ' . $timeLabel
                                                            . ' | ' . ($roomName !== '' ? $roomName : 'Online');
                                                        ?>
                                                        <div
                                                            class="classroom-week-chip mb-1 last:mb-0 cursor-context-menu rounded-lg border px-2 py-1.5 text-[11px] font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 <?= e($chipClasses); ?>"
                                                            data-classroom-slot="1"
                                                            data-course-id="<?= (int) $selectedCourseId; ?>"
                                                            data-class-id="<?= (int) $selectedClassId; ?>"
                                                            data-schedule-id="<?= $scheduleId; ?>"
                                                            data-lesson-id="<?= $assignedLessonId; ?>"
                                                            data-default-assignment-id="<?= $defaultAssignmentId; ?>"
                                                            data-assignment-count="<?= $assignmentCount; ?>"
                                                            data-submission-count="<?= $submissionCount; ?>"
                                                            data-ungraded-count="<?= $ungradedCount; ?>"
                                                            data-has-lesson="<?= $hasLesson ? '1' : '0'; ?>"
                                                            data-slot-label="<?= e($slotLabel); ?>"
                                                            data-lesson-title="<?= e($lessonTitle !== '' ? $lessonTitle : 'Buổi học'); ?>"
                                                            data-url-attendance="<?= e($attendanceUrl); ?>"
                                                            data-url-assignment="<?= e($assignmentUrl); ?>"
                                                            data-url-grading="<?= e($gradingUrl); ?>"
                                                            title="<?= e($slotLabel); ?>"
                                                            tabindex="0"
                                                            role="button"
                                                            aria-haspopup="menu"
                                                            aria-expanded="false"
                                                        >
                                                            <div class="truncate"><?= e($lessonTitle !== '' ? $lessonTitle : 'Buổi học'); ?></div>
                                                            <div class="text-[10px] font-medium opacity-80"><?= e($timeLabel . ' | ' . ($roomName !== '' ? $roomName : 'Online')); ?></div>
                                                            <?php if ($attendanceSummary !== ''): ?>
                                                                <div class="text-[10px] font-medium opacity-80"><?= e($attendanceSummary); ?></div>
                                                            <?php endif; ?>
                                                            <div class="mt-1 flex flex-wrap gap-1 text-[10px]">
                                                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-1.5 py-0.5 font-semibold">BT: <?= $assignmentCount; ?></span>
                                                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-1.5 py-0.5 font-semibold">Nộp: <?= $submissionCount; ?></span>
                                                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white/80 px-1.5 py-0.5 font-semibold">Chưa chấm: <?= $ungradedCount; ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </article>

        <?php if (!empty($unscheduledLessons)): ?>
            <article class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3>Giáo án chưa xếp vào khung lịch</h3>
                <p class="mb-3 text-sm text-slate-500">Kéo giáo án và thả vào ô lịch chưa có giáo án để xếp nhanh.</p>
                <div class="flex flex-wrap gap-2.5">
                    <?php foreach ($unscheduledLessons as $lesson): ?>
                        <?php
                        $lessonId = (int) ($lesson['id'] ?? 0);
                        $unscheduledTitle = (string) ($lesson['actual_title'] ?? ('Buổi #' . $lessonId));
                        ?>
                        <a
                            class="inline-flex max-w-[320px] items-center gap-2 rounded-lg border border-rose-300 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:border-rose-400 hover:bg-rose-100"
                            href="<?= e(page_url('classrooms-academic', ['course_id' => $selectedCourseId, 'class_id' => $selectedClassId, 'lesson_id' => $lessonId, 'week_start' => $weekStartValue, 'week_ref' => $weekRefValue])); ?>"
                            data-open-lesson-modal="1"
                            data-draggable-lesson="1"
                            data-lesson-id="<?= $lessonId; ?>"
                            data-schedule-id="0"
                            data-slot-label="<?= e($unscheduledTitle); ?>"
                            draggable="<?= $canUpdateLesson ? 'true' : 'false'; ?>"
                            title="Kéo để xếp vào ô lịch trống hoặc click để chỉnh sửa"
                        >
                            <span class="truncate"><?= e($unscheduledTitle); ?></span>
                            <span class="shrink-0 rounded border border-rose-200 bg-white px-2 py-0.5 text-[10px] font-bold uppercase">Chưa xếp</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        </div>

        <article id="classroom-exams-card" class="rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-5 shadow-sm transition duration-200">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <h3 class="flex flex-wrap items-center gap-2">
                        <span>Danh sách học viên & bảng điểm</span>
                        <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700">Theo dõi trực tiếp</span>
                    </h3>
                    <p class="text-sm text-slate-600">Bấm vào tên học viên để xem hồ sơ. Bấm vào ô điểm để nhập điểm và nhận xét.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <?php if ($canManageExams): ?>
                        <button id="classroom-exams-open-create" class="<?= ui_btn_primary_classes(); ?>" type="button">Tạo cột điểm</button>
                    <?php else: ?>
                        <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Bạn chưa có quyền tạo/nhập điểm.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Học viên: <strong id="classroom-exams-meta-students" class="ml-1 text-slate-900">0</strong></span>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">Cột điểm: <strong id="classroom-exams-meta-columns" class="ml-1 text-slate-900">0</strong></span>
                <label class="ml-auto flex w-full max-w-xs items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs font-semibold text-slate-600">
                    <span class="shrink-0">Lọc học viên</span>
                    <input id="classroom-exams-filter" type="search" placeholder="Nhập tên học viên..." class="h-7 w-full border-0 bg-transparent px-1 text-sm text-slate-800 outline-none placeholder:text-slate-400">
                </label>
            </div>

            <div id="classroom-exams-banner" class="mt-3 hidden rounded-xl border p-3 text-sm"></div>

            <div id="classroom-exams-table-wrap" class="classroom-exams-wrap mt-3 overflow-auto rounded-xl border border-slate-200 bg-white/80">
                <div id="classroom-exams-state" class="p-4 text-sm text-slate-600">Đang tải bảng điểm...</div>
                <table id="classroom-exams-table" class="classroom-exams-table hidden min-w-full text-sm">
                    <thead id="classroom-exams-thead"></thead>
                    <tbody id="classroom-exams-tbody"></tbody>
                </table>
            </div>
        </article>
    <?php endif; ?>
</div>

<div
    id="classroom-context-menu"
    class="fixed z-[95] hidden min-w-[240px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl"
    role="menu"
    aria-label="Menu thao tác nhanh cho buổi học"
    tabindex="-1"
>
    <button id="classroom-menu-attendance" data-menu-item="1" data-action="attendance" role="menuitem" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 focus:bg-blue-50 focus:outline-none">Điểm danh</button>
    <button id="classroom-menu-detail" data-menu-item="1" data-action="lesson" role="menuitem" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 focus:bg-blue-50 focus:outline-none">Soạn giáo án buổi học</button>
    <button id="classroom-menu-assignment" data-menu-item="1" data-action="assignment" role="menuitem" type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 focus:bg-blue-50 focus:outline-none">Giao bài tập</button>
    <button id="classroom-menu-grading" data-menu-item="1" data-action="grading" role="menuitem" type="button" class="block w-full px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 focus:bg-blue-50 focus:outline-none">Chấm điểm</button>
</div>

<div
    id="classroom-exam-column-menu"
    class="fixed z-[97] hidden min-w-[220px] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl"
    role="menu"
    aria-label="Tùy chọn cột điểm"
    tabindex="-1"
>
    <button id="classroom-exam-column-edit" type="button" role="menuitem" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700 focus:bg-blue-50 focus:outline-none">Chỉnh sửa thông tin cột điểm</button>
    <button id="classroom-exam-column-delete" type="button" role="menuitem" class="block w-full px-3 py-2 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50 hover:text-rose-800 focus:bg-rose-50 focus:outline-none">Xóa cột điểm</button>
</div>

<form id="classroom-lesson-quick-assign-form" class="hidden" method="post" action="/api/lessons/save">
    <?= csrf_input(); ?>
    <input type="hidden" name="id" value="0">
    <input type="hidden" name="redirect_page" value="classrooms-academic">
    <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
    <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
    <input type="hidden" name="focus_schedule_id" value="0">
    <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
    <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">
    <input type="hidden" name="roadmap_id" value="0">
    <input type="hidden" name="actual_title" value="">
    <input type="hidden" name="actual_content" value="">
    <input type="hidden" name="schedule_id" value="0">
</form>

<div id="classroom-attendance-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-attendance-modal-title">
    <div class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 id="classroom-attendance-modal-title">Điểm danh theo buổi</h3>
                <p id="classroom-attendance-context" class="text-sm text-slate-600">Chưa chọn buổi học.</p>
            </div>
            <button id="classroom-attendance-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-attendance-form" class="grid min-h-0 gap-3" method="post" action="/api/lessons/attendance">
            <?= csrf_input(); ?>
            <input type="hidden" name="redirect_page" value="classrooms-academic">
            <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
            <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="classroom-attendance-schedule-id" type="hidden" name="schedule_id" value="0">
            <input id="classroom-attendance-focus-schedule-id" type="hidden" name="focus_schedule_id" value="0">
            <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
            <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">Tổng: <span id="classroom-attendance-summary-total" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">Có mặt: <span id="classroom-attendance-summary-present" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Đi muộn: <span id="classroom-attendance-summary-late" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 font-semibold text-rose-700">Vắng: <span id="classroom-attendance-summary-absent" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">Chưa đánh dấu: <span id="classroom-attendance-summary-unmarked" class="ml-1">0</span></span>
            </div>

            <?php if (!$canManageAttendance): ?>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">Bạn có quyền xem nhưng chưa có quyền cập nhật điểm danh.</div>
            <?php endif; ?>

            <div id="classroom-attendance-state" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">Chọn buổi học để tải danh sách điểm danh.</div>
            <div id="classroom-attendance-list" class="max-h-[50vh] overflow-y-auto rounded-xl border border-slate-200"></div>

            <div class="mt-1 flex flex-wrap gap-2">
                <?php if ($canManageAttendance): ?>
                    <button id="classroom-attendance-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu điểm danh</button>
                <?php endif; ?>
                <button id="classroom-attendance-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-lesson-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-lesson-modal-title">
    <div class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-lesson-modal-title">Soạn giáo án buổi học</h3>
                <p id="classroom-lesson-context" class="text-sm text-slate-600">Chưa chọn buổi học.</p>
            </div>
            <button id="classroom-lesson-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <?php if (!$canCreateLesson && !$canUpdateLesson): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">Bạn chưa có quyền soạn hoặc cập nhật giáo án buổi học.</div>
        <?php else: ?>
            <form id="classroom-lesson-form" class="grid min-h-0 gap-3" method="post" action="/api/lessons/save">
                <?= csrf_input(); ?>
                <input id="classroom-lesson-id" type="hidden" name="id" value="0">
                <input type="hidden" name="redirect_page" value="classrooms-academic">
                <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
                <input id="classroom-lesson-focus-schedule-id" type="hidden" name="focus_schedule_id" value="0">
                <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
                <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">

                <label>
                    Lộ trình (tùy chọn)
                    <select id="classroom-lesson-roadmap-id" name="roadmap_id">
                        <option value="0">-- Không gán lộ trình --</option>
                        <?php foreach ($roadmaps as $roadmap): ?>
                            <?php $roadmapId = (int) ($roadmap['id'] ?? 0); ?>
                            <option value="<?= $roadmapId; ?>">
                                Buổi <?= (int) ($roadmap['order'] ?? 0); ?> | <?= e((string) ($roadmap['topic_title'] ?? ('Roadmap #' . $roadmapId))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tiêu đề buổi học
                    <input id="classroom-lesson-actual-title" type="text" name="actual_title" required placeholder="Ví dụ: Speaking Practice - Topic Debate">
                </label>
                <label>
                    Nội dung giáo án
                    <textarea id="classroom-lesson-actual-content" name="actual_content" rows="5" placeholder="Mục tiêu, hoạt động lớp, tổng hợp nội dung buổi học..."></textarea>
                </label>
                <label>
                    Xếp vào lịch học (có thể để trống)
                    <select id="classroom-lesson-schedule-id" name="schedule_id">
                        <option value="0">-- Chưa xếp lịch học --</option>
                    </select>
                </label>

                <div class="mt-1 flex flex-wrap gap-2">
                    <button id="classroom-lesson-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu giáo án</button>
                    <button id="classroom-lesson-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<div id="classroom-assignment-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-assignment-modal-title">
    <div class="mx-auto mt-8 w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-assignment-modal-title">Giao bài tập theo buổi</h3>
                <p id="classroom-assignment-context" class="text-sm text-slate-600">Chưa chọn buổi học.</p>
            </div>
            <button id="classroom-assignment-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-assignment-form" class="grid gap-3" method="post" action="/api/assignments/save" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <input id="classroom-assignment-id" type="hidden" name="id" value="0">
            <input id="classroom-assignment-existing-file-url" type="hidden" name="existing_file_url" value="">
            <input type="hidden" name="redirect_page" value="classrooms-academic">
            <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
            <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="classroom-assignment-lesson-id" type="hidden" name="lesson_id" value="0">
            <input id="classroom-assignment-schedule-id" type="hidden" name="schedule_id" value="0">
            <input id="classroom-assignment-focus-schedule-id" type="hidden" name="focus_schedule_id" value="0">
            <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
            <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">

            <label>
                Tiêu đề bài tập
                <input id="classroom-assignment-title" type="text" name="title" required placeholder="Ví dụ: Homework - Unit 5">
            </label>
            <label>
                Mô tả
                <textarea name="description" rows="4" placeholder="Yêu cầu, tiêu chí chấm, định dạng file nộp..."></textarea>
            </label>
            <label>
                Hạn nộp
                <input type="datetime-local" name="deadline" required>
            </label>
            <label>
                File đính kèm (tùy chọn)
                <input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png">
            </label>

            <div class="mt-1 flex flex-wrap gap-2">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu bài tập</button>
                <button id="classroom-assignment-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-grading-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-grading-modal-title">
    <div class="mx-auto mt-4 flex max-h-[calc(100vh-2rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
            <div>
                <h3 id="classroom-grading-modal-title">Chấm điểm theo buổi</h3>
                <p id="classroom-grading-context" class="text-sm text-slate-600">Chưa chọn buổi học.</p>
            </div>
            <button id="classroom-grading-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-grading-form" class="grid min-h-0 gap-3" method="post" action="/api/submissions/grade">
            <?= csrf_input(); ?>
            <input type="hidden" name="redirect_page" value="classrooms-academic">
            <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
            <input id="classroom-grading-class-id" type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="classroom-grading-lesson-id" type="hidden" name="lesson_id" value="0">
            <input id="classroom-grading-schedule-id" type="hidden" name="schedule_id" value="0">
            <input id="classroom-grading-focus-schedule-id" type="hidden" name="focus_schedule_id" value="0">
            <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
            <input type="hidden" name="week_ref" value="<?= e($weekRefValue); ?>">
            <input type="hidden" name="grade_status" value="pending">
            <input type="hidden" name="submission_page" value="1">
            <input type="hidden" name="submission_per_page" value="10">

            <label class="max-w-md">
                Bài tập cần chấm
                <select id="classroom-grading-assignment-select" name="assignment_id" required>
                    <option value="0">-- Chọn bài tập --</option>
                </select>
            </label>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">Tổng: <span id="classroom-grading-summary-total" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 font-semibold text-blue-700">Đã nộp: <span id="classroom-grading-summary-submitted" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">Chưa nộp: <span id="classroom-grading-summary-missing" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">Đã chấm: <span id="classroom-grading-summary-graded" class="ml-1">0</span></span>
                <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 font-semibold text-rose-700">Chưa chấm: <span id="classroom-grading-summary-pending" class="ml-1">0</span></span>
            </div>

            <?php if ($canGradeSubmission): ?>
                <div class="flex flex-wrap items-center gap-2">
                    <button id="classroom-grading-select-all" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Chọn tất cả đã nộp</button>
                    <button id="classroom-grading-clear" type="button" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Bỏ chọn</button>
                </div>
            <?php else: ?>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">Bạn chưa có quyền chấm điểm.</div>
            <?php endif; ?>

            <div id="classroom-grading-state" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">Chọn bài tập để tải danh sách chấm điểm.</div>
            <div id="classroom-grading-list" class="grid max-h-[50vh] gap-2 overflow-y-auto pr-1"></div>

            <div class="mt-1 flex flex-wrap gap-2">
                <?php if ($canGradeSubmission): ?>
                    <button id="classroom-grading-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu chấm điểm đã chọn</button>
                <?php endif; ?>
                <button id="classroom-grading-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-student-profile-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-student-profile-modal-title">
    <div class="mx-auto mt-6 w-full max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-student-profile-modal-title">Hồ sơ học viên</h3>
                <p id="classroom-student-profile-context" class="text-sm text-slate-600">Chưa chọn học viên.</p>
            </div>
            <button id="classroom-student-profile-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <div id="classroom-student-profile-state" class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">Đang tải hồ sơ...</div>
        <div id="classroom-student-profile-body" class="mt-3 hidden grid gap-3 text-sm sm:grid-cols-2"></div>
    </div>
</div>

<div id="classroom-exam-create-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-exam-create-modal-title">
    <div class="mx-auto mt-6 w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-exam-create-modal-title">Tạo cột điểm mới</h3>
                <p class="text-sm text-slate-600">Nhập thông tin cơ bản để tạo cột và sinh record cho toàn bộ học viên.</p>
            </div>
            <button id="classroom-exam-create-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-exam-create-form" class="grid gap-3" method="post" action="/api/exams/create-column" autocomplete="off">
            <?= csrf_input(); ?>
            <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Tên cột điểm
                <input type="text" name="exam_name" required placeholder="Ví dụ: Quiz 1 / Midterm" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Loại
                <select name="exam_type" required class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">-- Chọn loại --</option>
                    <option value="entry">Entry</option>
                    <option value="periodic">Periodic</option>
                    <option value="final">Final</option>
                </select>
            </label>
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Ngày kiểm tra
                <input type="date" name="exam_date" required class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>

            <div class="mt-1 flex flex-wrap gap-2">
                <button id="classroom-exam-create-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Tạo cột điểm</button>
                <button id="classroom-exam-create-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-exam-edit-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-exam-edit-modal-title">
    <div class="mx-auto mt-6 w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-exam-edit-modal-title">Chỉnh sửa cột điểm</h3>
                <p id="classroom-exam-edit-context" class="text-sm text-slate-600">Cập nhật tên, loại và ngày kiểm tra của cột điểm.</p>
            </div>
            <button id="classroom-exam-edit-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-exam-edit-form" class="grid gap-3" method="post" action="/api/exams/update-column" autocomplete="off">
            <?= csrf_input(); ?>
            <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="classroom-exam-edit-old-exam-key" type="hidden" name="old_exam_key" value="">
            <input id="classroom-exam-edit-old-name" type="hidden" name="old_exam_name" value="">
            <input id="classroom-exam-edit-old-type" type="hidden" name="old_exam_type" value="">
            <input id="classroom-exam-edit-old-date" type="hidden" name="old_exam_date" value="">

            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Tên cột điểm
                <input id="classroom-exam-edit-name" type="text" name="exam_name" required placeholder="Ví dụ: Quiz 1 / Midterm" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Loại
                <select id="classroom-exam-edit-type" name="exam_type" required class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">-- Chọn loại --</option>
                    <option value="entry">Entry</option>
                    <option value="periodic">Periodic</option>
                    <option value="final">Final</option>
                </select>
            </label>
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Ngày kiểm tra
                <input id="classroom-exam-edit-date" type="date" name="exam_date" required class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>

            <div class="mt-1 flex flex-wrap gap-2">
                <button id="classroom-exam-edit-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu thay đổi</button>
                <button id="classroom-exam-edit-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-exam-score-modal" class="fixed inset-0 z-[96] hidden overflow-y-auto bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="classroom-exam-score-modal-title">
    <div class="mx-auto mt-6 w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
        <div class="mb-4 flex items-start justify-between gap-2">
            <div>
                <h3 id="classroom-exam-score-modal-title">Nhập điểm</h3>
                <p id="classroom-exam-score-context" class="text-sm text-slate-600">Chưa chọn học viên/cột điểm.</p>
            </div>
            <button id="classroom-exam-score-close" type="button" class="inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">Đóng</button>
        </div>

        <form id="classroom-exam-score-form" class="grid gap-3" method="post" action="/api/exams/save-score" autocomplete="off">
            <?= csrf_input(); ?>
            <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
            <input id="classroom-exam-score-student-id" type="hidden" name="student_id" value="0">
            <input id="classroom-exam-score-exam-id" type="hidden" name="exam_id" value="0">
            <input id="classroom-exam-score-exam-name" type="hidden" name="exam_name" value="">
            <input id="classroom-exam-score-exam-type" type="hidden" name="exam_type" value="">
            <input id="classroom-exam-score-exam-date" type="hidden" name="exam_date" value="">

            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Điểm / Kết quả
                <input id="classroom-exam-score-result" type="text" name="result" placeholder="Ví dụ: 8.5 hoặc A+" class="h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="grid gap-1 text-sm font-semibold text-slate-700">
                Nhận xét (tùy chọn)
                <textarea id="classroom-exam-score-comment" name="teacher_comment" rows="4" placeholder="Nhận xét nhanh về bài kiểm tra..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-2 focus:ring-blue-100"></textarea>
            </label>

            <div class="mt-1 flex flex-wrap gap-2">
                <button id="classroom-exam-score-submit" class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu</button>
                <button id="classroom-exam-score-cancel" class="<?= ui_btn_secondary_classes(); ?>" type="button">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div id="classroom-drag-toast" class="pointer-events-none fixed bottom-4 right-4 z-[120] hidden rounded-lg border px-3 py-2 text-xs font-semibold shadow-lg transition duration-150"></div>

<style>
.classroom-drop-hint-valid {
    box-shadow: inset 0 0 0 2px rgba(16, 185, 129, 0.65);
    background-color: rgba(16, 185, 129, 0.08);
}

.classroom-drop-hint-invalid {
    box-shadow: inset 0 0 0 2px rgba(244, 63, 94, 0.45);
    background-color: rgba(244, 63, 94, 0.06);
}

.classroom-drop-hover-valid {
    box-shadow: inset 0 0 0 3px rgba(5, 150, 105, 0.75);
    background-color: rgba(16, 185, 129, 0.14);
}

.classroom-drop-hover-invalid {
    box-shadow: inset 0 0 0 3px rgba(225, 29, 72, 0.55);
    background-color: rgba(244, 63, 94, 0.12);
}

.classroom-exams-table .classroom-exams-head-cell {
    position: sticky;
    top: 0;
    z-index: 4;
    border-bottom: 1px solid rgb(148 163 184 / 0.7);
    border-right: 1px solid rgb(148 163 184 / 0.55);
    background: rgba(248, 250, 252, 0.95);
    backdrop-filter: blur(4px);
}

.classroom-exam-head-inner {
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 8px;
    width: 100%;
}

.classroom-exam-head-text {
    min-width: 0;
    flex: 1 1 auto;
}

.classroom-exams-head-cell.is-column-actionable:hover {
    background: rgb(239 246 255 / 0.95);
}

.classroom-exams-head-cell.is-column-actionable:focus-visible {
    outline: 2px solid rgb(147 197 253 / 1);
    outline-offset: -2px;
}

.classroom-exams-table .classroom-exams-sticky-col {
    position: sticky;
    left: 0;
    z-index: 3;
    box-shadow: 1px 0 0 rgb(148 163 184 / 0.55);
}

.classroom-exams-table tbody .classroom-exams-sticky-col {
    background: rgb(248 250 252 / 1);
}

.classroom-exams-table tbody tr:nth-child(even) td {
    background: rgb(252 252 253 / 1);
}

.classroom-exams-table tbody tr:hover td {
    background: rgb(247 251 255 / 1);
}

.classroom-exams-table .classroom-exams-cell {
    border-bottom: 1px solid rgb(203 213 225 / 0.95);
    border-right: 1px solid rgb(203 213 225 / 0.95);
    padding: 10px 10px;
    vertical-align: top;
}

.classroom-exams-metric-cell {
    min-width: 145px;
    font-variant-numeric: tabular-nums;
}

.classroom-exams-name-btn {
    color: rgb(37 99 235 / 1);
    font-weight: 700;
    transition: color 0.18s ease;
}

.classroom-exams-name-btn:hover {
    color: rgb(29 78 216 / 1);
    text-decoration: underline;
}

.classroom-exams-score-btn {
    width: 100%;
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 7px 9px;
    text-align: left;
    transition: all 0.18s ease;
}

.classroom-exams-score-btn[data-has-value="0"] {
    color: rgb(190 24 93 / 1);
    background: rgb(255 241 242 / 1);
    border-color: rgb(254 205 211 / 1);
}

.classroom-exams-score-btn[data-has-value="0"]:hover {
    color: rgb(159 18 57 / 1);
    background: rgb(255 228 230 / 1);
}

.classroom-exams-score-btn[data-has-value="1"] {
    color: rgb(15 23 42 / 1);
    background: rgb(241 245 249 / 1);
    border-color: rgb(226 232 240 / 1);
    font-weight: 700;
}

.classroom-exams-score-btn[data-has-value="1"]:hover {
    color: rgb(30 64 175 / 1);
    background: rgb(239 246 255 / 1);
    border-color: rgb(191 219 254 / 1);
}

.classroom-exams-score-btn.is-updated {
    animation: classroomExamPulse 0.85s ease;
}

.classroom-exams-col-added {
    animation: classroomExamAppear 0.32s ease;
}

@keyframes classroomExamPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.45);
    }
    100% {
        box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
    }
}

@keyframes classroomExamAppear {
    0% {
        opacity: 0;
        transform: translateY(4px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
(function () {
    const contextMenu = document.getElementById('classroom-context-menu');
    const itemAttendance = document.getElementById('classroom-menu-attendance');
    const itemDetail = document.getElementById('classroom-menu-detail');
    const itemAssignment = document.getElementById('classroom-menu-assignment');
    const itemGrading = document.getElementById('classroom-menu-grading');
    const menuItems = contextMenu ? Array.from(contextMenu.querySelectorAll('[data-menu-item="1"]')) : [];
    const dragToast = document.getElementById('classroom-drag-toast');

    const filterForm = document.querySelector('form[data-classroom-filter="1"]');
    const filterCourseSelect = document.getElementById('classroom-filter-course');
    const filterClassWrap = document.getElementById('classroom-filter-class-wrap');
    const filterClassSelect = document.getElementById('classroom-filter-class');

    const examsCard = document.getElementById('classroom-exams-card');
    const examsOpenCreateButton = document.getElementById('classroom-exams-open-create');
    const examsFilterInput = document.getElementById('classroom-exams-filter');
    const examsMetaStudents = document.getElementById('classroom-exams-meta-students');
    const examsMetaColumns = document.getElementById('classroom-exams-meta-columns');
    const examsBanner = document.getElementById('classroom-exams-banner');
    const examsState = document.getElementById('classroom-exams-state');
    const examsTableWrap = document.getElementById('classroom-exams-table-wrap');
    const examsTable = document.getElementById('classroom-exams-table');
    const examsThead = document.getElementById('classroom-exams-thead');
    const examsTbody = document.getElementById('classroom-exams-tbody');

    const studentProfileModal = document.getElementById('classroom-student-profile-modal');
    const studentProfileContext = document.getElementById('classroom-student-profile-context');
    const studentProfileState = document.getElementById('classroom-student-profile-state');
    const studentProfileBody = document.getElementById('classroom-student-profile-body');
    const studentProfileCloseButton = document.getElementById('classroom-student-profile-close');

    const examCreateModal = document.getElementById('classroom-exam-create-modal');
    const examCreateForm = document.getElementById('classroom-exam-create-form');
    const examCreateCloseButton = document.getElementById('classroom-exam-create-close');
    const examCreateCancelButton = document.getElementById('classroom-exam-create-cancel');
    const examCreateSubmitButton = document.getElementById('classroom-exam-create-submit');

    const examColumnMenu = document.getElementById('classroom-exam-column-menu');
    const examColumnEditButton = document.getElementById('classroom-exam-column-edit');
    const examColumnDeleteButton = document.getElementById('classroom-exam-column-delete');

    const examEditModal = document.getElementById('classroom-exam-edit-modal');
    const examEditForm = document.getElementById('classroom-exam-edit-form');
    const examEditContext = document.getElementById('classroom-exam-edit-context');
    const examEditCloseButton = document.getElementById('classroom-exam-edit-close');
    const examEditCancelButton = document.getElementById('classroom-exam-edit-cancel');
    const examEditSubmitButton = document.getElementById('classroom-exam-edit-submit');
    const examEditOldExamKeyInput = document.getElementById('classroom-exam-edit-old-exam-key');
    const examEditOldNameInput = document.getElementById('classroom-exam-edit-old-name');
    const examEditOldTypeInput = document.getElementById('classroom-exam-edit-old-type');
    const examEditOldDateInput = document.getElementById('classroom-exam-edit-old-date');
    const examEditNameInput = document.getElementById('classroom-exam-edit-name');
    const examEditTypeInput = document.getElementById('classroom-exam-edit-type');
    const examEditDateInput = document.getElementById('classroom-exam-edit-date');

    const examScoreModal = document.getElementById('classroom-exam-score-modal');
    const examScoreForm = document.getElementById('classroom-exam-score-form');
    const examScoreContext = document.getElementById('classroom-exam-score-context');
    const examScoreCloseButton = document.getElementById('classroom-exam-score-close');
    const examScoreCancelButton = document.getElementById('classroom-exam-score-cancel');
    const examScoreSubmitButton = document.getElementById('classroom-exam-score-submit');
    const examScoreStudentIdInput = document.getElementById('classroom-exam-score-student-id');
    const examScoreExamIdInput = document.getElementById('classroom-exam-score-exam-id');
    const examScoreExamNameInput = document.getElementById('classroom-exam-score-exam-name');
    const examScoreExamTypeInput = document.getElementById('classroom-exam-score-exam-type');
    const examScoreExamDateInput = document.getElementById('classroom-exam-score-exam-date');
    const examScoreResultInput = document.getElementById('classroom-exam-score-result');
    const examScoreCommentInput = document.getElementById('classroom-exam-score-comment');

    const quickAssignForm = document.getElementById('classroom-lesson-quick-assign-form');
    const quickAssignIdInput = quickAssignForm ? quickAssignForm.querySelector('input[name="id"]') : null;
    const quickAssignRoadmapInput = quickAssignForm ? quickAssignForm.querySelector('input[name="roadmap_id"]') : null;
    const quickAssignTitleInput = quickAssignForm ? quickAssignForm.querySelector('input[name="actual_title"]') : null;
    const quickAssignContentInput = quickAssignForm ? quickAssignForm.querySelector('input[name="actual_content"]') : null;
    const quickAssignScheduleInput = quickAssignForm ? quickAssignForm.querySelector('input[name="schedule_id"]') : null;
    const quickAssignFocusScheduleInput = quickAssignForm ? quickAssignForm.querySelector('input[name="focus_schedule_id"]') : null;

    const attendanceModal = document.getElementById('classroom-attendance-modal');
    const attendanceForm = document.getElementById('classroom-attendance-form');
    const attendanceCloseButton = document.getElementById('classroom-attendance-close');
    const attendanceCancelButton = document.getElementById('classroom-attendance-cancel');
    const attendanceContext = document.getElementById('classroom-attendance-context');
    const attendanceScheduleIdInput = document.getElementById('classroom-attendance-schedule-id');
    const attendanceFocusScheduleIdInput = document.getElementById('classroom-attendance-focus-schedule-id');
    const attendanceState = document.getElementById('classroom-attendance-state');
    const attendanceList = document.getElementById('classroom-attendance-list');
    const attendanceSubmitButton = document.getElementById('classroom-attendance-submit');
    const attendanceSummaryTotal = document.getElementById('classroom-attendance-summary-total');
    const attendanceSummaryPresent = document.getElementById('classroom-attendance-summary-present');
    const attendanceSummaryLate = document.getElementById('classroom-attendance-summary-late');
    const attendanceSummaryAbsent = document.getElementById('classroom-attendance-summary-absent');
    const attendanceSummaryUnmarked = document.getElementById('classroom-attendance-summary-unmarked');

    const lessonCreateButton = document.getElementById('classroom-open-lesson-create');
    const lessonModal = document.getElementById('classroom-lesson-modal');
    const lessonForm = document.getElementById('classroom-lesson-form');
    const lessonCloseButton = document.getElementById('classroom-lesson-close');
    const lessonCancelButton = document.getElementById('classroom-lesson-cancel');
    const lessonContext = document.getElementById('classroom-lesson-context');
    const lessonModalTitle = document.getElementById('classroom-lesson-modal-title');
    const lessonSubmitButton = document.getElementById('classroom-lesson-submit');
    const lessonIdInput = document.getElementById('classroom-lesson-id');
    const lessonRoadmapInput = document.getElementById('classroom-lesson-roadmap-id');
    const lessonTitleInput = document.getElementById('classroom-lesson-actual-title');
    const lessonContentInput = document.getElementById('classroom-lesson-actual-content');
    const lessonScheduleInput = document.getElementById('classroom-lesson-schedule-id');
    const lessonFocusScheduleInput = document.getElementById('classroom-lesson-focus-schedule-id');

    const assignmentModal = document.getElementById('classroom-assignment-modal');
    const assignmentForm = document.getElementById('classroom-assignment-form');
    const assignmentCloseButton = document.getElementById('classroom-assignment-close');
    const assignmentCancelButton = document.getElementById('classroom-assignment-cancel');
    const assignmentContext = document.getElementById('classroom-assignment-context');
    const assignmentTitleInput = document.getElementById('classroom-assignment-title');
    const assignmentLessonIdInput = document.getElementById('classroom-assignment-lesson-id');
    const assignmentScheduleIdInput = document.getElementById('classroom-assignment-schedule-id');
    const assignmentFocusScheduleIdInput = document.getElementById('classroom-assignment-focus-schedule-id');

    const gradingModal = document.getElementById('classroom-grading-modal');
    const gradingForm = document.getElementById('classroom-grading-form');
    const gradingCloseButton = document.getElementById('classroom-grading-close');
    const gradingCancelButton = document.getElementById('classroom-grading-cancel');
    const gradingContext = document.getElementById('classroom-grading-context');
    const gradingAssignmentSelect = document.getElementById('classroom-grading-assignment-select');
    const gradingClassIdInput = document.getElementById('classroom-grading-class-id');
    const gradingLessonIdInput = document.getElementById('classroom-grading-lesson-id');
    const gradingScheduleIdInput = document.getElementById('classroom-grading-schedule-id');
    const gradingFocusScheduleIdInput = document.getElementById('classroom-grading-focus-schedule-id');
    const gradingState = document.getElementById('classroom-grading-state');
    const gradingList = document.getElementById('classroom-grading-list');
    const gradingSelectAllButton = document.getElementById('classroom-grading-select-all');
    const gradingClearButton = document.getElementById('classroom-grading-clear');
    const gradingSubmitButton = document.getElementById('classroom-grading-submit');

    const summaryTotal = document.getElementById('classroom-grading-summary-total');
    const summarySubmitted = document.getElementById('classroom-grading-summary-submitted');
    const summaryMissing = document.getElementById('classroom-grading-summary-missing');
    const summaryGraded = document.getElementById('classroom-grading-summary-graded');
    const summaryPending = document.getElementById('classroom-grading-summary-pending');

    const canCreateLesson = <?= $canCreateLesson ? 'true' : 'false'; ?>;
    const canUpdateLesson = <?= $canUpdateLesson ? 'true' : 'false'; ?>;
    const canViewAttendance = <?= $canViewAttendance ? 'true' : 'false'; ?>;
    const canManageAttendance = <?= $canManageAttendance ? 'true' : 'false'; ?>;
    const canCreateAssignment = <?= $canCreateAssignment ? 'true' : 'false'; ?>;
    const canGradeSubmission = <?= $canGradeSubmission ? 'true' : 'false'; ?>;
    const canManageExams = <?= $canManageExams ? 'true' : 'false'; ?>;
    const csrfToken = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const allClasses = <?= $classroomAllClassesJson; ?>;
    const selectedCourseId = <?= (int) $selectedCourseId; ?>;
    const selectedClassId = <?= (int) $selectedClassId; ?>;
    const lessonsById = <?= $classroomLessonsByIdJson; ?>;
    const schedulesForLessonModal = <?= $classroomScheduleOptionsJson; ?>;
    const assignmentsByLesson = <?= $classroomAssignmentsByLessonJson; ?>;
    const hasSuccessFlash = <?= $success ? 'true' : 'false'; ?>;
    const initialLessonId = <?= (int) ($editingLesson['id'] ?? 0); ?>;
    const initialPrefillScheduleId = <?= (int) $prefillScheduleId; ?>;

    if (!contextMenu || !itemAttendance || !itemDetail || !itemAssignment || !itemGrading) {
        return;
    }

    let slots = [];
    let activeSlotElement = null;
    let activeSlotContext = null;
    let activeMenuIndex = 0;
    let activeModal = null;
    let modalReturnFocusElement = null;
    let isWeekLoading = false;
    let isExamsLoading = false;
    let draggedLessonId = 0;
    let dragToastTimer = 0;
    let activeExamCellTrigger = null;
    let activeExamColumnTrigger = null;
    let activeExamColumnContext = null;
    let examsGridData = {
        students: [],
        exams: [],
        cells: {},
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

    function formatDateDisplay(isoDate) {
        const text = normalizeText(isoDate);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(text)) {
            return text;
        }

        const parts = text.split('-');
        return parts[2] + '/' + parts[1];
    }

    function formatExamTypeLabel(type) {
        const normalized = normalizeText(type);
        if (normalized === 'entry') {
            return 'Entry';
        }
        if (normalized === 'periodic') {
            return 'Periodic';
        }
        if (normalized === 'final') {
            return 'Final';
        }
        return normalized !== '' ? normalized : 'Khác';
    }

    function normalizeMetricRate(value) {
        const numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : null;
    }

    function formatRateText(rateValue) {
        if (!(typeof rateValue === 'number' && Number.isFinite(rateValue))) {
            return '--';
        }

        const rounded = Math.max(0, Math.round(rateValue * 10) / 10);
        if (Math.abs(rounded - Math.round(rounded)) < 0.001) {
            return String(Math.round(rounded)) + '%';
        }

        return rounded.toFixed(1) + '%';
    }

    function createExamMetricHeaderCell(title, subtitle) {
        const th = document.createElement('th');
        th.className = 'classroom-exams-head-cell min-w-[145px] px-3 py-2 text-center';
        th.innerHTML = '<div class="text-sm font-bold text-slate-800">' + escapeHtml(title) + '</div>'
            + '<div class="mt-0.5 text-xs font-semibold text-slate-500">' + escapeHtml(subtitle) + '</div>';
        return th;
    }

    function createStudentMetricCell(metric, mode) {
        const td = document.createElement('td');
        td.className = 'classroom-exams-cell classroom-exams-metric-cell whitespace-nowrap text-center';

        const total = Math.max(0, toInt(metric && metric.total));
        const done = Math.max(0, Math.min(total, toInt(metric && metric.done)));
        const rateValue = normalizeMetricRate(metric && metric.rate);
        const rateText = formatRateText(rateValue);

        let badgeClass = 'border-slate-200 bg-slate-100 text-slate-700';
        if (rateValue !== null) {
            if (rateValue >= 90) {
                badgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700';
            } else if (rateValue >= 70) {
                badgeClass = 'border-amber-200 bg-amber-50 text-amber-700';
            } else {
                badgeClass = 'border-rose-200 bg-rose-50 text-rose-700';
            }
        }

        const detailText = total > 0
            ? (String(done) + '/' + String(total) + (mode === 'attendance' ? ' buổi' : ' bài'))
            : 'Chưa có dữ liệu';

        td.innerHTML = '<div class="inline-flex items-center justify-center rounded-full border px-2.5 py-1 text-xs font-bold ' + badgeClass + '">' + escapeHtml(rateText) + '</div>'
            + '<div class="mt-1 text-[11px] font-semibold text-slate-500">' + escapeHtml(detailText) + '</div>';
        return td;
    }

    function setExamsBanner(kind, message) {
        if (!(examsBanner instanceof HTMLElement)) {
            return;
        }

        const text = normalizeText(message);
        if (text === '') {
            examsBanner.classList.add('hidden');
            examsBanner.textContent = '';
            return;
        }

        examsBanner.classList.remove('hidden');
        examsBanner.textContent = text;

        examsBanner.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
        examsBanner.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-700');
        examsBanner.classList.remove('border-slate-200', 'bg-slate-50', 'text-slate-700');

        if (kind === 'success') {
            examsBanner.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
        } else if (kind === 'error') {
            examsBanner.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
        } else {
            examsBanner.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700');
        }
    }

    function setClassSelectVisible(visible) {
        if (!(filterClassWrap instanceof HTMLElement)) {
            return;
        }

        if (visible) {
            filterClassWrap.classList.remove('hidden');
            filterClassWrap.classList.add('opacity-0', 'translate-y-1');
            window.requestAnimationFrame(function () {
                filterClassWrap.classList.remove('opacity-0', 'translate-y-1');
            });
            return;
        }

        filterClassWrap.classList.add('opacity-0', 'translate-y-1');
        window.setTimeout(function () {
            filterClassWrap.classList.add('hidden');
        }, 200);
    }

    function populateClassSelectForCourse(courseId) {
        if (!(filterClassSelect instanceof HTMLSelectElement)) {
            return;
        }

        const targetCourseId = toInt(courseId);
        const previousValue = toInt(filterClassSelect.value);
        filterClassSelect.innerHTML = '';

        if (targetCourseId <= 0) {
            const option = document.createElement('option');
            option.value = '0';
            option.textContent = '-- Chọn khóa học trước --';
            filterClassSelect.appendChild(option);
            filterClassSelect.disabled = true;
            filterClassSelect.value = '0';
            return;
        }

        const placeholder = document.createElement('option');
        placeholder.value = '0';
        placeholder.textContent = '-- Chọn lớp học --';
        filterClassSelect.appendChild(placeholder);

        let hasPrevious = false;
        allClasses.forEach(function (row) {
            const rowCourseId = toInt(row && row.course_id);
            const classId = toInt(row && row.id);
            if (classId <= 0 || rowCourseId !== targetCourseId) {
                return;
            }

            const option = document.createElement('option');
            option.value = String(classId);
            const className = normalizeText(row && row.class_name);
            option.textContent = className !== '' ? className : ('Lớp #' + classId);
            filterClassSelect.appendChild(option);

            if (previousValue > 0 && classId === previousValue) {
                hasPrevious = true;
            }
        });

        filterClassSelect.disabled = false;
        filterClassSelect.value = hasPrevious ? String(previousValue) : '0';
    }

    function initCourseFirstFilter() {
        if (!(filterCourseSelect instanceof HTMLSelectElement)) {
            return;
        }

        const currentCourse = toInt(filterCourseSelect.value || String(selectedCourseId));
        if (currentCourse > 0) {
            populateClassSelectForCourse(currentCourse);
            setClassSelectVisible(true);
        } else {
            populateClassSelectForCourse(0);
            setClassSelectVisible(false);
        }

        filterCourseSelect.addEventListener('change', function () {
            const nextCourseId = toInt(filterCourseSelect.value);
            populateClassSelectForCourse(nextCourseId);
            setClassSelectVisible(nextCourseId > 0);
        });
    }

    async function fetchJson(url, options) {
        const response = await fetch(url, options || {});
        const json = await response.json();
        return json;
    }

    function buildLocalExamKey(examName, examType, examDate) {
        return 'local|' + normalizeText(examName).toLowerCase() + '|' + normalizeText(examType).toLowerCase() + '|' + normalizeText(examDate);
    }

    function setExamsMetaSummary(studentsCount, columnsCount) {
        if (examsMetaStudents instanceof HTMLElement) {
            examsMetaStudents.textContent = String(toInt(studentsCount));
        }
        if (examsMetaColumns instanceof HTMLElement) {
            examsMetaColumns.textContent = String(toInt(columnsCount));
        }
    }

    function setExamsSyncing(isLoading) {
        isExamsLoading = isLoading;

        if (examsCard instanceof HTMLElement) {
            examsCard.classList.toggle('ring-1', isLoading);
            examsCard.classList.toggle('ring-blue-200', isLoading);
        }

        if (examsTableWrap instanceof HTMLElement) {
            examsTableWrap.classList.toggle('is-syncing', isLoading);
        }
    }

    function normalizeExamGridPayload(payload) {
        const students = Array.isArray(payload && payload.students)
            ? payload.students.map(function (student) {
                const studentMetrics = student && student.metrics && typeof student.metrics === 'object'
                    ? student.metrics
                    : {};
                const attendanceMetrics = studentMetrics.attendance && typeof studentMetrics.attendance === 'object'
                    ? studentMetrics.attendance
                    : {};
                const submissionMetrics = studentMetrics.submission && typeof studentMetrics.submission === 'object'
                    ? studentMetrics.submission
                    : {};

                return {
                    id: toInt(student && student.id),
                    name: normalizeText(student && student.name),
                    attendance: {
                        total: Math.max(0, toInt(attendanceMetrics.total_sessions)),
                        done: Math.max(0, toInt(attendanceMetrics.attended_sessions)),
                        rate: normalizeMetricRate(attendanceMetrics.rate),
                    },
                    submission: {
                        total: Math.max(0, toInt(submissionMetrics.total_assignments)),
                        done: Math.max(0, toInt(submissionMetrics.on_time_assignments)),
                        rate: normalizeMetricRate(submissionMetrics.rate),
                    },
                };
            }).filter(function (student) {
                return student.id > 0;
            })
            : [];

        const exams = Array.isArray(payload && payload.exams)
            ? payload.exams.map(function (exam) {
                const examName = normalizeText(exam && exam.exam_name);
                const examType = normalizeText(exam && exam.exam_type);
                const examDate = normalizeText(exam && exam.exam_date);
                const key = normalizeText(exam && exam.key) !== ''
                    ? normalizeText(exam && exam.key)
                    : buildLocalExamKey(examName, examType, examDate);

                return {
                    key: key,
                    exam_name: examName,
                    exam_type: examType,
                    exam_date: examDate,
                };
            })
            : [];

        const sourceCells = payload && typeof payload.cells === 'object' && payload.cells ? payload.cells : {};
        const cells = {};

        Object.keys(sourceCells).forEach(function (studentKey) {
            const studentId = toInt(studentKey);
            if (studentId <= 0) {
                return;
            }

            const studentCellsRaw = sourceCells[studentKey];
            if (!studentCellsRaw || typeof studentCellsRaw !== 'object') {
                return;
            }

            cells[String(studentId)] = {};
            Object.keys(studentCellsRaw).forEach(function (examKey) {
                const cell = studentCellsRaw[examKey] && typeof studentCellsRaw[examKey] === 'object'
                    ? studentCellsRaw[examKey]
                    : {};
                cells[String(studentId)][String(examKey)] = {
                    exam_id: toInt(cell.exam_id),
                    result: normalizeText(cell.result),
                    teacher_comment: normalizeText(cell.teacher_comment),
                };
            });
        });

        return {
            students: students,
            exams: exams,
            cells: cells,
        };
    }

    function createExamHeaderCell(exam) {
        const th = document.createElement('th');
        th.className = 'classroom-exams-head-cell min-w-[150px] px-3 py-2 text-center';
        const examKey = normalizeText(exam && exam.key);
        th.setAttribute('data-exam-key', examKey);

        const name = normalizeText(exam && exam.exam_name);
        const date = normalizeText(exam && exam.exam_date);
        const type = normalizeText(exam && exam.exam_type);
        th.setAttribute('data-exam-name', name);
        th.setAttribute('data-exam-type', type);
        th.setAttribute('data-exam-date', date);

        if (canManageExams) {
            th.classList.add('cursor-context-menu', 'is-column-actionable');
            th.setAttribute('aria-haspopup', 'menu');
            th.setAttribute('aria-expanded', 'false');
            th.setAttribute('tabindex', '0');
            th.title = 'Chuột phải để chỉnh sửa/xóa cột điểm';
        }

        const inner = document.createElement('div');
        inner.className = 'classroom-exam-head-inner';

        const textWrap = document.createElement('div');
        textWrap.className = 'classroom-exam-head-text';
        textWrap.innerHTML = '<div class="truncate text-[15px] font-bold text-slate-800">' + escapeHtml(name !== '' ? name : 'Cột điểm') + '</div>'
            + '<div class="mt-0.5 truncate text-xs font-semibold text-slate-500">' + escapeHtml(formatDateDisplay(date)) + ' | ' + escapeHtml(formatExamTypeLabel(type)) + '</div>';
        inner.appendChild(textWrap);

        th.appendChild(inner);
        return th;
    }

    function setExamCellButtonContent(buttonElement, result, comment) {
        if (!(buttonElement instanceof HTMLButtonElement)) {
            return;
        }

        const normalizedResult = normalizeText(result);
        const normalizedComment = normalizeText(comment);
        const hasValue = normalizedResult !== '';

        buttonElement.setAttribute('data-exam-result', normalizedResult);
        buttonElement.setAttribute('data-exam-comment', normalizedComment);
        buttonElement.setAttribute('data-has-value', hasValue ? '1' : '0');

        if (hasValue) {
            buttonElement.textContent = normalizedResult;
            buttonElement.title = normalizedComment !== '' ? normalizedComment : 'Bấm để cập nhật điểm';
            return;
        }

        buttonElement.innerHTML = '<span class="inline-flex items-center gap-1.5 font-semibold">'
            + '<span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span>'
            + 'Nhập điểm</span>';
        buttonElement.title = 'Bấm để nhập điểm';
    }

    function createExamCellButton(meta) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'classroom-exams-score-btn text-sm';
        button.setAttribute('data-exam-cell', '1');
        button.setAttribute('data-student-id', String(toInt(meta.studentId)));
        button.setAttribute('data-student-name', normalizeText(meta.studentName));
        button.setAttribute('data-exam-id', String(toInt(meta.examId)));
        button.setAttribute('data-exam-name', normalizeText(meta.examName));
        button.setAttribute('data-exam-type', normalizeText(meta.examType));
        button.setAttribute('data-exam-date', normalizeText(meta.examDate));
        button.setAttribute('data-exam-key', normalizeText(meta.examKey));
        setExamCellButtonContent(button, normalizeText(meta.result), normalizeText(meta.comment));
        return button;
    }

    function createExamCellTd(meta) {
        const td = document.createElement('td');
        td.className = 'classroom-exams-cell';

        if (!canManageExams) {
            td.textContent = normalizeText(meta.result) !== '' ? normalizeText(meta.result) : '-';
            return td;
        }

        td.appendChild(createExamCellButton(meta));
        return td;
    }

    function applyExamsStudentFilter() {
        if (!(examsFilterInput instanceof HTMLInputElement) || !(examsTbody instanceof HTMLElement) || !(examsState instanceof HTMLElement)) {
            return;
        }

        const keyword = normalizeText(examsFilterInput.value).toLowerCase();
        const rows = Array.from(examsTbody.querySelectorAll('tr[data-student-row="1"]'));
        let visibleRows = 0;

        rows.forEach(function (row) {
            if (!(row instanceof HTMLTableRowElement)) {
                return;
            }

            const rowName = normalizeText(row.getAttribute('data-student-name')).toLowerCase();
            const isVisible = keyword === '' || rowName.includes(keyword);
            row.classList.toggle('hidden', !isVisible);
            if (isVisible) {
                visibleRows++;
            }
        });

        if (rows.length > 0 && keyword !== '' && visibleRows === 0) {
            examsState.textContent = 'Không tìm thấy học viên phù hợp bộ lọc.';
            examsState.classList.remove('hidden');
        } else if (examsGridData.exams.length > 0) {
            examsState.classList.add('hidden');
        }
    }

    function renderExamsGrid(payload) {
        if (!(examsState instanceof HTMLElement) || !(examsTable instanceof HTMLTableElement) || !(examsThead instanceof HTMLElement) || !(examsTbody instanceof HTMLElement)) {
            return;
        }

        examsGridData = normalizeExamGridPayload(payload);
        const students = examsGridData.students;
        const exams = examsGridData.exams;
        const cells = examsGridData.cells;

        setExamsMetaSummary(students.length, exams.length);

        if (students.length === 0) {
            examsState.textContent = 'Lớp học chưa có học viên.';
            examsState.classList.remove('hidden');
            examsTable.classList.add('hidden');
            return;
        }

        examsThead.innerHTML = '';
        examsTbody.innerHTML = '';

        const headerRow = document.createElement('tr');

        const studentHeader = document.createElement('th');
        studentHeader.textContent = 'Học viên';
        studentHeader.className = 'classroom-exams-head-cell classroom-exams-sticky-col min-w-[220px] whitespace-nowrap px-3 py-2 text-left text-sm font-bold uppercase tracking-wide text-slate-800';
        headerRow.appendChild(studentHeader);

        headerRow.appendChild(createExamMetricHeaderCell('Đi học đầy đủ', 'Tỉ lệ buổi có mặt/muộn'));
        headerRow.appendChild(createExamMetricHeaderCell('Nộp đúng hạn', 'Tỉ lệ bài nộp đúng hạn'));

        exams.forEach(function (exam) {
            headerRow.appendChild(createExamHeaderCell(exam));
        });

        examsThead.appendChild(headerRow);

        students.forEach(function (student) {
            const studentId = toInt(student && student.id);
            const studentName = normalizeText(student && student.name);
            const tr = document.createElement('tr');
            tr.setAttribute('data-student-row', '1');
            tr.setAttribute('data-student-id', String(studentId));
            tr.setAttribute('data-student-name', studentName);

            const nameTd = document.createElement('td');
            nameTd.className = 'classroom-exams-cell classroom-exams-sticky-col whitespace-nowrap text-sm font-semibold text-slate-800';
            nameTd.innerHTML = '<button type="button" class="classroom-exams-name-btn text-left" data-student-profile="1" data-student-id="'
                + String(studentId)
                + '">' + escapeHtml(studentName !== '' ? studentName : ('Học viên #' + studentId)) + '</button>';
            tr.appendChild(nameTd);

            tr.appendChild(createStudentMetricCell(student && student.attendance, 'attendance'));
            tr.appendChild(createStudentMetricCell(student && student.submission, 'submission'));

            exams.forEach(function (exam) {
                const key = normalizeText(exam && exam.key);
                const metaName = normalizeText(exam && exam.exam_name);
                const metaType = normalizeText(exam && exam.exam_type);
                const metaDate = normalizeText(exam && exam.exam_date);

                const studentCells = cells && (cells[String(studentId)] || cells[studentId]) ? (cells[String(studentId)] || cells[studentId]) : null;
                const cell = studentCells && key !== '' ? studentCells[key] : null;
                const examId = toInt(cell && cell.exam_id);
                const result = normalizeText(cell && cell.result);
                const comment = normalizeText(cell && cell.teacher_comment);

                tr.appendChild(createExamCellTd({
                    studentId: studentId,
                    studentName: studentName,
                    examId: examId,
                    examName: metaName,
                    examType: metaType,
                    examDate: metaDate,
                    examKey: key,
                    result: result,
                    comment: comment,
                }));
            });

            examsTbody.appendChild(tr);
        });

        if (exams.length === 0) {
            examsState.textContent = canManageExams ? 'Chưa có cột điểm. Bấm “Tạo cột điểm” để thêm.' : 'Chưa có cột điểm.';
            examsState.classList.remove('hidden');
        } else {
            examsState.classList.add('hidden');
        }

        examsTable.classList.remove('hidden');
        applyExamsStudentFilter();
    }

    function appendExamColumnToGrid(meta) {
        const examName = normalizeText(meta && meta.exam_name);
        const examType = normalizeText(meta && meta.exam_type);
        const examDate = normalizeText(meta && meta.exam_date);
        if (examName === '' || examType === '' || examDate === '') {
            return false;
        }

        if (!(examsTable instanceof HTMLTableElement) || !(examsThead instanceof HTMLElement) || !(examsTbody instanceof HTMLElement)) {
            return false;
        }

        const exists = examsGridData.exams.some(function (exam) {
            return normalizeText(exam.exam_name) === examName
                && normalizeText(exam.exam_type) === examType
                && normalizeText(exam.exam_date) === examDate;
        });
        if (exists) {
            return false;
        }

        const examKey = buildLocalExamKey(examName, examType, examDate);
        const newExam = {
            key: examKey,
            exam_name: examName,
            exam_type: examType,
            exam_date: examDate,
        };
        examsGridData.exams.push(newExam);

        let headerRow = examsThead.querySelector('tr');
        if (!(headerRow instanceof HTMLTableRowElement)) {
            headerRow = document.createElement('tr');
            examsThead.appendChild(headerRow);

            const studentHeader = document.createElement('th');
            studentHeader.textContent = 'Học viên';
            studentHeader.className = 'classroom-exams-head-cell classroom-exams-sticky-col min-w-[220px] whitespace-nowrap px-3 py-2 text-left text-sm font-bold uppercase tracking-wide text-slate-800';
            headerRow.appendChild(studentHeader);

            headerRow.appendChild(createExamMetricHeaderCell('Đi học đầy đủ', 'Tỉ lệ buổi có mặt/muộn'));
            headerRow.appendChild(createExamMetricHeaderCell('Nộp đúng hạn', 'Tỉ lệ bài nộp đúng hạn'));
        }

        const newHeaderCell = createExamHeaderCell(newExam);
        newHeaderCell.classList.add('classroom-exams-col-added');
        headerRow.appendChild(newHeaderCell);

        const rows = Array.from(examsTbody.querySelectorAll('tr[data-student-row="1"]'));
        rows.forEach(function (rowElement) {
            if (!(rowElement instanceof HTMLTableRowElement)) {
                return;
            }

            const studentId = toInt(rowElement.getAttribute('data-student-id'));
            const studentName = normalizeText(rowElement.getAttribute('data-student-name'));

            const cellTd = createExamCellTd({
                studentId: studentId,
                studentName: studentName,
                examId: 0,
                examName: examName,
                examType: examType,
                examDate: examDate,
                examKey: examKey,
                result: '',
                comment: '',
            });
            cellTd.classList.add('classroom-exams-col-added');
            rowElement.appendChild(cellTd);

            if (!examsGridData.cells[String(studentId)] || typeof examsGridData.cells[String(studentId)] !== 'object') {
                examsGridData.cells[String(studentId)] = {};
            }
            examsGridData.cells[String(studentId)][examKey] = {
                exam_id: 0,
                result: '',
                teacher_comment: '',
            };
        });

        setExamsMetaSummary(examsGridData.students.length, examsGridData.exams.length);
        examsTable.classList.remove('hidden');
        examsState.classList.add('hidden');
        applyExamsStudentFilter();
        return true;
    }

    function findExamIndexByContext(context) {
        const examKey = normalizeText(context && context.examKey);
        if (examKey !== '') {
            const byKey = examsGridData.exams.findIndex(function (exam) {
                return normalizeText(exam && exam.key) === examKey;
            });
            if (byKey >= 0) {
                return byKey;
            }
        }

        const examName = normalizeText(context && context.exam_name);
        const examType = normalizeText(context && context.exam_type);
        const examDate = normalizeText(context && context.exam_date);
        if (examName === '' || examType === '' || examDate === '') {
            return -1;
        }

        return examsGridData.exams.findIndex(function (exam) {
            return normalizeText(exam && exam.exam_name) === examName
                && normalizeText(exam && exam.exam_type) === examType
                && normalizeText(exam && exam.exam_date) === examDate;
        });
    }

    function updateExamColumnInGrid(payload) {
        const oldMeta = payload && payload.oldMeta && typeof payload.oldMeta === 'object' ? payload.oldMeta : {};
        const newMeta = payload && payload.newMeta && typeof payload.newMeta === 'object' ? payload.newMeta : {};

        const index = findExamIndexByContext(oldMeta);
        if (index < 0 || !examsGridData.exams[index]) {
            return false;
        }

        const examRecord = examsGridData.exams[index];
        const examKey = normalizeText(examRecord && examRecord.key);

        examRecord.exam_name = normalizeText(newMeta.exam_name);
        examRecord.exam_type = normalizeText(newMeta.exam_type);
        examRecord.exam_date = normalizeText(newMeta.exam_date);

        if (examRecord.exam_name === '' || examRecord.exam_type === '' || examRecord.exam_date === '') {
            return false;
        }

        if (examsThead instanceof HTMLElement) {
            const headerCells = Array.from(examsThead.querySelectorAll('th[data-exam-key]'));
            const targetHeader = headerCells.find(function (headerCell) {
                return headerCell instanceof HTMLElement
                    && normalizeText(headerCell.getAttribute('data-exam-key')) === examKey;
            });

            if (targetHeader instanceof HTMLElement) {
                targetHeader.replaceWith(createExamHeaderCell(examRecord));
            }
        }

        if (examsTbody instanceof HTMLElement) {
            const examButtons = Array.from(examsTbody.querySelectorAll('[data-exam-cell="1"]'));
            examButtons.forEach(function (buttonElement) {
                if (!(buttonElement instanceof HTMLButtonElement)) {
                    return;
                }

                if (normalizeText(buttonElement.getAttribute('data-exam-key')) !== examKey) {
                    return;
                }

                buttonElement.setAttribute('data-exam-name', examRecord.exam_name);
                buttonElement.setAttribute('data-exam-type', examRecord.exam_type);
                buttonElement.setAttribute('data-exam-date', examRecord.exam_date);
            });
        }

        return true;
    }

    function removeExamColumnFromGrid(context) {
        const index = findExamIndexByContext(context);
        if (index < 0 || !examsGridData.exams[index]) {
            return false;
        }

        const removedExam = examsGridData.exams.splice(index, 1)[0];
        const removedKey = normalizeText(removedExam && removedExam.key);

        if (removedKey !== '' && examsThead instanceof HTMLElement) {
            const headerCells = Array.from(examsThead.querySelectorAll('th[data-exam-key]'));
            headerCells.forEach(function (headerCell) {
                if (!(headerCell instanceof HTMLElement)) {
                    return;
                }

                if (normalizeText(headerCell.getAttribute('data-exam-key')) === removedKey) {
                    headerCell.remove();
                }
            });
        }

        if (removedKey !== '' && examsTbody instanceof HTMLElement) {
            const rows = Array.from(examsTbody.querySelectorAll('tr[data-student-row="1"]'));
            rows.forEach(function (rowElement) {
                if (!(rowElement instanceof HTMLTableRowElement)) {
                    return;
                }

                const examButtons = Array.from(rowElement.querySelectorAll('[data-exam-cell="1"]'));
                const targetButton = examButtons.find(function (buttonElement) {
                    return buttonElement instanceof HTMLButtonElement
                        && normalizeText(buttonElement.getAttribute('data-exam-key')) === removedKey;
                });

                if (targetButton instanceof HTMLElement) {
                    const td = targetButton.closest('td');
                    if (td instanceof HTMLElement) {
                        td.remove();
                    }
                }
            });
        }

        Object.keys(examsGridData.cells).forEach(function (studentId) {
            const studentCells = examsGridData.cells[studentId];
            if (!studentCells || typeof studentCells !== 'object') {
                return;
            }

            if (removedKey !== '') {
                delete studentCells[removedKey];
            }
        });

        setExamsMetaSummary(examsGridData.students.length, examsGridData.exams.length);

        if (examsGridData.exams.length === 0 && examsState instanceof HTMLElement) {
            examsState.textContent = canManageExams ? 'Chưa có cột điểm. Bấm “Tạo cột điểm” để thêm.' : 'Chưa có cột điểm.';
            examsState.classList.remove('hidden');
        }

        applyExamsStudentFilter();
        return true;
    }

    function updateExamCellInGrid(payload) {
        const studentId = toInt(payload && payload.studentId);
        const examName = normalizeText(payload && payload.examName);
        const examType = normalizeText(payload && payload.examType);
        const examDate = normalizeText(payload && payload.examDate);
        const examId = toInt(payload && payload.examId);
        const result = normalizeText(payload && payload.result);
        const comment = normalizeText(payload && payload.comment);

        if (studentId <= 0 || examName === '' || examType === '' || examDate === '') {
            return false;
        }

        let targetButton = payload && payload.button instanceof HTMLButtonElement ? payload.button : null;
        if (!(targetButton instanceof HTMLButtonElement)) {
            const candidates = examsTbody instanceof HTMLElement
                ? Array.from(examsTbody.querySelectorAll('[data-exam-cell="1"]'))
                : [];
            targetButton = candidates.find(function (candidate) {
                if (!(candidate instanceof HTMLButtonElement)) {
                    return false;
                }

                return toInt(candidate.getAttribute('data-student-id')) === studentId
                    && normalizeText(candidate.getAttribute('data-exam-name')) === examName
                    && normalizeText(candidate.getAttribute('data-exam-type')) === examType
                    && normalizeText(candidate.getAttribute('data-exam-date')) === examDate;
            }) || null;
        }

        if (targetButton instanceof HTMLButtonElement) {
            targetButton.setAttribute('data-exam-id', String(examId));
            setExamCellButtonContent(targetButton, result, comment);
            targetButton.classList.remove('is-updated');
            void targetButton.offsetWidth;
            targetButton.classList.add('is-updated');
            window.setTimeout(function () {
                targetButton.classList.remove('is-updated');
            }, 900);
        }

        const matchedExam = examsGridData.exams.find(function (exam) {
            return normalizeText(exam.exam_name) === examName
                && normalizeText(exam.exam_type) === examType
                && normalizeText(exam.exam_date) === examDate;
        });
        const examKey = normalizeText(matchedExam && matchedExam.key) !== ''
            ? normalizeText(matchedExam && matchedExam.key)
            : buildLocalExamKey(examName, examType, examDate);

        if (!examsGridData.cells[String(studentId)] || typeof examsGridData.cells[String(studentId)] !== 'object') {
            examsGridData.cells[String(studentId)] = {};
        }
        examsGridData.cells[String(studentId)][examKey] = {
            exam_id: examId,
            result: result,
            teacher_comment: comment,
        };

        return true;
    }

    async function loadExamsGrid(options) {
        if (!(examsState instanceof HTMLElement) || selectedClassId <= 0 || isExamsLoading) {
            return;
        }

        const opts = options && typeof options === 'object' ? options : {};
        const silent = opts.silent === true;

        if (!silent) {
            setExamsBanner('neutral', '');
            examsState.textContent = 'Đang tải bảng điểm...';
            examsState.classList.remove('hidden');
            if (examsTable instanceof HTMLElement) {
                examsTable.classList.add('hidden');
            }
        }

        setExamsSyncing(true);

        try {
            const url = '/api/exams/class-grid?class_id=' + encodeURIComponent(String(selectedClassId)) + '&format=json';
            const json = await fetchJson(url, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!json || json.status !== 'success') {
                throw new Error((json && json.message) ? String(json.message) : 'Không tải được bảng điểm.');
            }

            renderExamsGrid(json.data || {});
        } catch (error) {
            if (!silent) {
                examsState.textContent = 'Không tải được bảng điểm.';
            }
            setExamsBanner('error', error instanceof Error ? error.message : 'Không tải được bảng điểm.');
        } finally {
            setExamsSyncing(false);
        }
    }

    function openStudentProfileModal(studentId, studentName, returnFocusElement) {
        if (!(studentProfileModal instanceof HTMLElement) || !(studentProfileState instanceof HTMLElement) || !(studentProfileBody instanceof HTMLElement)) {
            return;
        }

        const normalizedName = normalizeText(studentName);
        if (studentProfileContext instanceof HTMLElement) {
            studentProfileContext.textContent = normalizedName !== '' ? normalizedName : ('Học viên #' + String(studentId));
        }

        studentProfileState.textContent = 'Đang tải hồ sơ...';
        studentProfileState.classList.remove('hidden');
        studentProfileBody.classList.add('hidden');
        studentProfileBody.innerHTML = '';

        openModal(studentProfileModal, studentProfileCloseButton instanceof HTMLElement ? studentProfileCloseButton : studentProfileState, returnFocusElement);

        const url = '/api/classes/student-profile?class_id=' + encodeURIComponent(String(selectedClassId))
            + '&student_id=' + encodeURIComponent(String(studentId))
            + '&format=json';

        fetchJson(url, {
            headers: {
                'Accept': 'application/json',
            },
        }).then(function (json) {
            if (!json || json.status !== 'success') {
                throw new Error((json && json.message) ? String(json.message) : 'Không tải được hồ sơ.');
            }

            const user = (json.data && json.data.user) ? json.data.user : null;
            if (!user) {
                throw new Error('Không tải được hồ sơ.');
            }

            const roleProfile = (user && user.role_profile && typeof user.role_profile === 'object') ? user.role_profile : {};
            const rows = [
                ['Họ tên', normalizeText(user.full_name)],
                ['Điện thoại', normalizeText(user.phone)],
                ['Email', normalizeText(user.email)],
                ['Phụ huynh', normalizeText(roleProfile.student_parent_name)],
                ['SĐT phụ huynh', normalizeText(roleProfile.student_parent_phone)],
                ['Trường', normalizeText(roleProfile.student_school_name)],
                ['Mục tiêu điểm', normalizeText(roleProfile.student_target_score)],
            ];

            const profileCards = rows.filter(function (pair) {
                return normalizeText(pair[1]) !== '';
            });

            if (profileCards.length === 0) {
                studentProfileBody.innerHTML = '<div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">Chưa có thêm dữ liệu hồ sơ.</div>';
            } else {
                studentProfileBody.innerHTML = profileCards.map(function (pair) {
                    return '<div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">'
                        + '<div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">' + escapeHtml(pair[0]) + '</div>'
                        + '<div class="mt-1 text-sm font-semibold text-slate-800">' + escapeHtml(pair[1]) + '</div>'
                        + '</div>';
                }).join('');
            }

            studentProfileState.classList.add('hidden');
            studentProfileBody.classList.remove('hidden');
        }).catch(function (error) {
            studentProfileState.textContent = error instanceof Error ? error.message : 'Không tải được hồ sơ.';
        });
    }

    function openExamCreateModal(returnFocusElement) {
        if (!(examCreateModal instanceof HTMLElement) || !(examCreateForm instanceof HTMLFormElement)) {
            return;
        }

        examCreateForm.reset();
        setExamsBanner('neutral', '');
        const nameInput = examCreateForm.querySelector('input[name="exam_name"]');
        openModal(examCreateModal, nameInput instanceof HTMLElement ? nameInput : examCreateCloseButton, returnFocusElement);
    }

    function openExamScoreModalFromTrigger(trigger, returnFocusElement) {
        if (!(examScoreModal instanceof HTMLElement) || !(examScoreForm instanceof HTMLFormElement)) {
            return;
        }

        activeExamCellTrigger = trigger;

        const studentId = toInt(trigger.getAttribute('data-student-id'));
        const studentName = normalizeText(trigger.getAttribute('data-student-name'));
        const examId = toInt(trigger.getAttribute('data-exam-id'));
        const examName = normalizeText(trigger.getAttribute('data-exam-name'));
        const examType = normalizeText(trigger.getAttribute('data-exam-type'));
        const examDate = normalizeText(trigger.getAttribute('data-exam-date'));
        const result = normalizeText(trigger.getAttribute('data-exam-result'));
        const comment = normalizeText(trigger.getAttribute('data-exam-comment'));

        if (examScoreContext instanceof HTMLElement) {
            examScoreContext.textContent = (studentName !== '' ? studentName : ('Học viên #' + String(studentId)))
                + ' | ' + (examName !== '' ? examName : 'Cột điểm')
                + ' | ' + formatDateDisplay(examDate);
        }

        if (examScoreStudentIdInput instanceof HTMLInputElement) {
            examScoreStudentIdInput.value = String(studentId);
        }
        if (examScoreExamIdInput instanceof HTMLInputElement) {
            examScoreExamIdInput.value = String(examId);
        }
        if (examScoreExamNameInput instanceof HTMLInputElement) {
            examScoreExamNameInput.value = examName;
        }
        if (examScoreExamTypeInput instanceof HTMLInputElement) {
            examScoreExamTypeInput.value = examType;
        }
        if (examScoreExamDateInput instanceof HTMLInputElement) {
            examScoreExamDateInput.value = examDate;
        }
        if (examScoreResultInput instanceof HTMLInputElement) {
            examScoreResultInput.value = result;
        }
        if (examScoreCommentInput instanceof HTMLTextAreaElement) {
            examScoreCommentInput.value = comment;
        }

        openModal(examScoreModal, examScoreResultInput instanceof HTMLElement ? examScoreResultInput : examScoreCloseButton, returnFocusElement);
    }

    function getExamColumnContextFromElement(sourceElement) {
        if (!(sourceElement instanceof HTMLElement)) {
            return null;
        }

        const examKey = normalizeText(sourceElement.getAttribute('data-exam-key'));
        const examName = normalizeText(sourceElement.getAttribute('data-exam-name'));
        const examType = normalizeText(sourceElement.getAttribute('data-exam-type'));
        const examDate = normalizeText(sourceElement.getAttribute('data-exam-date'));

        if (examKey === '' && (examName === '' || examType === '' || examDate === '')) {
            return null;
        }

        return {
            examKey: examKey,
            exam_name: examName,
            exam_type: examType,
            exam_date: examDate,
        };
    }

    function closeExamColumnMenu(restoreFocus) {
        if (!(examColumnMenu instanceof HTMLElement)) {
            return;
        }

        examColumnMenu.classList.add('hidden');

        if (activeExamColumnTrigger instanceof HTMLElement) {
            activeExamColumnTrigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) {
                activeExamColumnTrigger.focus();
            }
        }

        activeExamColumnTrigger = null;
        activeExamColumnContext = null;
    }

    function placeExamColumnMenu(posX, posY, anchorElement) {
        if (!(examColumnMenu instanceof HTMLElement)) {
            return;
        }

        const viewportPadding = 8;
        const menuRect = examColumnMenu.getBoundingClientRect();

        let left = Number.isFinite(posX) ? Number(posX) : NaN;
        let top = Number.isFinite(posY) ? Number(posY) : NaN;

        if (!Number.isFinite(left) || !Number.isFinite(top)) {
            if (anchorElement instanceof HTMLElement) {
                const anchorRect = anchorElement.getBoundingClientRect();
                left = anchorRect.right - menuRect.width;
                top = anchorRect.bottom + 6;
            } else {
                left = viewportPadding;
                top = viewportPadding;
            }
        }

        if ((left + menuRect.width + viewportPadding) > window.innerWidth) {
            left = window.innerWidth - menuRect.width - viewportPadding;
        }
        if ((top + menuRect.height + viewportPadding) > window.innerHeight) {
            if (anchorElement instanceof HTMLElement) {
                top = anchorElement.getBoundingClientRect().top - menuRect.height - 6;
            } else {
                top = window.innerHeight - menuRect.height - viewportPadding;
            }
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }
        if (top < viewportPadding) {
            top = viewportPadding;
        }

        examColumnMenu.style.left = left + 'px';
        examColumnMenu.style.top = top + 'px';
    }

    function openExamColumnMenu(sourceElement, posX, posY) {
        if (!canManageExams || !(examColumnMenu instanceof HTMLElement) || !(sourceElement instanceof HTMLElement)) {
            return;
        }

        const context = getExamColumnContextFromElement(sourceElement);
        if (!context) {
            return;
        }

        closeMenu(false);
        if (activeExamColumnTrigger instanceof HTMLElement && activeExamColumnTrigger !== sourceElement) {
            activeExamColumnTrigger.setAttribute('aria-expanded', 'false');
        }

        activeExamColumnTrigger = sourceElement;
        activeExamColumnContext = context;

        sourceElement.setAttribute('aria-expanded', 'true');
        examColumnMenu.classList.remove('hidden');
        placeExamColumnMenu(posX, posY, sourceElement);
    }

    function openExamEditModal(context, returnFocusElement) {
        if (!(examEditModal instanceof HTMLElement) || !(examEditForm instanceof HTMLFormElement)) {
            return;
        }

        const examName = normalizeText(context && context.exam_name);
        const examType = normalizeText(context && context.exam_type);
        const examDate = normalizeText(context && context.exam_date);
        const examKey = normalizeText(context && context.examKey);

        if (examName === '' || examType === '' || examDate === '') {
            return;
        }

        if (examEditContext instanceof HTMLElement) {
            examEditContext.textContent = (examName !== '' ? examName : 'Cột điểm') + ' | ' + formatDateDisplay(examDate) + ' | ' + formatExamTypeLabel(examType);
        }

        if (examEditOldExamKeyInput instanceof HTMLInputElement) {
            examEditOldExamKeyInput.value = examKey;
        }
        if (examEditOldNameInput instanceof HTMLInputElement) {
            examEditOldNameInput.value = examName;
        }
        if (examEditOldTypeInput instanceof HTMLInputElement) {
            examEditOldTypeInput.value = examType;
        }
        if (examEditOldDateInput instanceof HTMLInputElement) {
            examEditOldDateInput.value = examDate;
        }
        if (examEditNameInput instanceof HTMLInputElement) {
            examEditNameInput.value = examName;
        }
        if (examEditTypeInput instanceof HTMLSelectElement) {
            examEditTypeInput.value = examType;
        }
        if (examEditDateInput instanceof HTMLInputElement) {
            examEditDateInput.value = examDate;
        }

        setExamsBanner('neutral', '');
        openModal(examEditModal, examEditNameInput instanceof HTMLElement ? examEditNameInput : examEditCloseButton, returnFocusElement);
    }

    function getSlotContext(slotElement) {
        return {
            hasLesson: slotElement.getAttribute('data-has-lesson') === '1',
            courseId: toInt(slotElement.getAttribute('data-course-id')),
            classId: toInt(slotElement.getAttribute('data-class-id')),
            scheduleId: toInt(slotElement.getAttribute('data-schedule-id')),
            lessonId: toInt(slotElement.getAttribute('data-lesson-id')),
            defaultAssignmentId: toInt(slotElement.getAttribute('data-default-assignment-id')),
            lessonTitle: normalizeText(slotElement.getAttribute('data-lesson-title')),
            slotLabel: normalizeText(slotElement.getAttribute('data-slot-label')),
            assignmentUrl: normalizeText(slotElement.getAttribute('data-url-assignment')),
            gradingUrl: normalizeText(slotElement.getAttribute('data-url-grading')),
        };
    }

    function setMenuItemDisabled(element, disabled, reasonText) {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (disabled) {
            element.setAttribute('aria-disabled', 'true');
            element.setAttribute('data-disabled', '1');
            element.setAttribute('tabindex', '-1');
            element.classList.add('opacity-50', 'cursor-not-allowed');
            if (normalizeText(reasonText) !== '') {
                element.setAttribute('title', reasonText);
            } else {
                element.removeAttribute('title');
            }
        } else {
            element.setAttribute('aria-disabled', 'false');
            element.setAttribute('data-disabled', '0');
            element.removeAttribute('tabindex');
            element.classList.remove('opacity-50', 'cursor-not-allowed');
            element.removeAttribute('title');
        }
    }

    function isMenuItemDisabled(element) {
        return element instanceof HTMLElement && element.getAttribute('data-disabled') === '1';
    }

    function closeMenu(restoreFocus) {
        contextMenu.classList.add('hidden');
        if (activeSlotElement instanceof HTMLElement) {
            activeSlotElement.setAttribute('aria-expanded', 'false');
            if (restoreFocus) {
                activeSlotElement.focus();
            }
        }
    }

    function getEnabledMenuItems() {
        return menuItems.filter((item) => !isMenuItemDisabled(item));
    }

    function focusMenuItem(index) {
        const enabledItems = getEnabledMenuItems();
        if (enabledItems.length === 0) {
            return;
        }

        const normalizedIndex = ((index % enabledItems.length) + enabledItems.length) % enabledItems.length;
        activeMenuIndex = normalizedIndex;
        enabledItems[normalizedIndex].focus();
    }

    function placeMenu(posX, posY) {
        const viewportPadding = 8;
        const menuRect = contextMenu.getBoundingClientRect();
        let left = posX;
        let top = posY;

        if ((left + menuRect.width + viewportPadding) > window.innerWidth) {
            left = window.innerWidth - menuRect.width - viewportPadding;
        }
        if ((top + menuRect.height + viewportPadding) > window.innerHeight) {
            top = window.innerHeight - menuRect.height - viewportPadding;
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }
        if (top < viewportPadding) {
            top = viewportPadding;
        }

        contextMenu.style.left = left + 'px';
        contextMenu.style.top = top + 'px';
    }

    function openMenu(slotElement, posX, posY, shouldFocusMenu) {
        closeExamColumnMenu(false);

        if (activeSlotElement instanceof HTMLElement && activeSlotElement !== slotElement) {
            activeSlotElement.setAttribute('aria-expanded', 'false');
        }

        activeSlotElement = slotElement;
        activeSlotContext = getSlotContext(slotElement);
        activeMenuIndex = 0;

        const canOpenAttendance = canViewAttendance && activeSlotContext.scheduleId > 0;

        const canOpenLesson = activeSlotContext.hasLesson
            ? (canUpdateLesson && activeSlotContext.lessonId > 0)
            : canCreateLesson;
        const lessonReason = canOpenLesson
            ? ''
            : (activeSlotContext.hasLesson
                ? 'Bạn chưa có quyền cập nhật giáo án buổi học.'
                : 'Bạn chưa có quyền soạn giáo án buổi học.');

        const canOpenAssignment = canCreateAssignment && activeSlotContext.hasLesson && activeSlotContext.lessonId > 0;
        const canOpenGrading = canGradeSubmission && activeSlotContext.hasLesson && activeSlotContext.lessonId > 0;

        setMenuItemDisabled(itemAttendance, !canOpenAttendance, canViewAttendance ? 'Không xác định được lịch học cho buổi này.' : 'Bạn chưa có quyền xem điểm danh.');
        setMenuItemDisabled(itemDetail, !canOpenLesson, lessonReason);
        setMenuItemDisabled(itemAssignment, !canOpenAssignment, canCreateAssignment ? 'Cần soạn giáo án buổi học trước khi giao bài tập.' : 'Bạn chưa có quyền giao bài tập.');
        setMenuItemDisabled(itemGrading, !canOpenGrading, canGradeSubmission ? 'Cần soạn giáo án buổi học trước khi chấm điểm.' : 'Bạn chưa có quyền chấm điểm.');

        slotElement.setAttribute('aria-expanded', 'true');
        contextMenu.classList.remove('hidden');
        placeMenu(posX, posY);

        if (shouldFocusMenu) {
            const enabledItems = getEnabledMenuItems();
            if (enabledItems.length > 0) {
                activeMenuIndex = 0;
                enabledItems[0].focus();
            } else {
                contextMenu.focus();
            }
        }
    }

    function openModal(modalElement, focusElement, returnFocusElement) {
        if (!(modalElement instanceof HTMLElement)) {
            return;
        }

        activeModal = modalElement;
        modalReturnFocusElement = returnFocusElement instanceof HTMLElement ? returnFocusElement : activeSlotElement;
        modalElement.classList.remove('hidden');
        document.body.classList.add('admin-modal-open');

        if (focusElement instanceof HTMLElement) {
            window.requestAnimationFrame(function () {
                focusElement.focus();
            });
        }
    }

    function closeModal(modalElement, restoreFocus) {
        if (!(modalElement instanceof HTMLElement)) {
            return;
        }

        modalElement.classList.add('hidden');
        if (activeModal === modalElement) {
            activeModal = null;
        }
        document.body.classList.remove('admin-modal-open');

        if (restoreFocus && modalReturnFocusElement instanceof HTMLElement) {
            try {
                modalReturnFocusElement.focus({ preventScroll: true });
            } catch (error) {
                modalReturnFocusElement.focus();
            }
        }
    }

    function resetAttendanceSummary() {
        if (attendanceSummaryTotal) {
            attendanceSummaryTotal.textContent = '0';
        }
        if (attendanceSummaryPresent) {
            attendanceSummaryPresent.textContent = '0';
        }
        if (attendanceSummaryLate) {
            attendanceSummaryLate.textContent = '0';
        }
        if (attendanceSummaryAbsent) {
            attendanceSummaryAbsent.textContent = '0';
        }
        if (attendanceSummaryUnmarked) {
            attendanceSummaryUnmarked.textContent = '0';
        }
    }

    function renderAttendanceState(message, isError) {
        if (!(attendanceState instanceof HTMLElement)) {
            return;
        }

        attendanceState.textContent = message;
        attendanceState.className = isError
            ? 'rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700'
            : 'rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600';
    }

    function renderAttendanceSummary(summary) {
        if (attendanceSummaryTotal) {
            attendanceSummaryTotal.textContent = String(toInt(summary.total));
        }
        if (attendanceSummaryPresent) {
            attendanceSummaryPresent.textContent = String(toInt(summary.present));
        }
        if (attendanceSummaryLate) {
            attendanceSummaryLate.textContent = String(toInt(summary.late));
        }
        if (attendanceSummaryAbsent) {
            attendanceSummaryAbsent.textContent = String(toInt(summary.absent));
        }
        if (attendanceSummaryUnmarked) {
            attendanceSummaryUnmarked.textContent = String(toInt(summary.unmarked));
        }
    }

    function recalculateAttendanceSummaryFromInputs() {
        if (!(attendanceList instanceof HTMLElement)) {
            return;
        }

        const selects = Array.from(attendanceList.querySelectorAll('select[data-attendance-select="1"]'));
        if (selects.length === 0) {
            return;
        }

        let present = 0;
        let late = 0;
        let absent = 0;
        let unmarked = 0;

        selects.forEach(function (selectElement) {
            if (!(selectElement instanceof HTMLSelectElement)) {
                return;
            }

            const value = normalizeText(selectElement.value);
            if (value === 'present') {
                present++;
                return;
            }
            if (value === 'late') {
                late++;
                return;
            }
            if (value === 'absent') {
                absent++;
                return;
            }

            unmarked++;
        });

        renderAttendanceSummary({
            total: selects.length,
            present: present,
            late: late,
            absent: absent,
            unmarked: unmarked,
        });
    }

    function learningStatusBadgeClass(status) {
        const normalized = normalizeText(status).toLowerCase();
        if (normalized === 'trial') {
            return 'border-blue-200 bg-blue-50 text-blue-700';
        }
        if (normalized === 'suspended') {
            return 'border-rose-200 bg-rose-50 text-rose-700';
        }
        return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    }

    function renderAttendanceRows(rows) {
        if (!(attendanceList instanceof HTMLElement)) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            attendanceList.innerHTML = '';
            renderAttendanceState('Lịch học này chưa có học viên trong lớp.', false);
            if (attendanceSubmitButton instanceof HTMLButtonElement) {
                attendanceSubmitButton.disabled = true;
            }
            return;
        }

        const statusDisabledAttr = canManageAttendance ? '' : ' disabled';
        const noteReadonlyAttr = canManageAttendance ? '' : ' readonly';

        const tableRows = rows.map(function (row) {
            const studentId = toInt(row.student_id);
            if (studentId <= 0) {
                return '';
            }

            const studentName = escapeHtml(normalizeText(row.student_name) || ('Học viên #' + studentId));
            const learningStatus = normalizeText(row.learning_status).toLowerCase() || 'official';
            const attendanceStatus = normalizeText(row.attendance_status).toLowerCase();
            const attendanceNote = escapeHtml(normalizeText(row.attendance_note));

            const optionUnmarked = attendanceStatus === '' ? ' selected' : '';
            const optionPresent = attendanceStatus === 'present' ? ' selected' : '';
            const optionLate = attendanceStatus === 'late' ? ' selected' : '';
            const optionAbsent = attendanceStatus === 'absent' ? ' selected' : '';

            return ''
                + '<tr class="border-b border-slate-100 last:border-b-0">'
                    + '<td class="px-3 py-2 align-top"><strong>' + studentName + '</strong></td>'
                    + '<td class="px-3 py-2 align-top">'
                        + '<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold capitalize ' + learningStatusBadgeClass(learningStatus) + '">' + escapeHtml(learningStatus) + '</span>'
                    + '</td>'
                    + '<td class="px-3 py-2 align-top">'
                        + '<select data-attendance-select="1" name="attendance_status[' + studentId + ']" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-sm"' + statusDisabledAttr + '>'
                            + '<option value=""' + optionUnmarked + '>Chưa đánh dấu</option>'
                            + '<option value="present"' + optionPresent + '>Có mặt</option>'
                            + '<option value="late"' + optionLate + '>Đi muộn</option>'
                            + '<option value="absent"' + optionAbsent + '>Vắng</option>'
                        + '</select>'
                    + '</td>'
                    + '<td class="px-3 py-2 align-top">'
                        + '<input type="text" name="attendance_note[' + studentId + ']" value="' + attendanceNote + '" placeholder="Ghi chú thêm (nếu có)" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-sm"' + noteReadonlyAttr + '>'
                    + '</td>'
                + '</tr>';
        }).join('');

        attendanceList.innerHTML = ''
            + '<div class="overflow-x-auto">'
                + '<table class="min-w-full border-collapse text-sm">'
                    + '<thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">'
                        + '<tr>'
                            + '<th class="px-3 py-2">Học viên</th>'
                            + '<th class="px-3 py-2">Trạng thái</th>'
                            + '<th class="px-3 py-2">Điểm danh</th>'
                            + '<th class="px-3 py-2">Ghi chú</th>'
                        + '</tr>'
                    + '</thead>'
                    + '<tbody>' + tableRows + '</tbody>'
                + '</table>'
            + '</div>';

        renderAttendanceState('Đang hiển thị danh sách điểm danh theo buổi đã chọn.', false);

        if (attendanceSubmitButton instanceof HTMLButtonElement) {
            attendanceSubmitButton.disabled = !canManageAttendance;
        }

        if (canManageAttendance) {
            attendanceList.querySelectorAll('select[data-attendance-select="1"]').forEach(function (selectElement) {
                if (selectElement instanceof HTMLSelectElement) {
                    selectElement.addEventListener('change', function () {
                        recalculateAttendanceSummaryFromInputs();
                    });
                }
            });
        }

        recalculateAttendanceSummaryFromInputs();
    }

    async function loadAttendanceRoster(scheduleId) {
        if (!(attendanceList instanceof HTMLElement)) {
            return;
        }

        if (scheduleId <= 0) {
            attendanceList.innerHTML = '';
            resetAttendanceSummary();
            renderAttendanceState('Chọn buổi học để tải danh sách điểm danh.', false);
            if (attendanceSubmitButton instanceof HTMLButtonElement) {
                attendanceSubmitButton.disabled = true;
            }
            return;
        }

        attendanceList.innerHTML = '';
        resetAttendanceSummary();
        renderAttendanceState('Đang tải danh sách điểm danh...', false);
        if (attendanceSubmitButton instanceof HTMLButtonElement) {
            attendanceSubmitButton.disabled = true;
        }

        try {
            const endpoint = '/api/lessons/attendance-roster?schedule_id=' + encodeURIComponent(String(scheduleId)) + '&format=json';
            const response = await fetch(endpoint, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();
            if (!response.ok || !payload || payload.status !== 'success') {
                const message = normalizeText(payload && payload.message ? payload.message : 'Không tải được dữ liệu điểm danh.');
                throw new Error(message);
            }

            const data = payload.data && typeof payload.data === 'object' ? payload.data : {};
            const summary = data.summary && typeof data.summary === 'object' ? data.summary : {};
            const rows = Array.isArray(data.rows) ? data.rows : [];

            renderAttendanceSummary(summary);
            renderAttendanceRows(rows);
        } catch (error) {
            attendanceList.innerHTML = '';
            resetAttendanceSummary();
            renderAttendanceState(error instanceof Error ? error.message : 'Không tải được dữ liệu điểm danh.', true);
            if (attendanceSubmitButton instanceof HTMLButtonElement) {
                attendanceSubmitButton.disabled = true;
            }
        }
    }

    function openAttendanceModal(context) {
        if (!canViewAttendance || !context || context.scheduleId <= 0) {
            return;
        }

        if (!(attendanceModal instanceof HTMLElement) || !(attendanceForm instanceof HTMLFormElement)) {
            return;
        }

        if (attendanceScheduleIdInput instanceof HTMLInputElement) {
            attendanceScheduleIdInput.value = String(context.scheduleId);
        }
        if (attendanceFocusScheduleIdInput instanceof HTMLInputElement) {
            attendanceFocusScheduleIdInput.value = String(context.scheduleId);
        }
        if (attendanceContext instanceof HTMLElement) {
            attendanceContext.textContent = context.slotLabel !== '' ? context.slotLabel : 'Buổi học đã chọn';
        }

        closeMenu(false);
        openModal(attendanceModal, attendanceCloseButton instanceof HTMLElement ? attendanceCloseButton : attendanceState, activeSlotElement);
        loadAttendanceRoster(context.scheduleId);
    }

    function submitQuickLessonAssign(lessonId, scheduleId) {
        if (!canUpdateLesson || lessonId <= 0 || scheduleId <= 0) {
            return;
        }

        if (!(quickAssignForm instanceof HTMLFormElement)) {
            return;
        }

        const lessonRecord = resolveLessonRecord(lessonId);
        if (!lessonRecord) {
            return;
        }

        const lessonTitle = normalizeText(lessonRecord.actual_title);
        if (lessonTitle === '') {
            return;
        }

        if (quickAssignIdInput instanceof HTMLInputElement) {
            quickAssignIdInput.value = String(lessonId);
        }
        if (quickAssignRoadmapInput instanceof HTMLInputElement) {
            quickAssignRoadmapInput.value = String(toInt(lessonRecord.roadmap_id));
        }
        if (quickAssignTitleInput instanceof HTMLInputElement) {
            quickAssignTitleInput.value = lessonTitle;
        }
        if (quickAssignContentInput instanceof HTMLInputElement) {
            quickAssignContentInput.value = String(lessonRecord.actual_content || '');
        }
        if (quickAssignScheduleInput instanceof HTMLInputElement) {
            quickAssignScheduleInput.value = String(scheduleId);
        }
        if (quickAssignFocusScheduleInput instanceof HTMLInputElement) {
            quickAssignFocusScheduleInput.value = String(scheduleId);
        }

        quickAssignForm.submit();
    }

    function canSlotReceiveDraggedLesson(slotElement) {
        if (!(slotElement instanceof HTMLElement)) {
            return false;
        }

        const slotContext = getSlotContext(slotElement);
        return !slotContext.hasLesson && slotContext.scheduleId > 0;
    }

    function showDragToast(message, isError) {
        if (!(dragToast instanceof HTMLElement)) {
            return;
        }

        dragToast.textContent = message;
        dragToast.classList.remove('hidden', 'border-emerald-300', 'bg-emerald-50', 'text-emerald-700', 'border-rose-300', 'bg-rose-50', 'text-rose-700');
        dragToast.classList.add(isError ? 'border-rose-300' : 'border-emerald-300');
        dragToast.classList.add(isError ? 'bg-rose-50' : 'bg-emerald-50');
        dragToast.classList.add(isError ? 'text-rose-700' : 'text-emerald-700');

        if (dragToastTimer > 0) {
            window.clearTimeout(dragToastTimer);
        }

        dragToastTimer = window.setTimeout(function () {
            dragToast.classList.add('hidden');
        }, 1300);
    }

    function applyDropSlotHints() {
        slots.forEach(function (slotElement) {
            if (!(slotElement instanceof HTMLElement)) {
                return;
            }

            if (draggedLessonId > 0 && canUpdateLesson) {
                if (canSlotReceiveDraggedLesson(slotElement)) {
                    slotElement.classList.add('classroom-drop-hint-valid');
                    slotElement.classList.remove('classroom-drop-hint-invalid');
                    return;
                }

                slotElement.classList.add('classroom-drop-hint-invalid');
                slotElement.classList.remove('classroom-drop-hint-valid');
                return;
            }

            slotElement.classList.remove('classroom-drop-hint-valid', 'classroom-drop-hint-invalid');
        });
    }

    function clearDropSlotStyles() {
        slots.forEach(function (slotElement) {
            if (!(slotElement instanceof HTMLElement)) {
                return;
            }

            slotElement.classList.remove(
                'classroom-drop-hint-valid',
                'classroom-drop-hint-invalid',
                'classroom-drop-hover-valid',
                'classroom-drop-hover-invalid'
            );
        });
    }

    function resolveLessonRecord(lessonId) {
        if (lessonId <= 0) {
            return null;
        }

        const lesson = lessonsById[String(lessonId)];
        if (!lesson || typeof lesson !== 'object') {
            return null;
        }

        return lesson;
    }

    function setLessonModalMode(isEditing) {
        if (lessonModalTitle instanceof HTMLElement) {
            lessonModalTitle.textContent = isEditing ? 'Cập nhật giáo án buổi học' : 'Soạn giáo án buổi học';
        }

        if (lessonSubmitButton instanceof HTMLButtonElement) {
            lessonSubmitButton.textContent = isEditing ? 'Cập nhật giáo án' : 'Lưu giáo án';
        }
    }

    function populateLessonScheduleOptions(currentLessonId, preferredScheduleId) {
        if (!(lessonScheduleInput instanceof HTMLSelectElement)) {
            return 0;
        }

        lessonScheduleInput.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '0';
        placeholderOption.textContent = '-- Chưa xếp lịch học --';
        lessonScheduleInput.appendChild(placeholderOption);

        let selectedValue = 0;
        const normalizedPreferred = toInt(preferredScheduleId);

        if (Array.isArray(schedulesForLessonModal)) {
            schedulesForLessonModal.forEach(function (scheduleRow) {
                const scheduleId = toInt(scheduleRow.id);
                if (scheduleId <= 0) {
                    return;
                }

                const assignedLessonId = toInt(scheduleRow.assigned_lesson_id);
                const isAssignedToAnother = assignedLessonId > 0 && assignedLessonId !== toInt(currentLessonId);

                const option = document.createElement('option');
                option.value = String(scheduleId);
                option.textContent = normalizeText(scheduleRow.label) || ('Khung lịch #' + scheduleId);
                option.disabled = isAssignedToAnother;
                lessonScheduleInput.appendChild(option);

                if (!isAssignedToAnother && normalizedPreferred > 0 && scheduleId === normalizedPreferred) {
                    selectedValue = scheduleId;
                }
            });
        }

        lessonScheduleInput.value = selectedValue > 0 ? String(selectedValue) : '0';
        return toInt(lessonScheduleInput.value);
    }

    function openLessonModal(config) {
        if (!(lessonModal instanceof HTMLElement) || !(lessonForm instanceof HTMLFormElement)) {
            return;
        }

        const requestedLessonId = toInt(config && config.lessonId);
        const lessonRecord = resolveLessonRecord(requestedLessonId);
        const isEditing = lessonRecord !== null;

        if (isEditing && !canUpdateLesson) {
            return;
        }
        if (!isEditing && !canCreateLesson) {
            return;
        }

        lessonForm.reset();

        if (lessonIdInput instanceof HTMLInputElement) {
            lessonIdInput.value = isEditing ? String(requestedLessonId) : '0';
        }
        if (lessonRoadmapInput instanceof HTMLSelectElement) {
            lessonRoadmapInput.value = String(toInt(isEditing ? lessonRecord.roadmap_id : 0));
        }
        if (lessonTitleInput instanceof HTMLInputElement) {
            lessonTitleInput.value = normalizeText(isEditing ? lessonRecord.actual_title : '');
        }
        if (lessonContentInput instanceof HTMLTextAreaElement) {
            lessonContentInput.value = String(isEditing ? (lessonRecord.actual_content || '') : '');
        }

        const preferredScheduleId = isEditing
            ? toInt(lessonRecord.schedule_id)
            : toInt(config && config.preferredScheduleId);
        const selectedScheduleId = populateLessonScheduleOptions(isEditing ? requestedLessonId : 0, preferredScheduleId);

        if (lessonFocusScheduleInput instanceof HTMLInputElement) {
            const configFocusScheduleId = toInt(config && config.focusScheduleId);
            const resolvedFocusScheduleId = configFocusScheduleId > 0 ? configFocusScheduleId : selectedScheduleId;
            lessonFocusScheduleInput.value = String(resolvedFocusScheduleId);
        }

        if (lessonContext instanceof HTMLElement) {
            const fallbackContext = isEditing
                ? (normalizeText(lessonRecord.actual_title) || 'Giáo án đang chỉnh sửa')
                : 'Soạn giáo án mới';
            const contextText = normalizeText(config && config.contextLabel);
            lessonContext.textContent = contextText !== '' ? contextText : fallbackContext;
        }

        setLessonModalMode(isEditing);
        closeMenu(false);
        openModal(
            lessonModal,
            lessonTitleInput instanceof HTMLElement ? lessonTitleInput : lessonCloseButton,
            config && config.returnFocusElement instanceof HTMLElement ? config.returnFocusElement : activeSlotElement
        );
    }

    function openLessonModalFromContext(context) {
        if (!context || typeof context !== 'object') {
            return;
        }

        const hasLesson = context.hasLesson === true && toInt(context.lessonId) > 0;
        const slotLabel = normalizeText(context.slotLabel);

        openLessonModal({
            lessonId: hasLesson ? toInt(context.lessonId) : 0,
            preferredScheduleId: toInt(context.scheduleId),
            focusScheduleId: toInt(context.scheduleId),
            contextLabel: slotLabel !== '' ? slotLabel : 'Buổi học đã chọn',
            returnFocusElement: activeSlotElement,
        });
    }

    function setWeeklyLoading(isLoading) {
        const weeklyShell = document.querySelector('[data-weekly-shell="1"]');
        if (weeklyShell instanceof HTMLElement) {
            weeklyShell.classList.toggle('opacity-70', isLoading);
            weeklyShell.classList.toggle('pointer-events-none', isLoading);
        }

        const loadingBadge = document.getElementById('classroom-weekly-loading');
        if (loadingBadge instanceof HTMLElement) {
            loadingBadge.classList.toggle('hidden', !isLoading);
        }
    }

    function syncWeekContextInputs(weekStart, weekRef) {
        const normalizedWeekStart = normalizeText(weekStart);
        const normalizedWeekRef = normalizeText(weekRef);

        if (normalizedWeekStart !== '') {
            document.querySelectorAll('input[name="week_start"]').forEach(function (inputElement) {
                if (inputElement instanceof HTMLInputElement) {
                    inputElement.value = normalizedWeekStart;
                }
            });
        }

        if (normalizedWeekRef !== '') {
            document.querySelectorAll('input[name="week_ref"]').forEach(function (inputElement) {
                if (inputElement instanceof HTMLInputElement) {
                    inputElement.value = normalizedWeekRef;
                }
            });
        }
    }

    async function loadWeekView(targetUrl, pushHistory) {
        if (isWeekLoading) {
            return;
        }

        const currentWeeklyShell = document.querySelector('[data-weekly-shell="1"]');
        if (!(currentWeeklyShell instanceof HTMLElement)) {
            window.location.assign(targetUrl);
            return;
        }

        isWeekLoading = true;
        setWeeklyLoading(true);

        try {
            const response = await fetch(targetUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const html = await response.text();
            if (!response.ok) {
                throw new Error('Không tải được dữ liệu tuần mới.');
            }

            const parser = new DOMParser();
            const nextDocument = parser.parseFromString(html, 'text/html');
            const nextWeeklyShell = nextDocument.querySelector('[data-weekly-shell="1"]');
            if (!(nextWeeklyShell instanceof HTMLElement)) {
                throw new Error('Không tìm thấy dữ liệu tuần để cập nhật.');
            }

            currentWeeklyShell.innerHTML = nextWeeklyShell.innerHTML;
            currentWeeklyShell.setAttribute('data-week-start', normalizeText(nextWeeklyShell.getAttribute('data-week-start')));
            currentWeeklyShell.setAttribute('data-week-ref', normalizeText(nextWeeklyShell.getAttribute('data-week-ref')));

            syncWeekContextInputs(
                currentWeeklyShell.getAttribute('data-week-start') || '',
                currentWeeklyShell.getAttribute('data-week-ref') || ''
            );

            if (typeof nextDocument.title === 'string' && nextDocument.title.trim() !== '') {
                document.title = nextDocument.title;
            }

            if (activeModal instanceof HTMLElement) {
                closeModal(activeModal, false);
            }

            contextMenu.classList.add('hidden');
            activeSlotElement = null;
            activeSlotContext = null;
            bindSlotListeners();
            bindLessonDragSources();

            if (pushHistory) {
                window.history.pushState({ classroomWeekUrl: targetUrl }, '', targetUrl);
            }
        } catch (error) {
            window.location.assign(targetUrl);
        } finally {
            isWeekLoading = false;
            setWeeklyLoading(false);
        }
    }

    function resetGradingSummary() {
        if (summaryTotal) {
            summaryTotal.textContent = '0';
        }
        if (summarySubmitted) {
            summarySubmitted.textContent = '0';
        }
        if (summaryMissing) {
            summaryMissing.textContent = '0';
        }
        if (summaryGraded) {
            summaryGraded.textContent = '0';
        }
        if (summaryPending) {
            summaryPending.textContent = '0';
        }
    }

    function renderGradingState(message, isError) {
        if (!(gradingState instanceof HTMLElement)) {
            return;
        }

        gradingState.textContent = message;
        gradingState.className = isError
            ? 'rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700'
            : 'rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600';
    }

    function renderGradingSummary(summary) {
        if (summaryTotal) {
            summaryTotal.textContent = String(toInt(summary.total));
        }
        if (summarySubmitted) {
            summarySubmitted.textContent = String(toInt(summary.submitted));
        }
        if (summaryMissing) {
            summaryMissing.textContent = String(toInt(summary.missing));
        }
        if (summaryGraded) {
            summaryGraded.textContent = String(toInt(summary.graded));
        }
        if (summaryPending) {
            summaryPending.textContent = String(toInt(summary.pending));
        }
    }

    function renderGradingRows(rows) {
        if (!(gradingList instanceof HTMLElement)) {
            return;
        }

        if (!Array.isArray(rows) || rows.length === 0) {
            gradingList.innerHTML = '';
            renderGradingState('Không có dữ liệu bài nộp cho bài tập đã chọn.', false);
            if (gradingSubmitButton instanceof HTMLButtonElement) {
                gradingSubmitButton.disabled = true;
            }
            return;
        }

        const html = rows.map(function (row) {
            const studentName = escapeHtml(normalizeText(row.student_name));
            const submissionId = toInt(row.submission_id);
            const hasSubmission = submissionId > 0;
            const scoreValue = escapeHtml(normalizeText(row.score));
            const commentValue = escapeHtml(normalizeText(row.teacher_comment));
            const fileUrl = normalizeText(row.file_url);
            const submittedAt = escapeHtml(normalizeText(row.submitted_at));
            const isLateSubmission = toInt(row.is_late_submission) === 1;

            const submissionMeta = hasSubmission
                ? '<div class="text-[11px] text-slate-500">Nộp lúc: ' + (submittedAt !== '' ? submittedAt : '--') + '</div>'
                : '<div class="text-[11px] font-semibold text-amber-700">Chưa nộp</div>';

            const fileLink = (hasSubmission && fileUrl !== '')
                ? '<a class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 hover:border-blue-300 hover:bg-blue-100" href="' + escapeHtml(fileUrl) + '" target="_blank" rel="noopener noreferrer">Mở file nộp</a>'
                : '<span class="text-[11px] text-slate-400">Không có file nộp</span>';

            const timingBadge = hasSubmission
                ? (isLateSubmission
                    ? '<span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">Trễ hạn</span>'
                    : '<span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Đúng hạn</span>')
                : '';

            const checkbox = hasSubmission
                ? '<label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600"><input class="classroom-grade-checkbox h-4 w-4 rounded border-slate-300 text-blue-600" type="checkbox" name="selected_submission_ids[]" value="' + submissionId + '" checked>Chọn cập nhật</label>'
                : '<span class="text-[11px] text-slate-400">Không thể chấm khi chưa nộp</span>';

            const scoreField = hasSubmission
                ? '<input type="number" name="score[' + submissionId + ']" min="0" max="10" step="0.1" value="' + scoreValue + '" class="h-9 rounded-md border border-slate-300 bg-white px-2 text-sm">'
                : '<span class="text-sm text-slate-400">-</span>';

            const commentField = hasSubmission
                ? '<textarea name="teacher_comment[' + submissionId + ']" rows="2" class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-sm" placeholder="Nhận xét của giáo viên">' + commentValue + '</textarea>'
                : '<span class="text-sm text-slate-400">-</span>';

            return '' +
                '<div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">' +
                    '<div class="flex flex-wrap items-start justify-between gap-2">' +
                        '<div>' +
                            '<div class="font-semibold text-slate-800">' + studentName + '</div>' +
                            submissionMeta +
                        '</div>' +
                        '<div class="flex items-center gap-1.5">' + timingBadge + fileLink + '</div>' +
                    '</div>' +
                    '<div class="mt-2 grid gap-2 md:grid-cols-[150px_1fr]">' +
                        '<div class="grid gap-2">' +
                            checkbox +
                            '<label class="text-[11px] font-semibold text-slate-600">Điểm</label>' +
                            scoreField +
                        '</div>' +
                        '<div class="grid gap-1">' +
                            '<label class="text-[11px] font-semibold text-slate-600">Nhận xét</label>' +
                            commentField +
                        '</div>' +
                    '</div>' +
                '</div>';
        }).join('');

        gradingList.innerHTML = html;
        renderGradingState('Đang hiển thị danh sách bài nộp theo bài tập đã chọn.', false);

        if (gradingSubmitButton instanceof HTMLButtonElement) {
            gradingSubmitButton.disabled = false;
        }
    }

    async function loadGradingRoster(classId, assignmentId) {
        if (!(gradingList instanceof HTMLElement)) {
            return;
        }

        if (classId <= 0 || assignmentId <= 0) {
            gradingList.innerHTML = '';
            resetGradingSummary();
            renderGradingState('Chọn bài tập để tải danh sách chấm điểm.', false);
            if (gradingSubmitButton instanceof HTMLButtonElement) {
                gradingSubmitButton.disabled = true;
            }
            return;
        }

        gradingList.innerHTML = '';
        resetGradingSummary();
        renderGradingState('Đang tải danh sách bài nộp...', false);
        if (gradingSubmitButton instanceof HTMLButtonElement) {
            gradingSubmitButton.disabled = true;
        }

        try {
            const endpoint = '/api/submissions/roster?class_id=' + encodeURIComponent(String(classId))
                + '&assignment_id=' + encodeURIComponent(String(assignmentId))
                + '&format=json';

            const response = await fetch(endpoint, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            });

            const payload = await response.json();
            if (!response.ok || !payload || payload.status !== 'success') {
                const message = normalizeText(payload && payload.message ? payload.message : 'Không tải được dữ liệu bài nộp.');
                throw new Error(message);
            }

            const data = payload.data && typeof payload.data === 'object' ? payload.data : {};
            const summary = data.summary && typeof data.summary === 'object' ? data.summary : {};
            const rows = Array.isArray(data.rows) ? data.rows : [];

            renderGradingSummary(summary);
            renderGradingRows(rows);
        } catch (error) {
            gradingList.innerHTML = '';
            resetGradingSummary();
            const message = error instanceof Error ? error.message : 'Không tải được dữ liệu bài nộp.';
            renderGradingState(message, true);
            if (gradingSubmitButton instanceof HTMLButtonElement) {
                gradingSubmitButton.disabled = true;
            }
        }
    }

    function openAssignmentModal(context) {
        if (!canCreateAssignment || !context.hasLesson || context.lessonId <= 0) {
            return;
        }

        if (!(assignmentModal instanceof HTMLElement) || !(assignmentForm instanceof HTMLFormElement)) {
            return;
        }

        assignmentForm.reset();
        if (assignmentLessonIdInput instanceof HTMLInputElement) {
            assignmentLessonIdInput.value = String(context.lessonId);
        }
        if (assignmentScheduleIdInput instanceof HTMLInputElement) {
            assignmentScheduleIdInput.value = String(context.scheduleId);
        }
        if (assignmentFocusScheduleIdInput instanceof HTMLInputElement) {
            assignmentFocusScheduleIdInput.value = String(context.scheduleId);
        }
        if (assignmentContext instanceof HTMLElement) {
            assignmentContext.textContent = context.slotLabel !== '' ? context.slotLabel : 'Buổi học đã chọn';
        }
        if (assignmentTitleInput instanceof HTMLInputElement) {
            const lessonTitle = normalizeText(context.lessonTitle);
            assignmentTitleInput.value = lessonTitle !== '' ? ('Bài tập - ' + lessonTitle) : '';
        }

        closeMenu(false);
        openModal(assignmentModal, assignmentTitleInput instanceof HTMLElement ? assignmentTitleInput : assignmentCloseButton, activeSlotElement);
    }

    function populateGradingAssignments(context) {
        if (!(gradingAssignmentSelect instanceof HTMLSelectElement)) {
            return 0;
        }

        const lessonIdKey = String(context.lessonId);
        const assignmentRows = Array.isArray(assignmentsByLesson[lessonIdKey]) ? assignmentsByLesson[lessonIdKey] : [];
        gradingAssignmentSelect.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '0';
        placeholderOption.textContent = '-- Chọn bài tập --';
        gradingAssignmentSelect.appendChild(placeholderOption);

        let firstAssignmentId = 0;
        assignmentRows.forEach(function (assignmentRow) {
            const assignmentId = toInt(assignmentRow.id);
            if (assignmentId <= 0) {
                return;
            }

            if (firstAssignmentId <= 0) {
                firstAssignmentId = assignmentId;
            }

            const option = document.createElement('option');
            option.value = String(assignmentId);

            const title = normalizeText(assignmentRow.title) || ('Bài tập #' + assignmentId);
            const deadline = normalizeText(assignmentRow.deadline);
            option.textContent = deadline !== '' ? (title + ' | Hạn: ' + deadline) : title;

            gradingAssignmentSelect.appendChild(option);
        });

        const defaultAssignmentId = context.defaultAssignmentId > 0 ? context.defaultAssignmentId : firstAssignmentId;
        gradingAssignmentSelect.value = defaultAssignmentId > 0 ? String(defaultAssignmentId) : '0';
        gradingAssignmentSelect.disabled = assignmentRows.length === 0;
        return toInt(gradingAssignmentSelect.value);
    }

    function openGradingModal(context) {
        if (!canGradeSubmission || !context.hasLesson || context.lessonId <= 0) {
            return;
        }

        if (!(gradingModal instanceof HTMLElement) || !(gradingForm instanceof HTMLFormElement)) {
            return;
        }

        if (gradingLessonIdInput instanceof HTMLInputElement) {
            gradingLessonIdInput.value = String(context.lessonId);
        }
        if (gradingScheduleIdInput instanceof HTMLInputElement) {
            gradingScheduleIdInput.value = String(context.scheduleId);
        }
        if (gradingFocusScheduleIdInput instanceof HTMLInputElement) {
            gradingFocusScheduleIdInput.value = String(context.scheduleId);
        }
        if (gradingClassIdInput instanceof HTMLInputElement && context.classId > 0) {
            gradingClassIdInput.value = String(context.classId);
        }
        if (gradingContext instanceof HTMLElement) {
            gradingContext.textContent = context.slotLabel !== '' ? context.slotLabel : 'Buổi học đã chọn';
        }

        const assignmentId = populateGradingAssignments(context);
        closeMenu(false);
        openModal(gradingModal, gradingAssignmentSelect instanceof HTMLElement ? gradingAssignmentSelect : gradingCloseButton, activeSlotElement);

        if (assignmentId > 0) {
            loadGradingRoster(toInt(gradingClassIdInput instanceof HTMLInputElement ? gradingClassIdInput.value : context.classId), assignmentId);
        } else {
            if (gradingList instanceof HTMLElement) {
                gradingList.innerHTML = '';
            }
            resetGradingSummary();
            renderGradingState('Buổi học này chưa có bài tập. Hãy tạo bài tập trước khi chấm điểm.', false);
            if (gradingSubmitButton instanceof HTMLButtonElement) {
                gradingSubmitButton.disabled = true;
            }
        }
    }

    function bindSlotListeners() {
        slots = Array.from(document.querySelectorAll('[data-classroom-slot="1"]'));
        slots.forEach(function (slotElement) {
            slotElement.addEventListener('contextmenu', function (event) {
                event.preventDefault();
                openMenu(slotElement, event.clientX, event.clientY, false);
            });

            slotElement.addEventListener('keydown', function (event) {
                const isContextKey = event.key === 'ContextMenu' || (event.shiftKey && event.key === 'F10');
                const isActivationKey = event.key === 'Enter' || event.key === ' ';

                if (!isContextKey && !isActivationKey) {
                    return;
                }

                event.preventDefault();
                const rect = slotElement.getBoundingClientRect();
                const posX = rect.left + Math.min(rect.width / 2, 36);
                const posY = rect.top + Math.min(rect.height / 2, 24);
                openMenu(slotElement, posX, posY, true);
            });

            if (!canUpdateLesson) {
                return;
            }

            slotElement.addEventListener('dragover', function (event) {
                if (draggedLessonId <= 0) {
                    return;
                }

                event.preventDefault();

                const canDrop = canSlotReceiveDraggedLesson(slotElement);
                if (event.dataTransfer) {
                    event.dataTransfer.dropEffect = canDrop ? 'move' : 'none';
                }

                slotElement.classList.remove('classroom-drop-hover-valid', 'classroom-drop-hover-invalid');
                slotElement.classList.add(canDrop ? 'classroom-drop-hover-valid' : 'classroom-drop-hover-invalid');
            });

            slotElement.addEventListener('dragleave', function (event) {
                const relatedTarget = event.relatedTarget instanceof Node ? event.relatedTarget : null;
                if (relatedTarget && slotElement.contains(relatedTarget)) {
                    return;
                }

                slotElement.classList.remove('classroom-drop-hover-valid', 'classroom-drop-hover-invalid');
            });

            slotElement.addEventListener('drop', function (event) {
                if (draggedLessonId <= 0) {
                    return;
                }

                event.preventDefault();
                slotElement.classList.remove('classroom-drop-hover-valid', 'classroom-drop-hover-invalid');

                const canDrop = canSlotReceiveDraggedLesson(slotElement);
                if (!canDrop) {
                    showDragToast('Chi co the tha vao o lich chua co giao an.', true);
                    return;
                }

                const slotContext = getSlotContext(slotElement);
                const transferLessonId = event.dataTransfer ? toInt(event.dataTransfer.getData('text/plain')) : 0;
                const lessonId = transferLessonId > 0 ? transferLessonId : draggedLessonId;
                if (lessonId <= 0 || slotContext.scheduleId <= 0) {
                    showDragToast('Khong xac dinh du lieu giao an de xep lich.', true);
                    return;
                }

                showDragToast('Dang xep giao an vao khung lich...', false);
                submitQuickLessonAssign(lessonId, slotContext.scheduleId);
            });
        });

        applyDropSlotHints();
    }

    function bindLessonDragSources() {
        const lessonItems = Array.from(document.querySelectorAll('[data-draggable-lesson="1"]'));
        lessonItems.forEach(function (lessonItem) {
            if (!(lessonItem instanceof HTMLElement)) {
                return;
            }

            const lessonId = toInt(lessonItem.getAttribute('data-lesson-id'));
            const canDrag = canUpdateLesson && lessonId > 0;
            lessonItem.setAttribute('draggable', canDrag ? 'true' : 'false');

            if (!canDrag) {
                return;
            }

            lessonItem.addEventListener('dragstart', function (event) {
                draggedLessonId = lessonId;
                lessonItem.classList.add('opacity-60');
                applyDropSlotHints();
                showDragToast('Keo giao an den o lich chua co giao an.', false);

                if (event.dataTransfer) {
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', String(lessonId));
                }
            });

            lessonItem.addEventListener('dragend', function () {
                draggedLessonId = 0;
                lessonItem.classList.remove('opacity-60');
                clearDropSlotStyles();
            });
        });
    }

    contextMenu.addEventListener('click', function (event) {
        const target = event.target instanceof HTMLElement ? event.target.closest('[data-menu-item="1"]') : null;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (isMenuItemDisabled(target)) {
            event.preventDefault();
            return;
        }

        const action = normalizeText(target.getAttribute('data-action'));
        if (action === 'attendance' && activeSlotContext) {
            event.preventDefault();
            openAttendanceModal(activeSlotContext);
            return;
        }

        if (action === 'lesson' && activeSlotContext) {
            event.preventDefault();
            openLessonModalFromContext(activeSlotContext);
            return;
        }

        if (action === 'assignment' && activeSlotContext) {
            event.preventDefault();
            openAssignmentModal(activeSlotContext);
            return;
        }

        if (action === 'grading' && activeSlotContext) {
            event.preventDefault();
            openGradingModal(activeSlotContext);
            return;
        }

        closeMenu(false);
    });

    contextMenu.addEventListener('keydown', function (event) {
        if (contextMenu.classList.contains('hidden')) {
            return;
        }

        const enabledItems = getEnabledMenuItems();
        if (enabledItems.length === 0) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeMenu(true);
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            focusMenuItem(activeMenuIndex + 1);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            focusMenuItem(activeMenuIndex - 1);
            return;
        }

        if (event.key === 'Home') {
            event.preventDefault();
            focusMenuItem(0);
            return;
        }

        if (event.key === 'End') {
            event.preventDefault();
            focusMenuItem(enabledItems.length - 1);
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeMenu(true);
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            const focusedItem = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            if (focusedItem && focusedItem.getAttribute('data-menu-item') === '1') {
                event.preventDefault();
                focusedItem.click();
            }
        }
    });

    if (attendanceCloseButton instanceof HTMLButtonElement && attendanceModal instanceof HTMLElement) {
        attendanceCloseButton.addEventListener('click', function () {
            closeModal(attendanceModal, true);
        });
    }
    if (attendanceCancelButton instanceof HTMLButtonElement && attendanceModal instanceof HTMLElement) {
        attendanceCancelButton.addEventListener('click', function () {
            closeModal(attendanceModal, true);
        });
    }
    if (attendanceModal instanceof HTMLElement) {
        attendanceModal.addEventListener('click', function (event) {
            if (event.target === attendanceModal) {
                closeModal(attendanceModal, true);
            }
        });
    }

    if (attendanceSubmitButton instanceof HTMLButtonElement) {
        attendanceSubmitButton.disabled = !canManageAttendance;
    }

    if (lessonCreateButton instanceof HTMLButtonElement) {
        lessonCreateButton.addEventListener('click', function () {
            openLessonModal({
                lessonId: 0,
                preferredScheduleId: 0,
                focusScheduleId: 0,
                contextLabel: 'Soạn giáo án mới cho lớp đang chọn.',
                returnFocusElement: lessonCreateButton,
            });
        });
    }

    if (lessonScheduleInput instanceof HTMLSelectElement && lessonFocusScheduleInput instanceof HTMLInputElement) {
        lessonScheduleInput.addEventListener('change', function () {
            lessonFocusScheduleInput.value = String(toInt(lessonScheduleInput.value));
        });
    }

    if (lessonCloseButton instanceof HTMLButtonElement && lessonModal instanceof HTMLElement) {
        lessonCloseButton.addEventListener('click', function () {
            closeModal(lessonModal, true);
        });
    }
    if (lessonCancelButton instanceof HTMLButtonElement && lessonModal instanceof HTMLElement) {
        lessonCancelButton.addEventListener('click', function () {
            closeModal(lessonModal, true);
        });
    }
    if (lessonModal instanceof HTMLElement) {
        lessonModal.addEventListener('click', function (event) {
            if (event.target === lessonModal) {
                closeModal(lessonModal, true);
            }
        });
    }

    if (assignmentCloseButton instanceof HTMLButtonElement && assignmentModal instanceof HTMLElement) {
        assignmentCloseButton.addEventListener('click', function () {
            closeModal(assignmentModal, true);
        });
    }
    if (assignmentCancelButton instanceof HTMLButtonElement && assignmentModal instanceof HTMLElement) {
        assignmentCancelButton.addEventListener('click', function () {
            closeModal(assignmentModal, true);
        });
    }
    if (assignmentModal instanceof HTMLElement) {
        assignmentModal.addEventListener('click', function (event) {
            if (event.target === assignmentModal) {
                closeModal(assignmentModal, true);
            }
        });
    }

    if (gradingCloseButton instanceof HTMLButtonElement && gradingModal instanceof HTMLElement) {
        gradingCloseButton.addEventListener('click', function () {
            closeModal(gradingModal, true);
        });
    }
    if (gradingCancelButton instanceof HTMLButtonElement && gradingModal instanceof HTMLElement) {
        gradingCancelButton.addEventListener('click', function () {
            closeModal(gradingModal, true);
        });
    }
    if (gradingModal instanceof HTMLElement) {
        gradingModal.addEventListener('click', function (event) {
            if (event.target === gradingModal) {
                closeModal(gradingModal, true);
            }
        });
    }

    if (gradingAssignmentSelect instanceof HTMLSelectElement) {
        gradingAssignmentSelect.addEventListener('change', function () {
            const classId = toInt(gradingClassIdInput instanceof HTMLInputElement ? gradingClassIdInput.value : '0');
            const assignmentId = toInt(gradingAssignmentSelect.value);
            loadGradingRoster(classId, assignmentId);
        });
    }

    if (gradingSelectAllButton instanceof HTMLButtonElement && gradingList instanceof HTMLElement) {
        gradingSelectAllButton.addEventListener('click', function () {
            gradingList.querySelectorAll('.classroom-grade-checkbox').forEach(function (inputElement) {
                if (inputElement instanceof HTMLInputElement) {
                    inputElement.checked = true;
                }
            });
        });
    }

    if (gradingClearButton instanceof HTMLButtonElement && gradingList instanceof HTMLElement) {
        gradingClearButton.addEventListener('click', function () {
            gradingList.querySelectorAll('.classroom-grade-checkbox').forEach(function (inputElement) {
                if (inputElement instanceof HTMLInputElement) {
                    inputElement.checked = false;
                }
            });
        });
    }

    if (examsFilterInput instanceof HTMLInputElement) {
        examsFilterInput.addEventListener('input', function () {
            applyExamsStudentFilter();
        });
    }

    if (examsOpenCreateButton instanceof HTMLButtonElement) {
        examsOpenCreateButton.addEventListener('click', function () {
            if (!canManageExams) {
                return;
            }

            openExamCreateModal(examsOpenCreateButton);
        });
    }

    if (studentProfileCloseButton instanceof HTMLButtonElement && studentProfileModal instanceof HTMLElement) {
        studentProfileCloseButton.addEventListener('click', function () {
            closeModal(studentProfileModal, true);
        });
    }
    if (studentProfileModal instanceof HTMLElement) {
        studentProfileModal.addEventListener('click', function (event) {
            if (event.target === studentProfileModal) {
                closeModal(studentProfileModal, true);
            }
        });
    }

    if (examCreateCloseButton instanceof HTMLButtonElement && examCreateModal instanceof HTMLElement) {
        examCreateCloseButton.addEventListener('click', function () {
            closeModal(examCreateModal, true);
        });
    }
    if (examCreateCancelButton instanceof HTMLButtonElement && examCreateModal instanceof HTMLElement) {
        examCreateCancelButton.addEventListener('click', function () {
            closeModal(examCreateModal, true);
        });
    }
    if (examCreateModal instanceof HTMLElement) {
        examCreateModal.addEventListener('click', function (event) {
            if (event.target === examCreateModal) {
                closeModal(examCreateModal, true);
            }
        });
    }

    if (examColumnEditButton instanceof HTMLButtonElement) {
        examColumnEditButton.addEventListener('click', function () {
            if (!canManageExams) {
                return;
            }

            const context = activeExamColumnContext && typeof activeExamColumnContext === 'object'
                ? {
                    examKey: normalizeText(activeExamColumnContext.examKey),
                    exam_name: normalizeText(activeExamColumnContext.exam_name),
                    exam_type: normalizeText(activeExamColumnContext.exam_type),
                    exam_date: normalizeText(activeExamColumnContext.exam_date),
                }
                : null;
            const returnFocusElement = activeExamColumnTrigger instanceof HTMLElement ? activeExamColumnTrigger : null;

            closeExamColumnMenu(false);

            if (!context) {
                return;
            }

            openExamEditModal(context, returnFocusElement);
        });
    }

    if (examColumnDeleteButton instanceof HTMLButtonElement) {
        examColumnDeleteButton.addEventListener('click', async function () {
            if (!canManageExams) {
                return;
            }

            const context = activeExamColumnContext && typeof activeExamColumnContext === 'object'
                ? {
                    examKey: normalizeText(activeExamColumnContext.examKey),
                    exam_name: normalizeText(activeExamColumnContext.exam_name),
                    exam_type: normalizeText(activeExamColumnContext.exam_type),
                    exam_date: normalizeText(activeExamColumnContext.exam_date),
                }
                : null;

            closeExamColumnMenu(false);

            if (!context || context.exam_name === '' || context.exam_type === '' || context.exam_date === '') {
                return;
            }

            const confirmed = window.confirm('Bạn có chắc muốn xóa cột điểm “' + context.exam_name + '” (' + formatDateDisplay(context.exam_date) + ')? Hành động này sẽ xóa toàn bộ điểm trong cột này.');
            if (!confirmed) {
                return;
            }

            setExamsBanner('neutral', '');
            setExamsSyncing(true);

            try {
                const formData = new FormData();
                formData.append('_csrf', csrfToken);
                formData.append('class_id', String(selectedClassId));
                formData.append('exam_name', context.exam_name);
                formData.append('exam_type', context.exam_type);
                formData.append('exam_date', context.exam_date);

                const response = await fetch('/api/exams/delete-column?format=json', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const payload = await response.json();

                if (!response.ok || !payload || payload.status !== 'success') {
                    throw new Error(normalizeText(payload && payload.message ? payload.message : 'Không xóa được cột điểm.'));
                }

                const removed = removeExamColumnFromGrid(context);
                if (!removed) {
                    setExamsBanner('error', 'Đã xóa cột điểm nhưng chưa cập nhật được màn hình. Vui lòng tải lại trang.');
                }
            } catch (error) {
                setExamsBanner('error', error instanceof Error ? error.message : 'Không xóa được cột điểm.');
            } finally {
                setExamsSyncing(false);
            }
        });
    }

    if (examEditCloseButton instanceof HTMLButtonElement && examEditModal instanceof HTMLElement) {
        examEditCloseButton.addEventListener('click', function () {
            closeModal(examEditModal, true);
        });
    }
    if (examEditCancelButton instanceof HTMLButtonElement && examEditModal instanceof HTMLElement) {
        examEditCancelButton.addEventListener('click', function () {
            closeModal(examEditModal, true);
        });
    }
    if (examEditModal instanceof HTMLElement) {
        examEditModal.addEventListener('click', function (event) {
            if (event.target === examEditModal) {
                closeModal(examEditModal, true);
            }
        });
    }

    if (examEditForm instanceof HTMLFormElement) {
        examEditForm.addEventListener('submit', async function (event) {
            if (!canManageExams) {
                return;
            }

            event.preventDefault();
            setExamsBanner('neutral', '');

            const classIdInput = examEditForm.querySelector('input[name="class_id"]');
            if (classIdInput instanceof HTMLInputElement) {
                classIdInput.value = String(selectedClassId);
            }

            const submitButton = examEditSubmitButton instanceof HTMLButtonElement
                ? examEditSubmitButton
                : examEditForm.querySelector('button[type="submit"]');
            const resolvedSubmitButton = submitButton instanceof HTMLButtonElement ? submitButton : null;
            const previousText = resolvedSubmitButton ? resolvedSubmitButton.textContent : '';

            if (resolvedSubmitButton) {
                resolvedSubmitButton.disabled = true;
                resolvedSubmitButton.textContent = 'Đang lưu...';
            }

            const oldMeta = {
                examKey: normalizeText(examEditOldExamKeyInput instanceof HTMLInputElement ? examEditOldExamKeyInput.value : ''),
                exam_name: normalizeText(examEditOldNameInput instanceof HTMLInputElement ? examEditOldNameInput.value : ''),
                exam_type: normalizeText(examEditOldTypeInput instanceof HTMLInputElement ? examEditOldTypeInput.value : ''),
                exam_date: normalizeText(examEditOldDateInput instanceof HTMLInputElement ? examEditOldDateInput.value : ''),
            };
            const newMeta = {
                exam_name: normalizeText(examEditNameInput instanceof HTMLInputElement ? examEditNameInput.value : ''),
                exam_type: normalizeText(examEditTypeInput instanceof HTMLSelectElement ? examEditTypeInput.value : ''),
                exam_date: normalizeText(examEditDateInput instanceof HTMLInputElement ? examEditDateInput.value : ''),
            };

            setExamsSyncing(true);

            try {
                const rawAction = normalizeText(examEditForm.getAttribute('action'));
                const action = rawAction !== '' ? rawAction : examEditForm.action;
                const endpoint = action + (action.includes('?') ? '&' : '?') + 'format=json';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(examEditForm),
                });
                const payload = await response.json();

                if (!response.ok || !payload || payload.status !== 'success') {
                    throw new Error(normalizeText(payload && payload.message ? payload.message : 'Không cập nhật được cột điểm.'));
                }

                closeModal(examEditModal, true);
                const updated = updateExamColumnInGrid({
                    oldMeta: oldMeta,
                    newMeta: newMeta,
                });

                if (!updated) {
                    setExamsBanner('error', 'Đã cập nhật cột điểm nhưng chưa phản ánh trên màn hình. Vui lòng tải lại trang.');
                }
            } catch (error) {
                setExamsBanner('error', error instanceof Error ? error.message : 'Không cập nhật được cột điểm.');
            } finally {
                setExamsSyncing(false);
                if (resolvedSubmitButton) {
                    resolvedSubmitButton.disabled = false;
                    resolvedSubmitButton.textContent = previousText;
                }
            }
        });
    }

    if (examScoreCloseButton instanceof HTMLButtonElement && examScoreModal instanceof HTMLElement) {
        examScoreCloseButton.addEventListener('click', function () {
            activeExamCellTrigger = null;
            closeModal(examScoreModal, true);
        });
    }
    if (examScoreCancelButton instanceof HTMLButtonElement && examScoreModal instanceof HTMLElement) {
        examScoreCancelButton.addEventListener('click', function () {
            activeExamCellTrigger = null;
            closeModal(examScoreModal, true);
        });
    }
    if (examScoreModal instanceof HTMLElement) {
        examScoreModal.addEventListener('click', function (event) {
            if (event.target === examScoreModal) {
                activeExamCellTrigger = null;
                closeModal(examScoreModal, true);
            }
        });
    }

    if (examCreateSubmitButton instanceof HTMLButtonElement) {
        examCreateSubmitButton.disabled = !canManageExams;
    }
    if (examEditSubmitButton instanceof HTMLButtonElement) {
        examEditSubmitButton.disabled = !canManageExams;
    }
    if (examScoreSubmitButton instanceof HTMLButtonElement) {
        examScoreSubmitButton.disabled = !canManageExams;
    }

    if (examCreateForm instanceof HTMLFormElement) {
        examCreateForm.addEventListener('submit', async function (event) {
            if (!canManageExams) {
                return;
            }

            event.preventDefault();
            setExamsBanner('neutral', '');

            const classIdInput = examCreateForm.querySelector('input[name="class_id"]');
            if (classIdInput instanceof HTMLInputElement) {
                classIdInput.value = String(selectedClassId);
            }

            const submitButton = examCreateSubmitButton instanceof HTMLButtonElement
                ? examCreateSubmitButton
                : examCreateForm.querySelector('button[type="submit"]');
            const resolvedSubmitButton = submitButton instanceof HTMLButtonElement ? submitButton : null;
            const previousText = resolvedSubmitButton ? resolvedSubmitButton.textContent : '';

            if (resolvedSubmitButton) {
                resolvedSubmitButton.disabled = true;
                resolvedSubmitButton.textContent = 'Đang tạo...';
            }

            const examNameInput = examCreateForm.querySelector('input[name="exam_name"]');
            const examTypeInput = examCreateForm.querySelector('select[name="exam_type"]');
            const examDateInput = examCreateForm.querySelector('input[name="exam_date"]');

            const pendingMeta = {
                exam_name: normalizeText(examNameInput instanceof HTMLInputElement ? examNameInput.value : ''),
                exam_type: normalizeText(examTypeInput instanceof HTMLSelectElement ? examTypeInput.value : ''),
                exam_date: normalizeText(examDateInput instanceof HTMLInputElement ? examDateInput.value : ''),
            };

            try {
                const rawAction = normalizeText(examCreateForm.getAttribute('action'));
                const action = rawAction !== '' ? rawAction : examCreateForm.action;
                const endpoint = action + (action.includes('?') ? '&' : '?') + 'format=json';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(examCreateForm),
                });
                const payload = await response.json();

                if (!response.ok || !payload || payload.status !== 'success') {
                    throw new Error(normalizeText(payload && payload.message ? payload.message : 'Không tạo được cột điểm.'));
                }

                closeModal(examCreateModal, true);
                appendExamColumnToGrid(pendingMeta);
            } catch (error) {
                setExamsBanner('error', error instanceof Error ? error.message : 'Không tạo được cột điểm.');
            } finally {
                if (resolvedSubmitButton) {
                    resolvedSubmitButton.disabled = false;
                    resolvedSubmitButton.textContent = previousText;
                }
            }
        });
    }

    if (examScoreForm instanceof HTMLFormElement) {
        examScoreForm.addEventListener('submit', async function (event) {
            if (!canManageExams) {
                return;
            }

            event.preventDefault();
            setExamsBanner('neutral', '');

            const classIdInput = examScoreForm.querySelector('input[name="class_id"]');
            if (classIdInput instanceof HTMLInputElement) {
                classIdInput.value = String(selectedClassId);
            }

            const submitButton = examScoreSubmitButton instanceof HTMLButtonElement
                ? examScoreSubmitButton
                : examScoreForm.querySelector('button[type="submit"]');
            const resolvedSubmitButton = submitButton instanceof HTMLButtonElement ? submitButton : null;
            const previousText = resolvedSubmitButton ? resolvedSubmitButton.textContent : '';

            if (resolvedSubmitButton) {
                resolvedSubmitButton.disabled = true;
                resolvedSubmitButton.textContent = 'Đang lưu...';
            }

            const studentId = toInt(examScoreStudentIdInput instanceof HTMLInputElement ? examScoreStudentIdInput.value : '0');
            const examName = normalizeText(examScoreExamNameInput instanceof HTMLInputElement ? examScoreExamNameInput.value : '');
            const examType = normalizeText(examScoreExamTypeInput instanceof HTMLInputElement ? examScoreExamTypeInput.value : '');
            const examDate = normalizeText(examScoreExamDateInput instanceof HTMLInputElement ? examScoreExamDateInput.value : '');

            try {
                const rawAction = normalizeText(examScoreForm.getAttribute('action'));
                const action = rawAction !== '' ? rawAction : examScoreForm.action;
                const endpoint = action + (action.includes('?') ? '&' : '?') + 'format=json';
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(examScoreForm),
                });
                const payload = await response.json();

                if (!response.ok || !payload || payload.status !== 'success') {
                    throw new Error(normalizeText(payload && payload.message ? payload.message : 'Không lưu được điểm.'));
                }

                const savedExamId = toInt(payload && payload.data ? payload.data.exam_id : 0);
                const scoreResult = normalizeText(examScoreResultInput instanceof HTMLInputElement ? examScoreResultInput.value : '');
                const scoreComment = normalizeText(examScoreCommentInput instanceof HTMLTextAreaElement ? examScoreCommentInput.value : '');

                const updated = updateExamCellInGrid({
                    button: activeExamCellTrigger,
                    studentId: studentId,
                    examName: examName,
                    examType: examType,
                    examDate: examDate,
                    examId: savedExamId,
                    result: scoreResult,
                    comment: scoreComment,
                });

                closeModal(examScoreModal, true);
                activeExamCellTrigger = null;
                if (!updated) {
                    setExamsBanner('error', 'Đã lưu điểm nhưng chưa cập nhật được ô hiển thị. Hãy mở lại ô điểm để kiểm tra.');
                }
            } catch (error) {
                setExamsBanner('error', error instanceof Error ? error.message : 'Không lưu được điểm.');
            } finally {
                if (resolvedSubmitButton) {
                    resolvedSubmitButton.disabled = false;
                    resolvedSubmitButton.textContent = previousText;
                }
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (!(event.target instanceof Node)) {
            return;
        }

        const clickTarget = event.target instanceof HTMLElement ? event.target : null;
        if (clickTarget) {
            const studentProfileTrigger = clickTarget.closest('[data-student-profile="1"]');
            if (studentProfileTrigger instanceof HTMLElement) {
                event.preventDefault();
                const studentId = toInt(studentProfileTrigger.getAttribute('data-student-id'));
                if (studentId > 0) {
                    openStudentProfileModal(studentId, normalizeText(studentProfileTrigger.textContent), studentProfileTrigger);
                }
                return;
            }

            const examCellTrigger = clickTarget.closest('[data-exam-cell="1"]');
            if (examCellTrigger instanceof HTMLElement) {
                event.preventDefault();
                if (canManageExams) {
                    openExamScoreModalFromTrigger(examCellTrigger, examCellTrigger);
                }
                return;
            }

            const weekNavLink = clickTarget.closest('[data-week-nav-link="1"]');
            if (weekNavLink instanceof HTMLAnchorElement) {
                event.preventDefault();
                loadWeekView(weekNavLink.href, true);
                return;
            }

            const lessonModalTrigger = clickTarget.closest('[data-open-lesson-modal="1"]');
            if (lessonModalTrigger instanceof HTMLElement) {
                event.preventDefault();
                openLessonModal({
                    lessonId: toInt(lessonModalTrigger.getAttribute('data-lesson-id')),
                    preferredScheduleId: toInt(lessonModalTrigger.getAttribute('data-schedule-id')),
                    focusScheduleId: toInt(lessonModalTrigger.getAttribute('data-schedule-id')),
                    contextLabel: normalizeText(lessonModalTrigger.getAttribute('data-slot-label')),
                    returnFocusElement: lessonModalTrigger,
                });
                return;
            }
        }

        if (!contextMenu.classList.contains('hidden') && !contextMenu.contains(event.target)) {
            closeMenu(false);
        }

        if (examColumnMenu instanceof HTMLElement && !examColumnMenu.classList.contains('hidden') && !examColumnMenu.contains(event.target)) {
            closeExamColumnMenu(false);
        }
    });

    document.addEventListener('contextmenu', function (event) {
        if (!canManageExams || !(event.target instanceof Node)) {
            return;
        }

        const targetElement = event.target instanceof HTMLElement ? event.target : null;
        if (!(targetElement instanceof HTMLElement)) {
            return;
        }

        const examHeaderCell = targetElement.closest('th.is-column-actionable[data-exam-key]');
        if (!(examHeaderCell instanceof HTMLElement)) {
            return;
        }

        const context = getExamColumnContextFromElement(examHeaderCell);
        if (!context || context.exam_name === '' || context.exam_type === '' || context.exam_date === '') {
            return;
        }

        event.preventDefault();
        openExamColumnMenu(examHeaderCell, event.clientX, event.clientY);
    });

    document.addEventListener('submit', function (event) {
        const formElement = event.target;
        if (!(formElement instanceof HTMLFormElement)) {
            return;
        }

        if (formElement.getAttribute('data-week-nav-form') !== '1') {
            return;
        }

        event.preventDefault();

        const action = normalizeText(formElement.getAttribute('action'));
        const targetUrl = new URL(action !== '' ? action : window.location.pathname, window.location.origin);
        const formData = new FormData(formElement);
        const searchParams = new URLSearchParams();

        formData.forEach(function (rawValue, rawKey) {
            if (typeof rawValue !== 'string') {
                return;
            }

            searchParams.set(String(rawKey), rawValue);
        });

        targetUrl.search = searchParams.toString();
        loadWeekView(targetUrl.toString(), true);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (activeModal instanceof HTMLElement) {
            event.preventDefault();
            if (activeModal === examScoreModal) {
                activeExamCellTrigger = null;
            }
            closeModal(activeModal, true);
            return;
        }

        if (!contextMenu.classList.contains('hidden')) {
            event.preventDefault();
            closeMenu(true);
            return;
        }

        if (examColumnMenu instanceof HTMLElement && !examColumnMenu.classList.contains('hidden')) {
            event.preventDefault();
            closeExamColumnMenu(true);
        }
    });

    window.addEventListener('popstate', function () {
        const weeklyShell = document.querySelector('[data-weekly-shell="1"]');
        if (weeklyShell instanceof HTMLElement) {
            loadWeekView(window.location.href, false);
        }
    });

    window.addEventListener('scroll', function () {
        if (!contextMenu.classList.contains('hidden')) {
            closeMenu(false);
        }

        if (examColumnMenu instanceof HTMLElement && !examColumnMenu.classList.contains('hidden')) {
            closeExamColumnMenu(false);
        }
    }, true);

    window.addEventListener('resize', function () {
        if (!contextMenu.classList.contains('hidden')) {
            closeMenu(false);
        }

        if (examColumnMenu instanceof HTMLElement && !examColumnMenu.classList.contains('hidden')) {
            closeExamColumnMenu(false);
        }
    });

    initCourseFirstFilter();
    if (examsFilterInput instanceof HTMLInputElement) {
        examsFilterInput.disabled = selectedClassId <= 0;
    }
    if (examsOpenCreateButton instanceof HTMLButtonElement && selectedClassId <= 0) {
        examsOpenCreateButton.disabled = true;
        examsOpenCreateButton.classList.add('opacity-50', 'cursor-not-allowed');
    }

    if (selectedClassId > 0) {
        loadExamsGrid();
    } else if (examsState instanceof HTMLElement) {
        setExamsMetaSummary(0, 0);
        examsState.textContent = 'Chọn lớp học để xem bảng điểm.';
        examsState.classList.remove('hidden');
        if (examsTable instanceof HTMLElement) {
            examsTable.classList.add('hidden');
        }
    }

    bindSlotListeners();
    bindLessonDragSources();

    if (!hasSuccessFlash && initialLessonId > 0) {
        openLessonModal({
            lessonId: initialLessonId,
            preferredScheduleId: initialPrefillScheduleId,
            focusScheduleId: initialPrefillScheduleId,
            contextLabel: 'Đang mở giáo án theo liên kết đã chọn.',
            returnFocusElement: null,
        });
    } else if (!hasSuccessFlash && initialPrefillScheduleId > 0 && canCreateLesson) {
        openLessonModal({
            lessonId: 0,
            preferredScheduleId: initialPrefillScheduleId,
            focusScheduleId: initialPrefillScheduleId,
            contextLabel: 'Soạn giáo án cho khung lịch đã chọn.',
            returnFocusElement: null,
        });
    }
})();
</script>

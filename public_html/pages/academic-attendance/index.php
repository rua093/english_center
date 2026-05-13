<?php
require_permission('academic.schedules.view');

$academicModel = new AcademicModel();
$lookups = $academicModel->classroomLookups();

$courses = is_array($lookups['courses'] ?? null) ? $lookups['courses'] : [];
$classRows = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];

$selectedCourseId = max(0, (int) ($_GET['course_id'] ?? 0));
$selectedClassId = max(0, (int) ($_GET['class_id'] ?? 0));
$selectedScheduleId = max(0, (int) ($_GET['schedule_id'] ?? 0));

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
        return true;
    }

    return (int) ($classRow['course_id'] ?? 0) === $selectedCourseId;
}));

if ($selectedClassId > 0 && isset($classMap[$selectedClassId])) {
    $selectedClassCourseId = (int) ($classMap[$selectedClassId]['course_id'] ?? 0);
    if ($selectedCourseId > 0 && $selectedClassCourseId !== $selectedCourseId) {
        $selectedClassId = 0;
        $selectedScheduleId = 0;
    }
}

$selectedClass = $selectedClassId > 0 ? ($classMap[$selectedClassId] ?? null) : null;

$scheduleRows = [];
$scheduleById = [];
if ($selectedClassId > 0) {
    $scheduleRows = $academicModel->listSchedulesByClass($selectedClassId);
    foreach ($scheduleRows as $scheduleRow) {
        $scheduleId = (int) ($scheduleRow['id'] ?? 0);
        if ($scheduleId <= 0) {
            continue;
        }

        $scheduleById[$scheduleId] = $scheduleRow;
    }
}

if ($selectedScheduleId > 0 && !isset($scheduleById[$selectedScheduleId])) {
    $selectedScheduleId = 0;
}

if ($selectedScheduleId <= 0 && !empty($scheduleRows)) {
    $selectedScheduleId = (int) ($scheduleRows[0]['id'] ?? 0);
}

$selectedSchedule = $selectedScheduleId > 0 ? ($scheduleById[$selectedScheduleId] ?? null) : null;

$attendanceRoster = [];
if ($selectedScheduleId > 0) {
    $attendanceRoster = $academicModel->listAttendanceRosterBySchedule($selectedScheduleId);
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

$success = get_flash('success');
$error = get_flash('error');

$canManageAttendance = has_permission('academic.schedules.update');

$module = 'attendance';
$adminTitle = t('admin.attendance.title');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3><?= e(t('admin.attendance.filter_title')); ?></h3>
        <form class="grid gap-3 md:grid-cols-4" method="get" action="<?= e(page_url('attendance-academic')); ?>">
            <input type="hidden" name="page" value="attendance-academic">
            <label>
                <?= e(t('admin.attendance.course')); ?>
                <select name="course_id">
                    <option value="0"><?= e(t('admin.attendance.all_courses')); ?></option>
                    <?php foreach ($courses as $course): ?>
                        <?php $courseId = (int) ($course['id'] ?? 0); ?>
                        <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>>
                            <?= e((string) ($course['course_name'] ?? t('admin.attendance.course_fallback', ['id' => (string) $courseId]))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <?= e(t('admin.attendance.class')); ?>
                <select name="class_id">
                    <option value="0"><?= e(t('admin.attendance.choose_class')); ?></option>
                    <?php foreach ($filteredClasses as $classRow): ?>
                        <?php $classId = (int) ($classRow['id'] ?? 0); ?>
                        <option value="<?= $classId; ?>" <?= $selectedClassId === $classId ? 'selected' : ''; ?>>
                            <?= e((string) ($classRow['class_name'] ?? t('admin.attendance.class_fallback', ['id' => (string) $classId]))); ?>
                            <?= !empty($classRow['course_name']) ? ' | ' . e((string) $classRow['course_name']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="md:col-span-2">
                <?= e(t('admin.attendance.scheduled_lesson')); ?>
                <select name="schedule_id">
                    <option value="0"><?= e(t('admin.attendance.choose_lesson')); ?></option>
                    <?php foreach ($scheduleRows as $scheduleRow): ?>
                        <?php
                        $scheduleId = (int) ($scheduleRow['id'] ?? 0);
                        $lessonTitle = trim((string) ($scheduleRow['assigned_lesson_title'] ?? ''));
                        if ($lessonTitle === '') {
                            $lessonTitle = t('admin.attendance.no_lesson_detail');
                        }

                        $label = $lessonTitle
                            . ' | ' . $formatDate((string) ($scheduleRow['study_date'] ?? ''))
                            . ' ' . $formatTime((string) ($scheduleRow['start_time'] ?? ''))
                            . '-' . $formatTime((string) ($scheduleRow['end_time'] ?? ''))
                            . ' | ' . trim((string) ($scheduleRow['room_name'] ?? t('admin.attendance.online')));
                        ?>
                        <option value="<?= $scheduleId; ?>" <?= $selectedScheduleId === $scheduleId ? 'selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="flex items-end gap-2 md:col-span-4">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.attendance.view_roster')); ?></button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('attendance-academic')); ?>"><?= e(t('admin.attendance.reset')); ?></a>
            </div>
        </form>
    </article>

    <?php if (!is_array($selectedClass)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            <?= e(t('admin.attendance.pick_course_class')); ?>
        </article>
    <?php elseif (empty($scheduleRows)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            <?= e(t('admin.attendance.no_schedule_for_class')); ?>
        </article>
    <?php elseif (!is_array($selectedSchedule)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            <?= e(t('admin.attendance.pick_schedule')); ?>
        </article>
    <?php else: ?>
        <?php
        $selectedLessonTitle = trim((string) ($selectedSchedule['assigned_lesson_title'] ?? ''));
        if ($selectedLessonTitle === '') {
            $selectedLessonTitle = t('admin.attendance.no_lesson_detail');
        }
        ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e(t('admin.attendance.lesson_attendance')); ?></h3>
            <p class="text-sm text-slate-600">
                <strong><?= e($selectedLessonTitle); ?></strong>
                | <?= e($formatDate((string) ($selectedSchedule['study_date'] ?? ''))); ?>
                <?= e($formatTime((string) ($selectedSchedule['start_time'] ?? ''))); ?> - <?= e($formatTime((string) ($selectedSchedule['end_time'] ?? ''))); ?>
                | <?= e((string) ($selectedSchedule['room_name'] ?? t('admin.attendance.online'))); ?>
            </p>

            <?php if (!$canManageAttendance): ?>
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                    <?= e(t('admin.attendance.view_only_notice')); ?> <strong>academic.schedules.update</strong>.
                </div>
            <?php endif; ?>

            <form class="mt-3" method="post" action="/api/lessons/attendance">
                <?= csrf_input(); ?>
                <input type="hidden" name="redirect_page" value="attendance-academic">
                <input type="hidden" name="course_id" value="<?= (int) $selectedCourseId; ?>">
                <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
                <input type="hidden" name="schedule_id" value="<?= (int) $selectedScheduleId; ?>">

                <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th><?= e(t('admin.attendance.student_code')); ?></th>
                                <th><?= e(t('admin.attendance.student')); ?></th>
                                <th><?= e(t('admin.attendance.attendance')); ?></th>
                                <th><?= e(t('admin.attendance.note')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendanceRoster)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.attendance.empty_roster')); ?></div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendanceRoster as $studentRow): ?>
                                    <?php $studentId = (int) ($studentRow['student_id'] ?? 0); ?>
                                    <tr>
                                        <td><?= e((string) ($studentRow['student_code'] ?? '-')); ?></td>
                                        <td><strong><?= e((string) ($studentRow['full_name'] ?? ($studentRow['student_name'] ?? t('admin.attendance.student_fallback', ['id' => (string) $studentId])))); ?></strong></td>
                                        <td>
                                            <select name="attendance_status[<?= $studentId; ?>]" <?= $canManageAttendance ? '' : 'disabled'; ?>>
                                                <option value="" <?= trim((string) ($studentRow['attendance_status'] ?? '')) === '' ? 'selected' : ''; ?>><?= e(t('admin.attendance.unmarked')); ?></option>
                                                <option value="present" <?= (string) ($studentRow['attendance_status'] ?? '') === 'present' ? 'selected' : ''; ?>><?= e(t('admin.attendance.present')); ?></option>
                                                <option value="late" <?= (string) ($studentRow['attendance_status'] ?? '') === 'late' ? 'selected' : ''; ?>><?= e(t('admin.attendance.late')); ?></option>
                                                <option value="absent" <?= (string) ($studentRow['attendance_status'] ?? '') === 'absent' ? 'selected' : ''; ?>><?= e(t('admin.attendance.absent')); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance_note[<?= $studentId; ?>]" value="<?= e((string) ($studentRow['attendance_note'] ?? '')); ?>" placeholder="<?= e(t('admin.attendance.note_placeholder')); ?>" <?= $canManageAttendance ? '' : 'readonly'; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($canManageAttendance): ?>
                    <div class="mt-3">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.attendance.save')); ?></button>
                    </div>
                <?php endif; ?>
            </form>
        </article>
    <?php endif; ?>
</div>

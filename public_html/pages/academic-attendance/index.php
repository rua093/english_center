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
$adminTitle = 'Học vụ - Điểm danh';
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3>Bộ lọc điểm danh</h3>
        <form class="grid gap-3 md:grid-cols-4" method="get" action="<?= e(page_url('attendance-academic')); ?>">
            <input type="hidden" name="page" value="attendance-academic">
            <label>
                Khóa học
                <select name="course_id">
                    <option value="0">-- Tất cả khóa học --</option>
                    <?php foreach ($courses as $course): ?>
                        <?php $courseId = (int) ($course['id'] ?? 0); ?>
                        <option value="<?= $courseId; ?>" <?= $selectedCourseId === $courseId ? 'selected' : ''; ?>>
                            <?= e((string) ($course['course_name'] ?? ('Khóa #' . $courseId))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Lớp học
                <select name="class_id">
                    <option value="0">-- Chọn lớp học --</option>
                    <?php foreach ($filteredClasses as $classRow): ?>
                        <?php $classId = (int) ($classRow['id'] ?? 0); ?>
                        <option value="<?= $classId; ?>" <?= $selectedClassId === $classId ? 'selected' : ''; ?>>
                            <?= e((string) ($classRow['class_name'] ?? ('Lớp #' . $classId))); ?>
                            <?= !empty($classRow['course_name']) ? ' | ' . e((string) $classRow['course_name']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="md:col-span-2">
                Buổi học (đã gắn lịch)
                <select name="schedule_id">
                    <option value="0">-- Chọn buổi --</option>
                    <?php foreach ($scheduleRows as $scheduleRow): ?>
                        <?php
                        $scheduleId = (int) ($scheduleRow['id'] ?? 0);
                        $lessonTitle = trim((string) ($scheduleRow['assigned_lesson_title'] ?? ''));
                        if ($lessonTitle === '') {
                            $lessonTitle = 'Chưa gắn chi tiết buổi học';
                        }

                        $label = $lessonTitle
                            . ' | ' . $formatDate((string) ($scheduleRow['study_date'] ?? ''))
                            . ' ' . $formatTime((string) ($scheduleRow['start_time'] ?? ''))
                            . '-' . $formatTime((string) ($scheduleRow['end_time'] ?? ''))
                            . ' | ' . trim((string) ($scheduleRow['room_name'] ?? 'Online'));
                        ?>
                        <option value="<?= $scheduleId; ?>" <?= $selectedScheduleId === $scheduleId ? 'selected' : ''; ?>><?= e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="flex items-end gap-2 md:col-span-4">
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Xem danh sách điểm danh</button>
                <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('attendance-academic')); ?>">Đặt lại</a>
            </div>
        </form>
    </article>

    <?php if (!is_array($selectedClass)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            Chọn khóa học và lớp học để bắt đầu điểm danh.
        </article>
    <?php elseif (empty($scheduleRows)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            Lớp này chưa có khung lịch học nào. Hãy vào trang Quản lý lớp học hoặc Lịch học để tạo lịch trước khi điểm danh.
        </article>
    <?php elseif (!is_array($selectedSchedule)): ?>
        <article class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 shadow-sm">
            Chọn một khung lịch để hiển thị danh sách học viên.
        </article>
    <?php else: ?>
        <?php
        $selectedLessonTitle = trim((string) ($selectedSchedule['assigned_lesson_title'] ?? ''));
        if ($selectedLessonTitle === '') {
            $selectedLessonTitle = 'Chưa gắn chi tiết buổi học';
        }
        ?>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3>Điểm danh buổi học</h3>
            <p class="text-sm text-slate-600">
                <strong><?= e($selectedLessonTitle); ?></strong>
                | <?= e($formatDate((string) ($selectedSchedule['study_date'] ?? ''))); ?>
                <?= e($formatTime((string) ($selectedSchedule['start_time'] ?? ''))); ?> - <?= e($formatTime((string) ($selectedSchedule['end_time'] ?? ''))); ?>
                | <?= e((string) ($selectedSchedule['room_name'] ?? 'Online')); ?>
            </p>

            <?php if (!$canManageAttendance): ?>
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                    Bạn có quyền xem nhưng chưa có quyền cập nhật điểm danh. Cần quyền <strong>academic.schedules.update</strong>.
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
                                <th>Học viên</th>
                                <th>Trạng thái học viên</th>
                                <th>Điểm danh</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendanceRoster)): ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500">Lịch học này chưa có học viên trong lớp.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($attendanceRoster as $studentRow): ?>
                                    <?php $studentId = (int) ($studentRow['student_id'] ?? 0); ?>
                                    <tr>
                                        <td><strong><?= e((string) ($studentRow['student_name'] ?? ('Học viên #' . $studentId))); ?></strong></td>
                                        <td>
                                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold capitalize is-<?= e((string) ($studentRow['learning_status'] ?? 'official')); ?>">
                                                <?= e((string) ($studentRow['learning_status'] ?? 'official')); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <select name="attendance_status[<?= $studentId; ?>]" <?= $canManageAttendance ? '' : 'disabled'; ?>>
                                                <option value="" <?= trim((string) ($studentRow['attendance_status'] ?? '')) === '' ? 'selected' : ''; ?>>Chưa đánh dấu</option>
                                                <option value="present" <?= (string) ($studentRow['attendance_status'] ?? '') === 'present' ? 'selected' : ''; ?>>Có mặt</option>
                                                <option value="late" <?= (string) ($studentRow['attendance_status'] ?? '') === 'late' ? 'selected' : ''; ?>>Đi muộn</option>
                                                <option value="absent" <?= (string) ($studentRow['attendance_status'] ?? '') === 'absent' ? 'selected' : ''; ?>>Vắng</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance_note[<?= $studentId; ?>]" value="<?= e((string) ($studentRow['attendance_note'] ?? '')); ?>" placeholder="Ghi chú thêm (nếu có)" <?= $canManageAttendance ? '' : 'readonly'; ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($canManageAttendance): ?>
                    <div class="mt-3">
                        <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu điểm danh</button>
                    </div>
                <?php endif; ?>
            </form>
        </article>
    <?php endif; ?>
</div>

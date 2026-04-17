<?php
$scheduleId = (int) ($_GET['id'] ?? 0);
if ($scheduleId > 0) {
    require_permission('academic.schedules.update');
} else {
    require_permission('academic.schedules.create');
}

$academicModel = new AcademicModel();
$editingSchedule = $scheduleId > 0 ? $academicModel->findSchedule($scheduleId) : null;
$lookups = $academicModel->scheduleLookups();
$allSchedules = $academicModel->listSchedules();

$success = get_flash('success');
$error = get_flash('error');

$scheduleConflictDataset = array_map(static function (array $schedule): array {
    return [
        'id' => (int) ($schedule['id'] ?? 0),
        'class_id' => (int) ($schedule['class_id'] ?? 0),
        'room_id' => isset($schedule['room_id']) ? (int) $schedule['room_id'] : 0,
        'teacher_id' => (int) ($schedule['teacher_id'] ?? 0),
        'class_name' => (string) ($schedule['class_name'] ?? ''),
        'room_name' => (string) ($schedule['room_name'] ?? ''),
        'study_date' => (string) ($schedule['study_date'] ?? ''),
        'start_time' => (string) ($schedule['start_time'] ?? ''),
        'end_time' => (string) ($schedule['end_time'] ?? ''),
    ];
}, $allSchedules);

$module = 'schedules';
$adminTitle = $editingSchedule ? 'Học vụ - Sửa lịch học' : 'Học vụ - Thêm lịch học';
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= $editingSchedule ? 'Chỉnh sửa lịch học' : 'Thêm lịch học'; ?></h2>
        <form class="grid gap-3" method="post" action="/api/schedules/save" data-schedule-form="1">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingSchedule['id'] ?? 0); ?>">
                <label>Lớp học
                    <select name="class_id" required>
                        <?php foreach ($lookups['classes'] as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingSchedule['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>><?= e((string) $class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Phòng học
                    <select name="room_id">
                        <option value="">Online / chưa chọn phòng</option>
                        <?php foreach ($lookups['rooms'] as $room): ?>
                            <option value="<?= (int) $room['id']; ?>" <?= (int) ($editingSchedule['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : ''; ?>><?= e((string) $room['room_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Giáo viên
                    <select name="teacher_id" required>
                        <option value="">-- Chọn giáo viên --</option>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingSchedule['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e((string) $teacher['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Ngày học<input type="date" name="study_date" value="<?= e((string) ($editingSchedule['study_date'] ?? '')); ?>" required></label>
                <label>Giờ bắt đầu<input type="time" name="start_time" value="<?= e((string) ($editingSchedule['start_time'] ?? '')); ?>" required></label>
                <label>Giờ kết thúc<input type="time" name="end_time" value="<?= e((string) ($editingSchedule['end_time'] ?? '')); ?>" required></label>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit">Lưu lịch học</button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('schedules-academic')); ?>">Quay lại</a>
        </form>
    </article>
</div>

<script>
(function () {
    const conflictSource = <?= json_encode($scheduleConflictDataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function parseIntSafe(value) {
        const parsed = Number.parseInt(String(value ?? '').trim(), 10);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function toMinutes(timeValue) {
        const normalized = String(timeValue ?? '').trim();
        if (normalized === '') {
            return null;
        }

        const parts = normalized.split(':');
        if (parts.length < 2) {
            return null;
        }

        const hours = Number.parseInt(parts[0], 10);
        const minutes = Number.parseInt(parts[1], 10);
        if (!Number.isFinite(hours) || !Number.isFinite(minutes)) {
            return null;
        }

        if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
            return null;
        }

        return (hours * 60) + minutes;
    }

    function formatTime(timeValue) {
        const normalized = String(timeValue ?? '').trim();
        return normalized.length >= 5 ? normalized.slice(0, 5) : normalized;
    }

    function hasOverlap(startA, endA, startB, endB) {
        return startA < endB && endA > startB;
    }

    function sameSchedule(item, scheduleId) {
        return parseIntSafe(item.id) === scheduleId;
    }

    const forms = Array.from(document.querySelectorAll('form[data-schedule-form="1"]'));
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const scheduleId = parseIntSafe((form.querySelector('input[name="id"]') || {}).value ?? '0');
            const classId = parseIntSafe((form.querySelector('[name="class_id"]') || {}).value ?? '0');
            const teacherId = parseIntSafe((form.querySelector('[name="teacher_id"]') || {}).value ?? '0');
            const roomRaw = String((form.querySelector('[name="room_id"]') || {}).value ?? '').trim();
            const roomId = roomRaw === '' ? 0 : parseIntSafe(roomRaw);
            const studyDate = String((form.querySelector('[name="study_date"]') || {}).value ?? '').trim();
            const startTime = String((form.querySelector('[name="start_time"]') || {}).value ?? '').trim();
            const endTime = String((form.querySelector('[name="end_time"]') || {}).value ?? '').trim();

            const startMinutes = toMinutes(startTime);
            const endMinutes = toMinutes(endTime);

            if (startMinutes === null || endMinutes === null) {
                event.preventDefault();
                window.alert('Gio hoc khong hop le.');
                return;
            }

            if (startMinutes >= endMinutes) {
                event.preventDefault();
                window.alert('Gio ket thuc phai sau gio bat dau.');
                return;
            }

            const classConflict = conflictSource.find(function (item) {
                if (sameSchedule(item, scheduleId)) {
                    return false;
                }

                if (String(item.study_date ?? '').trim() !== studyDate || parseIntSafe(item.class_id) !== classId) {
                    return false;
                }

                const existingStart = toMinutes(item.start_time ?? '');
                const existingEnd = toMinutes(item.end_time ?? '');
                if (existingStart === null || existingEnd === null) {
                    return false;
                }

                return hasOverlap(startMinutes, endMinutes, existingStart, existingEnd);
            });

            if (classConflict) {
                event.preventDefault();
                window.alert('Lop hoc da co lich trung gio (' + formatTime(classConflict.start_time) + ' - ' + formatTime(classConflict.end_time) + ').');
                return;
            }

            const teacherConflict = conflictSource.find(function (item) {
                if (sameSchedule(item, scheduleId)) {
                    return false;
                }

                if (String(item.study_date ?? '').trim() !== studyDate || parseIntSafe(item.teacher_id) !== teacherId) {
                    return false;
                }

                const existingStart = toMinutes(item.start_time ?? '');
                const existingEnd = toMinutes(item.end_time ?? '');
                if (existingStart === null || existingEnd === null) {
                    return false;
                }

                return hasOverlap(startMinutes, endMinutes, existingStart, existingEnd);
            });

            if (teacherConflict) {
                event.preventDefault();
                const conflictClass = String(teacherConflict.class_name ?? '').trim();
                window.alert('Giao vien da co lich trung gio' + (conflictClass !== '' ? ' voi lop ' + conflictClass : '') + '.');
                return;
            }

            if (roomId > 0) {
                const roomConflict = conflictSource.find(function (item) {
                    if (sameSchedule(item, scheduleId)) {
                        return false;
                    }

                    if (String(item.study_date ?? '').trim() !== studyDate || parseIntSafe(item.room_id) !== roomId) {
                        return false;
                    }

                    const existingStart = toMinutes(item.start_time ?? '');
                    const existingEnd = toMinutes(item.end_time ?? '');
                    if (existingStart === null || existingEnd === null) {
                        return false;
                    }

                    return hasOverlap(startMinutes, endMinutes, existingStart, existingEnd);
                });

                if (roomConflict) {
                    event.preventDefault();
                    const roomName = String(roomConflict.room_name ?? '').trim();
                    window.alert('Phong hoc da co lich trung gio' + (roomName !== '' ? ' tai ' + roomName : '') + '.');
                }
            }
        });
    });
})();
</script>



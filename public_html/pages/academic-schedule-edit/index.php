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
$weekdayOptions = [
    1 => t('admin.schedules.monday'),
    2 => t('admin.schedules.tuesday'),
    3 => t('admin.schedules.wednesday'),
    4 => t('admin.schedules.thursday'),
    5 => t('admin.schedules.friday'),
    6 => t('admin.schedules.saturday'),
    7 => t('admin.schedules.sunday'),
];

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
$adminTitle = $editingSchedule ? t('admin.schedule_edit.title_edit') : t('admin.schedule_edit.title_add');
?>
<div class="grid gap-4">
    <?php if ($success): ?>
        <div class="rounded-xl border-l-4 border-emerald-500 bg-emerald-50 p-3 text-sm text-emerald-700"><?= e($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="rounded-xl border-l-4 border-rose-500 bg-rose-50 p-3 text-sm text-rose-700"><?= e($error); ?></div>
    <?php endif; ?>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2><?= e($editingSchedule ? t('admin.schedule_edit.heading_edit') : t('admin.schedule_edit.heading_add')); ?></h2>
        <form class="grid gap-3" method="post" action="/api/schedules/save" data-schedule-form="1">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingSchedule['id'] ?? 0); ?>">
                <label><?= e(t('admin.schedule_edit.class')); ?>
                    <select name="class_id" required>
                        <?php foreach ($lookups['classes'] as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingSchedule['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>><?= e((string) $class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.schedule_edit.room')); ?>
                    <select name="room_id">
                        <option value=""><?= e(t('admin.schedule_edit.online_or_no_room')); ?></option>
                        <?php foreach ($lookups['rooms'] as $room): ?>
                            <option value="<?= (int) $room['id']; ?>" <?= (int) ($editingSchedule['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : ''; ?>><?= e((string) $room['room_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.schedule_edit.teacher')); ?>
                    <select name="teacher_id" required>
                        <option value=""><?= e(t('admin.schedule_edit.choose_teacher')); ?></option>
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingSchedule['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e(teacher_dropdown_label($teacher)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?= e(t('admin.schedule_edit.study_date')); ?><input type="date" name="study_date" value="<?= e((string) ($editingSchedule['study_date'] ?? '')); ?>" required></label>
                <label><?= e(t('admin.schedule_edit.start_time')); ?><input type="time" name="start_time" value="<?= e((string) ($editingSchedule['start_time'] ?? '')); ?>" required></label>
                <label><?= e(t('admin.schedule_edit.end_time')); ?><input type="time" name="end_time" value="<?= e((string) ($editingSchedule['end_time'] ?? '')); ?>" required></label>
                <?php if (!$editingSchedule): ?>
                    <label><?= e(t('admin.schedule_edit.repeat_mode')); ?>
                        <select name="repeat_mode" data-repeat-mode="1">
                            <option value="none"><?= e(t('admin.schedule_edit.repeat_none')); ?></option>
                            <option value="weekly"><?= e(t('admin.schedule_edit.repeat_weekly')); ?></option>
                        </select>
                    </label>
                    <div class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4" data-repeat-panel="1">
                        <div class="grid gap-3">
                            <label><?= e(t('admin.schedule_edit.repeat_until')); ?><input type="date" name="repeat_until" value="" data-repeat-until="1"></label>
                            <fieldset class="grid gap-2">
                                <legend class="text-sm font-medium text-slate-700"><?= e(t('admin.schedule_edit.repeat_weekdays')); ?></legend>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($weekdayOptions as $weekdayValue => $weekdayLabel): ?>
                                        <label class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700">
                                            <input type="checkbox" name="repeat_weekdays[]" value="<?= (int) $weekdayValue; ?>" data-repeat-weekday="1">
                                            <span><?= e($weekdayLabel); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                            <p class="text-xs text-slate-500"><?= e(t('admin.schedule_edit.repeat_help')); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.schedule_edit.save')); ?></button>
            <a class="<?= ui_btn_secondary_classes(); ?>" href="<?= e(page_url('schedules-academic')); ?>"><?= e(t('admin.common.back')); ?></a>
        </form>
    </article>
</div>

<script>
(function () {
    const conflictSource = <?= json_encode($scheduleConflictDataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const scheduleEditI18n = {
        invalidTime: <?= json_encode(t('admin.schedule_edit.invalid_time'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        endAfterStart: <?= json_encode(t('admin.schedule_edit.end_after_start'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        classConflict: <?= json_encode(t('admin.schedule_edit.class_conflict'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        teacherConflict: <?= json_encode(t('admin.schedule_edit.teacher_conflict'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        withClass: <?= json_encode(t('admin.schedule_edit.with_class'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        roomConflict: <?= json_encode(t('admin.schedule_edit.room_conflict'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        atRoom: <?= json_encode(t('admin.schedule_edit.at_room'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        repeatUntilRequired: <?= json_encode(t('admin.schedule_edit.repeat_until_required'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        repeatUntilAfterStart: <?= json_encode(t('admin.schedule_edit.repeat_until_after_start'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        repeatWeekdaysRequired: <?= json_encode(t('admin.schedule_edit.repeat_weekdays_required'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    };

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

    function parseDateValue(value) {
        const normalized = String(value ?? '').trim();
        if (!/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
            return null;
        }

        const parsed = new Date(normalized + 'T00:00:00');
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    function formatDateValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function isoWeekdayFromDate(date) {
        const weekday = date.getDay();
        return weekday === 0 ? 7 : weekday;
    }

    function toggleRepeatPanel(form) {
        const repeatMode = form.querySelector('[data-repeat-mode="1"]');
        const repeatPanel = form.querySelector('[data-repeat-panel="1"]');
        if (!repeatMode || !repeatPanel) {
            return;
        }

        repeatPanel.classList.toggle('hidden', repeatMode.value !== 'weekly');
    }

    function syncStudyDateWeekday(form) {
        const repeatMode = form.querySelector('[data-repeat-mode="1"]');
        if (!(repeatMode instanceof HTMLSelectElement) || repeatMode.value !== 'weekly') {
            return;
        }

        const studyDateInput = form.querySelector('[name="study_date"]');
        if (!(studyDateInput instanceof HTMLInputElement)) {
            return;
        }

        const studyDate = parseDateValue(studyDateInput.value);
        if (!studyDate) {
            return;
        }

        const weekday = String(isoWeekdayFromDate(studyDate));
        const matchingInput = form.querySelector('[data-repeat-weekday="1"][value="' + weekday + '"]');
        if (matchingInput instanceof HTMLInputElement) {
            matchingInput.checked = true;
        }
    }

    function buildOccurrenceDates(form, studyDate, scheduleId) {
        const repeatMode = form.querySelector('[data-repeat-mode="1"]');
        if (!(repeatMode instanceof HTMLSelectElement) || repeatMode.value !== 'weekly' || scheduleId > 0) {
            return [studyDate];
        }

        const repeatUntilInput = form.querySelector('[data-repeat-until="1"]');
        const repeatUntil = String((repeatUntilInput || {}).value ?? '').trim();
        if (repeatUntil === '') {
            throw new Error(scheduleEditI18n.repeatUntilRequired);
        }

        const startDate = parseDateValue(studyDate);
        const endDate = parseDateValue(repeatUntil);
        if (!startDate || !endDate) {
            throw new Error(scheduleEditI18n.repeatUntilRequired);
        }

        if (endDate.getTime() < startDate.getTime()) {
            throw new Error(scheduleEditI18n.repeatUntilAfterStart);
        }

        const selectedWeekdays = new Set();
        form.querySelectorAll('[data-repeat-weekday="1"]:checked').forEach(function (input) {
            const weekday = parseIntSafe(input.value);
            if (weekday >= 1 && weekday <= 7) {
                selectedWeekdays.add(weekday);
            }
        });
        selectedWeekdays.add(isoWeekdayFromDate(startDate));

        if (selectedWeekdays.size === 0) {
            throw new Error(scheduleEditI18n.repeatWeekdaysRequired);
        }

        const dates = [];
        const cursor = new Date(startDate.getTime());
        while (cursor.getTime() <= endDate.getTime()) {
            if (selectedWeekdays.has(isoWeekdayFromDate(cursor))) {
                dates.push(formatDateValue(cursor));
            }
            cursor.setDate(cursor.getDate() + 1);
        }

        return dates;
    }

    const forms = Array.from(document.querySelectorAll('form[data-schedule-form="1"]'));
    forms.forEach(function (form) {
        toggleRepeatPanel(form);
        syncStudyDateWeekday(form);

        const repeatMode = form.querySelector('[data-repeat-mode="1"]');
        if (repeatMode instanceof HTMLSelectElement) {
            repeatMode.addEventListener('change', function () {
                toggleRepeatPanel(form);
                syncStudyDateWeekday(form);
            });
        }

        const studyDateInput = form.querySelector('[name="study_date"]');
        if (studyDateInput instanceof HTMLInputElement) {
            studyDateInput.addEventListener('change', function () {
                syncStudyDateWeekday(form);
            });
        }

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
                window.alert(scheduleEditI18n.invalidTime);
                return;
            }

            if (startMinutes >= endMinutes) {
                event.preventDefault();
                window.alert(scheduleEditI18n.endAfterStart);
                return;
            }

            let occurrenceDates = [studyDate];
            try {
                occurrenceDates = buildOccurrenceDates(form, studyDate, scheduleId);
            } catch (error) {
                event.preventDefault();
                window.alert(error instanceof Error ? error.message : String(error));
                return;
            }

            for (const occurrenceDate of occurrenceDates) {
                const classConflict = conflictSource.find(function (item) {
                    if (sameSchedule(item, scheduleId)) {
                        return false;
                    }

                    if (String(item.study_date ?? '').trim() !== occurrenceDate || parseIntSafe(item.class_id) !== classId) {
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
                    window.alert(scheduleEditI18n.classConflict.replace(':time', formatTime(classConflict.start_time) + ' - ' + formatTime(classConflict.end_time)));
                    return;
                }

                const teacherConflict = conflictSource.find(function (item) {
                    if (sameSchedule(item, scheduleId)) {
                        return false;
                    }

                    if (String(item.study_date ?? '').trim() !== occurrenceDate || parseIntSafe(item.teacher_id) !== teacherId) {
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
                    window.alert(scheduleEditI18n.teacherConflict + (conflictClass !== '' ? ' ' + scheduleEditI18n.withClass.replace(':class', conflictClass) : '') + '.');
                    return;
                }

                if (roomId > 0) {
                    const roomConflict = conflictSource.find(function (item) {
                        if (sameSchedule(item, scheduleId)) {
                            return false;
                        }

                        if (String(item.study_date ?? '').trim() !== occurrenceDate || parseIntSafe(item.room_id) !== roomId) {
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
                        window.alert(scheduleEditI18n.roomConflict + (roomName !== '' ? ' ' + scheduleEditI18n.atRoom.replace(':room', roomName) : '') + '.');
                        return;
                    }
                }
            }
        });
    });
})();
</script>



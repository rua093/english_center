<?php
require_permission('academic.schedules.view');

$academicModel = new AcademicModel();
$schedulePage = max(1, (int) ($_GET['schedule_page'] ?? 1));
$schedulePerPage = ui_pagination_resolve_per_page('schedule_per_page', 10);
$searchQuery = trim((string) ($_GET['search'] ?? ''));
$allSchedules = $academicModel->listSchedules();
$schedulePerPageOptions = ui_pagination_per_page_options();
$lookups = $academicModel->scheduleLookups();

$currentUser = auth_user() ?? [];
$currentUserRole = (string) ($currentUser['role'] ?? '');
$currentUserId = (int) ($currentUser['id'] ?? 0);

if ($currentUserRole === 'teacher' && $currentUserId > 0) {
    $allSchedules = array_values(array_filter($allSchedules, static function (array $schedule) use ($currentUserId): bool {
        return (int) ($schedule['teacher_id'] ?? 0) === $currentUserId;
    }));

    $lookupClasses = is_array($lookups['classes'] ?? null) ? $lookups['classes'] : [];
    $lookups['classes'] = array_values(array_filter($lookupClasses, static function (array $classRow) use ($currentUserId): bool {
        return (int) ($classRow['teacher_id'] ?? 0) === $currentUserId;
    }));

    $lookupTeachers = is_array($lookups['teachers'] ?? null) ? $lookups['teachers'] : [];
    $lookups['teachers'] = array_values(array_filter($lookupTeachers, static function (array $teacherRow) use ($currentUserId): bool {
        return (int) ($teacherRow['id'] ?? 0) === $currentUserId;
    }));
}

$classTeacherMap = [];
foreach ((array) ($lookups['classes'] ?? []) as $classRow) {
    $classId = (int) ($classRow['id'] ?? 0);
    $teacherId = (int) ($classRow['teacher_id'] ?? 0);
    if ($classId > 0 && $teacherId > 0) {
        $classTeacherMap[$classId] = $teacherId;
    }
}

$scheduleTotal = $academicModel->countSchedules($currentUserRole === 'teacher' ? $currentUserId : 0, $searchQuery);
$scheduleTotalPages = max(1, (int) ceil($scheduleTotal / $schedulePerPage));
if ($schedulePage > $scheduleTotalPages) {
    $schedulePage = $scheduleTotalPages;
}
$schedules = $academicModel->listSchedulesPage($schedulePage, $schedulePerPage, $currentUserRole === 'teacher' ? $currentUserId : 0, $searchQuery);

$editingSchedule = null;
if (!empty($_GET['edit'])) {
    $editingSchedule = $academicModel->findSchedule((int) $_GET['edit']);
    if (
        $currentUserRole === 'teacher' &&
        is_array($editingSchedule) &&
        (int) ($editingSchedule['teacher_id'] ?? 0) !== $currentUserId
    ) {
        $editingSchedule = null;
    }
}

$module = 'schedules';
$adminTitle = t('admin.schedules.title');

$success = get_flash('success');
$error = get_flash('error');

$canCreateClass = has_permission('academic.classes.create');
$canUpdateClass = has_permission('academic.classes.update');

$canCreateSchedule = has_permission('academic.schedules.create');
$canUpdateSchedule = has_permission('academic.schedules.update');
$canDeleteSchedule = has_permission('academic.schedules.delete');

$canCreateAssignment = has_permission('academic.assignments.create');
$canUpdateAssignment = has_permission('academic.assignments.update');

$canCreateMaterial = has_permission('materials.create');
$canUpdateMaterial = has_permission('materials.update');

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
$weekRefValue = $weekStartDate->format('o-\WW');
$prevWeekRef = $weekStartDate->modify('-7 days')->format('o-\WW');
$nextWeekRef = $weekStartDate->modify('+7 days')->format('o-\WW');
$todayDateValue = (new DateTimeImmutable('today'))->format('Y-m-d');
$currentTimeValue = (new DateTimeImmutable('now'))->format('H:i:s');

$weekDayLabels = [
    t('admin.schedules.monday'),
    t('admin.schedules.tuesday'),
    t('admin.schedules.wednesday'),
    t('admin.schedules.thursday'),
    t('admin.schedules.friday'),
    t('admin.schedules.saturday'),
    t('admin.schedules.sunday'),
];
$weekDays = [];
for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
    $currentDate = $weekStartDate->modify('+' . $dayOffset . ' days');
    $weekDays[] = [
        'label' => $weekDayLabels[$dayOffset],
        'value' => $currentDate->format('Y-m-d'),
        'display' => $currentDate->format('d/m/Y'),
    ];
}

$weekSchedules = array_values(array_filter($allSchedules, static function (array $schedule) use ($weekStartValue, $weekEndValue): bool {
    $studyDate = (string) ($schedule['study_date'] ?? '');
    return $studyDate >= $weekStartValue && $studyDate <= $weekEndValue;
}));

$weekTimeSlotMap = [];
$weekScheduleGrid = [];
foreach ($weekSchedules as $schedule) {
    $startTime = (string) ($schedule['start_time'] ?? '');
    $endTime = (string) ($schedule['end_time'] ?? '');
    $studyDate = (string) ($schedule['study_date'] ?? '');
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
    $weekScheduleGrid[$studyDate][$slotKey][] = $schedule;
}

$weekTimeSlots = array_values($weekTimeSlotMap);
usort($weekTimeSlots, static function (array $left, array $right): int {
    return strcmp(($left['start'] . '|' . $left['end']), ($right['start'] . '|' . $right['end']));
});

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
?>
<div class="grid gap-4">
        <?php if ($success): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-emerald-500 bg-emerald-50 text-emerald-700"><?= e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="rounded-xl border-l-4 p-3 text-sm border-rose-500 bg-rose-50 text-rose-700"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if ($canCreateSchedule || $canUpdateSchedule): ?>
        <article class="order-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3><?= e($editingSchedule ? t('admin.schedules.edit') : t('admin.schedules.add')); ?></h3>
            <form class="grid gap-3" method="post" action="/api/schedules/save" data-schedule-form="1">
                <?= csrf_input(); ?>
                <input type="hidden" name="id" value="<?= (int) ($editingSchedule['id'] ?? 0); ?>">
                <label>
                    <?= e(t('admin.schedule_edit.class')); ?>
                    <select name="class_id" required data-class-select="1">
                        <?php foreach ($lookups['classes'] as $class): ?>
                            <option value="<?= (int) $class['id']; ?>" <?= (int) ($editingSchedule['class_id'] ?? 0) === (int) $class['id'] ? 'selected' : ''; ?>><?= e((string) $class['class_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.schedule_edit.room')); ?>
                    <select name="room_id" required>
                        <?php foreach ($lookups['rooms'] as $room): ?>
                            <option value="<?= (int) $room['id']; ?>" <?= (int) ($editingSchedule['room_id'] ?? 0) === (int) $room['id'] ? 'selected' : ''; ?>><?= e((string) $room['room_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.schedule_edit.teacher')); ?>
                    <select name="teacher_id" required data-teacher-select="1">
                        <?php foreach ($lookups['teachers'] as $teacher): ?>
                            <option value="<?= (int) $teacher['id']; ?>" <?= (int) ($editingSchedule['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : ''; ?>><?= e(teacher_dropdown_label($teacher)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <?= e(t('admin.schedule_edit.study_date')); ?>
                    <input type="date" name="study_date" required value="<?= e((string) ($editingSchedule['study_date'] ?? '')); ?>">
                </label>
                <label>
                    <?= e(t('admin.schedule_edit.start_time')); ?>
                    <input type="time" name="start_time" required value="<?= e((string) ($editingSchedule['start_time'] ?? '')); ?>">
                </label>
                <label>
                    <?= e(t('admin.schedule_edit.end_time')); ?>
                    <input type="time" name="end_time" required value="<?= e((string) ($editingSchedule['end_time'] ?? '')); ?>">
                </label>
                <button class="<?= ui_btn_primary_classes(); ?>" type="submit"><?= e(t('admin.schedule_edit.save')); ?></button>
            </form>
        </article>
        <?php endif; ?>

        <article class="order-1 rounded-3xl border border-slate-200 bg-gradient-to-b from-white via-slate-50/70 to-white p-5 shadow-sm" data-weekly-card="1">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-slate-900"><?= e(t('admin.schedules.weekly_timetable')); ?></h3>
                    <p class="text-xs font-medium text-slate-500"><?= e(t('admin.schedules.week_range', ['from' => $weekStartDate->format('d/m/Y'), 'to' => $weekEndDate->format('d/m/Y')])); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <a class="inline-flex h-9 items-center rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700" data-week-nav-link="1" href="<?= e(page_url('schedules-academic', ['week_start' => $prevWeekStart, 'week_ref' => $prevWeekRef, 'schedule_page' => $schedulePage, 'schedule_per_page' => $schedulePerPage])); ?>"><?= e(t('admin.schedules.previous_week')); ?></a>
                    <span class="inline-flex h-9 items-center rounded-xl border border-slate-300 bg-slate-100 px-3 text-xs font-semibold text-slate-700 shadow-sm"><?= e($weekStartDate->format('d/m/Y')); ?> - <?= e($weekEndDate->format('d/m/Y')); ?></span>
                    <a class="inline-flex h-9 items-center rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700" data-week-nav-link="1" href="<?= e(page_url('schedules-academic', ['week_start' => $nextWeekStart, 'week_ref' => $nextWeekRef, 'schedule_page' => $schedulePage, 'schedule_per_page' => $schedulePerPage])); ?>"><?= e(t('admin.schedules.next_week')); ?></a>
                    <form class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-2 py-1 shadow-sm" method="get" action="<?= e(page_url('schedules-academic')); ?>" data-week-picker-form="1">
                        <input type="hidden" name="page" value="schedules-academic">
                        <input type="hidden" name="schedule_page" value="<?= (int) $schedulePage; ?>">
                        <input type="hidden" name="schedule_per_page" value="<?= (int) $schedulePerPage; ?>">
                        <label class="text-[11px] font-semibold text-slate-600" for="schedule-week-picker"><?= e(t('admin.schedules.choose_week')); ?></label>
                        <input id="schedule-week-picker" type="week" name="week_ref" value="<?= e($weekRefValue); ?>" class="h-8 rounded-lg border border-slate-300 bg-white px-2 text-xs font-semibold text-slate-700">
                        <button type="submit" class="inline-flex h-8 items-center rounded-lg border border-slate-300 bg-slate-50 px-2.5 text-xs font-semibold text-slate-700 transition hover:border-sky-400 hover:bg-sky-50 hover:text-sky-700"><?= e(t('admin.schedules.view')); ?></button>
                    </form>
                </div>
            </div>

            <?php if (empty($weekTimeSlots)): ?>
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.schedules.empty_week')); ?></div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-3xl border border-slate-400 bg-white shadow-[0_10px_30px_rgba(15,23,42,0.05)]">
                    <div class="min-w-[960px]">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap border-b border-r border-slate-400 bg-slate-100 px-3 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-700"><?= e(t('admin.schedules.time_slot')); ?></th>
                                <?php foreach ($weekDays as $weekDay): ?>
                                    <?php
                                    $isTodayColumn = (string) ($weekDay['value'] ?? '') === $todayDateValue;
                                    $weekDayHeaderClasses = $isTodayColumn
                                        ? 'border-b border-r border-sky-400 bg-[linear-gradient(180deg,rgba(248,252,255,1)_0%,rgba(238,248,255,1)_100%)] text-sky-950 shadow-[inset_0_-1px_0_rgba(56,189,248,0.28)]'
                                        : 'border-b border-r border-slate-400 bg-slate-50 text-slate-700';
                                    $weekDaySubLabelClasses = $isTodayColumn ? 'text-sky-700' : 'text-slate-500';
                                    ?>
                                    <th class="px-2 py-3 text-center <?= e($weekDayHeaderClasses); ?>">
                                        <div class="text-[15px] font-bold"><?= e((string) $weekDay['label']); ?></div>
                                        <div class="mt-0.5 text-[11px] font-semibold <?= e($weekDaySubLabelClasses); ?>"><?= e((string) $weekDay['display']); ?></div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($weekTimeSlots as $slot): ?>
                                <tr>
                                    <td class="whitespace-nowrap border-b border-r border-slate-400 bg-slate-50 px-3 py-3 text-xs font-semibold text-slate-700"><?= e((string) $slot['label']); ?></td>
                                    <?php foreach ($weekDays as $weekDay): ?>
                                        <?php $slotSchedules = $weekScheduleGrid[(string) $weekDay['value']][(string) $slot['key']] ?? []; ?>
                                        <?php
                                        $isTodayColumn = (string) ($weekDay['value'] ?? '') === $todayDateValue;
                                        $weekDayCellClasses = $isTodayColumn
                                            ? 'border-b border-r border-sky-300 bg-[linear-gradient(180deg,rgba(251,253,255,1)_0%,rgba(243,249,255,1)_100%)]'
                                            : 'border-b border-r border-slate-400 bg-white';
                                        ?>
                                        <td class="min-w-[150px] align-top px-2 py-2 <?= e($weekDayCellClasses); ?>">
                                            <?php if (empty($slotSchedules)): ?>
                                                <span class="text-xs font-medium <?= e($isTodayColumn ? 'text-sky-300' : 'text-slate-300'); ?>">-</span>
                                            <?php else: ?>
                                                <?php foreach ($slotSchedules as $slotSchedule): ?>
                                                    <?php
                                                    $slotRoomName = trim((string) ($slotSchedule['room_name'] ?? ''));
                                                    $slotTeacherName = trim((string) ($slotSchedule['teacher_name'] ?? ''));
                                                    $slotClassName = trim((string) ($slotSchedule['class_name'] ?? ''));
                                                    $slotStart = substr((string) ($slotSchedule['start_time'] ?? ''), 0, 5);
                                                    $slotEnd = substr((string) ($slotSchedule['end_time'] ?? ''), 0, 5);
                                                    $slotStudyDate = trim((string) ($slotSchedule['study_date'] ?? ''));
                                                    $isPastSchedule = $slotStudyDate < $todayDateValue
                                                        || ($slotStudyDate === $todayDateValue && trim((string) ($slotSchedule['end_time'] ?? '')) !== '' && trim((string) ($slotSchedule['end_time'] ?? '')) < $currentTimeValue);
                                                    $chipClasses = $isPastSchedule
                                                        ? 'border-slate-400 bg-slate-100 text-slate-400 opacity-75'
                                                        : 'border-sky-500 bg-[linear-gradient(180deg,rgba(239,246,255,1)_0%,rgba(224,242,254,0.9)_100%)] text-sky-800 shadow-[0_8px_18px_rgba(14,165,233,0.08)] hover:-translate-y-0.5 hover:border-sky-600 hover:shadow-[0_12px_24px_rgba(14,165,233,0.12)]';
                                                    ?>
                                                    <div
                                                        class="schedule-week-chip mb-1 last:mb-0 rounded-2xl border px-3 py-2 text-[11px] font-semibold transition duration-200 <?= e($chipClasses); ?>"
                                                        data-weekly-chip="1"
                                                        data-class-name="<?= e($slotClassName !== '' ? $slotClassName : '-'); ?>"
                                                        data-teacher-name="<?= e($slotTeacherName !== '' ? $slotTeacherName : '-'); ?>"
                                                        data-room-name="<?= e($slotRoomName !== '' ? $slotRoomName : t('admin.schedules.online')); ?>"
                                                        data-time-label="<?= e($slotStart . ' - ' . $slotEnd); ?>"
                                                        title="<?= e($slotClassName . ' | ' . $slotTeacherName . ' | ' . ($slotRoomName !== '' ? $slotRoomName : t('admin.schedules.online')) . ' | ' . $slotStart . '-' . $slotEnd); ?>"
                                                    >
                                                        <div class="truncate text-[12px] font-bold"><?= e($slotClassName !== '' ? $slotClassName : t('admin.schedules.lesson')); ?></div>
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
                <p class="mt-2 text-xs text-slate-500"><?= e(t('admin.schedules.week_hint')); ?></p>
            <?php endif; ?>
        </article>

        <article
            class="order-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            data-ajax-table-root="1"
            data-ajax-page-key="page"
            data-ajax-page-value="schedules-academic"
            data-ajax-page-param="schedule_page"
            data-ajax-search-param="search"
        >
            <h3><?= e(t('admin.schedules.list')); ?></h3>
            <div class="admin-table-toolbar mb-3 flex flex-wrap items-center gap-3">
                <label class="relative w-full max-w-sm">
                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-slate-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </span>
                    <input data-ajax-search="1" type="search" value="<?= e($searchQuery); ?>" placeholder="<?= e(t('admin.schedules.search_placeholder')); ?>" autocomplete="off" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                </label>
                <span data-ajax-row-info="1" class="text-sm font-medium text-slate-500"><?= e(t('admin.schedules.showing_rows', ['shown' => (int) count($schedules), 'total' => (int) $scheduleTotal])); ?></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full border-collapse text-sm" data-disable-global-filter="1" data-disable-row-detail="1">
                <thead>
                    <tr><th><?= e(t('admin.schedule_edit.class')); ?></th><th><?= e(t('admin.schedule_edit.room')); ?></th><th><?= e(t('admin.classes.teacher_code')); ?></th><th><?= e(t('admin.schedule_edit.teacher')); ?></th><th><?= e(t('admin.schedule_edit.study_date')); ?></th><th><?= e(t('admin.schedules.time')); ?></th><th><?= e(t('admin.common.actions')); ?></th></tr>
                </thead>
                <tbody data-ajax-tbody="1">
                    <?php if (empty($schedules)): ?>
                        <tr><td colspan="7"><div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500"><?= e(t('admin.schedules.empty')); ?></div></td></tr>
                    <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <?php
                        $scheduleStudyDate = trim((string) ($schedule['study_date'] ?? ''));
                        $scheduleEndTime = trim((string) ($schedule['end_time'] ?? ''));
                        $isPastScheduleRow = $scheduleStudyDate < $todayDateValue
                            || ($scheduleStudyDate === $todayDateValue && $scheduleEndTime !== '' && $scheduleEndTime < $currentTimeValue);
                        ?>
                        <tr class="<?= $isPastScheduleRow ? 'bg-slate-50/70 text-slate-500' : ''; ?>">
                            <td><?= e((string) $schedule['class_name']); ?></td>
                            <td><?= e((string) ($schedule['room_name'] ?? '')); ?></td>
                            <td><?= e((string) ($schedule['teacher_code'] ?? '-')); ?></td>
                            <td><?= e((string) ($schedule['teacher_name'] ?? t('admin.schedule_edit.teacher'))); ?></td>
                            <td><?= e(ui_format_date((string) ($schedule['study_date'] ?? ''))); ?></td>
                            <td><?= e((string) $schedule['start_time']); ?> - <?= e((string) $schedule['end_time']); ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?php if ($canUpdateSchedule): ?>
                                        <a
                                            href="<?= e(page_url('schedules-academic-edit', ['id' => (int) $schedule['id'], 'schedule_page' => $schedulePage, 'schedule_per_page' => $schedulePerPage, 'week_start' => $weekStartValue, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"
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
                                    <?php if ($canDeleteSchedule): ?>
                                        <form class="inline-block" method="post" action="/api/schedules/delete?id=<?= (int) $schedule['id']; ?>" onsubmit="return confirm(<?= e(json_encode(t('admin.schedules.delete_confirm'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>);">
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
                <?php if ($scheduleTotal > 0): ?>
                    <div data-ajax-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-3 py-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
                            <span data-ajax-row-info="1" class="min-w-0 flex-1 font-medium"><?= e(t('admin.schedules.page_info', ['current' => (int) $schedulePage, 'total' => (int) $scheduleTotalPages, 'count' => (int) $scheduleTotal])); ?></span>
                            <div class="ml-auto inline-flex items-center gap-1.5">
                                <form class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1" method="get" action="<?= e(page_url('schedules-academic')); ?>">
                                    <input type="hidden" name="page" value="schedules-academic">
                                    <input type="hidden" name="week_start" value="<?= e($weekStartValue); ?>">
                                    <input type="hidden" name="search" value="<?= e($searchQuery); ?>">
                                    <label class="text-[11px] font-semibold text-slate-500" for="schedule-per-page"><?= e(t('admin.common.rows')); ?></label>
                                    <select id="schedule-per-page" name="schedule_per_page" data-ajax-per-page="1" class="h-7 rounded-md border border-slate-200 bg-white px-2 text-xs font-semibold text-slate-700">
                                        <?php foreach ($schedulePerPageOptions as $option): ?>
                                            <option value="<?= (int) $option; ?>" <?= $schedulePerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if ($schedulePage > 1): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('schedules-academic', ['schedule_page' => $schedulePage - 1, 'schedule_per_page' => $schedulePerPage, 'week_start' => $weekStartValue, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.previous')); ?></a>
                                <?php else: ?>
                                    <span class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-slate-100 px-2.5 text-xs font-semibold text-slate-400"><?= e(t('admin.common.previous')); ?></span>
                                <?php endif; ?>

                                <?php if ($schedulePage < $scheduleTotalPages): ?>
                                    <a class="inline-flex h-7 items-center rounded-md border border-slate-200 bg-white px-2.5 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700" href="<?= e(page_url('schedules-academic', ['schedule_page' => $schedulePage + 1, 'schedule_per_page' => $schedulePerPage, 'week_start' => $weekStartValue, 'search' => $searchQuery !== '' ? $searchQuery : null])); ?>"><?= e(t('admin.common.next')); ?></a>
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
    const conflictSource = <?= json_encode($scheduleConflictDataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const classTeacherMap = <?= json_encode($classTeacherMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const scheduleI18n = <?= json_encode([
        'invalidTime' => t('admin.schedule_edit.invalid_time'),
        'endAfterStart' => t('admin.schedule_edit.end_after_start'),
        'classConflict' => t('admin.schedule_edit.class_conflict'),
        'teacherConflict' => t('admin.schedule_edit.teacher_conflict'),
        'withClass' => t('admin.schedule_edit.with_class'),
        'roomConflict' => t('admin.schedule_edit.room_conflict'),
        'atRoom' => t('admin.schedule_edit.at_room'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

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

    function syncTeacherByClass(form, forceUpdate) {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const classSelect = form.querySelector('[data-class-select="1"]');
        const teacherSelect = form.querySelector('[data-teacher-select="1"]');
        if (!(classSelect instanceof HTMLSelectElement) || !(teacherSelect instanceof HTMLSelectElement)) {
            return;
        }

        const classId = parseIntSafe(classSelect.value);
        const teacherId = parseIntSafe(classTeacherMap[String(classId)] ?? 0);
        if (teacherId <= 0) {
            return;
        }

        const currentTeacherId = teacherSelect.tomselect
            ? parseIntSafe(teacherSelect.tomselect.getValue())
            : parseIntSafe(teacherSelect.value);
        if (!forceUpdate && currentTeacherId > 0) {
            return;
        }

        const targetOption = Array.from(teacherSelect.options).find(function (option) {
            return parseIntSafe(option.value) === teacherId;
        });
        if (!targetOption) {
            return;
        }

        if (teacherSelect.tomselect) {
            teacherSelect.tomselect.setValue(String(teacherId), true);
        } else {
            teacherSelect.value = String(teacherId);
            teacherSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    const forms = Array.from(document.querySelectorAll('form[data-schedule-form="1"]'));
    forms.forEach(function (form) {
        syncTeacherByClass(form, false);

        const classSelect = form.querySelector('[data-class-select="1"]');
        if (classSelect instanceof HTMLSelectElement) {
            classSelect.addEventListener('change', function () {
                syncTeacherByClass(form, true);
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
                window.alert(scheduleI18n.invalidTime);
                return;
            }

            if (startMinutes >= endMinutes) {
                event.preventDefault();
                window.alert(scheduleI18n.endAfterStart);
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
                const conflictTime = formatTime(classConflict.start_time) + ' - ' + formatTime(classConflict.end_time);
                window.alert(String(scheduleI18n.classConflict || '').replace(':time', conflictTime));
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
                window.alert(
                    scheduleI18n.teacherConflict
                    + (conflictClass !== '' ? ' ' + String(scheduleI18n.withClass || '').replace(':class', conflictClass) : '')
                    + '.'
                );
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
                    window.alert(
                        scheduleI18n.roomConflict
                        + (roomName !== '' ? ' ' + String(scheduleI18n.atRoom || '').replace(':room', roomName) : '')
                        + '.'
                    );
                }
            }
        });
    });
})();

(function () {
    const TOOLTIP_ID = 'schedule-week-tooltip';
    const tooltipI18n = <?= json_encode([
        'class' => t('admin.schedule_edit.class'),
        'teacher' => t('admin.schedule_edit.teacher'),
        'room' => t('admin.schedule_edit.room'),
        'time' => t('admin.schedules.time'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    let tooltip = document.getElementById(TOOLTIP_ID);

    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = TOOLTIP_ID;
        tooltip.className = 'pointer-events-none fixed z-[80] hidden max-w-xs rounded-lg border border-slate-300 bg-white p-2 text-[11px] leading-5 text-slate-700 shadow-xl';
        document.body.appendChild(tooltip);
    }

    function setTooltipPosition(event) {
        const padding = 12;
        const offset = 14;
        const tooltipWidth = tooltip.offsetWidth;
        const tooltipHeight = tooltip.offsetHeight;

        let left = event.clientX + offset;
        let top = event.clientY + offset;

        if (left + tooltipWidth > window.innerWidth - padding) {
            left = window.innerWidth - tooltipWidth - padding;
        }

        if (top + tooltipHeight > window.innerHeight - padding) {
            top = event.clientY - tooltipHeight - offset;
        }

        if (top < padding) {
            top = padding;
        }

        if (left < padding) {
            left = padding;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    function showTooltip(event, chip) {
        const className = String(chip.getAttribute('data-class-name') || '-');
        const teacherName = String(chip.getAttribute('data-teacher-name') || '-');
        const roomName = String(chip.getAttribute('data-room-name') || '-');
        const timeLabel = String(chip.getAttribute('data-time-label') || '-');

        tooltip.innerHTML = '';

        function appendLine(label, value) {
            const line = document.createElement('div');
            const title = document.createElement('span');
            title.className = 'font-semibold text-slate-800';
            title.textContent = label + ': ';
            line.appendChild(title);
            line.appendChild(document.createTextNode(value));
            tooltip.appendChild(line);
        }

        appendLine(tooltipI18n.class, className);
        appendLine(tooltipI18n.teacher, teacherName);
        appendLine(tooltipI18n.room, roomName);
        appendLine(tooltipI18n.time, timeLabel);
        tooltip.classList.remove('hidden');

        setTooltipPosition(event);
    }

    function hideTooltip() {
        tooltip.classList.add('hidden');
    }

    function bindWeeklyChips(root) {
        const chips = root.querySelectorAll('[data-weekly-chip="1"]');
        chips.forEach(function (chip) {
            if (chip.dataset.tooltipBound === '1') {
                return;
            }

            chip.dataset.tooltipBound = '1';
            chip.addEventListener('mouseenter', function (event) {
                showTooltip(event, chip);
            });
            chip.addEventListener('mousemove', function (event) {
                setTooltipPosition(event);
            });
            chip.addEventListener('mouseleave', hideTooltip);
            chip.addEventListener('blur', hideTooltip);
        });
    }

    window.__bindWeeklyChips = bindWeeklyChips;
    bindWeeklyChips(document);
})();

(function () {
    let activeRequestId = 0;

    function currentWeeklyCard() {
        return document.querySelector('[data-weekly-card="1"]');
    }

    function setCardLoading(isLoading) {
        const card = currentWeeklyCard();
        if (!card) {
            return;
        }

        card.classList.toggle('opacity-70', isLoading);
        card.classList.toggle('pointer-events-none', isLoading);
    }

    function fetchAndReplace(url, shouldPushState) {
        const requestId = ++activeRequestId;
        setCardLoading(true);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Failed to load weekly schedule card.');
            }
            return response.text();
        }).then(function (html) {
            if (requestId !== activeRequestId) {
                return;
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const incomingCard = doc.querySelector('[data-weekly-card="1"]');
            const card = currentWeeklyCard();

            if (!incomingCard || !card) {
                window.location.href = url;
                return;
            }

            card.replaceWith(incomingCard);

            if (window.__bindWeeklyChips) {
                window.__bindWeeklyChips(incomingCard);
            }

            bindWeekNavigation(incomingCard);

            if (shouldPushState) {
                window.history.pushState({ weeklyUrl: url }, '', url);
            }
        }).catch(function () {
            window.location.href = url;
        }).finally(function () {
            if (requestId === activeRequestId) {
                setCardLoading(false);
            }
        });
    }

    function bindWeekNavigation(card) {
        const weeklyCard = card || currentWeeklyCard();
        if (!weeklyCard) {
            return;
        }

        const navLinks = weeklyCard.querySelectorAll('[data-week-nav-link="1"]');
        navLinks.forEach(function (link) {
            if (link.dataset.navBound === '1') {
                return;
            }
            link.dataset.navBound = '1';
            link.addEventListener('click', function (event) {
                event.preventDefault();
                fetchAndReplace(link.href, true);
            });
        });

        const pickerForm = weeklyCard.querySelector('[data-week-picker-form="1"]');
        if (pickerForm && pickerForm.dataset.navBound !== '1') {
            pickerForm.dataset.navBound = '1';

            pickerForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const targetUrl = new URL(pickerForm.action, window.location.origin);
                const formData = new FormData(pickerForm);
                formData.forEach(function (value, key) {
                    targetUrl.searchParams.set(key, String(value));
                });
                fetchAndReplace(targetUrl.toString(), true);
            });

            const weekInput = pickerForm.querySelector('input[name="week_ref"]');
            if (weekInput) {
                weekInput.addEventListener('change', function () {
                    if (weekInput.value !== '') {
                        pickerForm.requestSubmit();
                    }
                });
            }
        }
    }

    window.addEventListener('popstate', function () {
        fetchAndReplace(window.location.href, false);
    });

    bindWeekNavigation();
})();
</script>





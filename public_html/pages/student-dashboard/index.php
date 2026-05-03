<?php
declare(strict_types=1);

require_role(['student', 'admin']);

require_once __DIR__ . '/../../models/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/../../models/tables/AttendanceTableModel.php';
require_once __DIR__ . '/../../models/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/../../models/tables/AssignmentsTableModel.php';

$studentDashboardActiveTab = 'dashboard-student';
$user = auth_user() ?? [];
$studentId = (int) ($user['id'] ?? 0);

$classStudentsTable = new ClassStudentsTableModel();
$attendanceTable = new AttendanceTableModel();
$tuitionFeesTable = new TuitionFeesTableModel();
$assignmentsTable = new AssignmentsTableModel();

$myClasses = [];
$dbEvents = [];
$upcomingAssignments = [];
$totalTuitionAmount = 0.0;
$totalTuitionPaid = 0.0;
$nowTimestamp = time();
$assignmentWindowEnd = $nowTimestamp + (7 * 24 * 3600);
$calendarFocusDate = null;

if ($studentId > 0) {
    $palette = static function (string $seed): string {
        $colors = ['blue', 'emerald', 'rose', 'amber'];
        return $colors[abs(crc32($seed)) % count($colors)];
    };

    $classRows = $classStudentsTable->listMyClassesForStudent($studentId);
    $scheduleRows = $classStudentsTable->listSchedulesForStudent($studentId);
    $scheduleRowsByClass = [];
    foreach ($scheduleRows as $scheduleRow) {
        $classKey = (int) ($scheduleRow['class_id'] ?? 0);
        if ($classKey <= 0) {
            continue;
        }
        $scheduleRowsByClass[$classKey][] = $scheduleRow;
    }

    foreach ($classRows as $row) {
        $classId = (int) ($row['class_id'] ?? 0);
        if ($classId <= 0) {
            continue;
        }

        $className = trim((string) ($row['class_name'] ?? ''));
        $classSchedules = $scheduleRowsByClass[$classId] ?? [];
        $attendanceSummary = $attendanceTable->summaryByStudentForClass($studentId, $classId);
        $tuitionRow = $tuitionFeesTable->findByStudentAndClass($studentId, $classId);
        $assignmentRows = $assignmentsTable->listForStudentByClass($studentId, $classId);

        $classTuitionAmount = (float) ($tuitionRow['total_amount'] ?? 0);
        $classTuitionPaid = (float) ($tuitionRow['amount_paid'] ?? 0);
        $totalTuitionAmount += $classTuitionAmount;
        $totalTuitionPaid += $classTuitionPaid;

        foreach ($classSchedules as $scheduleRow) {
            $date = (string) ($scheduleRow['study_date'] ?? '');
            if ($date === '' || $className === '') {
                continue;
            }

            $startTime = substr((string) ($scheduleRow['start_time'] ?? ''), 0, 5);
            $endTime = substr((string) ($scheduleRow['end_time'] ?? ''), 0, 5);
            $scheduleTimestamp = strtotime($date . ' ' . $startTime);
            if ($scheduleTimestamp === false) {
                continue;
            }

            if ($calendarFocusDate === null || $scheduleTimestamp < strtotime($calendarFocusDate)) {
                $calendarFocusDate = $date;
            }

            $dbEvents[] = [
                'id' => (int) ($scheduleRow['schedule_id'] ?? 0),
                'date' => $date,
                'title' => $className,
                'time' => trim($startTime . ' - ' . $endTime, ' -'),
                'teacher' => trim((string) ($scheduleRow['teacher_name'] ?? '')),
                'room' => trim((string) ($scheduleRow['room_name'] ?? '')),
                'lesson_title' => trim((string) ($scheduleRow['lesson_title'] ?? '')),
                'lesson_content' => trim((string) ($scheduleRow['lesson_content'] ?? '')),
                'lesson_attachment_file_path' => trim((string) ($scheduleRow['lesson_attachment_file_path'] ?? '')),
                'type' => $palette($className . '|' . $date),
            ];
        }

        foreach ($assignmentRows as $assignmentRow) {
            $deadline = (string) ($assignmentRow['deadline'] ?? '');
            $submissionStatus = (string) ($assignmentRow['submission_status'] ?? 'Chưa nộp');
            $scoreValue = $assignmentRow['score'] ?? null;
            $deadlineTimestamp = $deadline !== '' ? strtotime($deadline) : false;
            if ($deadlineTimestamp === false || $deadlineTimestamp < $nowTimestamp || $deadlineTimestamp > $assignmentWindowEnd) {
                continue;
            }

            $upcomingAssignments[] = [
                'title' => (string) ($assignmentRow['title'] ?? ''),
                'class' => $className,
                'deadline' => $deadline !== '' ? date('d/m/Y', strtotime($deadline)) : '---',
                'deadline_sort' => $deadlineTimestamp,
                'left' => $submissionStatus,
                'progress' => $scoreValue !== null && $scoreValue !== '' ? (int) min(100, round((float) $scoreValue * 10)) : 0,
                'tone' => $palette((string) ($assignmentRow['title'] ?? '') . '|' . $classId),
                'icon' => $submissionStatus === 'Đã nộp' ? 'fa-circle-check' : 'fa-triangle-exclamation',
            ];
        }

        $myClasses[] = [
            'class_id' => $classId,
            'class_name' => $className,
            'teacher_name' => (string) ($row['teacher_name'] ?? ''),
            'schedule_count' => count($classSchedules),
            'student_count' => (int) ($row['student_count'] ?? 0),
            'attendance' => $attendanceSummary,
            'tuition_amount' => $classTuitionAmount,
            'tuition_paid' => $classTuitionPaid,
            'assignment_count' => count($assignmentRows),
            'next_schedule_date' => (string) ($classSchedules[0]['study_date'] ?? ''),
            'tone' => $palette($className . '|' . $classId),
        ];
    }

    usort($upcomingAssignments, static function (array $left, array $right): int {
        return ($left['deadline_sort'] ?? PHP_INT_MAX) <=> ($right['deadline_sort'] ?? PHP_INT_MAX);
    });
}

$upcomingAssignments = array_slice($upcomingAssignments, 0, 3);
$eventCount = count($dbEvents);
$classCount = count($myClasses);
$assignmentCount = count($upcomingAssignments);
$totalTuitionPercent = $totalTuitionAmount > 0 ? (int) round(($totalTuitionPaid / $totalTuitionAmount) * 100) : 0;
$tuitionStatusLabel = $totalTuitionAmount > 0
    ? ($totalTuitionPaid >= $totalTuitionAmount ? 'Đã hoàn tất ' . $totalTuitionPercent . '%' : $totalTuitionPercent . '% đã thanh toán')
    : 'Chưa có dữ liệu học phí';
$tuitionStatusNote = $totalTuitionAmount > 0
    ? ($totalTuitionPaid >= $totalTuitionAmount ? '* Tuyệt vời! Bạn không có nợ đọng học phí.' : '* Bạn còn học phí cần thanh toán.')
    : '* Chưa có bản ghi học phí cho tài khoản này.';
$recentClassNames = array_values(array_filter(array_map(static fn (array $classRow): string => $classRow['class_name'] ?? '', $myClasses)));
$upcomingClassLabel = $recentClassNames[0] ?? 'Chưa có lớp học';
$upcomingClassCount = count($recentClassNames);
$calendarFocusDate = $calendarFocusDate ?: date('Y-m-d');
?>

<section class="relative min-h-screen overflow-hidden bg-[#f8fafc] py-8 px-2 sm:px-4 lg:px-6 xl:px-8">
    <div class="absolute inset-0 z-0 opacity-[0.08] pointer-events-none" style="background-image: radial-gradient(#1e3a8a 2px, transparent 2px); background-size: 30px 30px;"></div>
    <div class="absolute inset-x-0 top-0 z-0 h-80 bg-gradient-to-b from-blue-100/50 via-cyan-50/20 to-transparent pointer-events-none"></div>
    <div class="absolute -right-24 top-32 z-0 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-28 bottom-20 z-0 h-80 w-80 rounded-full bg-cyan-200/30 blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-[1800px]">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-[16rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)] md:items-start">
            <aside class="self-start md:sticky md:top-24">
                <?php require __DIR__ . '/partials/nav.php'; ?>
            </aside>
            <div class="min-w-0">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.9fr)] 2xl:grid-cols-[minmax(0,1.55fr)_minmax(380px,0.92fr)] md:items-start">
                    <article class="relative overflow-hidden rounded-[2rem] border border-blue-100 bg-gradient-to-br from-white via-blue-50 to-cyan-50 p-5 shadow-2xl transition-all md:p-6">
                    <div class="pointer-events-none absolute -top-24 -right-24 h-64 w-64 rounded-full bg-blue-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-cyan-200/35 blur-3xl"></div>

                    <div class="relative z-10 flex w-full flex-col gap-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.35em] text-blue-400">Lịch học</p>
                                <h3 id="calendar-title" class="mt-1 text-2xl md:text-3xl font-black text-slate-800 tracking-tight"></h3>
                            </div>
                            <div class="flex gap-1 rounded-2xl border border-white/70 bg-white p-1.5 shadow-sm">
                                <button onclick="changeDate(-1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&larr;</button>
                                <button onclick="resetToToday()" class="rounded-xl bg-white px-4 py-2 text-[11px] font-black uppercase tracking-widest text-slate-700 transition hover:bg-blue-50 hover:text-blue-700">Hôm nay</button>
                                <button onclick="changeDate(1)" class="rounded-xl bg-white px-3 py-2 text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">&rarr;</button>
                            </div>
                        </div>
                        <div class="flex rounded-2xl border border-white/70 bg-white p-1.5 shadow-sm">
                            <button id="btn-view-month" onclick="setView('month')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tháng</button>
                            <button id="btn-view-week" onclick="setView('week')" class="px-5 py-2 text-xs font-black uppercase rounded-xl transition-all duration-300">Tuần</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-px rounded-[1.75rem] border border-blue-100 bg-white/90 overflow-hidden shadow-[0_10px_30px_rgba(37,99,235,0.08)]">
                        <?php 
                        $weekdays = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];
                        foreach ($weekdays as $day): ?>
                            <div class="bg-gradient-to-b from-blue-100 via-white to-cyan-100 py-5 text-center text-xs md:text-sm font-black text-blue-700 uppercase tracking-[0.28em] border-b border-blue-200/80 shadow-[inset_0_-1px_0_rgba(255,255,255,0.95)]">
                                <span class="inline-flex items-center justify-center rounded-full bg-white/90 px-3 py-1.5 shadow-sm ring-1 ring-blue-200/90 ring-offset-1 ring-offset-blue-50"><?= $day ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div id="calendar-grid" class="contents"></div>
                    </div>
                    </div>
                    </article>

                    <aside class="space-y-6 self-start md:sticky md:top-24">
                        <article class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 bg-rose-500 rounded-full animate-pulse"></span>
                            Sắp diễn ra (48h)
                        </h3>
                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded-full uppercase"><?= date('d/m') ?></span>
                    </div>
                    
                    <div id="upcoming-list" class="space-y-4">
                        </div>
                </article>

                        <article class="relative overflow-hidden rounded-3xl border border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50 p-6 shadow-2xl shadow-rose-100/50">
                    <div class="pointer-events-none absolute -top-16 right-0 h-40 w-40 rounded-full bg-rose-200/40 blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-12 left-10 h-32 w-32 rounded-full bg-amber-200/40 blur-3xl"></div>

                    <div class="relative z-10 mb-5 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.35em] text-rose-500">Nhắc nhở gấp</p>
                            <h3 class="mt-2 text-xl font-black text-slate-900">Bài tập sắp đến hạn nộp</h3>
                            <p class="mt-1 text-sm text-slate-500">Đừng để quá hạn, các bài dưới đây cần xử lý sớm.</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-600 text-white shadow-lg shadow-rose-200 animate-pulse">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                    </div>

                    <div class="relative z-10 space-y-4">
                        <div class="rounded-2xl bg-slate-950 px-4 py-4 text-white shadow-lg shadow-slate-900/10">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-rose-300">Còn lại</p>
                                    <p class="mt-1 text-2xl font-black leading-none"><?= (int) $assignmentCount; ?> bài tập</p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-rose-200"><?= $assignmentCount > 0 ? 'Sắp đến hạn' : 'Không có bài mới'; ?></span>
                            </div>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-white/10">
                                <div class="h-2 rounded-full bg-gradient-to-r from-rose-400 via-amber-300 to-cyan-300 animate-pulse" style="width: <?= $assignmentCount > 0 ? 100 : 18; ?>%"></div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <?php foreach ($upcomingAssignments as $index => $assignment): ?>
                                <div class="group relative overflow-hidden rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                                    <div class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" style="background: linear-gradient(135deg, rgba(255,255,255,0.65), rgba(255,255,255,0));"></div>
                                    <div class="relative z-10 flex items-start gap-4">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-100 text-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-600 shadow-inner">
                                            <i class="fa-solid <?= e($assignment['icon']); ?>"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <h4 class="truncate text-sm font-black text-slate-800"><?= e($assignment['title']); ?></h4>
                                                    <p class="mt-1 text-xs font-medium text-slate-500"><?= e($assignment['class']); ?></p>
                                                </div>
                                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest <?= $assignment['tone'] === 'rose' ? 'bg-rose-100 text-rose-700' : ($assignment['tone'] === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') ?>"><?= e($assignment['left']); ?></span>
                                            </div>

                                            <div class="mt-3">
                                                <div class="mb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                                    <span>Hoàn thành</span>
                                                    <span><?= (int) $assignment['progress']; ?>%</span>
                                                </div>
                                                <div class="h-2 rounded-full bg-slate-100">
                                                    <div class="h-2 rounded-full bg-gradient-to-r from-<?= e($assignment['tone']); ?>-500 to-amber-400 shadow-[0_0_20px_rgba(251,146,60,0.25)]" style="width: <?= (int) $assignment['progress']; ?>%"></div>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between gap-3 text-[11px] font-semibold text-slate-500">
                                                <span>Deadline: <strong class="text-slate-700"><?= e($assignment['deadline']); ?></strong></span>
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-1 text-slate-500">
                                                    <span class="h-2 w-2 rounded-full bg-<?= $assignment['tone'] === 'rose' ? 'rose' : ($assignment['tone'] === 'amber' ? 'amber' : 'blue') ?>-500 animate-pulse"></span>
                                                    <?= $index === 0 ? 'Ưu tiên cao' : 'Đang theo dõi'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                        </article>

                        <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-lg shadow-blue-200 overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-blue-100 text-xs font-bold uppercase tracking-widest mb-1">Trạng thái học phí</p>
                        <h4 class="text-xl font-black mb-4"><?= e($tuitionStatusLabel); ?></h4>
                        <div class="w-full bg-blue-500 rounded-full h-1.5 mb-2"><div class="bg-white h-1.5 rounded-full" style="width: <?= $totalTuitionAmount > 0 ? $totalTuitionPercent : 0; ?>%"></div></div>
                        <p class="text-[10px] text-blue-100 font-medium italic"><?= e($tuitionStatusNote); ?></p>
                    </div>
                    <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-blue-500 opacity-50 transform rotate-12 group-hover:scale-110 transition" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

        <div id="event-tooltip" class="fixed hidden z-[9999] w-72 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200 p-5 pointer-events-auto transition-all duration-200 opacity-0 scale-95 translate-y-2">
        <div class="flex items-start gap-3 mb-3">
            <div id="tooltip-color" class="w-1.5 h-10 rounded-full"></div>
            <div>
                <h4 id="tooltip-title" class="font-black text-slate-800 text-base leading-tight"></h4>
                <p id="tooltip-time" class="text-blue-600 text-xs font-bold mt-1"></p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-slate-100">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Giảng viên</p>
                <p id="tooltip-teacher" class="text-xs font-bold text-slate-700"></p>
            </div>
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Vị trí</p>
                <p id="tooltip-room" class="text-xs font-bold text-slate-700"></p>
            </div>
        </div>
        <div class="mt-3 rounded-2xl border border-blue-100 bg-blue-50/60 p-3">
            <p class="text-[10px] uppercase font-bold text-blue-400 tracking-wider">Buổi học</p>
            <p id="tooltip-lesson-title" class="mt-1 text-xs font-black text-slate-800"></p>
            <p id="tooltip-lesson-content" class="mt-1 text-[11px] leading-relaxed text-slate-600"></p>
            <p id="tooltip-material" class="mt-2 text-[11px] font-semibold text-blue-700"></p>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventsData = <?= json_encode($dbEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    let currentDate = new Date('<?= e($calendarFocusDate); ?>T00:00:00');
    let currentView = 'month';
    let tooltipHideTimer = null;
    let tooltipIsHovered = false;
    let activeEventChip = null;

    const colorMap = {
        blue: 'bg-blue-50 text-blue-700 border-blue-200 border-l-[3px] border-l-blue-600',
        emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200 border-l-[3px] border-l-emerald-600',
        rose: 'bg-rose-50 text-rose-700 border-rose-200 border-l-[3px] border-l-rose-600',
        amber: 'bg-amber-50 text-amber-700 border-amber-200 border-l-[3px] border-l-amber-600',
    };

    const tooltipColorMap = {
        blue: 'bg-blue-600',
        emerald: 'bg-emerald-600',
        rose: 'bg-rose-600',
        amber: 'bg-amber-600',
    };

    function renderCalendar() {
        const grid = document.getElementById('calendar-grid');
        const title = document.getElementById('calendar-title');
        if (!grid || !title) {
            return;
        }

        grid.innerHTML = '';

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        const todayStr = new Date().toISOString().split('T')[0];

        if (currentView === 'month') {
            title.innerText = `Tháng ${month + 1}, ${year}`;
            const firstDay = new Date(year, month, 1);
            const lastDayPrevMonth = new Date(year, month, 0).getDate();
            const startDayIdx = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = startDayIdx; i > 0; i--) {
                const d = lastDayPrevMonth - i + 1;
                renderDayCell(grid, d, 'prev', false);
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                renderDayCell(grid, d, 'current', dateStr === todayStr, dateStr);
            }

            const remainingCells = 42 - (startDayIdx + daysInMonth);
            for (let d = 1; d <= remainingCells; d++) {
                renderDayCell(grid, d, 'next', false);
            }
        } else {
            const current = new Date(currentDate);
            const firstVisible = current.getDate() - (current.getDay() === 0 ? 6 : current.getDay() - 1);
            const startWeek = new Date(current.setDate(firstVisible));
            title.innerText = `Tuần ${startWeek.getDate()}/${startWeek.getMonth() + 1}`;

            for (let i = 0; i < 7; i++) {
                const day = new Date(startWeek);
                day.setDate(startWeek.getDate() + i);
                const dateStr = day.toISOString().split('T')[0];
                renderWeekColumn(grid, day, dateStr === todayStr, dateStr);
            }
        }

        updateUpcomingPanel();
        setupTooltipListeners();
        updateButtons();
    }

    function renderDayCell(container, day, type, isToday, dateStr) {
        const isDimmed = type !== 'current';
        const bgClass = isToday ? 'bg-gradient-to-br from-blue-100 via-white to-cyan-100' : (isDimmed ? 'bg-slate-50/70' : 'bg-white');
        const textClass = isDimmed ? 'text-slate-300' : (isToday ? 'text-blue-700 font-black' : 'text-slate-700');

        let html = `<div class="${bgClass} min-h-[110px] p-2 border-t border-white/20 border-r border-r-slate-200/40 transition-all hover:z-20 group relative">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sm md:text-base font-black ${isToday ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : ''} ${textClass}">${day}</span>
            <div class="mt-1.5 space-y-1">`;

        if (!isDimmed) {
            eventsData.filter((event) => event.date === dateStr).forEach((event) => {
                html += `<div class="event-chip ${colorMap[event.type]} text-[9px] font-black px-1.5 py-1 rounded-md cursor-help truncate shadow-sm hover:brightness-95"
                    data-title="${event.title}" data-time="${event.time}" data-teacher="${event.teacher}" data-room="${event.room}" data-lesson-title="${encodeURIComponent(event.lesson_title || '')}" data-lesson-content="${encodeURIComponent(event.lesson_content || '')}" data-lesson-attachment="${encodeURIComponent(event.lesson_attachment_file_path || '')}" data-color="${event.type}">${event.title}</div>`;
            });
        }

        html += `</div></div>`;
        container.innerHTML += html;
    }

    function renderWeekColumn(container, dateObj, isToday, dateStr) {
        let html = `<div class="${isToday ? 'bg-gradient-to-b from-blue-50/70 to-cyan-50/40' : 'bg-white'} min-h-[400px] p-3 border-t border-white/35 border-r border-r-slate-200/40">
            <p class="text-center mb-4">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-xl font-black ${isToday ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-slate-100 text-slate-700'}">${dateObj.getDate()}</span>
                <span class="mt-2 block text-[10px] font-black uppercase tracking-[0.28em] text-blue-400">Tháng ${dateObj.getMonth() + 1}</span>
            </p>
            <div class="space-y-2">`;

        eventsData.filter((event) => event.date === dateStr).forEach((event) => {
            html += `<div class="event-chip ${colorMap[event.type]} p-2.5 rounded-xl text-[11px] font-black cursor-help shadow-sm"
                data-title="${event.title}" data-time="${event.time}" data-teacher="${event.teacher}" data-room="${event.room}" data-lesson-title="${encodeURIComponent(event.lesson_title || '')}" data-lesson-content="${encodeURIComponent(event.lesson_content || '')}" data-lesson-attachment="${encodeURIComponent(event.lesson_attachment_file_path || '')}" data-color="${event.type}">
                <div class="opacity-70 text-[9px] mb-1 uppercase tracking-tighter">${event.time}</div>
                <div class="leading-tight">${event.title}</div>
            </div>`;
        });

        html += `</div></div>`;
        container.innerHTML += html;
    }

    function updateUpcomingPanel() {
        const list = document.getElementById('upcoming-list');
        if (!list) {
            return;
        }

        const today = new Date();
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);
        const datesToShow = [today.toISOString().split('T')[0], tomorrow.toISOString().split('T')[0]];
        const upcoming = eventsData.filter((event) => datesToShow.includes(event.date));

        if (upcoming.length === 0) {
            list.innerHTML = `<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-500">Chưa có lịch học trong 48 giờ tới.</div>`;
            return;
        }

        list.innerHTML = upcoming.map((event) => `
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group cursor-pointer">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full ${event.type === 'blue' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'}">${event.date === datesToShow[0] ? 'Hôm nay' : 'Ngày mai'}</span>
                    <span class="text-[10px] font-bold text-slate-400">${event.time}</span>
                </div>
                <h4 class="text-sm font-black text-slate-800 group-hover:text-blue-600 transition">${event.title}</h4>
                <p class="text-[10px] text-slate-500 font-medium mt-1 uppercase tracking-wider">${event.room} • ${event.teacher}</p>
            </div>
        `).join('');
    }

    function setupTooltipListeners() {
        const tooltip = document.getElementById('event-tooltip');
        if (!tooltip) {
            return;
        }

        const cancelTooltipHide = () => {
            if (tooltipHideTimer) {
                clearTimeout(tooltipHideTimer);
                tooltipHideTimer = null;
            }
        };

        const scheduleTooltipHide = () => {
            cancelTooltipHide();
            tooltipHideTimer = window.setTimeout(() => {
                if (tooltipIsHovered || activeEventChip) {
                    return;
                }
                tooltip.classList.remove('opacity-100');
                tooltip.classList.add('hidden');
                tooltipHideTimer = null;
            }, 220);
        };

        tooltip.onmouseenter = () => {
            tooltipIsHovered = true;
            cancelTooltipHide();
        };
        tooltip.onmouseleave = () => {
            tooltipIsHovered = false;
            scheduleTooltipHide();
        };

        document.querySelectorAll('.event-chip').forEach((chip) => {
            chip.onmouseenter = () => {
                activeEventChip = chip;
                cancelTooltipHide();
                const rect = chip.getBoundingClientRect();
                const tooltipTitle = document.getElementById('tooltip-title');
                const tooltipTime = document.getElementById('tooltip-time');
                const tooltipTeacher = document.getElementById('tooltip-teacher');
                const tooltipRoom = document.getElementById('tooltip-room');
                const tooltipColor = document.getElementById('tooltip-color');
                const tooltipLessonTitle = document.getElementById('tooltip-lesson-title');
                const tooltipLessonContent = document.getElementById('tooltip-lesson-content');
                const tooltipMaterial = document.getElementById('tooltip-material');

                if (tooltipTitle) tooltipTitle.innerText = chip.dataset.title || '';
                if (tooltipTime) tooltipTime.innerText = chip.dataset.time || '';
                if (tooltipTeacher) tooltipTeacher.innerText = chip.dataset.teacher || '';
                if (tooltipRoom) tooltipRoom.innerText = chip.dataset.room || '';
                if (tooltipColor) tooltipColor.className = `w-1.5 h-10 rounded-full ${tooltipColorMap[chip.dataset.color || 'blue']}`;
                if (tooltipLessonTitle) tooltipLessonTitle.innerText = decodeURIComponent(chip.dataset.lessonTitle || '') || 'Chưa có nội dung buổi học';
                if (tooltipLessonContent) tooltipLessonContent.innerText = decodeURIComponent(chip.dataset.lessonContent || '') || 'Giáo viên chưa ghi nội dung chi tiết cho buổi này.';
                if (tooltipMaterial) {
                    const attachment = decodeURIComponent(chip.dataset.lessonAttachment || '');
                    tooltipMaterial.innerText = attachment ? `Tài liệu: ${attachment.split('/').pop()}` : 'Tài liệu: Chưa có file đính kèm';
                }

                tooltip.style.left = `${rect.left + rect.width / 2}px`;
                tooltip.style.top = `${rect.top - 10}px`;
                tooltip.style.transform = 'translate(-50%, -100%) scale(1)';
                tooltip.classList.remove('hidden');
                setTimeout(() => tooltip.classList.add('opacity-100'), 10);
            };

            chip.onmouseleave = () => {
                if (activeEventChip === chip) {
                    activeEventChip = null;
                }
                scheduleTooltipHide();
            };
        });
    }

    function setView(view) {
        currentView = view;
        renderCalendar();
    }

    function changeDate(offset) {
        if (currentView === 'month') {
            currentDate.setMonth(currentDate.getMonth() + offset);
        } else {
            currentDate.setDate(currentDate.getDate() + offset * 7);
        }
        renderCalendar();
    }

    function resetToToday() {
        currentDate = new Date();
        renderCalendar();
    }

    function updateButtons() {
        const monthButton = document.getElementById('btn-view-month');
        const weekButton = document.getElementById('btn-view-week');
        if (!monthButton || !weekButton) {
            return;
        }

        monthButton.className = currentView === 'month'
            ? 'px-5 py-2 text-xs font-bold uppercase rounded-lg bg-white shadow-md text-blue-700'
            : 'px-5 py-2 text-xs font-bold uppercase rounded-lg text-slate-400 hover:text-slate-600';
        weekButton.className = currentView === 'week'
            ? 'px-5 py-2 text-xs font-bold uppercase rounded-lg bg-white shadow-md text-blue-700'
            : 'px-5 py-2 text-xs font-bold uppercase rounded-lg text-slate-400 hover:text-slate-600';
    }

    window.setView = setView;
    window.changeDate = changeDate;
    window.resetToToday = resetToToday;

    renderCalendar();
});
</script>

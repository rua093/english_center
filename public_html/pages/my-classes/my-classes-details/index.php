<?php
declare(strict_types=1);

require_login();
require_once __DIR__ . '/../../../models/tables/AttendanceTableModel.php';
require_once __DIR__ . '/../../../models/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/../../../models/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/../../../models/tables/ExtracurricularActivitiesTableModel.php';
require_once __DIR__ . '/../../../models/tables/ExamsTableModel.php';

$classStudentsTable = new ClassStudentsTableModel();
$attendanceTable = new AttendanceTableModel();
$tuitionFeesTable = new TuitionFeesTableModel();
$assignmentsTable = new AssignmentsTableModel();
$activitiesTable = new ExtracurricularActivitiesTableModel();
$examsTable = new ExamsTableModel();

$user = auth_user() ?? [];
$studentId = (int) ($user['id'] ?? 0);
$selectedClassId = max(0, (int) ($_GET['class_id'] ?? 0));

$myClasses = $studentId > 0 ? $classStudentsTable->listMyClassesForStudent($studentId) : [];
if ($selectedClassId <= 0 && !empty($myClasses)) {
    $selectedClassId = (int) ($myClasses[0]['class_id'] ?? 0);
}

$classRow = null;
foreach ($myClasses as $row) {
    if ((int) ($row['class_id'] ?? 0) === $selectedClassId) {
        $classRow = $row;
        break;
    }
}

if (!is_array($classRow)) {
    header('Location: ' . page_url('classes-my'));
    exit;
}

$studentSchedules = $classStudentsTable->listSchedulesForStudent($studentId);
$classSchedules = array_values(array_filter($studentSchedules, static function (array $scheduleRow) use ($selectedClassId): bool {
    return (int) ($scheduleRow['class_id'] ?? 0) === $selectedClassId;
}));

$firstSchedule = $classSchedules[0] ?? null;
$scheduleItems = [];
foreach ($classSchedules as $scheduleRow) {
    $studyDate = !empty($scheduleRow['study_date']) ? date('d/m/Y', strtotime((string) $scheduleRow['study_date'])) : '---';
    $startTime = !empty($scheduleRow['start_time']) ? date('H:i', strtotime((string) $scheduleRow['start_time'])) : '--:--';
    $endTime = !empty($scheduleRow['end_time']) ? date('H:i', strtotime((string) $scheduleRow['end_time'])) : '--:--';
    $scheduleItems[] = [
        'date' => $studyDate,
        'time' => $startTime . ' - ' . $endTime,
        'room' => trim((string) ($scheduleRow['room_name'] ?? 'Online')),
    ];
}

$classStatusMap = [
    'upcoming' => 'Sắp học',
    'active' => 'Đang học',
    'graduated' => 'Đã hoàn thành',
    'cancelled' => 'Đã huỷ',
];
$classStatus = $classStatusMap[(string) ($classRow['class_status'] ?? '')] ?? 'Đang học';

$classDetail = [
    'name' => (string) ($classRow['class_name'] ?? ''),
    'teacher' => (string) ($classRow['teacher_name'] ?? ''),
    'status' => $classStatus,
    'schedule' => $firstSchedule ? (date('d/m/Y', strtotime((string) ($firstSchedule['study_date'] ?? ''))) . ' ' . date('H:i', strtotime((string) ($firstSchedule['start_time'] ?? ''))) . ' - ' . date('H:i', strtotime((string) ($firstSchedule['end_time'] ?? '')))) : 'Chưa có lịch học',
    'start_time' => $firstSchedule && !empty($firstSchedule['start_time']) ? date('H:i', strtotime((string) $firstSchedule['start_time'])) : '--:--',
    'end_time' => $firstSchedule && !empty($firstSchedule['end_time']) ? date('H:i', strtotime((string) $firstSchedule['end_time'])) : '--:--',
    'room' => $firstSchedule ? trim((string) ($firstSchedule['room_name'] ?? 'Online')) : 'Chưa phân phòng',
    'course' => (string) ($classRow['course_name'] ?? ''),
    'start_date' => !empty($classRow['start_date']) ? date('d/m/Y', strtotime((string) $classRow['start_date'])) : '---',
    'end_date' => !empty($classRow['end_date']) ? date('d/m/Y', strtotime((string) $classRow['end_date'])) : '---',
    'total_lessons' => (int) ($classRow['total_lessons'] ?? 0),
    'total_schedules' => (int) ($classRow['total_schedules'] ?? 0),
];

$attendanceRaw = $attendanceTable->summaryByStudentForClass($studentId, $selectedClassId);
$attendanceTotal = (int) ($attendanceRaw['total_sessions'] ?? 0);
$attendancePresent = (int) ($attendanceRaw['present_count'] ?? 0);
$attendanceLate = (int) ($attendanceRaw['late_count'] ?? 0);
$attendanceAbsent = (int) ($attendanceRaw['absent_count'] ?? 0);
$attendancePercent = $attendanceTotal > 0 ? (int) round((($attendancePresent + $attendanceLate) / $attendanceTotal) * 100) : 0;

$tuitionRow = $tuitionFeesTable->findByStudentAndClass($studentId, $selectedClassId) ?? [];
$tuitionTotal = (float) ($tuitionRow['total_amount'] ?? 0);
$tuitionPaid = (float) ($tuitionRow['amount_paid'] ?? 0);
$tuitionRemaining = max(0, $tuitionTotal - $tuitionPaid);
$tuitionPaidPercent = $tuitionTotal > 0 ? (int) round(($tuitionPaid / $tuitionTotal) * 100) : 0;
$tuitionStatus = $tuitionRow === [] ? 'Chưa có học phí' : (((string) ($tuitionRow['status'] ?? 'debt')) === 'paid' ? 'Đã hoàn thành' : 'Đang nợ');

$activityRows = $studentId > 0 ? $activitiesTable->listForStudentActivities($studentId) : [];
$activityFeeTotal = 0.0;
$activityFeePaid = 0.0;
$activityRegisteredCount = 0;
foreach ($activityRows as $activityRow) {
    if ((int) ($activityRow['is_registered'] ?? 0) !== 1) {
        continue;
    }

    $activityRegisteredCount++;
    $activityFeeTotal += (float) ($activityRow['fee'] ?? 0);

    $registrationRow = $activitiesTable->findStudentRegistration((int) ($activityRow['id'] ?? 0), $studentId);
    if (is_array($registrationRow)) {
        $paidAmount = (float) ($registrationRow['amount_paid'] ?? 0);
        if ($paidAmount <= 0 && (string) ($registrationRow['payment_status'] ?? '') === 'paid') {
            $paidAmount = (float) ($activityRow['fee'] ?? 0);
        }
        $activityFeePaid += $paidAmount;
    }
}
$activityFeeRemaining = max(0, $activityFeeTotal - $activityFeePaid);
$activityFeePercent = $activityFeeTotal > 0 ? (int) round(($activityFeePaid / $activityFeeTotal) * 100) : 0;

$assignmentRows = $assignmentsTable->listForStudentByClass($studentId, $selectedClassId);
$assignments = [];
$assignmentScores = [];
$nowTs = time();
foreach ($assignmentRows as $assignmentRow) {
    $submittedAt = (string) ($assignmentRow['submitted_at'] ?? '');
    $score = $assignmentRow['score'] !== null ? (float) $assignmentRow['score'] : null;
    $deadlineRaw = (string) ($assignmentRow['deadline'] ?? '');
    $deadlineTs = $deadlineRaw !== '' ? strtotime($deadlineRaw) : false;
    $isExpired = $deadlineTs !== false && $deadlineTs < $nowTs;
    $isGraded = $score !== null;
    $status = 'Chưa nộp';
    $color = 'slate';

    if ($submittedAt !== '') {
        if ($isGraded) {
            $status = 'Đã chấm';
            $color = 'emerald';
            $assignmentScores[] = $score;
        } else {
            $status = 'Chờ chấm';
            $color = 'blue';
        }
    }

    $assignments[] = [
        'id' => (int) ($assignmentRow['id'] ?? 0),
        'title' => (string) ($assignmentRow['title'] ?? ''),
        'note' => (string) ($assignmentRow['description'] ?? ''),
        'file_url' => (string) ($assignmentRow['file_url'] ?? ''),
        'deadline' => $deadlineRaw !== '' ? date('d/m/Y H:i', $deadlineTs !== false ? $deadlineTs : strtotime($deadlineRaw)) : '---',
        'deadline_raw' => $deadlineRaw,
        'status' => $status,
        'score' => $score !== null ? number_format($score, 1) : '--',
        'teacher_comment' => (string) ($assignmentRow['teacher_comment'] ?? ''),
        'color' => $color,
        'submitted_at' => $submittedAt !== '' ? date('d/m/Y H:i', strtotime($submittedAt)) : '',
        'can_resubmit' => $submittedAt !== '' && !$isExpired && !$isGraded,
        'can_submit' => $submittedAt === '' && !$isExpired && !$isGraded,
        'is_expired' => $isExpired,
        'is_graded' => $isGraded,
        'disabled_reason' => $isGraded ? 'Bài đã được chấm không thể nộp lại.' : ($isExpired ? 'Đã quá hạn nộp bài.' : ''),
    ];
}

$defaultHomework = null;
foreach ($assignments as $assignmentCandidate) {
    if ((string) ($assignmentCandidate['submitted_at'] ?? '') === '' && empty($assignmentCandidate['is_expired'])) {
        $defaultHomework = $assignmentCandidate;
        break;
    }
}
if ($defaultHomework === null) {
    $defaultHomework = $assignments[0] ?? null;
}

$assignmentPage = max(1, (int) ($_GET['assignment_page'] ?? 1));
$assignmentPerPage = ui_pagination_resolve_per_page('assignment_per_page', 5);
$assignmentPerPageOptions = ui_pagination_per_page_options();
$assignmentTotal = count($assignments);
$assignmentTotalPages = max(1, (int) ceil($assignmentTotal / $assignmentPerPage));
if ($assignmentPage > $assignmentTotalPages) {
	$assignmentPage = $assignmentTotalPages;
}
$assignmentOffset = ($assignmentPage - 1) * $assignmentPerPage;
$pagedAssignments = array_slice($assignments, $assignmentOffset, $assignmentPerPage);

$averageAssignmentScore = !empty($assignmentScores) ? number_format(array_sum($assignmentScores) / count($assignmentScores), 1) : '--';

$examRows = $examsTable->listExamRowsByClassAndStudent($selectedClassId, $studentId);
$exams = [];
foreach ($examRows as $examRow) {
    $scoreValues = array_values(array_filter([
        $examRow['score_listening'] ?? null,
        $examRow['score_speaking'] ?? null,
        $examRow['score_reading'] ?? null,
        $examRow['score_writing'] ?? null,
    ], static fn($value): bool => $value !== null && $value !== ''));

    $overall = !empty($scoreValues) ? number_format(array_sum(array_map('floatval', $scoreValues)) / count($scoreValues), 1) : trim((string) ($examRow['result'] ?? ''));
    if ($overall === '') {
        $overall = '--';
    }

    $exams[] = [
        'name' => (string) ($examRow['exam_name'] ?? ''),
        'type' => (string) ($examRow['exam_type'] ?? ''),
        'date' => !empty($examRow['exam_date']) ? date('d/m/Y', strtotime((string) $examRow['exam_date'])) : 'Chưa diễn ra',
        'listening' => $examRow['score_listening'] !== null ? number_format((float) $examRow['score_listening'], 1) : '--',
        'reading' => $examRow['score_reading'] !== null ? number_format((float) $examRow['score_reading'], 1) : '--',
        'writing' => $examRow['score_writing'] !== null ? number_format((float) $examRow['score_writing'], 1) : '--',
        'speaking' => $examRow['score_speaking'] !== null ? number_format((float) $examRow['score_speaking'], 1) : '--',
        'overall' => $overall,
    ];
}

?>

<style>
    .glass-card { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(18px); border: 1px solid rgba(203, 213, 225, 0.95); box-shadow: 0 30px 70px rgba(71, 85, 105, 0.16); }
    .soft-card { background: rgba(255, 255, 255, 0.98); border: 1px solid rgba(203, 213, 225, 0.95); box-shadow: 0 22px 55px rgba(71, 85, 105, 0.14); }
    .table-modern th { background-color: #eef2f7; color: #334155; font-weight: 900; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.25rem; border-bottom: 2px solid #cbd5e1; }
    .table-modern td { padding: 1.25rem; border-bottom: 1px solid #dbe4ee; font-size: 0.875rem; color: #1e293b; font-weight: 600; vertical-align: middle; }
    .table-modern tbody tr { transition: all 0.2s; }
    .table-modern tbody tr:hover { background-color: #f1f5f9; transform: scale(1.001); }
</style>

<section class="min-h-screen bg-[radial-gradient(circle_at_top,_#ffffff_0%,_#f2f6fb_32%,_#e3ebf4_100%)] pb-20 font-jakarta relative overflow-hidden">
    
    <div class="absolute top-0 left-0 w-full h-[300px] overflow-hidden -z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-200 via-white to-sky-100"></div>
        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#f8fafc] to-transparent"></div>
        <div class="absolute -top-20 -right-20 w-[420px] h-[420px] bg-emerald-400/08 rounded-full blur-[90px]"></div>
        <div class="absolute top-10 -left-20 w-[360px] h-[360px] bg-rose-400/08 rounded-full blur-[90px]"></div>
        <div class="absolute left-1/2 top-8 h-[300px] w-[300px] -translate-x-1/2 rounded-full bg-amber-300/08 blur-[90px]"></div>
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: radial-gradient(#475569 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="relative z-10 pt-6 px-4 sm:px-8 max-w-7xl mx-auto flex justify-between items-center">
        <a class="group inline-flex items-center gap-2 rounded-full bg-white backdrop-blur-md border border-slate-300 px-5 py-2.5 text-xs font-bold text-slate-800 shadow-lg shadow-slate-300/60 transition-all hover:-translate-y-0.5 hover:bg-white hover:text-emerald-700" href="<?= e(page_url('classes-my')); ?>">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Quay lại danh sách lớp
        </a>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10 mt-6 space-y-6">
        
        <div class="glass-card rounded-[2rem] p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border border-slate-200" data-aos="fade-up">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-emerald-400 to-cyan-500 text-white flex items-center justify-center text-3xl shadow-xl shadow-emerald-200/60 shrink-0 ring-1 ring-white/70">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight"><?= e($classDetail['name']) ?></h1>
                        <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-300 shadow-sm flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> <?= e($classDetail['status']) ?>
                        </span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 flex items-center gap-4">
                        <span><i class="fa-regular fa-user mr-1 text-slate-400"></i> GV: <span class="text-slate-700"><?= e($classDetail['teacher']) ?></span></span>
                        <span class="hidden sm:inline text-slate-300">|</span>
                        <span><i class="fa-regular fa-clock mr-1 text-slate-400"></i> <?= e($classDetail['schedule']) ?></span>
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2.5 py-1 shadow-sm">
                            <i class="fa-regular fa-hourglass-half text-slate-400"></i>
                            <span>Thời gian học: <?= e($classDetail['start_date']) ?> - <?= e($classDetail['end_date']) ?></span>
                        </span>
                    </p>
                    <?php if (!empty($scheduleItems)): ?>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php foreach (array_slice($scheduleItems, 0, 3) as $scheduleItem): ?>
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-700 shadow-sm">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= e($scheduleItem['date'] . ' · ' . $scheduleItem['time'] . ' · ' . $scheduleItem['room']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="#class-assignments" class="group inline-flex items-center gap-3 rounded-[1.25rem] border border-rose-300 bg-white px-5 py-4 text-left text-slate-900 shadow-2xl shadow-rose-200/50 transition-all hover:-translate-y-1 hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2 focus:ring-offset-white">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg shadow-rose-200/70 ring-1 ring-white/90 transition group-hover:scale-105">
                    <i class="fa-solid fa-clipboard-list text-base"></i>
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block text-[10px] font-black uppercase tracking-[0.35em] text-rose-500">Bài tập của lớp</span>
                    <span class="mt-1 block text-sm font-black tracking-tight text-slate-900">Xem bài tập ngay</span>
                </span>
                <span class="ml-1 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 transition group-hover:bg-rose-200">
                    <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-0.5"></i>
                </span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch" data-aos="fade-up" data-aos-delay="100">
            <div class="relative h-full overflow-hidden rounded-[2rem] border border-emerald-200 bg-white p-6 shadow-2xl shadow-emerald-200/60 transition-all hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(34,197,94,0.24)]">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-emerald-400 to-cyan-400"></div>
                <div class="relative z-10 flex h-full min-h-[220px] flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tỷ lệ chuyên cần</p>
                        <h3 class="text-3xl font-black text-slate-800"><?= (int) $attendancePercent ?>%</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shadow-md shadow-emerald-300"><i class="fa-solid fa-user-check"></i></div>
                </div>
                <div class="flex gap-3 flex-1">
                    <div class="flex-1 bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-center shadow-sm">
                        <p class="text-xl font-black text-emerald-600"><?= (int) $attendancePresent ?></p>
                        <p class="text-[9px] font-bold text-emerald-700 uppercase">Có mặt</p>
                    </div>
                    <div class="flex-1 bg-rose-50 border border-rose-200 rounded-xl p-3 text-center shadow-sm">
                        <p class="text-xl font-black text-rose-600"><?= (int) $attendanceAbsent ?></p>
                        <p class="text-[9px] font-bold text-rose-700 uppercase">Vắng</p>
                    </div>
                </div>
                <p class="mt-3 text-[11px] font-semibold text-slate-500">Tổng buổi có dữ liệu: <?= (int) $attendanceTotal ?> buổi</p>
                </div>
            </div>

            <div class="relative h-full overflow-hidden rounded-[2rem] border border-amber-200 bg-white p-6 shadow-2xl shadow-amber-200/60 transition-all hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(245,158,11,0.24)]">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500"></div>
                <div class="relative z-10 flex h-full min-h-[220px] flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Học phí khóa học</p>
                        <h3 class="text-2xl font-black text-slate-800"><?= number_format($tuitionPaid) ?> <span class="text-sm text-slate-400">/ <?= number_format($tuitionTotal) ?> đ</span></h3>
                        <p class="mt-1 text-[11px] font-semibold text-slate-500">Còn lại <?= number_format($tuitionRemaining) ?> đ, tương đương <?= 100 - $tuitionPaidPercent ?>% chưa thanh toán.</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg shadow-md shadow-amber-300"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </div>

                <div class="space-y-4 flex-1">
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <span>Tiến độ thanh toán</span>
                            <span><?= $tuitionPaidPercent ?>%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-3 rounded-full bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500" style="width: <?= $tuitionPaidPercent; ?>%"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                            <span>Đã thanh toán: <?= number_format($tuitionPaid) ?> đ</span>
                            <span>Còn lại: <?= number_format($tuitionRemaining) ?> đ</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Hoạt động ngoại khoá</p>
                                <p class="mt-1 text-sm font-bold text-slate-700"><?= (int) $activityRegisteredCount ?> hoạt động đã đăng ký</p>
                                <p class="mt-1 text-[11px] text-slate-500">Tổng tiền đã thanh toán cho ngoại khoá.</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-slate-800"><?= number_format($activityFeePaid) ?> <span class="text-xs text-slate-400">/ <?= number_format($activityFeeTotal) ?> đ</span></p>
                                <p class="text-[11px] font-semibold text-slate-500">Còn lại <?= number_format($activityFeeRemaining) ?> đ</p>
                            </div>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-cyan-500" style="width: <?= $activityFeePercent; ?>%"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-[11px] font-semibold text-slate-500">
                            <span>Đã thanh toán: <?= $activityFeePercent ?>%</span>
                            <span>Chưa thanh toán: <?= 100 - $activityFeePercent ?>%</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <?php if ($tuitionStatus === 'Đang nợ'): ?>
                            <span class="bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-rose-200">Đang nợ phí</span>
                            <a href="#" class="text-xs font-bold text-rose-600 hover:underline">Thanh toán ngay <i class="fa-solid fa-arrow-right text-[10px]"></i></a>
                        <?php elseif ($tuitionStatus === 'Đã hoàn thành'): ?>
                            <span class="bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-emerald-200">Đã hoàn thành</span>
                        <?php else: ?>
                            <span class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border border-slate-200"><?= e($tuitionStatus); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            </div>

            <div class="relative h-full overflow-hidden rounded-[2rem] border border-blue-200 bg-white p-6 shadow-2xl shadow-blue-200/60 transition-all hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(59,130,246,0.24)] flex flex-col justify-between">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
                <div class="relative z-10 flex h-full min-h-[220px] flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Điểm TB Bài tập</p>
                        <h3 class="text-3xl font-black text-slate-800"><?= e($averageAssignmentScore); ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg shadow-md shadow-blue-300"><i class="fa-solid fa-ranking-star"></i></div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 mt-auto shadow-sm">
                    <?php if ($averageAssignmentScore !== '--'): ?>
                        <p class="text-xs font-medium text-slate-600">Đánh giá: <span class="font-black text-emerald-600">Dựa trên bài đã chấm</span></p>
                        <p class="text-[10px] text-slate-400 mt-1">Điểm trung bình được tính từ các bài đã nộp và được chấm.</p>
                    <?php else: ?>
                        <p class="text-xs font-medium text-slate-600">Chưa có bài đã chấm</p>
                        <p class="text-[10px] text-slate-400 mt-1">Hãy nộp bài để hệ thống hiển thị điểm trung bình.</p>
                    <?php endif; ?>
                </div>
                </div>
            </div>
        </div>

        <div id="class-assignments" data-assignment-panel="1" class="scroll-mt-24 md:scroll-mt-8 rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-300/60 overflow-visible transition-all duration-300">
            <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-laptop-file"></i></div>
                    Danh sách Bài tập
                </h2>
                <?php $defaultHomeworkCanOpen = !empty($defaultHomework) && (!empty($defaultHomework['can_submit']) || !empty($defaultHomework['can_resubmit'])); ?>
                <span class="inline-flex group relative" <?= !empty($defaultHomework['disabled_reason']) ? 'title="' . e((string) $defaultHomework['disabled_reason']) . '"' : ''; ?>>
                    <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 via-sky-600 to-cyan-600 px-5 py-2.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:shadow-blue-500/40 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none" data-homework-open="1" data-homework-class="<?= e($classDetail['name']); ?>" data-homework-assignment-id="<?= (int) ($defaultHomework['id'] ?? 0); ?>" data-homework-assignment="<?= e((string) ($defaultHomework['title'] ?? '')); ?>" data-homework-deadline="<?= e((string) ($defaultHomework['deadline_raw'] ?? '')); ?>" data-homework-note="<?= e((string) ($defaultHomework['note'] ?? '')); ?>" data-homework-status="<?= e((string) ($defaultHomework['status'] ?? '')); ?>" data-homework-empty="<?= empty($assignments) ? '1' : '0'; ?>" <?= $defaultHomeworkCanOpen ? '' : 'disabled'; ?>>
                        <i class="fa-solid fa-plus"></i> <?= !empty($defaultHomework['can_resubmit']) ? 'Nộp lại' : 'Nộp bài mới'; ?>
                    </button>
                    <?php if (!$defaultHomeworkCanOpen && !empty($defaultHomework['disabled_reason'])): ?>
                        <span class="pointer-events-none absolute left-1/2 top-full z-[9999] mt-2 w-max max-w-[260px] -translate-x-1/2 rounded-xl bg-slate-900 px-3 py-2 text-[11px] font-semibold leading-tight text-white opacity-0 shadow-2xl transition group-hover:opacity-100">
                            <?= e((string) $defaultHomework['disabled_reason']); ?>
                        </span>
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="max-h-[70vh] md:max-h-[640px] overflow-y-auto overflow-x-hidden">
                <div class="space-y-3 p-3 md:hidden">
                    <?php if (empty($pagedAssignments)): ?>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm font-semibold text-slate-500">
                            Chưa có bài tập nào được giao cho lớp này.
                        </div>
                    <?php else: ?>
                        <?php foreach ($pagedAssignments as $hw): ?>
                            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm font-black leading-snug text-slate-800 break-words"><?= e($hw['title']) ?></h3>
                                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-500">
                                                <i class="fa-regular fa-clock"></i> <?= e($hw['deadline']) ?>
                                            </span>
                                            <?php if (!empty($hw['submitted_at'])): ?>
                                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                                    <i class="fa-regular fa-paper-plane"></i> <?= e($hw['submitted_at']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-slate-400">Chưa nộp</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php
                                        $badgeClass = match($hw['color']) {
                                            'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                            'rose' => 'bg-rose-50 text-rose-600 border-rose-200',
                                            'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
                                            default => 'bg-slate-50 text-slate-500 border-slate-200'
                                        };
                                    ?>
                                    <span class="shrink-0 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wider <?= $badgeClass ?>">
                                        <?= e($hw['status']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($hw['file_url'])): ?>
                                    <a href="<?= e(normalize_public_file_url((string) $hw['file_url'])); ?>" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100">
                                        <i class="fa-solid fa-paperclip"></i> Tải file đề
                                    </a>
                                <?php endif; ?>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Điểm số</p>
                                        <p class="text-sm font-black <?= $hw['score'] !== '--' ? ((float) $hw['score'] >= 5.0 ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-300'; ?>"><?= $hw['score'] !== '--' ? e($hw['score']) : '--'; ?></p>
                                    </div>
                                    <?php if (!empty($hw['can_submit']) || !empty($hw['can_resubmit'])): ?>
                                        <?php
                                            $actionClass = !empty($hw['can_resubmit'])
                                                ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35'
                                                : 'bg-gradient-to-r from-blue-600 via-sky-600 to-cyan-600 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35';
                                        ?>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-xl border border-transparent px-3 py-2 text-[11px] font-black uppercase tracking-widest transition-all active:scale-[0.98] <?= $actionClass ?>"
                                            data-homework-open="1"
                                            data-homework-assignment-id="<?= (int) ($hw['id'] ?? 0); ?>"
                                            data-homework-class="<?= e($classDetail['name']); ?>"
                                            data-homework-assignment="<?= e($hw['title']); ?>"
                                            data-homework-deadline="<?= e((string) ($hw['deadline_raw'] ?? '')); ?>"
                                            data-homework-note="<?= e((string) ($hw['note'] ?? '')); ?>"
                                            data-homework-status="<?= e((string) ($hw['status'] ?? '')); ?>"
                                            data-homework-empty="0"
                                        >
                                            <?= !empty($hw['can_resubmit']) ? 'Nộp lại' : 'Nộp bài'; ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="inline-flex max-w-[120px] items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400 text-center leading-tight">
                                            Hết hạn
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($hw['teacher_comment'])): ?>
                                    <p class="mt-3 rounded-xl bg-slate-50 px-3 py-2 text-[11px] leading-relaxed text-slate-500">
                                        <?= e($hw['teacher_comment']) ?>
                                    </p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <table class="hidden w-full table-modern table-fixed text-left md:table">
                    <thead>
                        <tr>
                            <th class="w-[31%]">Tên bài tập</th>
                            <th class="w-[17%]">Hạn nộp (Deadline)</th>
                            <th class="w-[15%]">Nộp lúc</th>
                            <th class="w-[14%]">Trạng thái</th>
                            <th class="w-[10%]">Điểm số</th>
                            <th class="w-[13%] text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagedAssignments)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm font-semibold text-slate-500">Chưa có bài tập nào được giao cho lớp này.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($pagedAssignments as $hw): ?>
                        <tr>
                            <td class="align-top">
                                <div class="whitespace-normal break-words text-sm font-black leading-snug text-slate-800"><?= e($hw['title']) ?></div>
                                <?php if (!empty($hw['file_url'])): ?>
                                    <a
                                        href="<?= e(normalize_public_file_url((string) $hw['file_url'])); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-1 inline-flex items-center gap-1 rounded-full border border-blue-100 bg-blue-50 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-blue-700 transition hover:bg-blue-100"
                                    >
                                        <i class="fa-solid fa-paperclip"></i> Tải file đề
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="align-top">
                                <span class="text-slate-500 font-bold bg-slate-50 px-2 py-1 rounded-md border border-slate-200 text-[11px]">
                                    <i class="fa-regular fa-clock"></i> <?= $hw['deadline'] ?>
                                </span>
                            </td>
                            <td class="align-top">
                                <?php if (!empty($hw['submitted_at'])): ?>
                                    <span class="text-slate-600 font-bold bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200 text-[11px]">
                                        <i class="fa-regular fa-paper-plane"></i> <?= e($hw['submitted_at']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 font-bold bg-slate-50 px-2 py-1 rounded-md border border-slate-200 text-[11px]">Chưa nộp</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-top">
                                <?php
                                    $badgeClass = match($hw['color']) {
                                        'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                        'rose' => 'bg-rose-50 text-rose-600 border-rose-200',
                                        'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
                                        default => 'bg-slate-50 text-slate-500 border-slate-200'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $badgeClass ?>">
                                    <?= $hw['status'] ?>
                                </span>
                            </td>
                            <td class="align-top">
                                <?php if($hw['score'] !== '--'): ?>
                                    <div class="space-y-1">
                                        <span class="font-black text-lg <?= (float) $hw['score'] >= 5.0 ? 'text-emerald-600' : 'text-rose-600' ?>"><?= e($hw['score']) ?></span>
                                        <?php if (!empty($hw['teacher_comment'])): ?>
                                            <p class="whitespace-normal break-words text-[11px] leading-relaxed text-slate-500">
                                                <?= e($hw['teacher_comment']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 font-black">--</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-top text-right">
                                <?php if (!empty($hw['can_submit']) || !empty($hw['can_resubmit'])): ?>
                                    <?php
                                        $actionClass = !empty($hw['can_resubmit'])
                                            ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35'
                                            : 'bg-gradient-to-r from-blue-600 via-sky-600 to-cyan-600 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/35';
                                    ?>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-transparent px-3 py-2 text-[11px] font-black uppercase tracking-widest transition-all hover:-translate-y-0.5 <?= $actionClass ?>"
                                        data-homework-open="1"
                                        data-homework-assignment-id="<?= (int) ($hw['id'] ?? 0); ?>"
                                        data-homework-class="<?= e($classDetail['name']); ?>"
                                        data-homework-assignment="<?= e($hw['title']); ?>"
                                        data-homework-deadline="<?= e((string) ($hw['deadline_raw'] ?? '')); ?>"
                                        data-homework-note="<?= e((string) ($hw['note'] ?? '')); ?>"
                                        data-homework-status="<?= e((string) ($hw['status'] ?? '')); ?>"
                                        data-homework-empty="0"
                                    >
                                        <?= !empty($hw['can_resubmit']) ? 'Nộp lại' : 'Nộp bài'; ?>
                                    </button>
                                <?php else: ?>
                                    <span class="group relative inline-flex max-w-[110px] items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400 whitespace-normal text-center leading-tight cursor-not-allowed" <?= !empty($hw['disabled_reason']) ? 'title="' . e((string) $hw['disabled_reason']) . '"' : 'title="Đã quá hạn nộp bài"'; ?>>
                                        Hết hạn
                                        <span class="pointer-events-none absolute left-1/2 top-full z-[9999] mt-2 w-max max-w-[240px] -translate-x-1/2 rounded-xl bg-slate-900 px-3 py-2 text-[11px] font-semibold leading-tight text-white opacity-0 shadow-2xl transition group-hover:opacity-100">
                                            <?= e((string) ($hw['disabled_reason'] ?: 'Đã quá hạn nộp bài')); ?>
                                        </span>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($assignmentTotalPages > 1): ?>
                <div data-assignment-pagination="1" class="border-t border-slate-200 bg-slate-50/80 px-4 py-3 sm:px-6">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="text-xs font-semibold text-slate-500">
                            Trang <?= (int) $assignmentPage; ?>/<?= (int) $assignmentTotalPages; ?> · Tổng <?= (int) $assignmentTotal; ?> bài tập
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end sm:gap-4">
                            <form class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2" method="get" action="<?= e(page_url('classes-my-details', ['class_id' => $selectedClassId])); ?>" data-assignment-per-page-form="1">
                                <input type="hidden" name="class_id" value="<?= (int) $selectedClassId; ?>">
                                <input type="hidden" name="assignment_page" value="1">
                                <label for="assignment-per-page" class="text-xs font-semibold text-slate-500">Số dòng</label>
                                <select id="assignment-per-page" name="assignment_per_page" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 shadow-sm sm:w-auto" data-assignment-per-page="1">
                                    <?php foreach ($assignmentPerPageOptions as $option): ?>
                                        <option value="<?= (int) $option; ?>" <?= $assignmentPerPage === (int) $option ? 'selected' : ''; ?>><?= (int) $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <div class="flex flex-wrap items-center justify-center gap-2 text-xs font-semibold text-slate-600">
                                <?php if ($assignmentPage > 1): ?>
                                    <a class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 sm:h-8" href="<?= e(page_url('classes-my-details', ['class_id' => $selectedClassId, 'assignment_page' => $assignmentPage - 1, 'assignment_per_page' => $assignmentPerPage])); ?>" data-assignment-page-link="1">Trước</a>
                                <?php else: ?>
                                    <span class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400 sm:h-8">Trước</span>
                                <?php endif; ?>

                                <span class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 sm:h-8">
                                    Trang <?= (int) $assignmentPage; ?>/<?= (int) $assignmentTotalPages; ?>
                                </span>

                                <?php if ($assignmentPage < $assignmentTotalPages): ?>
                                    <a class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 sm:h-8" href="<?= e(page_url('classes-my-details', ['class_id' => $selectedClassId, 'assignment_page' => $assignmentPage + 1, 'assignment_per_page' => $assignmentPerPage])); ?>" data-assignment-page-link="1">Sau</a>
                                <?php else: ?>
                                    <span class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-slate-100 px-3 text-xs font-semibold text-slate-400 sm:h-8">Sau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white shadow-2xl shadow-slate-300/60 overflow-hidden">
            <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm"><i class="fa-solid fa-file-contract"></i></div>
                <h2 class="text-lg font-black text-slate-800">Kết quả Kiểm tra Định kỳ</h2>
            </div>
            
            <div class="overflow-visible">
                <table class="w-full table-modern table-fixed text-left">
                    <thead>
                        <tr>
                            <th class="w-[28%]">Kỳ thi</th>
                            <th class="w-[14%]">Ngày thi</th>
                            <th class="w-[9%] text-center" title="Listening">Nghe</th>
                            <th class="w-[9%] text-center" title="Reading">Đọc</th>
                            <th class="w-[9%] text-center" title="Writing">Viết</th>
                            <th class="w-[9%] text-center" title="Speaking">Nói</th>
                            <th class="w-[12%] text-center border-l border-slate-200">Overall</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm font-semibold text-slate-500">Chưa có kết quả kiểm tra nào cho lớp này.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($exams as $ex): ?>
                        <tr>
                            <td class="align-top">
                                <div class="whitespace-normal break-words font-black text-slate-800 text-sm"><?= e($ex['name']) ?></div>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?= e($ex['type'] === 'entry' ? 'Đầu vào' : ($ex['type'] === 'periodic' ? 'Định kỳ' : ($ex['type'] === 'final' ? 'Cuối kỳ' : $ex['type']))); ?></p>
                            </td>
                            <td class="align-top">
                                <span class="text-slate-500 font-bold"><?= $ex['date'] ?></span>
                            </td>
                            <td class="align-top text-center font-bold text-slate-600"><?= $ex['listening'] ?></td>
                            <td class="align-top text-center font-bold text-slate-600"><?= $ex['reading'] ?></td>
                            <td class="align-top text-center font-bold text-slate-600"><?= $ex['writing'] ?></td>
                            <td class="align-top text-center font-bold text-slate-600"><?= $ex['speaking'] ?></td>
                            <td class="align-top text-center border-l border-slate-100">
                                <?php if($ex['overall'] !== '--'): ?>
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 font-black text-base shadow-sm">
                                        <?= e($ex['overall']) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 font-black">--</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="homework-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm">
            <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.3em] text-blue-400">Nộp bài tập</p>
                        <h3 class="mt-2 text-2xl font-black text-slate-800">Tải file bài làm lên hệ thống</h3>
                    </div>
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700" data-homework-close="1" aria-label="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form class="space-y-5 px-6 py-6" method="post" action="<?= e('/api/index.php?resource=assignments&method=submit'); ?>" enctype="multipart/form-data">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="redirect_to" value="<?= e(page_url('classes-my', ['class_id' => (int) $selectedClassId])); ?>">
                    <input type="hidden" name="assignment_id" id="homework-assignment-id" value="">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Lớp học</label>
                            <input type="hidden" name="class_name" id="homework-class-name" value="">
                            <input type="text" id="homework-class-display" value="" placeholder="Lớp học được chọn" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">Deadline</label>
                            <input type="hidden" name="assignment_deadline" id="homework-deadline" value="">
                            <input type="text" id="homework-deadline-display" placeholder="Ví dụ: 20/05/2026" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tên bài tập</label>
                        <input type="hidden" name="assignment_title" id="homework-assignment-title" value="">
                        <input type="text" id="homework-assignment-title-display" placeholder="Nhập tên bài tập" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Ghi chú</label>
                        <input type="hidden" name="note" id="homework-note" value="">
                        <textarea id="homework-note-display" rows="4" placeholder="Ghi chú thêm cho giáo viên nếu cần" disabled class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none transition cursor-not-allowed"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">File bài làm</label>
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-5 transition hover:border-blue-300 hover:bg-blue-50/40">
                            <input type="file" name="submission_file" id="homework-file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-blue-600">
                            <p class="mt-3 text-xs text-slate-500">Chấp nhận: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG.</p>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between">
                        <a href="<?= e(page_url('classes-my', ['class_id' => (int) $selectedClassId])); ?>" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            <i class="fa-solid fa-arrow-right mr-2 text-[11px]"></i> Xem lớp học này
                        </a>
                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50" data-homework-close="1">Đóng</button>
                        <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Nộp bài</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('homework-modal');
                let assignmentPanel = document.getElementById('class-assignments');
                if (!modal) return;

                const classInput = document.getElementById('homework-class-name');
                const classDisplay = document.getElementById('homework-class-display');
                const assignmentIdInput = document.getElementById('homework-assignment-id');
                const assignmentInput = document.getElementById('homework-assignment-title');
                const assignmentDisplay = document.getElementById('homework-assignment-title-display');
                const deadlineInput = document.getElementById('homework-deadline');
                const deadlineDisplay = document.getElementById('homework-deadline-display');
                const noteInput = document.getElementById('homework-note');
                const noteDisplay = document.getElementById('homework-note-display');
                const fileInput = document.getElementById('homework-file');
                const homeworkForm = modal.querySelector('form');
                const submitButton = homeworkForm ? homeworkForm.querySelector('button[type="submit"]') : null;

                function notify(type, message) {
                    if (typeof showNotify === 'function') {
                        showNotify(type, message);
                    }
                }

                let assignmentRequestId = 0;
                let assignmentRequestController = null;

                function openModal(button) {
                    if (classInput && button.dataset.homeworkClass) {
                        classInput.value = button.dataset.homeworkClass;
                    }
                    if (classDisplay) {
                        classDisplay.value = button.dataset.homeworkClass || '';
                    }
                    if (assignmentInput && button.dataset.homeworkAssignment) {
                        assignmentInput.value = button.dataset.homeworkAssignment;
                    }
                    if (assignmentIdInput) {
                        assignmentIdInput.value = button.dataset.homeworkAssignmentId || '';
                    }
                    if (assignmentDisplay) {
                        assignmentDisplay.value = button.dataset.homeworkAssignment || '';
                    }
                    if (deadlineInput && button.dataset.homeworkDeadline !== undefined) {
                        deadlineInput.value = button.dataset.homeworkDeadline || '';
                    }
                    if (deadlineDisplay) {
                        deadlineDisplay.value = button.dataset.homeworkDeadline || '';
                    }
                    if (noteInput) {
                        noteInput.value = button.dataset.homeworkNote || '';
                    }
                    if (noteDisplay) {
                        noteDisplay.value = button.dataset.homeworkNote || '';
                    }
                    if (fileInput) {
                        fileInput.value = '';
                    }
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }

                async function submitHomework(event) {
                    event.preventDefault();

                    if (!(homeworkForm instanceof HTMLFormElement)) {
                        return;
                    }

                    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                        notify('warning', 'Vui lòng tải lên file bài làm.');
                        return;
                    }

                    if (submitButton instanceof HTMLButtonElement) {
                        submitButton.disabled = true;
                        submitButton.dataset.originalText = submitButton.textContent || '';
                        submitButton.textContent = 'Đang nộp...';
                    }

                    try {
                        const response = await fetch(homeworkForm.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: new FormData(homeworkForm)
                        });

                        const payload = await response.json().catch(function () {
                            return null;
                        });

                        if (!response.ok || !payload || payload.status !== 'success') {
                            throw new Error((payload && payload.message) || 'Nộp bài thất bại. Vui lòng thử lại.');
                        }

                        closeModal();
                        notify('success', payload.message || 'Đã nộp bài thành công.');
                    } catch (error) {
                        notify('error', error instanceof Error ? error.message : 'Nộp bài thất bại. Vui lòng thử lại.');
                    } finally {
                        if (submitButton instanceof HTMLButtonElement) {
                            submitButton.disabled = false;
                            submitButton.textContent = submitButton.dataset.originalText || 'Nộp bài';
                            delete submitButton.dataset.originalText;
                        }
                    }
                }

                function setAssignmentPanelLoading(isLoading) {
                    if (!assignmentPanel) {
                        return;
                    }

                    assignmentPanel.classList.toggle('opacity-60', isLoading);
                    assignmentPanel.classList.toggle('translate-y-1', isLoading);
                    assignmentPanel.classList.toggle('pointer-events-none', isLoading);
                }

                function getAssignmentPageUrl(urlLike) {
                    return new URL(urlLike, window.location.href);
                }

                async function loadAssignmentPanel(url, pushState) {
                    if (!assignmentPanel) {
                        window.location.href = url.toString();
                        return;
                    }

                    const requestId = ++assignmentRequestId;
                    if (assignmentRequestController instanceof AbortController) {
                        assignmentRequestController.abort();
                    }

                    assignmentRequestController = new AbortController();
                    setAssignmentPanelLoading(true);

                    try {
                        const response = await fetch(url.toString(), {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: assignmentRequestController.signal
                        });

                        if (!response.ok) {
                            throw new Error('Không thể tải danh sách bài tập.');
                        }

                        const html = await response.text();
                        if (requestId !== assignmentRequestId) {
                            return;
                        }

                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const incomingPanel = doc.getElementById('class-assignments');

                        if (!(incomingPanel instanceof HTMLElement)) {
                            throw new Error('Không tìm thấy danh sách bài tập mới.');
                        }

                        incomingPanel.classList.add('opacity-0', 'translate-y-3');
                        const previousPanel = assignmentPanel;
                        previousPanel.replaceWith(incomingPanel);
                        assignmentPanel = incomingPanel;

                        window.requestAnimationFrame(function () {
                            incomingPanel.classList.remove('opacity-0', 'translate-y-3');
                        });

                        incomingPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });

                        if (pushState) {
                            window.history.pushState({ assignmentPanel: true }, '', url.toString());
                        } else {
                            window.history.replaceState({ assignmentPanel: true }, '', url.toString());
                        }
                    } catch (error) {
                        if (error instanceof DOMException && error.name === 'AbortError') {
                            return;
                        }

                        window.location.href = url.toString();
                    } finally {
                        if (requestId === assignmentRequestId) {
                            setAssignmentPanelLoading(false);
                        }
                    }
                }

                document.addEventListener('click', function (event) {
                    if (!(event.target instanceof Element)) {
                        return;
                    }

                    const openButton = event.target.closest('[data-homework-open="1"]');
                    if (openButton instanceof HTMLElement) {
                        if (openButton.dataset.homeworkEmpty === '1') {
                            if (typeof showNotify === 'function') {
                                showNotify('info', 'Chưa có bài tập được giao');
                            }
                            return;
                        }

                        openModal(openButton);
                        return;
                    }

                    const closeButton = event.target.closest('[data-homework-close="1"]');
                    if (closeButton instanceof HTMLElement) {
                        closeModal();
                        return;
                    }

                    const paginationLink = event.target.closest('[data-assignment-page-link="1"]');
                    if (paginationLink instanceof HTMLAnchorElement) {
                        event.preventDefault();
                        const url = getAssignmentPageUrl(paginationLink.href);
                        loadAssignmentPanel(url, true);
                    }
                });

                document.addEventListener('change', function (event) {
                    if (!(event.target instanceof Element)) {
                        return;
                    }

                    const perPageSelect = event.target.closest('[data-assignment-per-page="1"]');
                    if (!(perPageSelect instanceof HTMLSelectElement)) {
                        return;
                    }

                    const form = perPageSelect.closest('[data-assignment-per-page-form="1"]');
                    if (!(form instanceof HTMLFormElement)) {
                        return;
                    }

                    const url = new URL(form.action, window.location.href);
                    const formData = new FormData(form);
                    formData.set('assignment_per_page', perPageSelect.value);
                    formData.set('assignment_page', '1');

                    formData.forEach(function (value, key) {
                        url.searchParams.set(key, String(value));
                    });

                    loadAssignmentPanel(url, true);
                });

                if (homeworkForm instanceof HTMLFormElement) {
                    homeworkForm.addEventListener('submit', submitHomework);
                }

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });

                if (window.location.search.includes('assignment_page=')) {
                    const anchor = document.getElementById('class-assignments');
                    if (anchor) {
                        window.requestAnimationFrame(function () {
                            anchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                }

                window.addEventListener('popstate', function () {
                    loadAssignmentPanel(new URL(window.location.href), false);
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>

    </div>
</section>
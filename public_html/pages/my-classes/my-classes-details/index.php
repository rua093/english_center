<?php
declare(strict_types=1);

require_login();

require_once __DIR__ . '/../../../models/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/../../../models/tables/AttendanceTableModel.php';
require_once __DIR__ . '/../../../models/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/../../../models/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/../../../models/tables/ExamsTableModel.php';

$classStudentsTable = new ClassStudentsTableModel();
$attendanceTable = new AttendanceTableModel();
$tuitionFeesTable = new TuitionFeesTableModel();
$assignmentsTable = new AssignmentsTableModel();
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
$tuitionStatus = $tuitionRow === [] ? 'Chưa có học phí' : (((string) ($tuitionRow['status'] ?? 'debt')) === 'paid' ? 'Đã hoàn thành' : 'Đang nợ');

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
    $status = 'Chưa nộp';
    $color = 'slate';

    if ($submittedAt !== '') {
        if ($score !== null) {
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
        'can_resubmit' => $submittedAt !== '' && !$isExpired,
        'can_submit' => $submittedAt === '' && !$isExpired,
        'is_expired' => $isExpired,
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
    .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); }
    .table-modern th { background-color: #f8fafc; color: #64748b; font-weight: 900; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.25rem; border-bottom: 2px solid #f1f5f9; }
    .table-modern td { padding: 1.25rem; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; color: #334155; font-weight: 600; vertical-align: middle; }
    .table-modern tbody tr { transition: all 0.2s; }
    .table-modern tbody tr:hover { background-color: #f8fafc; transform: scale(1.001); }
</style>

<section class="min-h-screen bg-[#f8fafc] pb-20 font-jakarta relative overflow-hidden">
    
    <div class="absolute top-0 left-0 w-full h-[280px] overflow-hidden -z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-emerald-900 to-rose-900"></div>
        <div class="absolute -top-20 -right-20 w-[400px] h-[400px] bg-emerald-500/20 rounded-full blur-[80px] animate-pulse"></div>
        <div class="absolute top-10 -left-20 w-[350px] h-[350px] bg-rose-500/20 rounded-full blur-[80px] animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute inset-0 opacity-[0.1]" style="background-image: radial-gradient(#ffffff 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="relative z-10 pt-6 px-4 sm:px-8 max-w-7xl mx-auto flex justify-between items-center">
        <a class="group inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-white hover:text-emerald-700" href="<?= e(page_url('classes-my')); ?>">
            <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i> Quay lại danh sách lớp
        </a>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 relative z-10 mt-6 space-y-6">
        
        <div class="glass-card rounded-[2rem] p-6 md:p-8 shadow-2xl shadow-slate-200/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-6" data-aos="fade-up">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-[1.25rem] bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-3xl shadow-lg shadow-emerald-500/30 shrink-0">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight"><?= e($classDetail['name']) ?></h1>
                        <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-200 shadow-sm flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> <?= e($classDetail['status']) ?>
                        </span>
                    </div>
                    <p class="text-sm font-bold text-slate-500 flex items-center gap-4">
                        <span><i class="fa-regular fa-user mr-1 text-slate-400"></i> GV: <span class="text-slate-700"><?= e($classDetail['teacher']) ?></span></span>
                        <span class="hidden sm:inline text-slate-300">|</span>
                        <span><i class="fa-regular fa-clock mr-1 text-slate-400"></i> <?= e($classDetail['schedule']) ?></span>
                    </p>
                    <?php if (!empty($scheduleItems)): ?>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php foreach (array_slice($scheduleItems, 0, 3) as $scheduleItem): ?>
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-semibold text-slate-600">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= e($scheduleItem['date'] . ' · ' . $scheduleItem['time'] . ' · ' . $scheduleItem['room']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="#" class="bg-rose-600 hover:bg-rose-700 text-white font-black px-6 py-3.5 rounded-xl shadow-lg shadow-rose-600/20 transition-all hover:-translate-y-1 text-xs uppercase tracking-widest flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-video"></i> Vào lớp Online
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up" data-aos-delay="100">
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tỷ lệ chuyên cần</p>
                        <h3 class="text-3xl font-black text-slate-800"><?= (int) $attendancePercent ?>%</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg"><i class="fa-solid fa-user-check"></i></div>
                </div>
                <div class="flex gap-3">
                    <div class="flex-1 bg-emerald-50 border border-emerald-100 rounded-xl p-3 text-center">
                        <p class="text-xl font-black text-emerald-600"><?= (int) $attendancePresent ?></p>
                        <p class="text-[9px] font-bold text-emerald-700 uppercase">Có mặt</p>
                    </div>
                    <div class="flex-1 bg-rose-50 border border-rose-100 rounded-xl p-3 text-center">
                        <p class="text-xl font-black text-rose-600"><?= (int) $attendanceAbsent ?></p>
                        <p class="text-[9px] font-bold text-rose-700 uppercase">Vắng</p>
                    </div>
                </div>
                <p class="mt-3 text-[11px] font-semibold text-slate-500">Tổng buổi có dữ liệu: <?= (int) $attendanceTotal ?> buổi</p>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Học phí môn này</p>
                        <h3 class="text-2xl font-black text-slate-800"><?= number_format($tuitionPaid) ?> <span class="text-sm text-slate-400">/ <?= number_format($tuitionTotal) ?> đ</span></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </div>
                
                <div class="mt-6 flex items-center justify-between">
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

            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all flex flex-col justify-between">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Điểm TB Bài tập</p>
                        <h3 class="text-3xl font-black text-slate-800"><?= e($averageAssignmentScore); ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg"><i class="fa-solid fa-ranking-star"></i></div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 mt-auto">
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

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
            <div class="p-6 md:p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-lg font-black text-slate-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-laptop-file"></i></div>
                    Danh sách Bài tập
                </h2>
                <?php $defaultHomeworkCanOpen = !empty($defaultHomework) && (!empty($defaultHomework['can_submit']) || !empty($defaultHomework['can_resubmit'])); ?>
                <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 via-sky-600 to-cyan-600 px-5 py-2.5 text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-blue-500/25 transition-all hover:-translate-y-0.5 hover:shadow-blue-500/40 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none" data-homework-open="1" data-homework-class="<?= e($classDetail['name']); ?>" data-homework-assignment-id="<?= (int) ($defaultHomework['id'] ?? 0); ?>" data-homework-assignment="<?= e((string) ($defaultHomework['title'] ?? '')); ?>" data-homework-deadline="<?= e((string) ($defaultHomework['deadline_raw'] ?? '')); ?>" data-homework-note="<?= e((string) ($defaultHomework['note'] ?? '')); ?>" data-homework-status="<?= e((string) ($defaultHomework['status'] ?? '')); ?>" data-homework-empty="<?= empty($assignments) ? '1' : '0'; ?>" <?= $defaultHomeworkCanOpen ? '' : 'disabled'; ?>>
                    <i class="fa-solid fa-plus"></i> <?= !empty($defaultHomework['can_resubmit']) ? 'Nộp lại' : 'Nộp bài mới'; ?>
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full table-modern text-left">
                    <thead>
                        <tr>
                            <th>Tên bài tập</th>
                            <th>Hạn nộp (Deadline)</th>
                            <th>Nộp lúc</th>
                            <th>Trạng thái</th>
                            <th>Điểm số</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assignments)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm font-semibold text-slate-500">Chưa có bài tập nào được giao cho lớp này.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($assignments as $hw): ?>
                        <tr>
                            <td>
                                <div class="font-black text-slate-800 text-sm"><?= e($hw['title']) ?></div>
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
                            <td>
                                <span class="text-slate-500 font-bold bg-slate-50 px-2 py-1 rounded-md border border-slate-200 text-[11px]">
                                    <i class="fa-regular fa-clock"></i> <?= $hw['deadline'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($hw['submitted_at'])): ?>
                                    <span class="text-slate-600 font-bold bg-emerald-50 px-2 py-1 rounded-md border border-emerald-200 text-[11px]">
                                        <i class="fa-regular fa-paper-plane"></i> <?= e($hw['submitted_at']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-300 font-bold bg-slate-50 px-2 py-1 rounded-md border border-slate-200 text-[11px]">Chưa nộp</span>
                                <?php endif; ?>
                            </td>
                            <td>
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
                            <td>
                                <?php if($hw['score'] !== '--'): ?>
                                    <div class="space-y-1">
                                        <span class="font-black text-lg <?= (float) $hw['score'] >= 5.0 ? 'text-emerald-600' : 'text-rose-600' ?>"><?= e($hw['score']) ?></span>
                                        <?php if (!empty($hw['teacher_comment'])): ?>
                                            <p class="max-w-[260px] text-[11px] leading-relaxed text-slate-500">
                                                <?= e($hw['teacher_comment']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-slate-300 font-black">--</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
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
                                    <span class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-black uppercase tracking-widest text-slate-400" title="Đã quá hạn nộp bài">
                                        Hết hạn
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden" data-aos="fade-up" data-aos-delay="300">
            <div class="p-6 md:p-8 border-b border-slate-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm"><i class="fa-solid fa-file-contract"></i></div>
                <h2 class="text-lg font-black text-slate-800">Kết quả Kiểm tra Định kỳ</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full table-modern text-left">
                    <thead>
                        <tr>
                            <th>Kỳ thi</th>
                            <th>Ngày thi</th>
                            <th class="text-center" title="Listening">Nghe</th>
                            <th class="text-center" title="Reading">Đọc</th>
                            <th class="text-center" title="Writing">Viết</th>
                            <th class="text-center" title="Speaking">Nói</th>
                            <th class="text-center border-l border-slate-200">Overall</th>
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
                            <td>
                                <div class="font-black text-slate-800 text-sm"><?= e($ex['name']) ?></div>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?= e($ex['type'] === 'entry' ? 'Đầu vào' : ($ex['type'] === 'periodic' ? 'Định kỳ' : ($ex['type'] === 'final' ? 'Cuối kỳ' : $ex['type']))); ?></p>
                            </td>
                            <td>
                                <span class="text-slate-500 font-bold"><?= $ex['date'] ?></span>
                            </td>
                            <td class="text-center font-bold text-slate-600"><?= $ex['listening'] ?></td>
                            <td class="text-center font-bold text-slate-600"><?= $ex['reading'] ?></td>
                            <td class="text-center font-bold text-slate-600"><?= $ex['writing'] ?></td>
                            <td class="text-center font-bold text-slate-600"><?= $ex['speaking'] ?></td>
                            <td class="text-center border-l border-slate-100">
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

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                        <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50" data-homework-close="1">Đóng</button>
                        <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Nộp bài</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('homework-modal');
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

                document.querySelectorAll('[data-homework-open="1"]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        if (button.dataset.homeworkEmpty === '1') {
                            if (typeof showNotify === 'function') {
                                showNotify('info', 'Chưa có bài tập được giao');
                            }
                            return;
                        }
                        openModal(button);
                    });
                });

                document.querySelectorAll('[data-homework-close="1"]').forEach(function (button) {
                    button.addEventListener('click', closeModal);
                });

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
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
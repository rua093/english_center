<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/api_helpers.php';
require_once __DIR__ . '/../../models/AcademicModel.php';

function lessons_manage_redirect_query(array $source): array
{
    $query = [];

    $courseId = input_int($source, 'course_id');
    if ($courseId > 0) {
        $query['course_id'] = $courseId;
    }

    $classId = input_int($source, 'class_id');
    if ($classId > 0) {
        $query['class_id'] = $classId;
    }

    $scheduleId = input_int($source, 'schedule_id');
    if ($scheduleId <= 0) {
        $scheduleId = input_int($source, 'attendance_schedule_id');
    }
    if ($scheduleId > 0) {
        $query['schedule_id'] = $scheduleId;
    }

    $focusScheduleId = input_int($source, 'focus_schedule_id');
    if ($focusScheduleId > 0) {
        $query['focus_schedule_id'] = $focusScheduleId;
    }

    $weekStart = input_string($source, 'week_start');
    if ($weekStart !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart) === 1) {
        $query['week_start'] = $weekStart;
    }

    $weekRef = input_string($source, 'week_ref');
    if ($weekRef !== '' && preg_match('/^\d{4}-W\d{2}$/', $weekRef) === 1) {
        $query['week_ref'] = $weekRef;
    }

    return $query;
}

function lessons_manage_redirect_page(array $source, string $fallback): string
{
    $requestedPage = resolve_page_slug(input_string($source, 'redirect_page'));
    $allowedPages = ['classrooms-academic', 'attendance-academic'];

    if (in_array($requestedPage, $allowedPages, true)) {
        return $requestedPage;
    }

    return $fallback;
}

function api_lessons_save_action(): void
{
    api_require_post(page_url('classrooms-academic'));

    $lessonId = input_int($_POST, 'id');
    api_guard_permission($lessonId > 0 ? 'academic.classes.update' : 'academic.classes.create');

    $redirectPage = lessons_manage_redirect_page($_POST, 'classrooms-academic');
    $redirectQuery = lessons_manage_redirect_query($_POST);
    $redirectPath = page_url($redirectPage, $redirectQuery);

    $classId = input_int($_POST, 'class_id');
    $title = input_string($_POST, 'actual_title');
    if ($classId <= 0 || $title === '') {
        set_flash('error', 'Vui lòng chọn lớp học và nhập tiêu đề buổi học.');
        redirect($redirectPath);
    }

    $payload = [
        'id' => $lessonId,
        'class_id' => $classId,
        'roadmap_id' => input_int($_POST, 'roadmap_id'),
        'actual_title' => $title,
        'actual_content' => input_string($_POST, 'actual_content'),
        'schedule_id' => input_int($_POST, 'schedule_id'),
    ];

    try {
        (new AcademicModel())->saveLesson($payload);
    } catch (InvalidArgumentException | DomainException $exception) {
        if (api_expects_json()) {
            api_error($exception->getMessage(), ['code' => 'LESSON_VALIDATION_FAILED'], 422);
        }

        set_flash('error', $exception->getMessage());
        redirect($redirectPath);
    }

    set_flash('success', $lessonId > 0 ? 'Đã cập nhật buổi học.' : 'Đã tạo buổi học mới.');
    redirect($redirectPath);
}

function api_lessons_attendance_roster_action(): void
{
    api_guard_permission('academic.schedules.view');

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        if (api_expects_json()) {
            api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
        }

        redirect(page_url('classrooms-academic'));
    }

    $scheduleId = input_int($_GET, 'schedule_id');
    if ($scheduleId <= 0) {
        api_error('Thiếu mã lịch học cần điểm danh.', ['code' => 'INVALID_SCHEDULE'], 422);
    }

    $academicModel = new AcademicModel();
    $schedule = $academicModel->findSchedule($scheduleId);
    if (!is_array($schedule)) {
        api_error('Không tìm thấy lịch học cần điểm danh.', ['code' => 'SCHEDULE_NOT_FOUND'], 404);
    }

    $rows = $academicModel->listAttendanceRosterBySchedule($scheduleId);

    $summary = [
        'total' => 0,
        'present' => 0,
        'late' => 0,
        'absent' => 0,
        'unmarked' => 0,
    ];

    foreach ($rows as $row) {
        $summary['total']++;

        $status = trim((string) ($row['attendance_status'] ?? ''));
        if ($status === 'present') {
            $summary['present']++;
            continue;
        }
        if ($status === 'late') {
            $summary['late']++;
            continue;
        }
        if ($status === 'absent') {
            $summary['absent']++;
            continue;
        }

        $summary['unmarked']++;
    }

    api_success('OK', [
        'schedule_id' => $scheduleId,
        'class_id' => (int) ($schedule['class_id'] ?? 0),
        'rows' => $rows,
        'summary' => $summary,
    ]);
}

function api_lessons_attendance_action(): void
{
    api_require_post(page_url('attendance-academic'));
    api_guard_permission('academic.schedules.update');

    $scheduleId = input_int($_POST, 'schedule_id');
    if ($scheduleId <= 0) {
        $scheduleId = input_int($_POST, 'attendance_schedule_id');
    }

    $redirectPage = lessons_manage_redirect_page($_POST, 'attendance-academic');
    $redirectQuery = lessons_manage_redirect_query($_POST);
    if ($scheduleId > 0) {
        $redirectQuery['schedule_id'] = $scheduleId;
        if ($redirectPage === 'classrooms-academic') {
            $redirectQuery['focus_schedule_id'] = $scheduleId;
        }
    }
    $redirectPath = page_url($redirectPage, $redirectQuery);

    if ($scheduleId <= 0) {
        set_flash('error', 'Vui lòng chọn buổi lịch học cần điểm danh.');
        redirect($redirectPath);
    }

    $statusMap = $_POST['attendance_status'] ?? [];
    $noteMap = $_POST['attendance_note'] ?? [];
    if (!is_array($statusMap)) {
        $statusMap = [];
    }
    if (!is_array($noteMap)) {
        $noteMap = [];
    }

    $entries = [];
    foreach ($statusMap as $rawStudentId => $rawStatus) {
        $studentId = (int) $rawStudentId;
        if ($studentId <= 0) {
            continue;
        }

        $entries[$studentId] = [
            'status' => trim((string) $rawStatus),
            'note' => trim((string) ($noteMap[$rawStudentId] ?? '')),
        ];
    }

    try {
        $updatedCount = (new AcademicModel())->saveAttendanceRosterBySchedule($scheduleId, $entries);
    } catch (InvalidArgumentException | DomainException $exception) {
        if (api_expects_json()) {
            api_error($exception->getMessage(), ['code' => 'ATTENDANCE_VALIDATION_FAILED'], 422);
        }

        set_flash('error', $exception->getMessage());
        redirect($redirectPath);
    }

    if ($updatedCount > 0) {
        set_flash('success', 'Đã cập nhật điểm danh cho ' . $updatedCount . ' học viên.');
    } else {
        set_flash('success', 'Đã lưu điểm danh (chưa có học viên hoặc chưa đánh dấu trạng thái).');
    }

    redirect($redirectPath);
}

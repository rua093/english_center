<?php
declare(strict_types=1);

require_once __DIR__ . '/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/tables/AttendanceTableModel.php';
require_once __DIR__ . '/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/tables/LessonsTableModel.php';
require_once __DIR__ . '/tables/NotificationsTableModel.php';
require_once __DIR__ . '/tables/SchedulesTableModel.php';
require_once __DIR__ . '/tables/SubmissionsTableModel.php';
require_once __DIR__ . '/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/BackofficeNotificationService.php';

final class UserModel
{
    private SchedulesTableModel $schedulesTable;
    private AssignmentsTableModel $assignmentsTable;
    private AttendanceTableModel $attendanceTable;
    private TuitionFeesTableModel $tuitionFeesTable;
    private NotificationsTableModel $notificationsTable;
    private SubmissionsTableModel $submissionsTable;
    private LessonsTableModel $lessonsTable;
    private ClassStudentsTableModel $classStudentsTable;
    private BackofficeNotificationService $backofficeNotificationService;

    public function __construct()
    {
        $this->schedulesTable = new SchedulesTableModel();
        $this->assignmentsTable = new AssignmentsTableModel();
        $this->attendanceTable = new AttendanceTableModel();
        $this->tuitionFeesTable = new TuitionFeesTableModel();
        $this->notificationsTable = new NotificationsTableModel();
        $this->submissionsTable = new SubmissionsTableModel();
        $this->lessonsTable = new LessonsTableModel();
        $this->classStudentsTable = new ClassStudentsTableModel();
        $this->backofficeNotificationService = new BackofficeNotificationService();
    }

    public function studentDashboard(int $studentId): array
    {
        return [
            'upcoming_schedules' => $this->schedulesTable->listUpcomingForStudent($studentId, 5),
            'assignments' => $this->assignmentsTable->listForStudentDashboard($studentId, 6),
            'attendance_summary' => $this->attendanceTable->summaryByStudent($studentId),
            'tuition' => $this->tuitionFeesTable->findLatestByStudent($studentId),
            'notifications' => $this->notificationsTable->listByUser($studentId, 5),
        ];
    }

    public function submitAssignment(int $studentId, int $assignmentId, string $fileUrl): void
    {
        $result = $this->submissionsTable->upsertStudentSubmission($studentId, $assignmentId, $fileUrl);
        if (!empty($result['created'])) {
            $detail = $this->submissionsTable->findDetailedByAssignmentAndStudent($assignmentId, $studentId);
            if (is_array($detail)) {
                try {
                    $this->backofficeNotificationService->notifyNewSubmission(
                        (int) ($detail['id'] ?? 0),
                        trim((string) ($detail['student_name'] ?? '')),
                        trim((string) ($detail['class_name'] ?? '')),
                        trim((string) ($detail['assignment_title'] ?? '')),
                        (int) ($detail['class_id'] ?? 0),
                        (int) ($detail['course_id'] ?? 0),
                        (int) ($detail['schedule_id'] ?? 0),
                        trim((string) ($detail['study_date'] ?? '')),
                        (int) ($detail['assignment_id'] ?? 0),
                        $studentId
                    );
                } catch (Throwable $exception) {
                    app_log('warning', 'Backoffice submission notification failed', [
                        'error' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'assignment_id' => $assignmentId,
                        'student_id' => $studentId,
                    ]);
                }
            }
        }
    }

    public function updateTuitionPayment(int $studentId, int $tuitionId, float $amount): void
    {
        $row = $this->tuitionFeesTable->findByIdAndStudent($tuitionId, $studentId);
        if (!$row) {
            return;
        }

        $newPaid = (float) ($row['amount_paid'] ?? 0) + $amount;
        $total = (float) ($row['total_amount'] ?? 0);
        $status = $newPaid >= $total ? 'paid' : 'debt';

        $this->tuitionFeesTable->updateAmountPaidStatus($tuitionId, $newPaid, $status);
    }

    public function homeWidgetData(int $userId, string $role): array
    {
        if ($role === 'student') {
            return [
                'student_progress' => $this->studentProgress($userId),
                'teacher_schedules' => [],
            ];
        }

        if ($role === 'teacher') {
            return [
                'student_progress' => null,
                'teacher_schedules' => $this->teacherUpcomingSchedules($userId),
            ];
        }

        return [
            'student_progress' => null,
            'teacher_schedules' => [],
        ];
    }

    public function teacherUpcomingSchedules(int $teacherId, int $days = 7): array
    {
        $endDate = (new DateTimeImmutable('+' . max(1, $days) . ' days'))->format('Y-m-d');
        return $this->schedulesTable->listUpcomingForTeacher($teacherId, $endDate, 10);
    }

    public function teacherUpcomingSchedulesFromNow(int $teacherId, int $days = 7): array
    {
        $endAt = (new DateTimeImmutable('now'))
            ->modify('+' . max(1, $days) . ' days')
            ->format('Y-m-d H:i:s');

        return $this->schedulesTable->listUpcomingForTeacherFromNow($teacherId, $endAt, 10);
    }

    private function studentProgress(int $studentId): array
    {
        $subjectCount = $this->classStudentsTable->countByStudent($studentId);
        $attendanceSummary = $this->attendanceTable->summaryByStudent($studentId);
        $totalLessons = $this->lessonsTable->countByStudent($studentId);
        $completedLessons = $this->lessonsTable->countCompletedByStudent($studentId);
        $classes = $this->classStudentsTable->listRecentClassNamesForStudent($studentId, 3);

        $attendanceTotal = (int) ($attendanceSummary['total_sessions'] ?? 0);
        if ($attendanceTotal <= 0) {
            $attendanceTotal = (int) ($attendanceSummary['present_count'] ?? 0)
                + (int) ($attendanceSummary['late_count'] ?? 0)
                + (int) ($attendanceSummary['absent_count'] ?? 0);
        }

        $attendanceRate = $attendanceTotal > 0
            ? (int) min(100, round(((int) ($attendanceSummary['present_count'] ?? 0) + (int) ($attendanceSummary['late_count'] ?? 0)) / $attendanceTotal * 100))
            : 0;

        $progressPercent = $totalLessons > 0
            ? (int) min(100, round(($completedLessons / $totalLessons) * 100))
            : 0;

        return [
            'subject_count' => $subjectCount,
            'attendance_total' => $attendanceTotal,
            'attendance_percent' => $attendanceRate,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress_percent' => $progressPercent,
            'classes' => $classes,
        ];
    }
}

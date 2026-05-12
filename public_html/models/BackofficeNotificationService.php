<?php
declare(strict_types=1);

require_once __DIR__ . '/tables/NotificationsTableModel.php';
require_once __DIR__ . '/tables/RolesTableModel.php';
require_once __DIR__ . '/tables/ClassesTableModel.php';

final class BackofficeNotificationService
{
    private NotificationsTableModel $notificationsTable;
    private RolesTableModel $rolesTable;
    private ClassesTableModel $classesTable;

    public function __construct()
    {
        $this->notificationsTable = new NotificationsTableModel();
        $this->rolesTable = new RolesTableModel();
        $this->classesTable = new ClassesTableModel();
    }

    public function notifyNewStudentLead(int $leadId, array $leadData, int $senderId = 0): void
    {
        $studentName = trim((string) ($leadData['student_name'] ?? 'Học viên chưa rõ tên'));
        $parentName = trim((string) ($leadData['parent_name'] ?? ''));
        $phone = trim((string) ($leadData['parent_phone'] ?? ''));

        $message = 'Lead #' . $leadId . ' vừa được gửi từ website.'
            . ' Học viên: ' . $studentName . '.'
            . ($parentName !== '' ? ' Phụ huynh: ' . $parentName . '.' : '')
            . ($phone !== '' ? ' SĐT: ' . $phone . '.' : '');

        $this->notifyBackofficeRoles(
            'Có học viên đăng ký mới',
            $message,
            page_url('student-leads-manage', [
                'edit' => $leadId,
                'highlight_lead_id' => $leadId,
            ]),
            $senderId
        );
    }

    public function notifyNewJobApplication(int $applicationId, array $applicationData, int $senderId = 0): void
    {
        $fullName = trim((string) ($applicationData['full_name'] ?? 'Ứng viên chưa rõ tên'));
        $position = trim((string) ($applicationData['position_applied'] ?? ''));
        $phone = trim((string) ($applicationData['phone'] ?? ''));
        $email = trim((string) ($applicationData['email'] ?? ''));

        $message = 'Hồ sơ #' . $applicationId . ' vừa được gửi từ website.'
            . ' Ứng viên: ' . $fullName . '.'
            . ($position !== '' ? ' Vị trí: ' . $position . '.' : '')
            . ($phone !== '' ? ' SĐT: ' . $phone . '.' : '')
            . ($email !== '' ? ' Email: ' . $email . '.' : '');

        $this->notifyBackofficeRoles(
            'Có hồ sơ ứng tuyển mới',
            $message,
            page_url('job-applications-manage', [
                'edit' => $applicationId,
                'highlight_application_id' => $applicationId,
            ]),
            $senderId
        );
    }

    public function notifyNewActivityRegistration(
        int $activityId,
        string $activityName,
        int $studentId,
        string $studentName,
        int $senderId = 0
    ): void {
        $message = 'Học viên #' . $studentId . ' - ' . ($studentName !== '' ? $studentName : 'Chưa rõ tên')
            . ' vừa đăng ký hoạt động ngoại khóa #' . $activityId
            . ($activityName !== '' ? ' - ' . $activityName : '')
            . '.';

        $this->notifyBackofficeRoles(
            'Có đăng ký ngoại khóa mới',
            $message,
            page_url('activities-manage', [
                'registrations_activity' => $activityId,
                'registration_student' => $studentId,
                'highlight_registration_student' => $studentId,
            ]) . '#activity-registration-list',
            $senderId
        );
    }

    public function notifyNewSubmission(
        int $submissionId,
        string $studentName,
        string $className,
        string $assignmentTitle,
        int $classId,
        int $courseId,
        int $scheduleId,
        string $studyDate,
        int $assignmentId,
        int $senderId = 0
    ): void {
        $message = 'Bài nộp #' . $submissionId . ' vừa được gửi.'
            . ($studentName !== '' ? ' Học viên: ' . $studentName . '.' : '')
            . ($className !== '' ? ' Lớp: ' . $className . '.' : '')
            . ($assignmentTitle !== '' ? ' Bài tập: ' . $assignmentTitle . '.' : '');

        $actionUrl = page_url('classrooms-academic', array_filter([
            'course_id' => $courseId > 0 ? $courseId : null,
            'class_id' => $classId > 0 ? $classId : null,
            'focus_schedule_id' => $scheduleId > 0 ? $scheduleId : null,
            'highlight_assignment_id' => $assignmentId > 0 ? $assignmentId : null,
            'highlight_submission_id' => $submissionId > 0 ? $submissionId : null,
            'open_grading' => ($scheduleId > 0 && $assignmentId > 0) ? 1 : null,
            'week_start' => $this->resolveWeekStartFromDate($studyDate),
            'week_ref' => $this->resolveWeekRefFromDate($studyDate),
        ], static fn ($value): bool => $value !== null && $value !== ''));

        $targets = $this->collectBackofficeRoleTargets();
        if ($classId > 0) {
            $class = $this->classesTable->findById($classId);
            $teacherId = (int) ($class['teacher_id'] ?? 0);
            if ($teacherId > 0) {
                // Route classroom submission notifications directly to the assigned teacher
                // so students in the same class never inherit them via CLASS targeting.
                $targets[] = [
                    'target_type' => 'USER',
                    'target_id' => $teacherId,
                ];
            }
        }

        $targets = $this->deduplicateTargets($targets);

        $this->notifyTargets('Có bài nộp mới', $message, $targets, $actionUrl, $senderId);
    }

    public function notifyNewApprovalRequest(
        int $approvalId,
        string $approvalType,
        string $requesterName,
        string $messagePreview,
        int $senderId = 0
    ): void {
        $typeLabel = $this->formatApprovalTypeLabel($approvalType);
        $message = 'Phiếu #' . $approvalId . ' vừa được tạo.'
            . ' Loại: ' . $typeLabel . '.'
            . ($requesterName !== '' ? ' Người gửi: ' . $requesterName . '.' : '')
            . ($messagePreview !== '' ? ' Nội dung: ' . $messagePreview : '');

        $this->notifyBackofficeRoles(
            'Có yêu cầu phê duyệt mới',
            $message,
            page_url('approvals-manage', [
                'edit' => $approvalId,
                'highlight_approval_id' => $approvalId,
            ]),
            $senderId
        );
    }

    private function notifyBackofficeRoles(string $title, string $message, ?string $actionUrl = null, int $senderId = 0): void
    {
        $this->notifyTargets($title, $message, $this->collectBackofficeRoleTargets(), $actionUrl, $senderId);
    }

    private function notifyTargets(string $title, string $message, array $targets, ?string $actionUrl = null, int $senderId = 0): void
    {
        if ($targets === []) {
            return;
        }

        $this->notificationsTable->save([
            'sender_id' => $senderId > 0 ? $senderId : null,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'targets' => $targets,
        ]);
    }

    private function collectBackofficeRoleTargets(): array
    {
        $targets = [];
        foreach (['admin', 'staff'] as $roleName) {
            $role = $this->rolesTable->findByRoleName($roleName);
            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $targets[] = [
                'target_type' => 'ROLE',
                'target_id' => $roleId,
            ];
        }

        return $targets;
    }

    private function deduplicateTargets(array $targets): array
    {
        $uniqueTargets = [];
        $seen = [];

        foreach ($targets as $target) {
            $targetType = strtoupper(trim((string) ($target['target_type'] ?? '')));
            $targetId = array_key_exists('target_id', $target) ? (string) $target['target_id'] : '';
            $key = $targetType . ':' . $targetId;
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueTargets[] = [
                'target_type' => $targetType,
                'target_id' => $target['target_id'] ?? null,
            ];
        }

        return $uniqueTargets;
    }

    private function resolveWeekStartFromDate(string $date): ?string
    {
        $normalized = trim($date);
        if ($normalized === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($normalized))->modify('monday this week')->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function resolveWeekRefFromDate(string $date): ?string
    {
        $normalized = trim($date);
        if ($normalized === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($normalized))->format('o-\\WW');
        } catch (Throwable) {
            return null;
        }
    }

    private function formatApprovalTypeLabel(string $approvalType): string
    {
        $normalized = strtolower(trim($approvalType));

        return match ($normalized) {
            'teacher_leave' => 'Giáo viên xin nghỉ',
            'schedule_change' => 'Đổi lịch dạy',
            'finance_adjust' => 'Điều chỉnh tài chính',
            'tuition_delete' => 'Xóa học phí',
            'tuition_discount' => 'Giảm học phí',
            default => $approvalType !== '' ? $approvalType : 'Khác',
        };
    }
}

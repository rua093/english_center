<?php
declare(strict_types=1);

require_once __DIR__ . '/tables/ApprovalsTableModel.php';
require_once __DIR__ . '/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/tables/AttendanceTableModel.php';
require_once __DIR__ . '/tables/BankAccountsTableModel.php';
require_once __DIR__ . '/tables/ClassesTableModel.php';
require_once __DIR__ . '/tables/CoursesTableModel.php';
require_once __DIR__ . '/tables/ExtracurricularActivitiesTableModel.php';
require_once __DIR__ . '/tables/FeedbacksTableModel.php';
require_once __DIR__ . '/tables/LessonsTableModel.php';
require_once __DIR__ . '/tables/MaterialsTableModel.php';
require_once __DIR__ . '/tables/NotificationsTableModel.php';
require_once __DIR__ . '/tables/PaymentTransactionsTableModel.php';
require_once __DIR__ . '/tables/RoomsTableModel.php';
require_once __DIR__ . '/tables/SchedulesTableModel.php';
require_once __DIR__ . '/tables/StudentPortfoliosTableModel.php';
require_once __DIR__ . '/tables/SubmissionsTableModel.php';
require_once __DIR__ . '/tables/TuitionFeesTableModel.php';
require_once __DIR__ . '/tables/UsersTableModel.php';

final class AcademicModel
{
    private PaymentTransactionsTableModel $paymentTransactionsTable;
    private AttendanceTableModel $attendanceTable;
    private ClassesTableModel $classesTable;
    private UsersTableModel $usersTable;
    private AssignmentsTableModel $assignmentsTable;
    private SubmissionsTableModel $submissionsTable;
    private MaterialsTableModel $materialsTable;
    private TuitionFeesTableModel $tuitionFeesTable;
    private StudentPortfoliosTableModel $portfoliosTable;
    private SchedulesTableModel $schedulesTable;
    private CoursesTableModel $coursesTable;
    private RoomsTableModel $roomsTable;
    private LessonsTableModel $lessonsTable;
    private NotificationsTableModel $notificationsTable;
    private FeedbacksTableModel $feedbacksTable;
    private ApprovalsTableModel $approvalsTable;
    private ExtracurricularActivitiesTableModel $activitiesTable;
    private BankAccountsTableModel $bankAccountsTable;

    public function __construct()
    {
        $this->paymentTransactionsTable = new PaymentTransactionsTableModel();
        $this->attendanceTable = new AttendanceTableModel();
        $this->classesTable = new ClassesTableModel();
        $this->usersTable = new UsersTableModel();
        $this->assignmentsTable = new AssignmentsTableModel();
        $this->submissionsTable = new SubmissionsTableModel();
        $this->materialsTable = new MaterialsTableModel();
        $this->tuitionFeesTable = new TuitionFeesTableModel();
        $this->portfoliosTable = new StudentPortfoliosTableModel();
        $this->schedulesTable = new SchedulesTableModel();
        $this->coursesTable = new CoursesTableModel();
        $this->roomsTable = new RoomsTableModel();
        $this->lessonsTable = new LessonsTableModel();
        $this->notificationsTable = new NotificationsTableModel();
        $this->feedbacksTable = new FeedbacksTableModel();
        $this->approvalsTable = new ApprovalsTableModel();
        $this->activitiesTable = new ExtracurricularActivitiesTableModel();
        $this->bankAccountsTable = new BankAccountsTableModel();
    }

    public function dashboardChartData(): array
    {
        $months = $this->paymentTransactionsTable->monthlyCreatedCounts(6);

        return [
            'months' => array_reverse($months),
            'attendance' => $this->attendanceTable->aggregateStatuses(),
        ];
    }

    public function dashboardStats(): array
    {
        return [
            'class_count' => $this->classesTable->countAll(),
            'student_count' => $this->usersTable->countByRoleName('student'),
            'teacher_count' => $this->usersTable->countByRoleName('teacher'),
            'assignment_count' => $this->assignmentsTable->countAll(),
            'submission_count' => $this->submissionsTable->countAll(),
            'material_count' => $this->materialsTable->countAll(),
            'tuition_total' => $this->tuitionFeesTable->sumTotalAmount(),
            'tuition_paid' => $this->tuitionFeesTable->sumAmountPaid(),
        ];
    }

    public function listMaterials(): array
    {
        return $this->materialsTable->listDetailed();
    }

    public function findMaterial(int $id): ?array
    {
        return $this->materialsTable->findById($id);
    }

    public function saveMaterial(array $data): void
    {
        $this->materialsTable->save($data);
    }

    public function deleteMaterial(int $id): void
    {
        $this->materialsTable->deleteById($id);
    }

    public function listPortfolios(): array
    {
        return $this->portfoliosTable->listDetailed();
    }

    public function findPortfolio(int $id): ?array
    {
        return $this->portfoliosTable->findById($id);
    }

    public function savePortfolio(array $data): void
    {
        $this->portfoliosTable->save($data);
    }

    public function deletePortfolio(int $id): void
    {
        $this->portfoliosTable->deleteById($id);
    }

    public function findClass(int $id): ?array
    {
        return $this->classesTable->findById($id);
    }

    public function findSchedule(int $id): ?array
    {
        return $this->schedulesTable->findById($id);
    }

    public function findAssignment(int $id): ?array
    {
        return $this->assignmentsTable->findById($id);
    }

    public function listClasses(): array
    {
        return $this->classesTable->listDetailedWithProgress();
    }

    public function listSchedules(): array
    {
        return $this->schedulesTable->listDetailed();
    }

    public function listAssignments(): array
    {
        return $this->assignmentsTable->listDetailed();
    }

    public function listSubmissionsForGrading(): array
    {
        return $this->submissionsTable->listForGrading();
    }

    public function classLookups(): array
    {
        return [
            'courses' => $this->coursesTable->listSimple(),
            'teachers' => $this->usersTable->listByRoleNames(['teacher', 'staff', 'admin']),
        ];
    }

    public function scheduleLookups(): array
    {
        return [
            'classes' => $this->classesTable->listSimple(),
            'rooms' => $this->roomsTable->listSimple(),
            'teachers' => $this->usersTable->listByRoleNames(['teacher', 'staff', 'admin']),
        ];
    }

    public function assignmentLookups(): array
    {
        return $this->lessonsTable->listForAssignmentLookup();
    }

    public function saveClass(array $data): void
    {
        $this->classesTable->save($data);
    }

    public function deleteClass(int $id): void
    {
        $this->classesTable->deleteById($id);
    }

    public function saveSchedule(array $data): void
    {
        $this->schedulesTable->save($data);
    }

    public function deleteSchedule(int $id): void
    {
        $this->schedulesTable->deleteById($id);
    }

    public function saveAssignment(array $data): void
    {
        $this->assignmentsTable->save($data);
    }

    public function deleteAssignment(int $id): void
    {
        $this->assignmentsTable->deleteById($id);
    }

    public function gradeSubmission(int $submissionId, ?float $score, string $comment): void
    {
        $this->submissionsTable->grade($submissionId, $score, $comment);
    }

    public function listNotifications(int $userId = 0): array
    {
        if ($userId > 0) {
            return $this->notificationsTable->listByUser($userId, 100);
        }
        return $this->notificationsTable->listRecent(100);
    }

    public function saveNotification(array $data): void
    {
        $this->notificationsTable->insert($data);
    }

    public function markNotificationRead(int $id): void
    {
        $this->notificationsTable->markRead($id);
    }

    public function listFeedbacks(): array
    {
        return $this->feedbacksTable->listDetailed();
    }

    public function saveFeedback(array $data): void
    {
        $this->feedbacksTable->save($data);
    }

    public function deleteFeedback(int $id): void
    {
        $this->feedbacksTable->deleteById($id);
    }

    public function listApprovals(): array
    {
        return $this->approvalsTable->listDetailed();
    }

    public function findApproval(int $id): ?array
    {
        return $this->approvalsTable->findById($id);
    }

    public function saveApproval(array $data): void
    {
        $this->approvalsTable->save($data);
    }

    public function decideApproval(int $approvalId, int $approverId, string $status, string $decisionNote = ''): void
    {
        $approval = $this->findApproval($approvalId);
        if (!$approval) {
            return;
        }

        $allowedStatus = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'pending';
        }

        $content = (string) ($approval['content'] ?? '');
        if ($decisionNote !== '') {
            $meta = $this->decodeApprovalContent($content);
            if (!empty($meta)) {
                $meta['decision_note'] = $decisionNote;
                $content = (string) json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $content .= "\n[Ghi chú duyệt] " . $decisionNote;
            }
        }

        $this->approvalsTable->updateDecision($approvalId, $approverId, $status, $content);

        if ($status === 'approved' && (string) ($approval['status'] ?? '') !== 'approved') {
            $updatedApproval = $this->findApproval($approvalId);
            if ($updatedApproval) {
                $this->executeApprovalAction($updatedApproval);
            }
        }

        if ($status !== 'pending') {
            $this->saveNotification([
                'user_id' => (int) ($approval['requester_id'] ?? 0),
                'title' => 'Cập nhật phê duyệt',
                'message' => 'Yêu cầu #' . $approvalId . ' đã được ' . ($status === 'approved' ? 'phê duyệt' : 'từ chối') . '.',
                'is_read' => 0,
            ]);
        }
    }

    private function executeApprovalAction(array $approval): void
    {
        $content = (string) ($approval['content'] ?? '');
        $meta = $this->decodeApprovalContent($content);
        $payload = is_array($meta['payload'] ?? null) ? $meta['payload'] : [];
        $action = (string) ($meta['action'] ?? '');

        if ($action === '') {
            $type = (string) ($approval['type'] ?? '');
            if ($type === 'teacher_leave') {
                $action = 'teacher_leave';
            }
            if ($type === 'schedule_change') {
                $action = 'schedule_change';
            }
        }

        switch ($action) {
            case 'tuition_delete':
                $tuitionId = (int) ($payload['tuition_id'] ?? 0);
                if ($tuitionId > 0) {
                    $this->deleteTuitionFee($tuitionId);
                }
                break;

            case 'finance_adjust':
                $tuitionId = (int) ($payload['tuition_id'] ?? 0);
                $requestedPaid = (float) ($payload['requested_amount_paid'] ?? 0);
                if ($tuitionId > 0) {
                    $this->updateTuitionAmountPaid($tuitionId, $requestedPaid);
                }
                break;

            case 'teacher_leave':
            case 'schedule_change':
                $scheduleId = (int) ($payload['schedule_id'] ?? 0);
                $newDate = trim((string) ($payload['new_date'] ?? ''));
                if ($scheduleId > 0 && $newDate !== '') {
                    $this->schedulesTable->rescheduleDate($scheduleId, $newDate);
                }
                break;

            default:
                break;
        }
    }

    private function updateTuitionAmountPaid(int $tuitionId, float $requestedPaid): void
    {
        $totalAmount = $this->tuitionFeesTable->findTotalById($tuitionId);
        if ($totalAmount === null) {
            return;
        }

        $amountPaid = max(0, $requestedPaid);
        $status = 'pending';
        if ($amountPaid >= $totalAmount && $totalAmount > 0) {
            $status = 'paid';
        } elseif ($amountPaid > 0) {
            $status = 'debt';
        }

        $this->tuitionFeesTable->updateAmountPaidStatus($tuitionId, $amountPaid, $status);
    }

    private function decodeApprovalContent(string $content): array
    {
        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function listActivities(): array
    {
        return $this->activitiesTable->listWithRegistrationCount();
    }

    public function findActivity(int $id): ?array
    {
        return $this->activitiesTable->findById($id);
    }

    public function saveActivity(array $data): void
    {
        $this->activitiesTable->save($data);
    }

    public function deleteActivity(int $id): void
    {
        $this->activitiesTable->deleteById($id);
    }

    public function listBankAccounts(): array
    {
        return $this->bankAccountsTable->listDetailed();
    }

    public function saveBankAccount(array $data): void
    {
        $this->bankAccountsTable->save($data);
    }

    public function deleteBankAccount(int $id): void
    {
        $this->bankAccountsTable->deleteById($id);
    }

    public function listTuitionFees(): array
    {
        return $this->tuitionFeesTable->listDetailed();
    }

    public function findTuitionFee(int $id): ?array
    {
        return $this->tuitionFeesTable->findDetailedById($id);
    }

    public function deleteTuitionFee(int $id): void
    {
        $this->tuitionFeesTable->deleteById($id);
    }

    public function saveTuitionPayment(int $tuitionId, float $amount, string $method = 'bank_transfer'): void
    {
        $this->tuitionFeesTable->executeInTransaction(function () use ($tuitionId, $amount, $method): void {
            $this->tuitionFeesTable->incrementAmountPaid($tuitionId, $amount);

            $transactionNo = sprintf('TXN-%d-%d', $tuitionId, time());
            $this->paymentTransactionsTable->insertSuccess($tuitionId, $transactionNo, $method, $amount);
        });
    }

    public function listPaymentTransactions(): array
    {
        return $this->paymentTransactionsTable->listDetailed();
    }
}
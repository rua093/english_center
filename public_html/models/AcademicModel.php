<?php
declare(strict_types=1);

require_once __DIR__ . '/tables/ApprovalsTableModel.php';
require_once __DIR__ . '/tables/AssignmentsTableModel.php';
require_once __DIR__ . '/tables/AttendanceTableModel.php';
require_once __DIR__ . '/tables/BankAccountsTableModel.php';
require_once __DIR__ . '/tables/ClassStudentsTableModel.php';
require_once __DIR__ . '/tables/ClassesTableModel.php';
require_once __DIR__ . '/tables/CoursePackagesTableModel.php';
require_once __DIR__ . '/tables/CourseRoadmapsTableModel.php';
require_once __DIR__ . '/tables/CoursesTableModel.php';
require_once __DIR__ . '/tables/ExtracurricularActivitiesTableModel.php';
require_once __DIR__ . '/tables/ExamsTableModel.php';
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
    private ClassStudentsTableModel $classStudentsTable;
    private CoursePackagesTableModel $coursePackagesTable;
    private CourseRoadmapsTableModel $courseRoadmapsTable;
    private ExamsTableModel $examsTable;

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
        $this->classStudentsTable = new ClassStudentsTableModel();
        $this->coursePackagesTable = new CoursePackagesTableModel();
        $this->courseRoadmapsTable = new CourseRoadmapsTableModel();
        $this->examsTable = new ExamsTableModel();
    }

    public function listStudentsForClass(int $classId): array
    {
        return $this->classStudentsTable->listStudentsForClass($classId);
    }

    public function summarizeAttendanceRateByClass(int $classId): array
    {
        return $this->attendanceTable->summarizeAttendanceRateByClass($classId);
    }

    public function summarizeOnTimeSubmissionRateByClass(int $classId): array
    {
        return $this->submissionsTable->summarizeOnTimeSubmissionRateByClass($classId);
    }

    public function listExamColumnsByClass(int $classId): array
    {
        return $this->examsTable->listExamColumnsByClass($classId);
    }

    public function listExamRowsByClass(int $classId): array
    {
        return $this->examsTable->listExamRowsByClass($classId);
    }

    public function countExamRowsForColumn(int $classId, string $examName, string $examType, string $examDate): int
    {
        return $this->examsTable->countExamRowsForColumn($classId, $examName, $examType, $examDate);
    }

    public function createExamColumnForStudents(int $classId, array $studentIds, string $examName, string $examType, string $examDate): int
    {
        return $this->examsTable->createExamColumnForStudents($classId, $studentIds, $examName, $examType, $examDate);
    }

    public function findExamRowById(int $examId): ?array
    {
        return $this->examsTable->findExamRowById($examId);
    }

    public function findExamRowByMeta(int $classId, int $studentId, string $examName, string $examType, string $examDate): ?array
    {
        return $this->examsTable->findExamRowByMeta($classId, $studentId, $examName, $examType, $examDate);
    }

    public function createExamRow(int $classId, int $studentId, string $examName, string $examType, string $examDate): int
    {
        return $this->examsTable->createExamRow($classId, $studentId, $examName, $examType, $examDate);
    }

    public function updateExamResult(
        int $examId,
        ?string $result,
        ?string $teacherComment,
        ?float $scoreListening = null,
        ?float $scoreSpeaking = null,
        ?float $scoreReading = null,
        ?float $scoreWriting = null
    ): void
    {
        $this->examsTable->updateExamResult(
            $examId,
            $result,
            $teacherComment,
            $scoreListening,
            $scoreSpeaking,
            $scoreReading,
            $scoreWriting
        );
    }

    public function updateExamColumnMeta(
        int $classId,
        string $oldExamName,
        string $oldExamType,
        string $oldExamDate,
        string $newExamName,
        string $newExamType,
        string $newExamDate
    ): int {
        return $this->examsTable->updateExamColumnMeta(
            $classId,
            $oldExamName,
            $oldExamType,
            $oldExamDate,
            $newExamName,
            $newExamType,
            $newExamDate
        );
    }

    public function deleteExamColumn(int $classId, string $examName, string $examType, string $examDate): int
    {
        return $this->examsTable->deleteExamColumn($classId, $examName, $examType, $examDate);
    }

    public function dashboardChartData(): array
    {
        $monthRows = $this->paymentTransactionsTable->monthlyCreatedCounts(6);
        $monthMap = [];
        foreach ($monthRows as $row) {
            $monthKey = (string) ($row['month'] ?? '');
            if ($monthKey !== '') {
                $monthMap[$monthKey] = (float) ($row['total'] ?? 0);
            }
        }

        $months = [];
        $currentMonth = new DateTimeImmutable('first day of this month');
        for ($offset = 5; $offset >= 0; $offset--) {
            $monthKey = $currentMonth->modify(sprintf('-%d month', $offset))->format('Y-m');
            $months[] = [
                'month' => $monthKey,
                'total' => (float) ($monthMap[$monthKey] ?? 0),
            ];
        }

        return [
            'months' => $months,
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

    public function countMaterials(): int
    {
        return $this->materialsTable->countDetailed();
    }

    public function listMaterialsPage(int $page, int $perPage): array
    {
        return $this->materialsTable->listDetailedPage($page, $perPage);
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

    public function countPortfolios(): int
    {
        return $this->portfoliosTable->countDetailed();
    }

    public function listPortfoliosPage(int $page, int $perPage): array
    {
        return $this->portfoliosTable->listDetailedPage($page, $perPage);
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

    public function findLesson(int $id): ?array
    {
        return $this->lessonsTable->findById($id);
    }

    public function listClasses(): array
    {
        return $this->classesTable->listDetailedWithProgress();
    }

    public function countClasses(): int
    {
        return $this->classesTable->countDetailed();
    }

    public function listClassesPage(int $page, int $perPage): array
    {
        return $this->classesTable->listDetailedWithProgressPage($page, $perPage);
    }

    public function listSchedules(): array
    {
        return $this->schedulesTable->listDetailed();
    }

    public function countCourses(): int
    {
        return $this->coursesTable->countDetailed();
    }

    public function listCoursesPage(int $page, int $perPage): array
    {
        return $this->coursesTable->listDetailedPage($page, $perPage);
    }

    public function saveCourse(array $data): void
    {
        $this->coursesTable->save($data);
    }

    public function deleteCourse(int $id): void
    {
        $this->coursesTable->deleteById($id);
    }

    public function countSchedules(): int
    {
        return $this->schedulesTable->countDetailed();
    }

    public function listSchedulesPage(int $page, int $perPage): array
    {
        return $this->schedulesTable->listDetailedPage($page, $perPage);
    }

    public function listAssignments(): array
    {
        return $this->assignmentsTable->listDetailed();
    }

    public function countAssignments(): int
    {
        return $this->assignmentsTable->countDetailed();
    }

    public function listAssignmentsPage(int $page, int $perPage): array
    {
        return $this->assignmentsTable->listDetailedPage($page, $perPage);
    }

    public function listSubmissionsForGrading(): array
    {
        return $this->submissionsTable->listForGrading();
    }

    public function countSubmissionsForGrading(): int
    {
        return $this->submissionsTable->countForGrading();
    }

    public function listSubmissionsForGradingPage(int $page, int $perPage): array
    {
        return $this->submissionsTable->listForGradingPage($page, $perPage);
    }

    public function listSubmissionRosterByClassAndAssignment(int $classId, int $assignmentId): array
    {
        return $this->submissionsTable->listRosterByClassAndAssignment($classId, $assignmentId);
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
        return $this->schedulesTable->listForAssignmentLookup();
    }

    public function classroomLookups(): array
    {
        return [
            'courses' => $this->coursesTable->listSimple(),
            'classes' => $this->classesTable->listForRegistration(),
        ];
    }

    public function listLessonsByClass(int $classId): array
    {
        return $this->lessonsTable->listByClass($classId);
    }

    public function listRoadmapsByClass(int $classId): array
    {
        return $this->lessonsTable->listRoadmapsByClass($classId);
    }

    public function countRoadmapsByCourse(int $courseId): int
    {
        return $this->courseRoadmapsTable->countByCourse($courseId);
    }

    public function listRoadmapsByCoursePage(int $courseId, int $page, int $perPage): array
    {
        return $this->courseRoadmapsTable->listByCoursePage($courseId, $page, $perPage);
    }

    public function findRoadmap(int $id): ?array
    {
        return $this->courseRoadmapsTable->findById($id);
    }

    public function saveRoadmap(array $data): void
    {
        $this->courseRoadmapsTable->save($data);
    }

    public function deleteRoadmap(int $id): void
    {
        $this->courseRoadmapsTable->deleteById($id);
    }

    public function listSchedulesByClass(int $classId): array
    {
        return $this->schedulesTable->listByClass($classId);
    }

    public function saveLesson(array $data): void
    {
        $this->lessonsTable->save($data);
    }

    public function listAttendanceRosterBySchedule(int $scheduleId): array
    {
        return $this->attendanceTable->listRosterBySchedule($scheduleId);
    }

    public function saveAttendanceRosterBySchedule(int $scheduleId, array $entries): int
    {
        return $this->attendanceTable->saveRosterBySchedule($scheduleId, $entries);
    }

    public function studentLookups(): array
    {
        return $this->usersTable->listByRoleNames(['student']);
    }

    public function registrationLookups(): array
    {
        return [
            'students' => $this->usersTable->listActiveByRoleNames(['student']),
            'courses' => $this->coursesTable->listForRegistration(),
            'classes' => $this->classesTable->listForRegistration(),
            'promotions' => $this->coursePackagesTable->listActiveForRegistration(),
        ];
    }

    public function findActiveUser(int $userId): ?array
    {
        return $this->usersTable->findActiveById($userId);
    }

    public function findCourse(int $id): ?array
    {
        return $this->coursesTable->findById($id);
    }

    public function findCoursePackage(int $id): ?array
    {
        return $this->coursePackagesTable->findById($id);
    }

    public function usesPromotionSchema(): bool
    {
        return $this->coursePackagesTable->usesPromotionSchema();
    }

    public function countPromotions(): int
    {
        return $this->coursePackagesTable->countDetailed();
    }

    public function listPromotionsPage(int $page, int $perPage): array
    {
        return $this->coursePackagesTable->listDetailedPage($page, $perPage);
    }

    public function findPromotion(int $id): ?array
    {
        return $this->coursePackagesTable->findDetailedById($id);
    }

    public function savePromotion(array $data): void
    {
        $this->coursePackagesTable->save($data);
    }

    public function deletePromotion(int $id): void
    {
        $this->coursePackagesTable->deleteById($id);
    }

    public function tuitionStudentClassLookups(): array
    {
        return $this->classStudentsTable->listStudentsByClass();
    }

    public function listRegistrationEnrollmentRows(int $limit = 300): array
    {
        return $this->classStudentsTable->listEnrollmentRowsForRegistration($limit);
    }

    public function findRegistrationEnrollmentRow(int $studentId, int $classId): ?array
    {
        if ($studentId <= 0 || $classId <= 0) {
            return null;
        }

        return $this->classStudentsTable->findEnrollmentRowForRegistration($classId, $studentId);
    }

    public function isStudentEnrolledInClass(int $studentId, int $classId): bool
    {
        if ($studentId <= 0 || $classId <= 0) {
            return false;
        }

        return $this->classStudentsTable->existsEnrollment($classId, $studentId);
    }

    public function hasTuitionFeeForStudentClass(int $studentId, int $classId): bool
    {
        if ($studentId <= 0 || $classId <= 0) {
            return false;
        }

        return $this->tuitionFeesTable->findByStudentAndClass($studentId, $classId) !== null;
    }

    public function updateRegistrationLearningStatus(int $studentId, int $classId, string $targetStatus): array
    {
        if ($studentId <= 0 || $classId <= 0) {
            throw new InvalidArgumentException('Dữ liệu học viên/lớp học không hợp lệ.');
        }

        $normalizedTargetStatus = in_array($targetStatus, ['trial', 'official'], true)
            ? $targetStatus
            : '';
        if ($normalizedTargetStatus === '') {
            throw new InvalidArgumentException('Trạng thái học viên không hợp lệ.');
        }

        $enrollment = $this->classStudentsTable->findEnrollment($classId, $studentId);
        if (!is_array($enrollment)) {
            throw new RuntimeException('Không tìm thấy ghi danh học viên trong lớp đã chọn.');
        }

        $currentStatus = (string) ($enrollment['learning_status'] ?? 'official');
        if ($currentStatus === $normalizedTargetStatus) {
            return [
                'updated' => false,
                'from_status' => $currentStatus,
                'to_status' => $normalizedTargetStatus,
                'tuition_created_id' => 0,
                'tuition_deleted_id' => 0,
            ];
        }

        $tuitionCreatedId = 0;
        $tuitionDeletedId = 0;

        $this->tuitionFeesTable->executeInTransaction(function () use (
            $studentId,
            $classId,
            $normalizedTargetStatus,
            &$tuitionCreatedId,
            &$tuitionDeletedId
        ): void {
            $existingTuition = $this->tuitionFeesTable->findByStudentAndClass($studentId, $classId);

            if ($normalizedTargetStatus === 'official') {
                if (!$existingTuition) {
                    $class = $this->classesTable->findById($classId);
                    if (!is_array($class)) {
                        throw new RuntimeException('Không tìm thấy lớp học để tính học phí.');
                    }

                    $courseId = (int) ($class['course_id'] ?? 0);
                    if ($courseId <= 0) {
                        throw new RuntimeException('Lớp học chưa liên kết khóa học hợp lệ.');
                    }

                    $course = $this->coursesTable->findById($courseId);
                    if (!is_array($course)) {
                        throw new RuntimeException('Không tìm thấy khóa học để tính học phí.');
                    }

                    $baseAmount = max(0, (float) ($course['base_price'] ?? 0));
                    $tuitionCreatedId = $this->tuitionFeesTable->createDebtForRegistration([
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'package_id' => 0,
                        'base_amount' => $baseAmount,
                        'discount_type' => 'none',
                        'discount_amount' => 0,
                        'payment_plan' => 'full',
                    ]);
                }
            } else {
                if ($existingTuition) {
                    $tuitionId = (int) ($existingTuition['id'] ?? 0);
                    $amountPaid = max(0, (float) ($existingTuition['amount_paid'] ?? 0));
                    $paidFromTransactions = $tuitionId > 0
                        ? max(0, $this->paymentTransactionsTable->sumSuccessAmountByTuitionId($tuitionId))
                        : 0.0;

                    if (max($amountPaid, $paidFromTransactions) > 0.0001) {
                        throw new DomainException('Không thể chuyển từ chính thức sang học thử vì học viên đã thanh toán học phí.');
                    }

                    if ($tuitionId > 0) {
                        $this->paymentTransactionsTable->deleteByTuitionFeeId($tuitionId);
                        $this->tuitionFeesTable->deleteById($tuitionId);
                        $tuitionDeletedId = $tuitionId;
                    }
                }
            }

            $updated = $this->classStudentsTable->updateLearningStatus($classId, $studentId, $normalizedTargetStatus);
            if (!$updated) {
                throw new RuntimeException('Không thể cập nhật trạng thái học viên trong lớp.');
            }
        });

        return [
            'updated' => true,
            'from_status' => $currentStatus,
            'to_status' => $normalizedTargetStatus,
            'tuition_created_id' => $tuitionCreatedId,
            'tuition_deleted_id' => $tuitionDeletedId,
        ];
    }

    public function registerCourseAndCreateDebtTuition(array $data): array
    {
        $studentId = (int) ($data['student_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $packageId = max(0, (int) ($data['package_id'] ?? 0));
        $baseAmount = max(0, (float) ($data['base_amount'] ?? 0));
        $discountType = (string) ($data['discount_type'] ?? 'none');
        $discountAmount = max(0, (float) ($data['discount_amount'] ?? 0));
        $paymentPlan = (string) ($data['payment_plan'] ?? 'full');
        $learningStatus = (string) ($data['learning_status'] ?? 'official');
        $enrollmentDate = trim((string) ($data['enrollment_date'] ?? ''));

        if ($studentId <= 0 || $classId <= 0) {
            throw new InvalidArgumentException('Dữ liệu đăng ký không hợp lệ.');
        }

        $normalizedLearningStatus = in_array($learningStatus, ['trial', 'official'], true)
            ? $learningStatus
            : 'official';

        if ($enrollmentDate === '') {
            $enrollmentDate = date('Y-m-d');
        }

        $alreadyEnrolled = false;
        $tuitionId = 0;

        $this->tuitionFeesTable->executeInTransaction(function () use (
            $studentId,
            $classId,
            $packageId,
            $baseAmount,
            $discountType,
            $discountAmount,
            $paymentPlan,
            $normalizedLearningStatus,
            $enrollmentDate,
            &$alreadyEnrolled,
            &$tuitionId
        ): void {
            $existingTuition = $this->tuitionFeesTable->findByStudentAndClass($studentId, $classId);
            if ($existingTuition) {
                throw new RuntimeException('Học viên đã có học phí cho lớp này. Vui lòng kiểm tra lại trước khi đăng ký.');
            }

            $alreadyEnrolled = $this->classStudentsTable->existsEnrollment($classId, $studentId);
            if (!$alreadyEnrolled) {
                $this->classStudentsTable->enrollStudent($classId, $studentId, $normalizedLearningStatus, $enrollmentDate);
            }

            if (!$this->classStudentsTable->existsEnrollment($classId, $studentId)) {
                throw new RuntimeException('Không thể thêm học viên vào lớp đã chọn. Vui lòng thử lại.');
            }

            $enrollment = $this->classStudentsTable->findEnrollment($classId, $studentId);
            if (!is_array($enrollment)) {
                throw new RuntimeException('Không thể xác định trạng thái ghi danh của học viên.');
            }

            $currentStatus = (string) ($enrollment['learning_status'] ?? 'official');
            if ($currentStatus !== $normalizedLearningStatus) {
                $this->classStudentsTable->updateLearningStatus($classId, $studentId, $normalizedLearningStatus);
            }

            if ($normalizedLearningStatus === 'trial') {
                $tuitionId = 0;
                return;
            }

            $tuitionId = $this->tuitionFeesTable->createDebtForRegistration([
                'student_id' => $studentId,
                'class_id' => $classId,
                'package_id' => $packageId,
                'base_amount' => $baseAmount,
                'discount_type' => $discountType,
                'discount_amount' => $discountAmount,
                'payment_plan' => $paymentPlan,
            ]);
        });

        return [
            'tuition_id' => $tuitionId,
            'already_enrolled' => $alreadyEnrolled,
            'learning_status' => $normalizedLearningStatus,
        ];
    }

    public function feedbackLookups(): array
    {
        return [
            'students' => $this->usersTable->listByRoleNames(['student']),
            'teachers' => $this->usersTable->listByRoleNames(['teacher']),
            'classes' => $this->classesTable->listSimple(),
        ];
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

    public function countFeedbacks(): int
    {
        return $this->feedbacksTable->countDetailed();
    }

    public function listFeedbacksPage(int $page, int $perPage): array
    {
        return $this->feedbacksTable->listDetailedPage($page, $perPage);
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

    public function countApprovals(): int
    {
        return $this->approvalsTable->countDetailed();
    }

    public function listApprovalsPage(int $page, int $perPage): array
    {
        return $this->approvalsTable->listDetailedPage($page, $perPage);
    }

    public function findApproval(int $id): ?array
    {
        return $this->approvalsTable->findById($id);
    }

    public function saveApproval(array $data): void
    {
        $this->approvalsTable->save($data);
    }

    public function deleteApproval(int $id): void
    {
        $this->approvalsTable->deleteById($id);
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

        $effectiveApproverId = $status === 'pending' ? 0 : $approverId;
        $this->approvalsTable->updateDecision($approvalId, $effectiveApproverId, $status, $content);

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
        $status = $amountPaid >= $totalAmount ? 'paid' : 'debt';

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

    public function countActivities(): int
    {
        return $this->activitiesTable->countDetailed();
    }

    public function listActivitiesPage(int $page, int $perPage): array
    {
        return $this->activitiesTable->listWithRegistrationCountPage($page, $perPage);
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

    public function countBankAccounts(): int
    {
        return $this->bankAccountsTable->countDetailed();
    }

    public function listBankAccountsPage(int $page, int $perPage): array
    {
        return $this->bankAccountsTable->listDetailedPage($page, $perPage);
    }

    public function findBankAccount(int $id): ?array
    {
        return $this->bankAccountsTable->findById($id);
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

    public function countTuitionFees(): int
    {
        return $this->tuitionFeesTable->countDetailed();
    }

    public function listTuitionFeesPage(int $page, int $perPage): array
    {
        return $this->tuitionFeesTable->listDetailedPage($page, $perPage);
    }

    public function findTuitionFee(int $id): ?array
    {
        return $this->tuitionFeesTable->findDetailedById($id);
    }

    public function findTuitionFeeForEdit(int $id): ?array
    {
        return $this->tuitionFeesTable->findForEdit($id);
    }

    public function saveTuitionFee(array $data): void
    {
        $this->tuitionFeesTable->save($data);
    }

    public function deleteTuitionFee(int $id): void
    {
        $this->tuitionFeesTable->deleteById($id);
    }

    private function generateTransactionNo(string $prefix, int $tuitionId): string
    {
        return sprintf('%s-%d-%s-%03d', $prefix, $tuitionId, date('YmdHis'), random_int(0, 999));
    }

    private function resolveTransactionNo(array $data, ?array $oldTransaction = null): string
    {
        $requestedNo = trim((string) ($data['transaction_no'] ?? ''));
        if ($requestedNo !== '') {
            return $requestedNo;
        }

        $existingNo = trim((string) ($oldTransaction['transaction_no'] ?? ''));
        if ($existingNo !== '') {
            return $existingNo;
        }

        $tuitionId = (int) ($data['tuition_fee_id'] ?? 0);
        return $this->generateTransactionNo('CENTER', max(0, $tuitionId));
    }

    private function syncTuitionFromPayments(int $tuitionId): void
    {
        if ($tuitionId <= 0) {
            return;
        }

        $fee = $this->tuitionFeesTable->findForEdit($tuitionId);
        if (!$fee) {
            return;
        }

        $paidAmount = $this->paymentTransactionsTable->sumSuccessAmountByTuitionId($tuitionId);
        $totalAmount = (float) ($fee['total_amount'] ?? 0);
        $status = $paidAmount >= $totalAmount ? 'paid' : 'debt';

        $this->tuitionFeesTable->updateAmountPaidStatus($tuitionId, $paidAmount, $status);
    }

    private function bootstrapLegacyPaymentLedger(int $tuitionId): void
    {
        if ($tuitionId <= 0) {
            return;
        }

        $fee = $this->tuitionFeesTable->findForEdit($tuitionId);
        if (!$fee) {
            return;
        }

        $successPaid = $this->paymentTransactionsTable->sumSuccessAmountByTuitionId($tuitionId);
        $currentPaid = (float) ($fee['amount_paid'] ?? 0);

        if ($successPaid > 0 || $currentPaid <= 0) {
            return;
        }

        $this->paymentTransactionsTable->save([
            'tuition_fee_id' => $tuitionId,
            'transaction_no' => $this->generateTransactionNo('LEGACY', $tuitionId),
            'payment_method' => 'cash',
            'amount' => $currentPaid,
            'transaction_status' => 'success',
        ]);
    }

    public function recordStudentWebPayment(int $studentId, int $tuitionId, float $amount, string $method = 'bank_transfer'): bool
    {
        if ($studentId <= 0 || $tuitionId <= 0 || $amount <= 0) {
            return false;
        }

        $fee = $this->tuitionFeesTable->findByIdAndStudent($tuitionId, $studentId);
        if (!$fee) {
            return false;
        }

        $this->tuitionFeesTable->executeInTransaction(function () use ($tuitionId, $amount, $method): void {
            $this->bootstrapLegacyPaymentLedger($tuitionId);

            $this->paymentTransactionsTable->save([
                'tuition_fee_id' => $tuitionId,
                'transaction_no' => $this->generateTransactionNo('WEB', $tuitionId),
                'payment_method' => $method,
                'amount' => $amount,
                'transaction_status' => 'success',
            ]);

            $this->syncTuitionFromPayments($tuitionId);
        });

        return true;
    }

    public function saveTuitionPayment(int $tuitionId, float $amount, string $method = 'bank_transfer'): void
    {
        if ($tuitionId <= 0 || $amount <= 0) {
            return;
        }

        $this->tuitionFeesTable->executeInTransaction(function () use ($tuitionId, $amount, $method): void {
            $this->bootstrapLegacyPaymentLedger($tuitionId);

            $this->paymentTransactionsTable->save([
                'tuition_fee_id' => $tuitionId,
                'transaction_no' => $this->generateTransactionNo('TXN', $tuitionId),
                'payment_method' => $method,
                'amount' => $amount,
                'transaction_status' => 'success',
            ]);

            $this->syncTuitionFromPayments($tuitionId);
        });
    }

    public function listPaymentTransactions(): array
    {
        return $this->paymentTransactionsTable->listDetailed();
    }

    public function countPaymentTransactions(): int
    {
        return $this->paymentTransactionsTable->countDetailed();
    }

    public function listPaymentTransactionsPage(int $page, int $perPage): array
    {
        return $this->paymentTransactionsTable->listDetailedPage($page, $perPage);
    }

    public function findPaymentTransaction(int $id): ?array
    {
        return $this->paymentTransactionsTable->findById($id);
    }

    public function savePaymentTransaction(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $old = $id > 0 ? $this->paymentTransactionsTable->findById($id) : null;
        $newTuitionId = (int) ($data['tuition_fee_id'] ?? 0);
        $data['transaction_no'] = $this->resolveTransactionNo($data, $old);

        $this->tuitionFeesTable->executeInTransaction(function () use ($data, $old, $newTuitionId): void {
            if ($newTuitionId > 0) {
                $this->bootstrapLegacyPaymentLedger($newTuitionId);
            }

            $this->paymentTransactionsTable->save($data);

            $tuitionIds = [];
            if ($old) {
                $oldTuitionId = (int) ($old['tuition_fee_id'] ?? 0);
                if ($oldTuitionId > 0) {
                    $tuitionIds[$oldTuitionId] = true;
                }
            }
            if ($newTuitionId > 0) {
                $tuitionIds[$newTuitionId] = true;
            }

            foreach (array_keys($tuitionIds) as $tuitionId) {
                $this->syncTuitionFromPayments((int) $tuitionId);
            }
        });
    }

    public function deletePaymentTransaction(int $id): void
    {
        $existing = $this->paymentTransactionsTable->findById($id);
        if (!$existing) {
            return;
        }

        $tuitionId = (int) ($existing['tuition_fee_id'] ?? 0);

        $this->tuitionFeesTable->executeInTransaction(function () use ($id, $tuitionId): void {
            $this->paymentTransactionsTable->deleteById($id);
            $this->syncTuitionFromPayments($tuitionId);
        });
    }
}
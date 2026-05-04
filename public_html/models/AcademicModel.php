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

    public function findUser(int $userId): ?array
    {
        return $this->usersTable->findActiveById($userId);
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

    public function countClasses(int $teacherId = 0): int
    {
        return $this->classesTable->countDetailed($teacherId);
    }

    public function listClassesPage(int $page, int $perPage, int $teacherId = 0): array
    {
        return $this->classesTable->listDetailedWithProgressPage($page, $perPage, $teacherId);
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

    public function averageFeedbackRating(): float
    {
        return $this->feedbacksTable->averageRating();
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

    public function listSubmissionDetailsByClass(int $classId): array
    {
        return $this->submissionsTable->listDetailedByClass($classId);
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
            'classes' => $this->classesTable->listSimpleByStatus('active'),
            'rooms' => $this->roomsTable->listSimple(),
            'teachers' => $this->usersTable->listActiveByRoleNames(['teacher']),
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

    public function buildStudentPerformanceExport(int $classId, array $studentIds): array
    {
        if ($classId <= 0) {
            return [];
        }

        $selectedIds = [];
        foreach ($studentIds as $studentId) {
            $normalizedId = (int) $studentId;
            if ($normalizedId > 0) {
                $selectedIds[$normalizedId] = true;
            }
        }

        if ($selectedIds === []) {
            return [];
        }

        $classRow = $this->findClass($classId);
        $students = $this->listStudentsForClass($classId);

        $selectedStudents = [];
        foreach ($students as $studentRow) {
            $studentId = (int) ($studentRow['student_id'] ?? 0);
            if ($studentId > 0 && isset($selectedIds[$studentId])) {
                $selectedStudents[$studentId] = $studentRow;
            }
        }

        if ($selectedStudents === []) {
            return [];
        }

        $attendanceByStudent = [];
        foreach ($this->summarizeAttendanceRateByClass($classId) as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if ($studentId > 0) {
                $attendanceByStudent[$studentId] = $row;
            }
        }

        $submissionByStudent = [];
        foreach ($this->summarizeOnTimeSubmissionRateByClass($classId) as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if ($studentId > 0) {
                $submissionByStudent[$studentId] = $row;
            }
        }

        $assignmentRows = [];
        $assignmentScoreBuckets = [];
        foreach ($this->listSubmissionDetailsByClass($classId) as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if (!isset($selectedStudents[$studentId])) {
                continue;
            }

            $score = $row['score'];
            if ($score !== null && $score !== '') {
                $assignmentScoreBuckets[$studentId][] = (float) $score;
            }

            $assignmentRows[] = $row;
        }

        $examRows = [];
        foreach ($this->listExamRowsByClass($classId) as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if (isset($selectedStudents[$studentId])) {
                $examRows[] = $row;
            }
        }

        $summaryRows = [];
        foreach ($selectedStudents as $studentId => $studentRow) {
            $attendance = $attendanceByStudent[$studentId] ?? [];
            $submission = $submissionByStudent[$studentId] ?? [];
            $totalSessions = max(0, (int) ($attendance['total_sessions'] ?? 0));
            $attendedSessions = max(0, (int) ($attendance['attended_sessions'] ?? 0));
            $totalAssignments = max(0, (int) ($submission['total_assignments'] ?? 0));
            $submittedAssignments = max(0, (int) ($submission['submitted_assignments'] ?? 0));
            $gradedAssignments = $assignmentScoreBuckets[$studentId] ?? [];

            $summaryRows[] = [
                'student_id' => $studentId,
                'student_name' => (string) ($studentRow['student_name'] ?? ''),
                'student_code' => (string) ($studentRow['student_code'] ?? ''),
                'total_sessions' => $totalSessions,
                'attended_sessions' => $attendedSessions,
                'present_sessions' => max(0, (int) ($attendance['present_sessions'] ?? 0)),
                'late_sessions' => max(0, (int) ($attendance['late_sessions'] ?? 0)),
                'absent_sessions' => max(0, (int) ($attendance['absent_sessions'] ?? 0)),
                'attendance_rate' => $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0.0,
                'total_assignments' => $totalAssignments,
                'submitted_assignments' => $submittedAssignments,
                'on_time_assignments' => max(0, (int) ($submission['on_time_assignments'] ?? 0)),
                'late_assignments' => max(0, (int) ($submission['late_assignments'] ?? 0)),
                'submission_rate' => $totalAssignments > 0 ? round(($submittedAssignments / $totalAssignments) * 100, 1) : 0.0,
                'graded_assignment_count' => count($gradedAssignments),
                'assignment_average' => $gradedAssignments !== []
                    ? round(array_sum($gradedAssignments) / count($gradedAssignments), 2)
                    : null,
            ];
        }

        usort($summaryRows, static function (array $left, array $right): int {
            return strcmp((string) ($left['student_name'] ?? ''), (string) ($right['student_name'] ?? ''));
        });

        return [
            'class' => $classRow,
            'students' => array_values($selectedStudents),
            'summary_rows' => $summaryRows,
            'assignment_rows' => $assignmentRows,
            'exam_rows' => $examRows,
        ];
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

    public function notificationRecipientLookups(): array
    {
        return $this->usersTable->listActiveByRoleNames(['admin', 'staff', 'teacher', 'student']);
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

    public function listActiveTeachers(): array
    {
        return $this->usersTable->listActiveByRoleNames(['teacher']);
    }

    public function listTeacherCertificatesByUserId(int $userId): array
    {
        return $this->usersTable->listTeacherCertificatesByUserId($userId);
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
        throw new RuntimeException('Trạng thái học viên trong lớp đã được chuẩn hóa mặc định là chính thức.');
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
        $enrollmentDate = trim((string) ($data['enrollment_date'] ?? ''));

        if ($studentId <= 0 || $classId <= 0) {
            throw new InvalidArgumentException('Dữ liệu đăng ký không hợp lệ.');
        }

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
                $this->classStudentsTable->enrollStudent($classId, $studentId, 'official', $enrollmentDate);
            }

            if (!$this->classStudentsTable->existsEnrollment($classId, $studentId)) {
                throw new RuntimeException('Không thể thêm học viên vào lớp đã chọn. Vui lòng thử lại.');
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
            'learning_status' => 'official',
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

    public function countNotifications(): int
    {
        return $this->notificationsTable->countDetailed();
    }

    public function listNotificationsPage(int $page, int $perPage): array
    {
        return $this->notificationsTable->listDetailedPage($page, $perPage);
    }

    public function findNotification(int $id): ?array
    {
        return $this->notificationsTable->findById($id);
    }

    public function saveNotification(array $data): void
    {
        $this->notificationsTable->save($data);
    }

    public function markNotificationRead(int $id): void
    {
        $this->notificationsTable->markRead($id);
    }

    public function deleteNotification(int $id): void
    {
        $this->notificationsTable->deleteById($id);
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

    public function findFeedback(int $id): ?array
    {
        return $this->feedbacksTable->findById($id);
    }
    public function listPublicFeedbacks(int $limit = 6): array
    {
        return $this->feedbacksTable->listPublicReviews($limit);
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

    public function listActivityRegistrations(int $activityId): array
    {
        return $this->activitiesTable->listRegistrationsByActivity($activityId);
    }

    public function removeActivityRegistration(int $activityId, int $userId): bool
    {
        return $this->activitiesTable->removeRegistration($activityId, $userId);
    }

    public function updateActivityRegistrationPayment(int $activityId, int $userId, string $paymentStatus, float $amountPaid, ?string $paymentDate): bool
    {
        return $this->activitiesTable->updateRegistrationPayment($activityId, $userId, $paymentStatus, $amountPaid, $paymentDate);
    }

    public function countRooms(): int
    {
        return $this->roomsTable->countDetailed();
    }

    public function listRoomsPage(int $page, int $perPage): array
    {
        return $this->roomsTable->listDetailedPage($page, $perPage);
    }

    public function findRoom(int $id): ?array
    {
        return $this->roomsTable->findById($id);
    }

    public function saveRoom(array $data): void
    {
        $this->roomsTable->save($data);
    }

    public function deleteRoom(int $id): void
    {
        $this->roomsTable->deleteById($id);
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

    public function countTuitionFees(string $searchQuery = ''): int
    {
        return $this->tuitionFeesTable->countDetailed($searchQuery);
    }

    public function listTuitionFeesPage(int $page, int $perPage, string $searchQuery = ''): array
    {
        return $this->tuitionFeesTable->listDetailedPage($page, $perPage, $searchQuery);
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

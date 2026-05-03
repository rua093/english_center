<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/tables/PermissionsTableModel.php';
require_once __DIR__ . '/tables/JobApplicationsTableModel.php';
require_once __DIR__ . '/tables/RolePermissionsTableModel.php';
require_once __DIR__ . '/tables/RolesTableModel.php';
require_once __DIR__ . '/tables/StudentLeadsTableModel.php';
require_once __DIR__ . '/tables/UsersTableModel.php';

final class AdminModel
{
    private UsersTableModel $usersTable;
    private RolesTableModel $rolesTable;
    private PermissionsTableModel $permissionsTable;
    private RolePermissionsTableModel $rolePermissionsTable;
    private StudentLeadsTableModel $studentLeadsTable;
    private JobApplicationsTableModel $jobApplicationsTable;

    public function __construct()
    {
        $this->usersTable = new UsersTableModel();
        $this->rolesTable = new RolesTableModel();
        $this->permissionsTable = new PermissionsTableModel();
        $this->rolePermissionsTable = new RolePermissionsTableModel();
        $this->studentLeadsTable = new StudentLeadsTableModel();
        $this->jobApplicationsTable = new JobApplicationsTableModel();
    }

    public function listUsers(): array
    {
        return $this->usersTable->listActiveWithRoles();
    }

    public function countUsers(): int
    {
        return $this->usersTable->countActiveWithRoles();
    }

    public function listUsersPage(int $page, int $perPage): array
    {
        return $this->usersTable->listActiveWithRolesPage($page, $perPage);
    }

    public function findUser(int $id): ?array
    {
        return $this->usersTable->findActiveById($id);
    }

    public function findRoleById(int $id): ?array
    {
        return $this->rolesTable->findById($id);
    }

    public function saveUser(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $password = (string) ($data['password'] ?? '');

        $role = $this->findRoleById((int) ($data['role_id'] ?? 0));
        if (!$role) {
            throw new RuntimeException('Vai trò không hợp lệ.');
        }

        $oldRoleName = '';
        if ($id > 0) {
            $existing = $this->findUser($id);
            $oldRoleName = is_array($existing) ? strtolower((string) ($existing['role_name'] ?? '')) : '';
        }

        $savedUserId = $this->usersTable->save($data);
        $newRoleName = strtolower((string) ($role['role_name'] ?? ''));
        $this->usersTable->saveRoleProfile($savedUserId, $newRoleName, $data);

        // Nếu đổi vai trò thì xóa profile của vai trò cũ để tránh tồn profile thừa
        if ($id > 0 && $oldRoleName !== '' && $oldRoleName !== $newRoleName) {
            $this->usersTable->removeRoleProfile($savedUserId, $oldRoleName);
        }

        if ($id > 0 && $password !== '') {
            $this->updateUserPassword($savedUserId, $password);
        }
    }

    public function softDeleteUser(int $id): void
    {
        $this->usersTable->softDelete($id);
    }

    public function listRoles(): array
    {
        return $this->rolesTable->listAll();
    }

    public function listPermissions(): array
    {
        return $this->permissionsTable->listAll();
    }

    public function dashboardOverviewData(array $filters = []): array
    {
        $months = $this->buildRecentMonthKeys(6);
        $studentMonthlyRows = $this->aggregateMonthlyUserCountsByRole('student', 6);
        $teacherMonthlyRows = $this->aggregateMonthlyUserCountsByRole('teacher', 6);

        $studentSeries = $this->mapMonthlyCounts($months, $studentMonthlyRows);
        $teacherSeries = $this->mapMonthlyCounts($months, $teacherMonthlyRows);

        $studentCurrentMonth = !empty($studentSeries['values']) ? (int) end($studentSeries['values']) : 0;
        $studentPreviousMonth = count($studentSeries['values']) >= 2 ? (int) $studentSeries['values'][count($studentSeries['values']) - 2] : 0;
        $teacherCurrentMonth = !empty($teacherSeries['values']) ? (int) end($teacherSeries['values']) : 0;
        $teacherPreviousMonth = count($teacherSeries['values']) >= 2 ? (int) $teacherSeries['values'][count($teacherSeries['values']) - 2] : 0;

        $leadSummary = $this->studentLeadConversionSummary();
        $applicationSummary = $this->jobApplicationConversionSummary();
        $feedbackSummary = $this->feedbackSummary();
        $classStatusSummary = $this->classStatusSummary();
        $periods = [
            'revenue' => $this->resolveDashboardPeriod($filters['revenue'] ?? [], 12),
            'growth' => $this->resolveDashboardPeriod($filters['growth'] ?? [], 6),
            'conversion' => $this->resolveDashboardPeriod($filters['conversion'] ?? [], 12),
            'feedback' => $this->resolveDashboardPeriod($filters['feedback'] ?? [], 12),
            'class_status' => $this->resolveDashboardPeriod($filters['class_status'] ?? [], 12),
            'tuition' => $this->resolveDashboardPeriod($filters['tuition'] ?? [], 12),
            'population' => $this->resolveDashboardPeriod($filters['population'] ?? [], 12),
            'class_size' => $this->resolveDashboardPeriod($filters['class_size'] ?? [], 12),
            'course_popularity' => $this->resolveDashboardPeriod($filters['course_popularity'] ?? [], 12),
        ];
        $growthPeriodSummary = $this->growthSummaryForPeriod($periods['growth']);

        return [
            'hero' => [
                'students_new' => $studentCurrentMonth,
                'students_delta' => $studentCurrentMonth - $studentPreviousMonth,
                'teachers_new' => $teacherCurrentMonth,
                'teachers_delta' => $teacherCurrentMonth - $teacherPreviousMonth,
                'lead_conversion_rate' => (float) ($leadSummary['conversion_rate'] ?? 0),
                'teacher_conversion_rate' => (float) ($applicationSummary['conversion_rate'] ?? 0),
                'avg_rating' => (float) ($feedbackSummary['avg_rating'] ?? 0),
                'feedback_total' => (int) ($feedbackSummary['total'] ?? 0),
            ],
            'periods' => $periods,
            'growth' => [
                'labels' => $growthPeriodSummary['labels'],
                'students' => $growthPeriodSummary['students'],
                'teachers' => $growthPeriodSummary['teachers'],
                'student_current_month' => $studentCurrentMonth,
                'student_previous_month' => $studentPreviousMonth,
                'teacher_current_month' => $teacherCurrentMonth,
                'teacher_previous_month' => $teacherPreviousMonth,
                'period_label' => $periods['growth']['label'],
            ],
            'lead_conversion' => $this->studentLeadConversionSummaryForPeriod($periods['conversion']),
            'teacher_conversion' => $this->jobApplicationConversionSummaryForPeriod($periods['conversion']),
            'feedback' => $this->feedbackSummaryForPeriod($periods['feedback']),
            'class_status' => $this->classStatusSummaryForPeriod($periods['class_status']),
            'revenue_history' => $this->revenueHistorySummaryForPeriod($periods['revenue']),
            'tuition' => $this->tuitionSummaryForPeriod($periods['tuition']),
            'population' => $this->populationSummaryForPeriod($periods['population']),
            'class_size_distribution' => $this->classSizeDistributionSummaryForPeriod($periods['class_size']),
            'course_popularity' => $this->coursePopularitySummaryForPeriod($periods['course_popularity']),
        ];
    }

    public function rolePermissionMap(): array
    {
        return $this->rolePermissionsTable->mapByRole();
    }

    public function saveRolePermissions(int $roleId, array $permissionIds): void
    {
        $this->rolePermissionsTable->replaceForRole($roleId, $permissionIds);
    }

    public function countStudentLeads(?string $statusFilter = null): int
    {
        return $this->studentLeadsTable->countDetailed($statusFilter);
    }

    public function listStudentLeadsPage(int $page, int $perPage, ?string $statusFilter = null): array
    {
        return $this->studentLeadsTable->listDetailedPage($page, $perPage, $statusFilter);
    }

    public function findStudentLead(int $id): ?array
    {
        return $this->studentLeadsTable->findById($id);
    }

    public function saveConsultationLead(array $data): int
    {
        return $this->studentLeadsTable->saveConsultationLead($data);
    }

    public function submitStudentLead(array $data): int
    {
        return $this->studentLeadsTable->createFromPublic($data);
    }

    public function updateStudentLeadReview(int $id, string $status, string $adminNote): void
    {
        $lead = $this->studentLeadsTable->findById($id);
        if (!$lead) {
            throw new RuntimeException('Khong tim thay lead hoc vien.');
        }

        $this->studentLeadsTable->updateReview($id, $status, $adminNote);
    }

    public function deleteStudentLead(int $id): void
    {
        $this->studentLeadsTable->deleteById($id);
    }

    public function convertStudentLeadToUser(int $leadId, array $options = []): array
    {
        $lead = $this->studentLeadsTable->findById($leadId);
        if (!$lead) {
            throw new RuntimeException('Khong tim thay lead hoc vien.');
        }

        if ((int) ($lead['converted_user_id'] ?? 0) > 0) {
            throw new RuntimeException('Lead nay da duoc chuyen thanh tai khoan hoc vien.');
        }

        $leadStatus = strtolower(trim((string) ($lead['status'] ?? 'new')));
        if (!in_array($leadStatus, ['trial_completed', 'official'], true)) {
            throw new RuntimeException('Chi duoc tao tai khoan sau khi hoan tat hoc thu.');
        }

        $studentRole = $this->rolesTable->findByRoleName('student');
        if (!$studentRole) {
            throw new RuntimeException('He thong chua co role student.');
        }

        $providedUsername = trim((string) ($options['username'] ?? ''));
        $seedUsername = $providedUsername !== ''
            ? $providedUsername
            : $this->buildUsernameSeed($lead, 'student');
        $username = $this->ensureUniqueUsername($seedUsername, 'student');

        $providedPassword = trim((string) ($options['password'] ?? ''));
        $password = $providedPassword !== '' ? $providedPassword : '123456';
        $adminNote = trim((string) ($options['admin_note'] ?? ''));

        $parentName = trim((string) ($lead['parent_name'] ?? ''));
        $leadPhone = trim((string) ($lead['parent_phone'] ?? ''));
        if ($leadPhone === '') {
            $leadPhone = $this->extractPhoneFromText((string) ($lead['parent_name'] ?? '') . ' ' . (string) ($lead['school_name'] ?? ''));
        }
        $leadEmail = $this->extractEmailFromText((string) ($lead['parent_name'] ?? '') . ' ' . (string) ($lead['school_name'] ?? ''));

        $payload = [
            'username' => $username,
            'password' => $password,
            'full_name' => trim((string) ($lead['student_name'] ?? '')),
            'role_id' => (int) ($studentRole['id'] ?? 0),
            'phone' => $leadPhone,
            'email' => $leadEmail,
            'status' => 'active',
            'student_parent_name' => trim((string) $parentName),
            'student_parent_phone' => $leadPhone,
            'student_school_name' => trim((string) ($lead['school_name'] ?? '')),
            'student_target_score' => trim((string) (($lead['current_level'] ?? '') !== '' ? $lead['current_level'] : ($lead['study_time'] ?? ''))),
            'student_entry_test_id' => 0,
        ];

        $createdUserId = 0;
        $this->usersTable->executeInTransaction(function () use ($payload, $leadId, $adminNote, &$createdUserId): void {
            $createdUserId = $this->usersTable->save($payload);
            $this->usersTable->saveRoleProfile($createdUserId, 'student', $payload);
            $this->studentLeadsTable->markConverted($leadId, $createdUserId, $adminNote);
        });

        return [
            'user_id' => $createdUserId,
            'username' => $username,
            'password' => $password,
            'used_default_password' => $providedPassword === '',
        ];
    }

    public function countJobApplications(?string $statusFilter = null): int
    {
        return $this->jobApplicationsTable->countDetailed($statusFilter);
    }

    public function listJobApplicationsPage(int $page, int $perPage, ?string $statusFilter = null): array
    {
        return $this->jobApplicationsTable->listDetailedPage($page, $perPage, $statusFilter);
    }

    public function findJobApplication(int $id): ?array
    {
        return $this->jobApplicationsTable->findById($id);
    }

    public function submitJobApplication(array $data): int
    {
        return $this->jobApplicationsTable->createFromPublic($data);
    }

    public function updateJobApplicationReview(int $id, string $status, string $adminNote): void
    {
        $application = $this->jobApplicationsTable->findById($id);
        if (!$application) {
            throw new RuntimeException('Khong tim thay ho so ung tuyen.');
        }

        $this->jobApplicationsTable->updateReview($id, $status, $adminNote);
    }

    public function deleteJobApplication(int $id): void
    {
        $this->jobApplicationsTable->deleteById($id);
    }

    public function convertJobApplicationToUser(int $applicationId, array $options = []): array
    {
        $application = $this->jobApplicationsTable->findById($applicationId);
        if (!$application) {
            throw new RuntimeException('Khong tim thay ho so ung tuyen.');
        }

        if ((int) ($application['converted_user_id'] ?? 0) > 0) {
            throw new RuntimeException('Ho so nay da duoc chuyen thanh tai khoan giao vien.');
        }

        $applicationStatus = strtoupper(trim((string) ($application['status'] ?? 'PENDING')));
        if (!in_array($applicationStatus, ['INTERVIEWING', 'PASSED'], true)) {
            throw new RuntimeException('Chi duoc tao tai khoan sau khi phong van dat.');
        }

        $teacherRole = $this->rolesTable->findByRoleName('teacher');
        if (!$teacherRole) {
            throw new RuntimeException('He thong chua co role teacher.');
        }

        $providedUsername = trim((string) ($options['username'] ?? ''));
        $seedUsername = $providedUsername !== ''
            ? $providedUsername
            : $this->buildUsernameSeed($application, 'teacher');
        $username = $this->ensureUniqueUsername($seedUsername, 'teacher');

        $providedPassword = trim((string) ($options['password'] ?? ''));
        $password = $providedPassword !== '' ? $providedPassword : '123456';
        $adminNote = trim((string) ($options['admin_note'] ?? ''));

        $applicationPhone = trim((string) ($application['phone'] ?? ''));
        if ($applicationPhone === '') {
            $applicationPhone = $this->extractPhoneFromText((string) ($application['work_history'] ?? ''));
        }

        $applicationEmail = trim((string) ($application['email'] ?? ''));
        if ($applicationEmail === '') {
            $applicationEmail = $this->extractEmailFromText((string) ($application['work_history'] ?? ''));
        }

        $experienceYears = max(0, (int) ($application['experience_years'] ?? 0));

        $payload = [
            'username' => $username,
            'password' => $password,
            'full_name' => trim((string) ($application['full_name'] ?? '')),
            'role_id' => (int) ($teacherRole['id'] ?? 0),
            'phone' => $applicationPhone,
            'email' => $applicationEmail,
            'status' => 'active',
            'teacher_degree' => trim((string) ($application['education_detail'] ?? '')),
            'teacher_experience_years' => $experienceYears,
            'teacher_bio' => trim((string) ($application['bio_summary'] ?? '')),
            'teacher_intro_video_url' => '',
        ];

        $createdUserId = 0;
        $this->usersTable->executeInTransaction(function () use ($payload, $applicationId, $adminNote, &$createdUserId): void {
            $createdUserId = $this->usersTable->save($payload);
            $this->usersTable->saveRoleProfile($createdUserId, 'teacher', $payload);
            $this->jobApplicationsTable->markConverted($applicationId, $createdUserId, $adminNote);
        });

        return [
            'user_id' => $createdUserId,
            'username' => $username,
            'password' => $password,
            'used_default_password' => $providedPassword === '',
        ];
    }

    private function updateUserPassword(int $id, string $plainPassword): void
    {
        $this->usersTable->updatePassword($id, $plainPassword);
    }

    private function aggregateMonthlyUserCountsByRole(string $roleName, int $limit): array
    {
        $limit = max(1, min(24, $limit));
        $pdo = Database::connection();
        $sql = "SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS month_key, COUNT(*) AS total
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.role_name = :role_name
              AND u.deleted_at IS NULL
            GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
            ORDER BY month_key DESC
            LIMIT {$limit}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['role_name' => $roleName]);

        return $stmt->fetchAll() ?: [];
    }

    private function studentLeadConversionSummary(): array
    {
        $pdo = Database::connection();
        $row = $pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) AS converted,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
             FROM student_leads"
        )->fetch();

        $total = (int) ($row['total'] ?? 0);
        $converted = (int) ($row['converted'] ?? 0);
        $cancelled = (int) ($row['cancelled'] ?? 0);

        return [
            'total' => $total,
            'converted' => $converted,
            'unconverted' => max(0, $total - $converted),
            'cancelled' => $cancelled,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
        ];
    }

    private function jobApplicationConversionSummary(): array
    {
        $pdo = Database::connection();
        $row = $pdo->query(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) AS converted,
                    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) AS rejected
             FROM job_applications"
        )->fetch();

        $total = (int) ($row['total'] ?? 0);
        $converted = (int) ($row['converted'] ?? 0);
        $rejected = (int) ($row['rejected'] ?? 0);

        return [
            'total' => $total,
            'converted' => $converted,
            'pending' => max(0, $total - $converted),
            'rejected' => $rejected,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
        ];
    }

    private function feedbackSummary(): array
    {
        $pdo = Database::connection();
        $summaryRow = $pdo->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(AVG(rating), 0) AS avg_rating,
                    SUM(CASE WHEN is_public_web = 1 THEN 1 ELSE 0 END) AS public_total
             FROM feedbacks"
        )->fetch();

        $distributionRows = $pdo->query(
            "SELECT rating, COUNT(*) AS total
             FROM feedbacks
             GROUP BY rating
             ORDER BY rating ASC"
        )->fetchAll() ?: [];

        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];

        foreach ($distributionRows as $row) {
            $rating = (int) ($row['rating'] ?? 0);
            if (!isset($distribution[$rating])) {
                continue;
            }

            $distribution[$rating] = (int) ($row['total'] ?? 0);
        }

        $total = (int) ($summaryRow['total'] ?? 0);
        $publicTotal = (int) ($summaryRow['public_total'] ?? 0);

        return [
            'total' => $total,
            'avg_rating' => round((float) ($summaryRow['avg_rating'] ?? 0), 1),
            'public_total' => $publicTotal,
            'public_rate' => $total > 0 ? round(($publicTotal / $total) * 100, 1) : 0.0,
            'distribution' => array_values($distribution),
        ];
    }

    private function classStatusSummary(): array
    {
        $pdo = Database::connection();
        $rows = $pdo->query(
            "SELECT status, COUNT(*) AS total
             FROM classes
             GROUP BY status"
        )->fetchAll() ?: [];

        $summary = [
            'upcoming' => 0,
            'active' => 0,
            'graduated' => 0,
            'cancelled' => 0,
        ];

        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row['status'] ?? '')));
            if (!isset($summary[$status])) {
                continue;
            }

            $summary[$status] = (int) ($row['total'] ?? 0);
        }

        return $summary;
    }

    private function resolveDashboardPeriod(array $input, int $defaultMonths): array
    {
        $defaultMonths = max(1, min(24, $defaultMonths));
        $currentMonth = new DateTimeImmutable('first day of this month');
        $defaultStart = $currentMonth->modify(sprintf('-%d month', $defaultMonths - 1))->format('Y-m');
        $defaultEnd = $currentMonth->format('Y-m');

        $startMonth = $this->normalizeMonthInput((string) ($input['start_month'] ?? '')) ?? $defaultStart;
        $endMonth = $this->normalizeMonthInput((string) ($input['end_month'] ?? '')) ?? $defaultEnd;

        if (strcmp($startMonth, $endMonth) > 0) {
            [$startMonth, $endMonth] = [$endMonth, $startMonth];
        }

        $startDate = $startMonth . '-01';
        $endDate = (new DateTimeImmutable($endMonth . '-01'))->modify('last day of this month')->format('Y-m-d');

        $startLabel = DateTimeImmutable::createFromFormat('Y-m', $startMonth);
        $endLabel = DateTimeImmutable::createFromFormat('Y-m', $endMonth);

        return [
            'start_month' => $startMonth,
            'end_month' => $endMonth,
            'start_at' => $startDate . ' 00:00:00',
            'end_at' => $endDate . ' 23:59:59',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month_keys' => $this->buildMonthKeysBetween($startMonth, $endMonth),
            'label' => ($startLabel instanceof DateTimeImmutable ? $startLabel->format('m/Y') : $startMonth)
                . ' - '
                . ($endLabel instanceof DateTimeImmutable ? $endLabel->format('m/Y') : $endMonth),
        ];
    }

    private function normalizeMonthInput(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m', $value);
        if (!$date instanceof DateTimeImmutable) {
            return null;
        }

        return $date->format('Y-m');
    }

    private function buildMonthKeysBetween(string $startMonth, string $endMonth): array
    {
        $months = [];
        $cursor = new DateTimeImmutable($startMonth . '-01');
        $last = new DateTimeImmutable($endMonth . '-01');

        while ($cursor <= $last) {
            $months[] = $cursor->format('Y-m');
            $cursor = $cursor->modify('+1 month');
        }

        return $months;
    }

    private function growthSummaryForPeriod(array $period): array
    {
        $studentRows = $this->aggregateMonthlyUserCountsByRange('student', $period['start_at'], $period['end_at']);
        $teacherRows = $this->aggregateMonthlyUserCountsByRange('teacher', $period['start_at'], $period['end_at']);
        $studentSeries = $this->mapMonthlyCounts($period['month_keys'], $studentRows);
        $teacherSeries = $this->mapMonthlyCounts($period['month_keys'], $teacherRows);

        return [
            'labels' => $studentSeries['labels'],
            'students' => $studentSeries['values'],
            'teachers' => $teacherSeries['values'],
        ];
    }

    private function aggregateMonthlyUserCountsByRange(string $roleName, string $startAt, string $endAt): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS month_key, COUNT(*) AS total
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.role_name = :role_name
               AND u.deleted_at IS NULL
               AND u.created_at BETWEEN :start_at AND :end_at
             GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
             ORDER BY month_key ASC"
        );
        $stmt->execute([
            'role_name' => $roleName,
            'start_at' => $startAt,
            'end_at' => $endAt,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    private function studentLeadConversionSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) AS converted,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
             FROM student_leads
             WHERE created_at BETWEEN :start_at AND :end_at"
        );
        $stmt->execute([
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
        ]);
        $row = $stmt->fetch();

        $total = (int) ($row['total'] ?? 0);
        $converted = (int) ($row['converted'] ?? 0);
        $cancelled = (int) ($row['cancelled'] ?? 0);

        return [
            'total' => $total,
            'converted' => $converted,
            'unconverted' => max(0, $total - $converted),
            'cancelled' => $cancelled,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
        ];
    }

    private function jobApplicationConversionSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN converted_user_id IS NOT NULL THEN 1 ELSE 0 END) AS converted,
                    SUM(CASE WHEN status = 'REJECTED' THEN 1 ELSE 0 END) AS rejected
             FROM job_applications
             WHERE created_at BETWEEN :start_at AND :end_at"
        );
        $stmt->execute([
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
        ]);
        $row = $stmt->fetch();

        $total = (int) ($row['total'] ?? 0);
        $converted = (int) ($row['converted'] ?? 0);
        $rejected = (int) ($row['rejected'] ?? 0);

        return [
            'total' => $total,
            'converted' => $converted,
            'pending' => max(0, $total - $converted),
            'rejected' => $rejected,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
        ];
    }

    private function feedbackSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $summaryStmt = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(AVG(rating), 0) AS avg_rating,
                    SUM(CASE WHEN is_public_web = 1 THEN 1 ELSE 0 END) AS public_total
             FROM feedbacks
             WHERE created_at BETWEEN :start_at AND :end_at"
        );
        $summaryStmt->execute([
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
        ]);
        $summaryRow = $summaryStmt->fetch();

        $distributionStmt = $pdo->prepare(
            "SELECT rating, COUNT(*) AS total
             FROM feedbacks
             WHERE created_at BETWEEN :start_at_filter AND :end_at_filter
             GROUP BY rating
             ORDER BY rating ASC"
        );
        $distributionStmt->execute([
            'start_at_filter' => $period['start_at'],
            'end_at_filter' => $period['end_at'],
        ]);
        $distributionRows = $distributionStmt->fetchAll() ?: [];

        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($distributionRows as $row) {
            $rating = (int) ($row['rating'] ?? 0);
            if (isset($distribution[$rating])) {
                $distribution[$rating] = (int) ($row['total'] ?? 0);
            }
        }

        $total = (int) ($summaryRow['total'] ?? 0);
        $publicTotal = (int) ($summaryRow['public_total'] ?? 0);

        return [
            'total' => $total,
            'avg_rating' => round((float) ($summaryRow['avg_rating'] ?? 0), 1),
            'public_total' => $publicTotal,
            'public_rate' => $total > 0 ? round(($publicTotal / $total) * 100, 1) : 0.0,
            'distribution' => array_values($distribution),
        ];
    }

    private function classStatusSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT status, COUNT(*) AS total
             FROM classes
             WHERE COALESCE(start_date, DATE(created_at)) BETWEEN :start_date AND :end_date
             GROUP BY status"
        );
        $stmt->execute([
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
        ]);
        $rows = $stmt->fetchAll() ?: [];

        $summary = ['upcoming' => 0, 'active' => 0, 'graduated' => 0, 'cancelled' => 0];
        foreach ($rows as $row) {
            $status = strtolower(trim((string) ($row['status'] ?? '')));
            if (isset($summary[$status])) {
                $summary[$status] = (int) ($row['total'] ?? 0);
            }
        }

        $summary['total_classes'] = array_sum($summary);

        return $summary;
    }

    private function revenueHistorySummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    COALESCE(SUM(CASE WHEN transaction_status = 'success' THEN amount ELSE 0 END), 0) AS total
             FROM payment_transactions
             WHERE created_at BETWEEN :start_at AND :end_at
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month_key ASC"
        );
        $stmt->execute([
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
        ]);
        $rows = $stmt->fetchAll() ?: [];
        $series = $this->mapMonthlyCounts($period['month_keys'], $rows);
        $labels = [];
        foreach ($period['month_keys'] as $monthKey) {
            $date = DateTimeImmutable::createFromFormat('Y-m', $monthKey);
            $labels[] = $date instanceof DateTimeImmutable ? $date->format('m/Y') : $monthKey;
        }

        $values = $series['values'];
        $latest = !empty($values) ? (float) end($values) : 0.0;
        $previous = count($values) >= 2 ? (float) $values[count($values) - 2] : 0.0;

        return [
            'labels' => $labels,
            'values' => $values,
            'latest' => $latest,
            'previous' => $previous,
        ];
    }

    private function tuitionSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(total_amount), 0) AS total_amount,
                    COALESCE(SUM(amount_paid), 0) AS amount_paid
             FROM tuition_fees
             WHERE created_at BETWEEN :start_at AND :end_at"
        );
        $stmt->execute([
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
        ]);
        $row = $stmt->fetch();

        $totalAmount = (float) ($row['total_amount'] ?? 0);
        $amountPaid = (float) ($row['amount_paid'] ?? 0);
        $amountDebt = max(0, $totalAmount - $amountPaid);

        return [
            'total_amount' => $totalAmount,
            'amount_paid' => $amountPaid,
            'amount_debt' => $amountDebt,
            'collection_rate' => $totalAmount > 0 ? round(($amountPaid / $totalAmount) * 100, 1) : 0.0,
        ];
    }

    private function populationSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = 'student' AND u.deleted_at IS NULL AND u.created_at BETWEEN :student_start_at AND :student_end_at) AS total_students,
                (SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = 'teacher' AND u.deleted_at IS NULL AND u.created_at BETWEEN :teacher_start_at AND :teacher_end_at) AS total_teachers,
                (SELECT COUNT(*) FROM classes c WHERE COALESCE(c.start_date, DATE(c.created_at)) BETWEEN :class_start_date AND :class_end_date) AS total_classes,
                (SELECT COUNT(*) FROM courses c WHERE c.deleted_at IS NULL AND c.created_at BETWEEN :course_start_at AND :course_end_at) AS total_courses,
                (SELECT COUNT(*) FROM class_students cs WHERE COALESCE(cs.enrollment_date, DATE(cs.created_at)) BETWEEN :enrollment_start_date AND :enrollment_end_date) AS total_enrollments"
        );
        $stmt->execute([
            'student_start_at' => $period['start_at'],
            'student_end_at' => $period['end_at'],
            'teacher_start_at' => $period['start_at'],
            'teacher_end_at' => $period['end_at'],
            'class_start_date' => $period['start_date'],
            'class_end_date' => $period['end_date'],
            'course_start_at' => $period['start_at'],
            'course_end_at' => $period['end_at'],
            'enrollment_start_date' => $period['start_date'],
            'enrollment_end_date' => $period['end_date'],
        ]);
        $row = $stmt->fetch();

        $totalClasses = (int) ($row['total_classes'] ?? 0);
        $totalEnrollments = (int) ($row['total_enrollments'] ?? 0);

        return [
            'total_students' => (int) ($row['total_students'] ?? 0),
            'total_teachers' => (int) ($row['total_teachers'] ?? 0),
            'total_classes' => $totalClasses,
            'total_courses' => (int) ($row['total_courses'] ?? 0),
            'avg_students_per_class' => $totalClasses > 0 ? round($totalEnrollments / $totalClasses, 1) : 0.0,
        ];
    }

    private function classSizeDistributionSummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT c.id, COUNT(cs.id) AS student_total
             FROM classes c
             LEFT JOIN class_students cs
               ON cs.class_id = c.id
              AND COALESCE(cs.enrollment_date, DATE(cs.created_at)) BETWEEN :enrollment_start_date AND :enrollment_end_date
             WHERE COALESCE(c.start_date, DATE(c.created_at)) BETWEEN :class_start_date AND :class_end_date
             GROUP BY c.id"
        );
        $stmt->execute([
            'enrollment_start_date' => $period['start_date'],
            'enrollment_end_date' => $period['end_date'],
            'class_start_date' => $period['start_date'],
            'class_end_date' => $period['end_date'],
        ]);
        $rows = $stmt->fetchAll() ?: [];

        $bands = [
            '0-5 học viên' => 0,
            '6-10 học viên' => 0,
            '11-15 học viên' => 0,
            '16+ học viên' => 0,
        ];

        foreach ($rows as $row) {
            $studentTotal = (int) ($row['student_total'] ?? 0);
            if ($studentTotal <= 5) {
                $bands['0-5 học viên']++;
            } elseif ($studentTotal <= 10) {
                $bands['6-10 học viên']++;
            } elseif ($studentTotal <= 15) {
                $bands['11-15 học viên']++;
            } else {
                $bands['16+ học viên']++;
            }
        }

        return [
            'labels' => array_keys($bands),
            'values' => array_values($bands),
        ];
    }

    private function coursePopularitySummaryForPeriod(array $period): array
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            "SELECT co.course_name,
                    COUNT(DISTINCT c.id) AS class_count,
                    COUNT(cs.id) AS enrollment_count
             FROM courses co
             LEFT JOIN classes c
               ON c.course_id = co.id
              AND COALESCE(c.start_date, DATE(c.created_at)) BETWEEN :class_start_date AND :class_end_date
             LEFT JOIN class_students cs
               ON cs.class_id = c.id
              AND COALESCE(cs.enrollment_date, DATE(cs.created_at)) BETWEEN :enrollment_start_date AND :enrollment_end_date
             WHERE co.deleted_at IS NULL
             GROUP BY co.id, co.course_name
             ORDER BY enrollment_count DESC, class_count DESC, co.course_name ASC
             LIMIT 8"
        );
        $stmt->execute([
            'class_start_date' => $period['start_date'],
            'class_end_date' => $period['end_date'],
            'enrollment_start_date' => $period['start_date'],
            'enrollment_end_date' => $period['end_date'],
        ]);
        $rows = $stmt->fetchAll() ?: [];

        $labels = [];
        $classCounts = [];
        $enrollmentCounts = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($row['course_name'] ?? 'Khóa học');
            $classCounts[] = (int) ($row['class_count'] ?? 0);
            $enrollmentCounts[] = (int) ($row['enrollment_count'] ?? 0);
        }

        return [
            'labels' => $labels,
            'class_counts' => $classCounts,
            'enrollment_counts' => $enrollmentCounts,
        ];
    }

    private function buildRecentMonthKeys(int $limit): array
    {
        $limit = max(1, min(24, $limit));
        $months = [];
        $currentMonth = new DateTimeImmutable('first day of this month');

        for ($offset = $limit - 1; $offset >= 0; $offset--) {
            $month = $currentMonth->modify(sprintf('-%d month', $offset));
            $months[] = $month->format('Y-m');
        }

        return $months;
    }

    private function mapMonthlyCounts(array $monthKeys, array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $key = (string) ($row['month_key'] ?? '');
            if ($key === '') {
                continue;
            }

            $map[$key] = (int) ($row['total'] ?? 0);
        }

        $labels = [];
        $values = [];
        foreach ($monthKeys as $monthKey) {
            $parsedMonth = DateTimeImmutable::createFromFormat('Y-m', (string) $monthKey);
            $labels[] = $parsedMonth instanceof DateTimeImmutable ? ('T' . $parsedMonth->format('m')) : (string) $monthKey;
            $values[] = (int) ($map[(string) $monthKey] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function buildUsernameSeed(array $record, string $prefix): string
    {
        $emailCandidates = [
            strtolower(trim((string) ($record['email'] ?? ''))),
            $this->extractEmailFromText((string) ($record['work_history'] ?? ($record['bio_summary'] ?? ''))),
            $this->extractEmailFromText((string) ($record['parent_name'] ?? '')),
        ];

        foreach ($emailCandidates as $email) {
            if ($email !== '' && str_contains($email, '@')) {
                $localPart = (string) strstr($email, '@', true);
                if ($localPart !== '') {
                    return $localPart;
                }
            }
        }

        $phoneCandidates = [
            preg_replace('/\D+/', '', (string) ($record['phone'] ?? '')),
            preg_replace('/\D+/', '', (string) ($record['parent_phone'] ?? '')),
            $this->extractPhoneFromText((string) ($record['work_history'] ?? ($record['bio_summary'] ?? ''))),
            $this->extractPhoneFromText((string) ($record['parent_name'] ?? '')),
        ];

        foreach ($phoneCandidates as $phone) {
            if (is_string($phone) && $phone !== '') {
                return $prefix . substr($phone, -6);
            }
        }

        $name = trim((string) ($record['student_name'] ?? ($record['full_name'] ?? '')));
        if ($name !== '') {
            return $prefix . '.' . strtolower(preg_replace('/\s+/', '.', $name) ?? $name);
        }

        return $prefix . '.' . date('ymdHis');
    }

    private function extractEmailFromText(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        if (preg_match('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $normalized, $matches) === 1) {
            return strtolower((string) ($matches[0] ?? ''));
        }

        return '';
    }

    private function extractPhoneFromText(string $value): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return '';
        }

        if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $normalized, $matches) !== 1) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', (string) ($matches[0] ?? ''));
        if (!is_string($digits) || strlen($digits) < 8) {
            return '';
        }

        return $digits;
    }

    private function extractFirstInteger(string $value): int
    {
        if (preg_match('/\d+/', $value, $matches) === 1) {
            return max(0, (int) ($matches[0] ?? 0));
        }

        return 0;
    }

    private function ensureUniqueUsername(string $candidate, string $fallbackPrefix): string
    {
        $base = $this->normalizeUsername($candidate);
        if ($base === '') {
            $base = $this->normalizeUsername($fallbackPrefix . '.' . date('ymdHis'));
        }

        if ($base === '') {
            $base = 'user.' . date('ymdHis');
        }

        $base = substr($base, 0, 110);
        $resolved = $base;
        $counter = 1;

        while ($this->usersTable->usernameExists($resolved)) {
            $suffix = '.' . $counter;
            $resolved = substr($base, 0, 120 - strlen($suffix)) . $suffix;
            $counter++;

            if ($counter > 5000) {
                throw new RuntimeException('Khong the tao username duy nhat.');
            }
        }

        return $resolved;
    }

    private function normalizeUsername(string $value): string
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/[^a-z0-9._-]+/', '.', $normalized) ?? '';
        $normalized = trim($normalized, '.-_');

        return $normalized;
    }
}

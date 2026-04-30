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

    public function dashboardOverviewData(): array
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

        return [
            'growth' => [
                'labels' => $studentSeries['labels'],
                'students' => $studentSeries['values'],
                'teachers' => $teacherSeries['values'],
                'student_current_month' => $studentCurrentMonth,
                'student_previous_month' => $studentPreviousMonth,
                'teacher_current_month' => $teacherCurrentMonth,
                'teacher_previous_month' => $teacherPreviousMonth,
            ],
            'lead_conversion' => $leadSummary,
            'teacher_conversion' => $applicationSummary,
            'feedback' => $feedbackSummary,
            'class_status' => $classStatusSummary,
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

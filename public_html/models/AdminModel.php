<?php
declare(strict_types=1);

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

        $savedUserId = $this->usersTable->save($data);
        $this->usersTable->saveRoleProfile($savedUserId, (string) ($role['role_name'] ?? ''), $data);

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

        $payload = [
            'username' => $username,
            'password' => $password,
            'full_name' => trim((string) ($lead['full_name'] ?? '')),
            'role_id' => (int) ($studentRole['id'] ?? 0),
            'phone' => trim((string) ($lead['phone'] ?? '')),
            'email' => trim((string) ($lead['email'] ?? '')),
            'status' => 'active',
            'student_parent_name' => trim((string) ($lead['parent_name'] ?? '')),
            'student_parent_phone' => trim((string) ($lead['parent_phone'] ?? '')),
            'student_school_name' => trim((string) ($lead['school_name'] ?? '')),
            'student_target_score' => trim((string) (($lead['target_score'] ?? '') !== '' ? $lead['target_score'] : ($lead['target_program'] ?? ''))),
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

    public function convertJobApplicationToUser(int $applicationId, array $options = []): array
    {
        $application = $this->jobApplicationsTable->findById($applicationId);
        if (!$application) {
            throw new RuntimeException('Khong tim thay ho so ung tuyen.');
        }

        if ((int) ($application['converted_user_id'] ?? 0) > 0) {
            throw new RuntimeException('Ho so nay da duoc chuyen thanh tai khoan giao vien.');
        }

        $applicationStatus = strtolower(trim((string) ($application['status'] ?? 'new')));
        if (!in_array($applicationStatus, ['interviewed', 'official'], true)) {
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

        $payload = [
            'username' => $username,
            'password' => $password,
            'full_name' => trim((string) ($application['full_name'] ?? '')),
            'role_id' => (int) ($teacherRole['id'] ?? 0),
            'phone' => trim((string) ($application['phone'] ?? '')),
            'email' => trim((string) ($application['email'] ?? '')),
            'status' => 'active',
            'teacher_degree' => trim((string) ($application['degree'] ?? '')),
            'teacher_experience_years' => max(0, (int) ($application['experience_years'] ?? 0)),
            'teacher_bio' => trim((string) ($application['intro'] ?? '')),
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

    private function buildUsernameSeed(array $record, string $prefix): string
    {
        $email = strtolower(trim((string) ($record['email'] ?? '')));
        if ($email !== '' && str_contains($email, '@')) {
            $localPart = (string) strstr($email, '@', true);
            if ($localPart !== '') {
                return $localPart;
            }
        }

        $phone = preg_replace('/\D+/', '', (string) ($record['phone'] ?? ''));
        if (is_string($phone) && $phone !== '') {
            return $prefix . substr($phone, -6);
        }

        return $prefix . '.' . date('ymdHis');
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

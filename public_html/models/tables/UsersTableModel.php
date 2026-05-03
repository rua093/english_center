<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class UsersTableModel extends BaseTableModel
{
    public function countActiveWithRoles(): int
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM users u WHERE u.deleted_at IS NULL',
            [],
            'total',
            0
        );
    }

    public function listActiveWithRoles(): array
    {
        $sql = "SELECT u.id, u.username, u.full_name, u.phone, u.email, u.avatar, u.status, u.created_at, u.role_id,
                r.role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.deleted_at IS NULL
            ORDER BY u.id DESC";
        return $this->fetchAll($sql);
    }

    public function listActiveWithRolesPage(int $page, int $perPage): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $sql = "SELECT u.id, u.username, u.full_name, u.phone, u.email, u.avatar, u.status, u.created_at, u.role_id,
                r.role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.deleted_at IS NULL
            ORDER BY u.id DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql);
    }

    public function findActiveById(int $id): ?array
    {
        $user = $this->fetchOne(
                "SELECT u.id, u.username, u.full_name, u.role_id, u.phone, u.email, u.avatar, u.status, u.created_at,
                    r.role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id AND u.deleted_at IS NULL
             LIMIT 1",
            ['id' => $id]
        );

        if (!$user) {
            return null;
        }

        $user['role_profile'] = $this->findRoleProfile((int) ($user['id'] ?? 0), (string) ($user['role_name'] ?? ''));
        return $user;
    }

    public function updateProfile(int $userId, array $data): void
    {
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $avatar = trim((string) ($data['avatar'] ?? ''));

        $params = [
            'id' => $userId,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email !== '' ? $email : null,
        ];

        $sql = 'UPDATE users
             SET phone = :phone,
                 email = :email';

        if ($avatar !== '') {
            $sql .= ', avatar = :avatar';
            $params['avatar'] = $avatar;
        }

        $sql .= ' WHERE id = :id AND deleted_at IS NULL';

        $this->executeStatement($sql, $params);
    }

    public function updateTeacherProfile(int $userId, array $data): void
    {
        $this->saveTeacherProfile($userId, $data);
    }

    public function listTeacherCertificatesByUserId(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $sql = 'SELECT tc.id, tc.certificate_name, tc.score, tc.image_url
            FROM teacher_certificates tc
            INNER JOIN teacher_profiles tp ON tp.id = tc.teacher_id
            INNER JOIN users u ON u.id = tp.user_id
            WHERE u.id = :user_id
            ORDER BY tc.id ASC';

        return $this->fetchAll($sql, ['user_id' => $userId]);
    }

    public function save(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $username = trim((string) ($data['username'] ?? ''));
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $roleId = (int) ($data['role_id'] ?? 0);
        $status = (string) ($data['status'] ?? 'active');

        if ($id > 0) {
            $sql = "UPDATE users
                SET username = :username,
                    full_name = :full_name,
                    role_id = :role_id,
                    phone = :phone,
                    email = :email,
                    status = :status
                WHERE id = :id";
            $this->executeStatement($sql, [
                'id' => $id,
                'username' => $username,
                'full_name' => $fullName,
                'role_id' => $roleId,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'status' => $status,
            ]);
            return $id;
        }

        $password = (string) ($data['password'] ?? '');
        $passwordHash = password_hash($password !== '' ? $password : '123456', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, full_name, role_id, phone, email, status)
            VALUES (:username, :password, :full_name, :role_id, :phone, :email, :status)";
        $this->executeStatement($sql, [
            'username' => $username,
            'password' => $passwordHash,
            'full_name' => $fullName,
            'role_id' => $roleId,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email !== '' ? $email : null,
            'status' => $status,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function saveRoleProfile(int $userId, string $roleName, array $data): void
    {
        if ($userId <= 0) {
            return;
        }

        $normalizedRole = strtolower(trim($roleName));
        if ($normalizedRole === 'staff') {
            $this->saveStaffProfile($userId, $data);
            return;
        }

        if ($normalizedRole === 'teacher') {
            $this->saveTeacherProfile($userId, $data);
            return;
        }

        if ($normalizedRole === 'student') {
            $this->saveStudentProfile($userId, $data);
        }
    }

    public function softDelete(int $id): void
    {
        $this->executeStatement('UPDATE users SET deleted_at = NOW(), status = "inactive" WHERE id = :id', ['id' => $id]);
    }

    public function updatePassword(int $id, string $plainPassword): void
    {
        $this->executeStatement('UPDATE users SET password = :password WHERE id = :id', [
            'id' => $id,
            'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ]);
    }

    public function findPasswordHashById(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        $hash = $this->fetchScalar(
            'SELECT password FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1',
            ['id' => $id],
            'password',
            null
        );

        return $hash !== null ? (string) $hash : null;
    }

    public function countByRoleName(string $roleName): int
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS count FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = :role_name',
            ['role_name' => $roleName],
            'count',
            0
        );
    }

    public function listByRoleNames(array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($roleNames) as $idx => $roleName) {
            $key = ':role_' . $idx;
            $placeholders[] = $key;
            $params['role_' . $idx] = (string) $roleName;
        }

        $sql = 'SELECT u.id, u.full_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.role_name IN (' . implode(',', $placeholders) . ')
            ORDER BY u.full_name ASC';
        return $this->fetchAll($sql, $params);
    }

    public function listActiveByRoleNames(array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($roleNames) as $idx => $roleName) {
            $key = ':active_role_' . $idx;
            $placeholders[] = $key;
            $params['active_role_' . $idx] = (string) $roleName;
        }

        $sql = 'SELECT u.id, u.full_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.role_name IN (' . implode(',', $placeholders) . ')
              AND u.deleted_at IS NULL
              AND u.status = "active"
            ORDER BY u.full_name ASC';
        return $this->fetchAll($sql, $params);
    }

    public function usernameExists(string $username, int $excludeUserId = 0): bool
    {
        $normalized = trim($username);
        if ($normalized === '') {
            return false;
        }

        $params = ['username' => $normalized];
        $sql = 'SELECT COUNT(*) AS total FROM users WHERE username = :username';

        if ($excludeUserId > 0) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeUserId;
        }

        $count = (int) $this->fetchScalar($sql, $params, 'total', 0);
        return $count > 0;
    }

    private function findRoleProfile(int $userId, string $roleName): array
    {
        if ($userId <= 0) {
            return [];
        }

        $normalizedRole = strtolower(trim($roleName));
        if ($normalizedRole === 'staff') {
            return $this->fetchOne(
                'SELECT position AS staff_position, approval_limit AS staff_approval_limit
                 FROM staff_profiles
                 WHERE user_id = :user_id
                 LIMIT 1',
                ['user_id' => $userId]
            ) ?? [];
        }

        if ($normalizedRole === 'teacher') {
            $teacherProfile = $this->fetchOne(
                'SELECT u.id AS user_id,
                        u.username AS teacher_username,
                        u.full_name AS teacher_full_name,
                        u.phone AS teacher_phone,
                        u.email AS teacher_email,
                        u.avatar AS teacher_avatar,
                        u.status AS teacher_status,
                        tp.id AS teacher_profile_id,
                        tp.teacher_code AS teacher_code,
                        tp.degree AS teacher_degree,
                        tp.experience_years AS teacher_experience_years,
                        tp.bio AS teacher_bio,
                        tp.intro_video_url AS teacher_intro_video_url
                 FROM users u
                 INNER JOIN teacher_profiles tp ON tp.user_id = u.id
                 WHERE u.id = :user_id
                   AND u.deleted_at IS NULL
                 LIMIT 1',
                ['user_id' => $userId]
            );

            if (!is_array($teacherProfile) || $teacherProfile === []) {
                return [];
            }

            $teacherProfile['teacher_certificates'] = $this->listTeacherCertificatesByUserId($userId);
            return $teacherProfile;
        }

        if ($normalizedRole === 'student') {
            $studentLeadProfile = $this->fetchOne(
                'SELECT sl.id AS student_lead_id,
                        sl.student_name AS student_name,
                        COALESCE(sp.student_code, CONCAT("HV", LPAD(u.id, 5, "0"))) AS student_code,
                        sl.parent_name AS student_parent_name,
                        sl.parent_phone AS student_parent_phone,
                        sl.school_name AS student_school_name,
                        sl.current_grade AS student_current_grade,
                        sl.current_level AS student_target_score,
                        sl.study_time AS student_study_time,
                        sl.status AS student_lead_status,
                        sl.converted_at AS student_converted_at
                 FROM student_leads sl
                 INNER JOIN users u ON u.id = sl.converted_user_id
                 LEFT JOIN student_profiles sp ON sp.user_id = u.id
                 WHERE u.id = :user_id
                 LIMIT 1',
                ['user_id' => $userId]
            );

            if (is_array($studentLeadProfile) && !empty($studentLeadProfile)) {
                return $studentLeadProfile;
            }

            return $this->fetchOne(
                'SELECT student_code AS student_code,
                        parent_name AS student_parent_name,
                        parent_phone AS student_parent_phone,
                        school_name AS student_school_name,
                        target_score AS student_target_score,
                        entry_test_id AS student_entry_test_id
                 FROM student_profiles
                 WHERE user_id = :user_id
                 LIMIT 1',
                ['user_id' => $userId]
            ) ?? [];
        }

        return [];
    }

    private function saveStaffProfile(int $userId, array $data): void
    {
        $position = trim((string) ($data['staff_position'] ?? ''));
        $approvalLimit = max(0, (float) ($data['staff_approval_limit'] ?? 0));

        $this->executeStatement(
            'INSERT INTO staff_profiles (user_id, position, approval_limit)
             VALUES (:user_id, :position, :approval_limit)
             ON DUPLICATE KEY UPDATE
                 position = VALUES(position),
                 approval_limit = VALUES(approval_limit)',
            [
                'user_id' => $userId,
                'position' => $position,
                'approval_limit' => $approvalLimit,
            ]
        );
    }

    private function saveTeacherProfile(int $userId, array $data): void
    {
        $degree = trim((string) ($data['teacher_degree'] ?? ''));
        $experienceYears = max(0, (int) ($data['teacher_experience_years'] ?? 0));
        $bio = trim((string) ($data['teacher_bio'] ?? ''));
        $introVideoUrl = trim((string) ($data['teacher_intro_video_url'] ?? ''));

        $this->executeStatement(
            'INSERT INTO teacher_profiles (user_id, degree, experience_years, bio, intro_video_url)
             VALUES (:user_id, :degree, :experience_years, :bio, :intro_video_url)
             ON DUPLICATE KEY UPDATE
                 degree = VALUES(degree),
                 experience_years = VALUES(experience_years),
                 bio = VALUES(bio),
                 intro_video_url = VALUES(intro_video_url)',
            [
                'user_id' => $userId,
                'degree' => $degree !== '' ? $degree : null,
                'experience_years' => $experienceYears,
                'bio' => $bio !== '' ? $bio : null,
                'intro_video_url' => $introVideoUrl !== '' ? $introVideoUrl : null,
            ]
        );
    }

    private function saveStudentProfile(int $userId, array $data): void
    {
        $parentName = trim((string) ($data['student_parent_name'] ?? ''));
        $parentPhone = trim((string) ($data['student_parent_phone'] ?? ''));
        $schoolName = trim((string) ($data['student_school_name'] ?? ''));
        $targetScore = trim((string) ($data['student_target_score'] ?? ''));
        $entryTestId = (int) ($data['student_entry_test_id'] ?? 0);

        $this->executeStatement(
            'INSERT INTO student_profiles (user_id, parent_name, parent_phone, school_name, target_score, entry_test_id)
             VALUES (:user_id, :parent_name, :parent_phone, :school_name, :target_score, :entry_test_id)
             ON DUPLICATE KEY UPDATE
                 parent_name = VALUES(parent_name),
                 parent_phone = VALUES(parent_phone),
                 school_name = VALUES(school_name),
                 target_score = VALUES(target_score),
                 entry_test_id = VALUES(entry_test_id)',
            [
                'user_id' => $userId,
                'parent_name' => $parentName !== '' ? $parentName : null,
                'parent_phone' => $parentPhone !== '' ? $parentPhone : null,
                'school_name' => $schoolName !== '' ? $schoolName : null,
                'target_score' => $targetScore !== '' ? $targetScore : null,
                'entry_test_id' => $entryTestId > 0 ? $entryTestId : null,
            ]
        );
    }
}
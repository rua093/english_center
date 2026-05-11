<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class UsersTableModel extends BaseTableModel
{
    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = ['u.deleted_at IS NULL'];

        $statusFilter = trim((string) ($filters['status'] ?? ''));
        if ($statusFilter !== '') {
            $params['filter_status'] = $statusFilter;
            $conditions[] = 'u.status = :filter_status';
        }

        $roleIdFilter = (int) ($filters['role_id'] ?? 0);
        if ($roleIdFilter > 0) {
            $params['filter_role_id'] = $roleIdFilter;
            $conditions[] = 'u.role_id = :filter_role_id';
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery === '') {
            return ' WHERE ' . implode(' AND ', $conditions);
        }

        $likeValue = '%' . $searchQuery . '%';
        $params['search_id'] = $likeValue;
        $params['search_username'] = $likeValue;
        $params['search_name'] = $likeValue;
        $params['search_phone'] = $likeValue;
        $params['search_email'] = $likeValue;
        $params['search_role'] = $likeValue;
        $params['search_status'] = $likeValue;
        $params['search_teacher_code'] = $likeValue;
        $params['search_student_code'] = $likeValue;

        $conditions[] = "(
            CAST(u.id AS CHAR) LIKE :search_id
            OR COALESCE(u.username, '') LIKE :search_username
            OR COALESCE(u.full_name, '') LIKE :search_name
            OR COALESCE(u.phone, '') LIKE :search_phone
            OR COALESCE(u.email, '') LIKE :search_email
            OR COALESCE(r.role_name, '') LIKE :search_role
            OR COALESCE(u.status, '') LIKE :search_status
            OR COALESCE(tp.teacher_code, '') LIKE :search_teacher_code
            OR COALESCE(sp.student_code, '') LIKE :search_student_code
        )";

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function countActiveWithRoles(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id'
             . $this->buildSearchWhereClause($searchQuery, $filters, $params),
            $params,
            'total',
            0
        );
    }

    public function listActiveWithRoles(): array
    {
        $sql = "SELECT u.id, u.username, u.full_name, u.phone, u.email, u.avatar, u.status, u.created_at, u.role_id,
                r.role_name, tp.teacher_code, sp.student_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.id DESC";
        return $this->fetchAll($sql);
    }

    public function listActiveWithRolesPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $params = [];
        $sql = "SELECT u.id, u.username, u.full_name, u.phone, u.email, u.avatar, u.status, u.created_at, u.role_id,
                r.role_name, tp.teacher_code, sp.student_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            " . $this->buildSearchWhereClause($searchQuery, $filters, $params) . "
            ORDER BY u.id DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql, $params);
    }

    public function findActiveById(int $id): ?array
    {
        $user = $this->fetchOne(
                "SELECT u.id, u.username, u.full_name, u.role_id, u.phone, u.email, u.avatar, u.status, u.created_at,
                    r.role_name, tp.teacher_code, sp.student_code
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
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

    public function findActiveByEmail(string $email): ?array
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '') {
            return null;
        }

        $user = $this->fetchOne(
            "SELECT u.id, u.username, u.full_name, u.role_id, u.phone, u.email, u.avatar, u.status, u.created_at,
                    r.role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL
               AND u.status = 'active'
               AND LOWER(COALESCE(u.email, '')) = :email
             ORDER BY u.id DESC
             LIMIT 1",
            ['email' => $normalizedEmail]
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
        $phone = $this->normalizePhone($data['phone'] ?? '');
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
        $phone = $this->normalizePhone($data['phone'] ?? '');
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

        $sql = 'SELECT u.id, u.full_name, r.role_name, tp.teacher_code, sp.student_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
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

        $sql = 'SELECT u.id, u.full_name, r.role_name, tp.teacher_code, sp.student_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE r.role_name IN (' . implode(',', $placeholders) . ')
              AND u.deleted_at IS NULL
              AND u.status = "active"
            ORDER BY u.full_name ASC';
        return $this->fetchAll($sql, $params);
    }

    public function countActiveByRoleNames(array $roleNames): int
    {
        if (empty($roleNames)) {
            return 0;
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($roleNames) as $idx => $roleName) {
            $key = ':active_count_role_' . $idx;
            $placeholders[] = $key;
            $params['active_count_role_' . $idx] = (string) $roleName;
        }

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS count
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.role_name IN (' . implode(',', $placeholders) . ')
               AND u.deleted_at IS NULL
               AND u.status = "active"',
            $params,
            'count',
            0
        );
    }

    public function listActiveByRoleNamesPage(int $page, int $perPage, array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        $pagination = $this->pagination($page, $perPage, 4, 100);
        $placeholders = [];
        $params = [];
        foreach (array_values($roleNames) as $idx => $roleName) {
            $key = ':active_page_role_' . $idx;
            $placeholders[] = $key;
            $params['active_page_role_' . $idx] = (string) $roleName;
        }

        $sql = 'SELECT u.id, u.full_name, r.role_name, tp.teacher_code, sp.student_code
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE r.role_name IN (' . implode(',', $placeholders) . ')
              AND u.deleted_at IS NULL
              AND u.status = "active"
            ORDER BY u.full_name ASC
            LIMIT ' . (int) $pagination['limit'] . ' OFFSET ' . (int) $pagination['offset'];

        return $this->fetchAll($sql, $params);
    }

    public function listRoleLookups(): array
    {
        return $this->fetchAll(
            'SELECT id, role_name
             FROM roles
             ORDER BY role_name ASC'
        );
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
                'SELECT position AS staff_position
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
                        p.father_name AS student_father_name,
                        p.father_phone AS student_father_phone,
                        p.father_id_card AS student_father_id_card,
                        p.mother_name AS student_mother_name,
                        p.mother_phone AS student_mother_phone,
                        p.mother_id_card AS student_mother_id_card,
                        p.social_links AS student_parent_social_links,
                        COALESCE(sp.school_name, sl.school_name) AS student_school_name,
                        sl.current_grade AS student_current_grade,
                        sp.target_score AS student_target_score,
                        sl.study_time AS student_study_time,
                        sl.status AS student_lead_status,
                        sl.converted_at AS student_converted_at
                 FROM student_leads sl
                 INNER JOIN users u ON u.id = sl.converted_user_id
                 LEFT JOIN student_profiles sp ON sp.user_id = u.id
                 LEFT JOIN parents p ON p.id = sp.parent_id
                 WHERE u.id = :user_id
                 LIMIT 1',
                ['user_id' => $userId]
            );

            if (is_array($studentLeadProfile) && !empty($studentLeadProfile)) {
                $studentLeadProfile['student_class_enrollments'] = $this->listStudentClassEnrollments($userId);
                return $studentLeadProfile;
            }

            $studentProfile = $this->fetchOne(
                'SELECT student_code AS student_code,
                        p.father_name AS student_father_name,
                        p.father_phone AS student_father_phone,
                        p.father_id_card AS student_father_id_card,
                        p.mother_name AS student_mother_name,
                        p.mother_phone AS student_mother_phone,
                        p.mother_id_card AS student_mother_id_card,
                        p.social_links AS student_parent_social_links,
                        sp.school_name AS student_school_name,
                        sp.target_score AS student_target_score
                 FROM student_profiles sp
                 LEFT JOIN parents p ON p.id = sp.parent_id
                 WHERE user_id = :user_id
                 LIMIT 1',
                ['user_id' => $userId]
            ) ?? [];

            if ($studentProfile !== []) {
                $studentProfile['student_class_enrollments'] = $this->listStudentClassEnrollments($userId);
            }

            return $studentProfile;
        }

        return [];
    }

    private function saveStaffProfile(int $userId, array $data): void
    {
        $position = trim((string) ($data['staff_position'] ?? ''));

        $this->executeStatement(
            'INSERT INTO staff_profiles (user_id, position)
             VALUES (:user_id, :position)
             ON DUPLICATE KEY UPDATE
                 position = VALUES(position)',
            [
                'user_id' => $userId,
                'position' => $position,
            ]
        );
    }

    private function saveTeacherProfile(int $userId, array $data): void
    {
        $degree = trim((string) ($data['teacher_degree'] ?? ''));
        $experienceYears = max(0, (int) ($data['teacher_experience_years'] ?? 0));
        $bio = trim((string) ($data['teacher_bio'] ?? ''));
        $introVideoUrl = trim((string) ($data['teacher_intro_video_url'] ?? ''));
        $teacherCode = $this->buildTeacherCode($userId);

        $this->executeStatement(
            'INSERT INTO teacher_profiles (user_id, teacher_code, degree, experience_years, bio, intro_video_url)
             VALUES (:user_id, :teacher_code, :degree, :experience_years, :bio, :intro_video_url)
             ON DUPLICATE KEY UPDATE
                 teacher_code = VALUES(teacher_code),
                 degree = VALUES(degree),
                 experience_years = VALUES(experience_years),
                 bio = VALUES(bio),
                 intro_video_url = VALUES(intro_video_url)',
            [
                'user_id' => $userId,
                'teacher_code' => $teacherCode,
                'degree' => $degree !== '' ? $degree : null,
                'experience_years' => $experienceYears,
                'bio' => $bio !== '' ? $bio : null,
                'intro_video_url' => $introVideoUrl !== '' ? $introVideoUrl : null,
            ]
        );
    }

    private function saveStudentProfile(int $userId, array $data): void
    {
        $fatherName = trim((string) ($data['student_father_name'] ?? ''));
        $fatherPhone = $this->normalizePhone($data['student_father_phone'] ?? '');
        $fatherIdCard = trim((string) ($data['student_father_id_card'] ?? ''));
        $motherName = trim((string) ($data['student_mother_name'] ?? ''));
        $motherPhone = $this->normalizePhone($data['student_mother_phone'] ?? '');
        $motherIdCard = trim((string) ($data['student_mother_id_card'] ?? ''));
        $socialLinks = trim((string) ($data['student_parent_social_links'] ?? ''));
        $schoolName = trim((string) ($data['student_school_name'] ?? ''));
        $targetScore = trim((string) ($data['student_target_score'] ?? ''));
        $studentCode = $this->buildStudentCode($userId);
        $existingParentId = (int) $this->fetchScalar(
            'SELECT parent_id AS parent_id
             FROM student_profiles
             WHERE user_id = :user_id
             LIMIT 1',
            ['user_id' => $userId],
            'parent_id',
            0
        );

        $parentPayload = [
            'father_name' => $fatherName,
            'father_phone' => $fatherPhone,
            'father_id_card' => $fatherIdCard,
            'mother_name' => $motherName,
            'mother_phone' => $motherPhone,
            'mother_id_card' => $motherIdCard,
            'social_links' => $socialLinks,
        ];
        $parentId = $this->saveParentProfile($parentPayload, $existingParentId > 0 ? $existingParentId : null);

        $this->executeStatement(
            'INSERT INTO student_profiles (user_id, parent_id, student_code, school_name, target_score)
             VALUES (:user_id, :parent_id, :student_code, :school_name, :target_score)
             ON DUPLICATE KEY UPDATE
                 parent_id = VALUES(parent_id),
                 student_code = VALUES(student_code),
                 school_name = VALUES(school_name),
                 target_score = VALUES(target_score)',
            [
                'user_id' => $userId,
                'parent_id' => $parentId,
                'student_code' => $studentCode,
                'school_name' => $schoolName !== '' ? $schoolName : null,
                'target_score' => $targetScore !== '' ? $targetScore : null,
            ]
        );

        if ($parentId === null && $existingParentId > 0) {
            $this->deleteParentIfUnused($existingParentId);
        }
    }

    public function removeRoleProfile(int $userId, string $roleName): void
    {
        if ($userId <= 0) {
            return;
        }

        $normalized = strtolower(trim($roleName));
        if ($normalized === 'staff') {
            $this->executeStatement('DELETE FROM staff_profiles WHERE user_id = :user_id', ['user_id' => $userId]);
            return;
        }

        if ($normalized === 'teacher') {
            $this->executeStatement('DELETE FROM teacher_profiles WHERE user_id = :user_id', ['user_id' => $userId]);
            return;
        }

        if ($normalized === 'student') {
            $parentId = (int) $this->fetchScalar(
                'SELECT parent_id AS parent_id
                 FROM student_profiles
                 WHERE user_id = :user_id
                 LIMIT 1',
                ['user_id' => $userId],
                'parent_id',
                0
            );
            $this->executeStatement('DELETE FROM student_profiles WHERE user_id = :user_id', ['user_id' => $userId]);
            if ($parentId > 0) {
                $this->deleteParentIfUnused($parentId);
            }
            return;
        }
    }

    private function saveParentProfile(array $data, ?int $existingParentId): ?int
    {
        if (!$this->hasParentProfileData($data)) {
            return null;
        }

        $params = [
            'father_name' => $this->nullIfEmpty($data['father_name'] ?? null),
            'father_phone' => $this->nullIfEmpty($data['father_phone'] ?? null),
            'father_id_card' => $this->nullIfEmpty($data['father_id_card'] ?? null),
            'mother_name' => $this->nullIfEmpty($data['mother_name'] ?? null),
            'mother_phone' => $this->nullIfEmpty($data['mother_phone'] ?? null),
            'mother_id_card' => $this->nullIfEmpty($data['mother_id_card'] ?? null),
            'social_links' => $this->nullIfEmpty($data['social_links'] ?? null),
        ];

        if ($existingParentId !== null && $existingParentId > 0) {
            $params['id'] = $existingParentId;
            $this->executeStatement(
                'UPDATE parents
                 SET father_name = :father_name,
                     father_phone = :father_phone,
                     father_id_card = :father_id_card,
                     mother_name = :mother_name,
                     mother_phone = :mother_phone,
                     mother_id_card = :mother_id_card,
                     social_links = :social_links
                 WHERE id = :id',
                $params
            );

            return $existingParentId;
        }

        $this->executeStatement(
            'INSERT INTO parents (father_name, father_phone, father_id_card, mother_name, mother_phone, mother_id_card, social_links)
             VALUES (:father_name, :father_phone, :father_id_card, :mother_name, :mother_phone, :mother_id_card, :social_links)',
            $params
        );

        return (int) $this->pdo->lastInsertId();
    }

    private function hasParentProfileData(array $data): bool
    {
        foreach (['father_name', 'father_phone', 'father_id_card', 'mother_name', 'mother_phone', 'mother_id_card', 'social_links'] as $key) {
            if (trim((string) ($data[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function deleteParentIfUnused(int $parentId): void
    {
        if ($parentId <= 0) {
            return;
        }

        $usageCount = (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM student_profiles
             WHERE parent_id = :parent_id',
            ['parent_id' => $parentId],
            'total',
            0
        );

        if ($usageCount === 0) {
            $this->executeStatement('DELETE FROM parents WHERE id = :id', ['id' => $parentId]);
        }
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }

    private function listStudentClassEnrollments(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        return $this->fetchAll(
            'SELECT c.id AS class_id,
                    c.class_name,
                    c.status AS class_status,
                    c.start_date,
                    c.end_date,
                    cs.enrollment_date,
                    co.course_name
             FROM class_students cs
             INNER JOIN classes c ON c.id = cs.class_id
             INNER JOIN users u ON u.id = cs.student_id
             LEFT JOIN courses co ON co.id = c.course_id
             WHERE cs.student_id = :user_id
               AND u.deleted_at IS NULL
             ORDER BY COALESCE(cs.enrollment_date, c.start_date, c.created_at) DESC, c.class_name ASC',
            ['user_id' => $userId]
        );
    }

    private function normalizePhone(mixed $value): string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $value));
        return is_string($digits) ? $digits : '';
    }

    private function buildTeacherCode(int $userId): string
    {
        return 'GV' . str_pad((string) max(0, $userId), 5, '0', STR_PAD_LEFT);
    }

    private function buildStudentCode(int $userId): string
    {
        return 'HV' . str_pad((string) max(0, $userId), 5, '0', STR_PAD_LEFT);
    }
}

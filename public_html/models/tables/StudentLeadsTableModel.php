<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class StudentLeadsTableModel
{
    use TableModelUtils;

    private const ALLOWED_STATUSES = [
        'new',
        'entry_tested',
        'trial_completed',
        'official',
        'cancelled',
    ];

    public function countDetailed(array $filters = [], string $searchQuery = ''): int
    {
        $params = [];
        $where = $this->buildSearchWhereClause($filters, $searchQuery, $params);

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM student_leads sl' . $where,
            $params,
            'total',
            0
        );
    }

    public function listDetailedPage(int $page, int $perPage, array $filters = [], string $searchQuery = ''): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $params = [];
        $where = $this->buildSearchWhereClause($filters, $searchQuery, $params);

        $sql = 'SELECT sl.id, sl.student_name, sl.gender, sl.dob, sl.interests, sl.school_name, sl.current_grade,
                    sl.personality, sl.parent_name, sl.parent_phone, sl.referral_source, sl.current_level,
                    sl.study_time, sl.parent_expectation, sl.status, sl.admin_note,
                    sl.converted_user_id, sl.converted_at,
                    sl.created_at,
                    u.username AS converted_username,
                    u.full_name AS converted_full_name
                FROM student_leads sl
                LEFT JOIN users u ON u.id = sl.converted_user_id'
            . $where
            . ' ORDER BY sl.created_at DESC
                LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT sl.id, sl.student_name, sl.gender, sl.dob, sl.interests, sl.school_name, sl.current_grade,
                    sl.personality, sl.parent_name, sl.parent_phone, sl.referral_source, sl.current_level,
                    sl.study_time, sl.parent_expectation, sl.status, sl.admin_note,
                    sl.converted_user_id, sl.converted_at,
                    sl.created_at,
                    u.username AS converted_username,
                    u.full_name AS converted_full_name
             FROM student_leads sl
             LEFT JOIN users u ON u.id = sl.converted_user_id
             WHERE sl.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function createFromPublic(array $data): int
    {
        $studentName = trim((string) ($data['student_name'] ?? $data['full_name'] ?? ''));

        // Prepare parent_name/parent_phone and school_name/current_grade with fallbacks from legacy keys
        $parentName = trim((string) ($data['parent_name'] ?? ''));
        $parentPhone = $this->normalizePhone($data['parent_phone'] ?? '');
        if ($parentPhone === '' && !empty($data['phone'] ?? '')) {
            $parentPhone = $this->normalizePhone($data['phone'] ?? '');
        }

        $schoolName = trim((string) ($data['school_name'] ?? ''));
        $currentGrade = trim((string) ($data['current_grade'] ?? ''));

        $sql = 'INSERT INTO student_leads (
                    student_name,
                    gender,
                    dob,
                    interests,
                    school_name,
                    current_grade,
                    personality,
                    parent_name,
                    parent_phone,
                    referral_source,
                    current_level,
                    study_time,
                    parent_expectation,
                    status
                ) VALUES (
                    :student_name,
                    :gender,
                    :dob,
                    :interests,
                    :school_name,
                    :current_grade,
                    :personality,
                    :parent_name,
                    :parent_phone,
                    :referral_source,
                    :current_level,
                    :study_time,
                    :parent_expectation,
                    :status
                )';

        $this->executeStatement($sql, [
            'student_name' => $studentName,
            'gender' => $this->nullIfEmpty($data['gender'] ?? null),
            'dob' => $this->nullIfEmpty($data['dob'] ?? null),
            'interests' => $this->nullIfEmpty($data['interests'] ?? null),
            'school_name' => $this->nullIfEmpty($schoolName),
            'current_grade' => $this->nullIfEmpty($currentGrade),
            'personality' => $this->nullIfEmpty($data['personality'] ?? null),
            'parent_name' => $this->nullIfEmpty($parentName),
            'parent_phone' => $this->nullIfEmpty($parentPhone),
            'referral_source' => $this->normalizeReferralSource((string) ($data['referral_source'] ?? 'website')),
            'current_level' => $this->nullIfEmpty($data['current_level'] ?? null),
            'study_time' => $this->nullIfEmpty($data['study_time'] ?? null),
            'parent_expectation' => $this->nullIfEmpty($data['parent_expectation'] ?? null),
            'status' => 'new',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateReview(int $id, string $status, string $adminNote): void
    {
        $normalizedStatus = $this->normalizeStatus($status);

        $this->executeStatement(
            'UPDATE student_leads
             SET status = :status,
                 admin_note = :admin_note
             WHERE id = :id',
            [
                'id' => $id,
                'status' => $normalizedStatus,
                'admin_note' => $this->nullIfEmpty($adminNote),
            ]
        );
    }

    public function markConverted(int $id, int $userId, ?string $adminNote = null): void
    {
        $params = [
            'id' => $id,
            'user_id' => $userId,
            'admin_note' => $this->nullIfEmpty($adminNote),
        ];

        $this->executeStatement(
            'UPDATE student_leads
             SET status = "official",
                 converted_user_id = :user_id,
                 converted_at = NOW(),
                 admin_note = COALESCE(:admin_note, admin_note)
             WHERE id = :id',
            $params
        );
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM student_leads WHERE id = :id', ['id' => $id]);
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        if (!in_array($normalized, self::ALLOWED_STATUSES, true)) {
            return 'new';
        }

        return $normalized;
    }

    private function buildSearchWhereClause(array $filters, string $searchQuery, array &$params): string
    {
        $conditions = [];

        foreach ($filters as $column => $value) {
            $normalizedColumn = strtolower(trim((string) $column));
            $normalizedValue = trim((string) $value);
            if ($normalizedValue === '') {
                continue;
            }

            if ($normalizedColumn === 'status') {
                $status = strtolower($normalizedValue);
                if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                    continue;
                }

                $params['filter_status'] = $status;
                $conditions[] = 'sl.status = :filter_status';
            }
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_student_name'] = $likeValue;
            $params['search_parent_name'] = $likeValue;
            $params['search_parent_phone'] = $likeValue;
            $params['search_source'] = $likeValue;
            $params['search_level'] = $likeValue;
            $params['search_status_text'] = $likeValue;
            $params['search_note'] = $likeValue;

            $conditions[] = "(
                CAST(sl.id AS CHAR) LIKE :search_id
                OR COALESCE(sl.student_name, '') LIKE :search_student_name
                OR COALESCE(sl.parent_name, '') LIKE :search_parent_name
                OR COALESCE(sl.parent_phone, '') LIKE :search_parent_phone
                OR COALESCE(sl.referral_source, '') LIKE :search_source
                OR COALESCE(sl.current_level, '') LIKE :search_level
                OR COALESCE(sl.status, '') LIKE :search_status_text
                OR COALESCE(sl.admin_note, '') LIKE :search_note
            )";
        }

        if (empty($conditions)) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    private function normalizeReferralSource(string $source): string
    {
        $normalized = strtolower(trim($source));
        if ($normalized === '') {
            return 'website';
        }

        return substr($normalized, 0, 120);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized === '' ? null : $normalized;
    }

    private function normalizePhone(mixed $value): string
    {
        $digits = preg_replace('/\D+/', '', trim((string) $value));
        return is_string($digits) ? $digits : '';
    }
}

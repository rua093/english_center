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

    public function countDetailed(?string $statusFilter = null): int
    {
        $params = [];
        $where = $this->buildStatusWhereClause($statusFilter, $params);

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM student_leads sl' . $where,
            $params,
            'total',
            0
        );
    }

    public function listDetailedPage(int $page, int $perPage, ?string $statusFilter = null): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $params = [];
        $where = $this->buildStatusWhereClause($statusFilter, $params);

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
        $parentPhone = trim((string) ($data['parent_phone'] ?? ''));
        if ($parentPhone === '' && !empty($data['phone'] ?? '')) {
            $p = trim((string) ($data['phone'] ?? ''));
            if (preg_match('/(?:\+?\d[\d\s().-]{7,}\d)/', $p, $m2) === 1) {
                $parentPhone = preg_replace('/\D+/', '', (string) ($m2[0] ?? ''));
            }
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

    private function buildStatusWhereClause(?string $statusFilter, array &$params): string
    {
        $status = strtolower(trim((string) $statusFilter));
        if ($status === '' || !in_array($status, self::ALLOWED_STATUSES, true)) {
            return '';
        }

        $params['status'] = $status;
        return ' WHERE sl.status = :status';
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
}

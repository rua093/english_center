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

        $sql = 'SELECT sl.id, sl.full_name, sl.phone, sl.email, sl.age, sl.parent_name, sl.parent_phone,
                    sl.school_name, sl.target_program, sl.target_score, sl.desired_schedule, sl.note,
                    sl.source, sl.status, sl.admin_note, sl.converted_user_id, sl.converted_at,
                    sl.created_at, sl.updated_at,
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
            'SELECT sl.id, sl.full_name, sl.phone, sl.email, sl.age, sl.parent_name, sl.parent_phone,
                    sl.school_name, sl.target_program, sl.target_score, sl.desired_schedule, sl.note,
                    sl.source, sl.status, sl.admin_note, sl.converted_user_id, sl.converted_at,
                    sl.created_at, sl.updated_at,
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
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $ageRaw = (int) ($data['age'] ?? 0);

        $sql = 'INSERT INTO student_leads (
                    full_name,
                    phone,
                    email,
                    age,
                    parent_name,
                    parent_phone,
                    school_name,
                    target_program,
                    target_score,
                    desired_schedule,
                    note,
                    source,
                    status
                ) VALUES (
                    :full_name,
                    :phone,
                    :email,
                    :age,
                    :parent_name,
                    :parent_phone,
                    :school_name,
                    :target_program,
                    :target_score,
                    :desired_schedule,
                    :note,
                    :source,
                    :status
                )';

        $this->executeStatement($sql, [
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'age' => $ageRaw > 0 ? $ageRaw : null,
            'parent_name' => $this->nullIfEmpty($data['parent_name'] ?? null),
            'parent_phone' => $this->nullIfEmpty($data['parent_phone'] ?? null),
            'school_name' => $this->nullIfEmpty($data['school_name'] ?? null),
            'target_program' => $this->nullIfEmpty($data['target_program'] ?? null),
            'target_score' => $this->nullIfEmpty($data['target_score'] ?? null),
            'desired_schedule' => $this->nullIfEmpty($data['desired_schedule'] ?? null),
            'note' => $this->nullIfEmpty($data['note'] ?? null),
            'source' => $this->normalizeSource((string) ($data['source'] ?? 'website')),
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

    private function normalizeSource(string $source): string
    {
        $normalized = strtolower(trim($source));
        if ($normalized === '') {
            return 'website';
        }

        return substr($normalized, 0, 80);
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized === '' ? null : $normalized;
    }
}

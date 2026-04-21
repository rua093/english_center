<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class JobApplicationsTableModel
{
    use TableModelUtils;

    private const ALLOWED_STATUSES = [
        'new',
        'interviewed',
        'official',
        'rejected',
    ];

    public function countDetailed(?string $statusFilter = null): int
    {
        $params = [];
        $where = $this->buildStatusWhereClause($statusFilter, $params);

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM job_applications ja' . $where,
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

        $sql = 'SELECT ja.id, ja.full_name, ja.phone, ja.email, ja.applying_position, ja.degree,
                    ja.experience_years, ja.available_schedule, ja.intro, ja.source, ja.status,
                    ja.admin_note, ja.converted_user_id, ja.converted_at, ja.created_at, ja.updated_at,
                    u.username AS converted_username,
                    u.full_name AS converted_full_name
                FROM job_applications ja
                LEFT JOIN users u ON u.id = ja.converted_user_id'
            . $where
            . ' ORDER BY ja.created_at DESC
                LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $this->fetchAll($sql, $params);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT ja.id, ja.full_name, ja.phone, ja.email, ja.applying_position, ja.degree,
                    ja.experience_years, ja.available_schedule, ja.intro, ja.source, ja.status,
                    ja.admin_note, ja.converted_user_id, ja.converted_at, ja.created_at, ja.updated_at,
                    u.username AS converted_username,
                    u.full_name AS converted_full_name
             FROM job_applications ja
             LEFT JOIN users u ON u.id = ja.converted_user_id
             WHERE ja.id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function createFromPublic(array $data): int
    {
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $experienceYears = max(0, (int) ($data['experience_years'] ?? 0));

        $sql = 'INSERT INTO job_applications (
                    full_name,
                    phone,
                    email,
                    applying_position,
                    degree,
                    experience_years,
                    available_schedule,
                    intro,
                    source,
                    status
                ) VALUES (
                    :full_name,
                    :phone,
                    :email,
                    :applying_position,
                    :degree,
                    :experience_years,
                    :available_schedule,
                    :intro,
                    :source,
                    :status
                )';

        $this->executeStatement($sql, [
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'applying_position' => $this->nullIfEmpty($data['applying_position'] ?? null),
            'degree' => $this->nullIfEmpty($data['degree'] ?? null),
            'experience_years' => $experienceYears,
            'available_schedule' => $this->nullIfEmpty($data['available_schedule'] ?? null),
            'intro' => $this->nullIfEmpty($data['intro'] ?? null),
            'source' => $this->normalizeSource((string) ($data['source'] ?? 'website')),
            'status' => 'new',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateReview(int $id, string $status, string $adminNote): void
    {
        $normalizedStatus = $this->normalizeStatus($status);

        $this->executeStatement(
            'UPDATE job_applications
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
            'UPDATE job_applications
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
        return ' WHERE ja.status = :status';
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

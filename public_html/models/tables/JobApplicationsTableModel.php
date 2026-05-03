<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class JobApplicationsTableModel
{
    use TableModelUtils;

    private const ALLOWED_STATUSES = [
        'PENDING',
        'INTERVIEWING',
        'PASSED',
        'REJECTED',
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

        $sql = 'SELECT ja.id, ja.full_name, ja.email, ja.phone, ja.address, ja.position_applied,
                    ja.work_mode, ja.highest_degree, ja.experience_years, ja.education_detail, ja.work_history, ja.skills_set, ja.bio_summary,
                    ja.start_date, ja.salary_expectation, ja.cv_file_url,
                    ja.status, ja.hr_note, ja.converted_user_id, ja.converted_at,
                    ja.created_at,
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
            'SELECT ja.id, ja.full_name, ja.email, ja.phone, ja.address, ja.position_applied,
                    ja.work_mode, ja.highest_degree, ja.experience_years, ja.education_detail, ja.work_history, ja.skills_set, ja.bio_summary,
                    ja.start_date, ja.salary_expectation, ja.cv_file_url,
                    ja.status, ja.hr_note, ja.converted_user_id, ja.converted_at,
                    ja.created_at,
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
        

        $sql = 'INSERT INTO job_applications (
                    full_name,
                    email,
                    phone,
                    address,
                    position_applied,
                    work_mode,
                    highest_degree,
                    experience_years,
                    education_detail,
                    work_history,
                    skills_set,
                    bio_summary,
                    start_date,
                    salary_expectation,
                    cv_file_url,
                    status
                ) VALUES (
                    :full_name,
                    :email,
                    :phone,
                    :address,
                    :position_applied,
                    :work_mode,
                    :highest_degree,
                    :experience_years,
                    :education_detail,
                    :work_history,
                    :skills_set,
                    :bio_summary,
                    :start_date,
                    :salary_expectation,
                    :cv_file_url,
                    :status
                )';

        $this->executeStatement($sql, [
            'full_name' => $fullName,
            'email' => $this->nullIfEmpty($data['email'] ?? null),
            'phone' => $this->nullIfEmpty($this->normalizePhone($data['phone'] ?? null)),
            'address' => $this->nullIfEmpty($data['address'] ?? null),
            'position_applied' => $this->nullIfEmpty($data['position_applied'] ?? null),
            'work_mode' => $this->nullIfEmpty($data['work_mode'] ?? null),
            'highest_degree' => $this->nullIfEmpty($data['highest_degree'] ?? null),
            'experience_years' => is_numeric($data['experience_years'] ?? null) ? (int) $data['experience_years'] : null,
            'education_detail' => $this->nullIfEmpty($data['education_detail'] ?? null),
            'work_history' => $this->nullIfEmpty($data['work_history'] ?? null),
            'skills_set' => $this->nullIfEmpty($data['skills_set'] ?? null),
            'bio_summary' => $this->nullIfEmpty($data['bio_summary'] ?? null),
            'start_date' => $this->nullIfEmpty($data['start_date'] ?? null),
            'salary_expectation' => $this->nullIfEmpty($data['salary_expectation'] ?? null),
            'cv_file_url' => $this->nullIfEmpty($data['cv_file_url'] ?? null),
            'status' => 'PENDING',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateReview(int $id, string $status, string $adminNote): void
    {
        $normalizedStatus = $this->normalizeStatus($status);

        $this->executeStatement(
            'UPDATE job_applications
             SET status = :status,
                 hr_note = :hr_note
             WHERE id = :id',
            [
                'id' => $id,
                'status' => $normalizedStatus,
                'hr_note' => $this->nullIfEmpty($adminNote),
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
             SET status = "PASSED",
                 converted_user_id = :user_id,
                 converted_at = NOW(),
                 hr_note = COALESCE(:admin_note, hr_note)
             WHERE id = :id',
            $params
        );
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM job_applications WHERE id = :id', ['id' => $id]);
    }

    private function normalizeStatus(string $status): string
    {
        $normalized = strtoupper(trim($status));
        if (!in_array($normalized, self::ALLOWED_STATUSES, true)) {
            return 'PENDING';
        }

        return $normalized;
    }

    private function buildStatusWhereClause(?string $statusFilter, array &$params): string
    {
        $status = strtoupper(trim((string) $statusFilter));
        if ($status === '' || !in_array($status, self::ALLOWED_STATUSES, true)) {
            return '';
        }

        $params['status'] = $status;
        return ' WHERE ja.status = :status';
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

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class StudentPortfoliosTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM student_portfolios', [], 'total', 0);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT p.id, p.student_id, p.type, p.media_url, p.description, p.is_public_web, p.created_at,
                u.full_name AS full_name, sp.student_code
            FROM student_portfolios p
            INNER JOIN users u ON u.id = p.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            ORDER BY p.id DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT p.id, p.student_id, p.type, p.media_url, p.description, p.is_public_web, p.created_at,
                u.full_name AS full_name, sp.student_code
            FROM student_portfolios p
            INNER JOIN users u ON u.id = p.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            ORDER BY p.id DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, student_id, type, media_url, description, is_public_web FROM student_portfolios WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE student_portfolios SET student_id = :student_id, type = :type, media_url = :media_url,
                description = :description, is_public_web = :is_public_web WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'student_id' => (int) $data['student_id'],
                'type' => $data['type'],
                'media_url' => $data['media_url'],
                'description' => $data['description'],
                'is_public_web' => (int) ($data['is_public_web'] ?? 0),
            ]);
            return;
        }

        $sql = 'INSERT INTO student_portfolios (student_id, type, media_url, description, is_public_web)
            VALUES (:student_id, :type, :media_url, :description, :is_public_web)';
        $this->executeStatement($sql, [
            'student_id' => (int) $data['student_id'],
            'type' => $data['type'],
            'media_url' => $data['media_url'],
            'description' => $data['description'],
            'is_public_web' => (int) ($data['is_public_web'] ?? 0),
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM student_portfolios WHERE id = :id', ['id' => $id]);
    }
}

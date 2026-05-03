<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class FeedbacksTableModel
{
    use TableModelUtils;

    private function baseSelectSql(): string
    {
        return "SELECT
                f.id,
                f.sender_id AS student_id,
                f.rating,
                f.content AS comment,
                f.is_public_web,
                CASE WHEN f.is_public_web = 1 THEN 'public' ELSE 'private' END AS status,
                f.created_at,
                u.full_name AS full_name,
                '' AS teacher_name,
                '' AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id";
    }

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM feedbacks', [], 'total', 0);
    }

    public function averageRating(): float
    {
        return (float) $this->fetchScalar(
            'SELECT COALESCE(AVG(f.rating), 0) AS avg_rating
             FROM feedbacks f
             WHERE f.rating > 0',
            [],
            'avg_rating',
            0
        );
    }

    public function listDetailed(): array
    {
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                u.full_name AS full_name, sp.student_code
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            ORDER BY f.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                u.full_name AS full_name, sp.student_code
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            ORDER BY f.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                       u.full_name AS full_name, sp.student_code
                FROM feedbacks f
                INNER JOIN users u ON u.id = f.sender_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE f.id = :id
                LIMIT 1";

        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function listPublicReviews(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 3, 12);
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                       u.full_name AS full_name, sp.student_code
                FROM feedbacks f
                INNER JOIN users u ON u.id = f.sender_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE f.is_public_web = 1
                ORDER BY f.created_at DESC
                LIMIT " . $limit;

        return $this->fetchAll($sql);
    }

    public function save(array $data): void
    {
        $senderId = (int) ($data['sender_id'] ?? $data['student_id'] ?? 0);
        $rating = (int) ($data['rating'] ?? 0);
        $content = (string) ($data['comment'] ?? $data['content'] ?? '');
        $isPublicWeb = (int) (($data['is_public_web'] ?? 0) ? 1 : 0);

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE feedbacks SET rating = :rating, content = :content, is_public_web = :is_public_web WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'rating' => $rating,
                'content' => $content,
                'is_public_web' => $isPublicWeb,
            ]);
            return;
        }

        $sql = 'INSERT INTO feedbacks (sender_id, rating, content, is_public_web)
            VALUES (:sender_id, :rating, :content, :is_public_web)';
        $this->executeStatement($sql, [
            'sender_id' => $senderId,
            'rating' => $rating,
            'content' => $content,
            'is_public_web' => $isPublicWeb,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM feedbacks WHERE id = :id', ['id' => $id]);
    }
}

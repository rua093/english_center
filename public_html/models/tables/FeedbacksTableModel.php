<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class FeedbacksTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM feedbacks', [], 'total', 0);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT f.id, f.sender_id AS student_id, f.class_id, f.teacher_id, f.rating, f.content AS comment, f.status, f.created_at,
                u.full_name AS full_name, t.full_name AS teacher_name, c.class_name AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN users t ON t.id = f.teacher_id
            LEFT JOIN classes c ON c.id = f.class_id
            ORDER BY f.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT f.id, f.sender_id AS student_id, f.class_id, f.teacher_id, f.rating, f.content AS comment, f.status, f.created_at,
                u.full_name AS full_name, t.full_name AS teacher_name, c.class_name AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN users t ON t.id = f.teacher_id
            LEFT JOIN classes c ON c.id = f.class_id
            ORDER BY f.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function listPublicReviews(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 3, 12);
        $sql = "SELECT f.id, f.sender_id AS student_id, f.class_id, f.teacher_id, f.rating, f.content AS comment, f.status, f.created_at,
                u.full_name AS full_name, t.full_name AS teacher_name, c.class_name AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN users t ON t.id = f.teacher_id
            LEFT JOIN classes c ON c.id = f.class_id
            WHERE f.status = 'reviewed'
            ORDER BY f.created_at DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql);
    }

    public function save(array $data): void
    {
        $senderId = (int) ($data['sender_id'] ?? $data['student_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? $data['course_id'] ?? 0);
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $rating = (int) ($data['rating'] ?? 0);
        $content = (string) ($data['comment'] ?? $data['content'] ?? '');
        $status = (string) ($data['status'] ?? 'pending');

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE feedbacks SET rating = :rating, content = :content, status = :status WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'rating' => $rating,
                'content' => $content,
                'status' => $status,
            ]);
            return;
        }

        $sql = 'INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
            VALUES (:sender_id, :class_id, :teacher_id, :rating, :content, :status)';
        $this->executeStatement($sql, [
            'sender_id' => $senderId,
            'class_id' => $classId,
            'teacher_id' => $teacherId,
            'rating' => $rating,
            'content' => $content,
            'status' => $status,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM feedbacks WHERE id = :id', ['id' => $id]);
    }
}

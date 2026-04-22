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
        $sql = "SELECT f.id, f.sender_id AS student_id, f.teacher_id, f.class_id, f.class_id AS course_id, f.rating, f.content AS comment, f.status, f.created_at,
                u.full_name AS full_name, t.full_name AS teacher_name, c.class_name AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN users t ON t.id = f.teacher_id
            INNER JOIN classes c ON c.id = f.class_id
            ORDER BY f.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT f.id, f.sender_id AS student_id, f.teacher_id, f.class_id, f.class_id AS course_id, f.rating, f.content AS comment, f.status, f.created_at,
                u.full_name AS full_name, t.full_name AS teacher_name, c.class_name AS course_name
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN users t ON t.id = f.teacher_id
            INNER JOIN classes c ON c.id = f.class_id
            ORDER BY f.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function save(array $data): void
    {
        $classId = (int) ($data['class_id'] ?? $data['course_id'] ?? 0);
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        if ($teacherId <= 0 && $classId > 0) {
            $teacherId = $this->teacherIdForClass($classId);
        }

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE feedbacks SET rating = :rating, content = :content, status = :status WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'rating' => (int) $data['rating'],
                'content' => $data['comment'] ?? $data['content'] ?? '',
                'status' => (string) ($data['status'] ?? 'reviewed'),
            ]);
            return;
        }

        $sql = 'INSERT INTO feedbacks (sender_id, class_id, teacher_id, rating, content, status)
            VALUES (:sender_id, :class_id, :teacher_id, :rating, :content, :status)';
        $this->executeStatement($sql, [
            'sender_id' => (int) ($data['student_id'] ?? $data['sender_id'] ?? 0),
            'class_id' => $classId,
            'teacher_id' => $teacherId,
            'rating' => (int) $data['rating'],
            'content' => $data['comment'] ?? $data['content'] ?? '',
            'status' => (string) ($data['status'] ?? 'reviewed'),
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM feedbacks WHERE id = :id', ['id' => $id]);
    }

    private function teacherIdForClass(int $classId): int
    {
        return (int) $this->fetchScalar(
            'SELECT teacher_id FROM classes WHERE id = :class_id LIMIT 1',
            ['class_id' => $classId],
            0,
            0
        );
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class AssignmentsTableModel
{
    use TableModelUtils;
    public function countAll(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS count FROM assignments', [], 'count', 0);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT a.id, a.lesson_id, a.title, a.description, a.deadline, a.file_url,
                l.actual_title AS lesson_title, c.class_name
            FROM assignments a
            INNER JOIN lessons l ON l.id = a.lesson_id
            INNER JOIN classes c ON c.id = l.class_id
            ORDER BY a.deadline DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, lesson_id, title, description, deadline, file_url FROM assignments WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'lesson_id' => (int) ($data['lesson_id'] ?? 0),
            'title' => trim((string) ($data['title'] ?? '')),
            'description' => (string) ($data['description'] ?? ''),
            'deadline' => (string) ($data['deadline'] ?? $data['due_date'] ?? ''),
            'file_url' => trim((string) ($data['file_url'] ?? '')) ?: null,
        ];

        if ($id > 0) {
            $sql = 'UPDATE assignments SET lesson_id=:lesson_id, title=:title, description=:description,
                deadline=:deadline, file_url=:file_url WHERE id=:id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return;
        }

        $sql = 'INSERT INTO assignments (lesson_id, title, description, deadline, file_url)
            VALUES (:lesson_id, :title, :description, :deadline, :file_url)';
        $this->executeStatement($sql, $payload);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM assignments WHERE id = :id', ['id' => $id]);
    }

    public function listForStudentDashboard(int $studentId, int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 100);
        $sql = "SELECT a.id, a.title, a.deadline, a.description,
                sub.submitted_at, sub.score, sub.teacher_comment
            FROM assignments a
            INNER JOIN lessons l ON l.id = a.lesson_id
            INNER JOIN classes c ON c.id = l.class_id
            INNER JOIN class_students cs ON cs.class_id = c.id AND cs.student_id = :student_id_class
            LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = :student_id_submission
            ORDER BY a.deadline ASC
            LIMIT " . $limit;
        return $this->fetchAll($sql, [
            'student_id_class' => $studentId,
            'student_id_submission' => $studentId,
        ]);
    }
}

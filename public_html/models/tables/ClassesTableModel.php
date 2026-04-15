<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class ClassesTableModel extends BaseTableModel
{
    public function countAll(): int
    {
        return $this->countAllFrom('classes');
    }

    public function listDetailedWithProgress(): array
    {
        $sql = "SELECT c.id, c.class_name, c.start_date, c.end_date, c.status,
                co.course_name, u.full_name AS teacher_name, c.course_id, c.teacher_id,
                COALESCE(lp.total_lessons, 0) AS total_lessons,
                COALESCE(lp.completed_lessons, 0) AS completed_lessons,
                CASE
                    WHEN COALESCE(lp.total_lessons, 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(lp.completed_lessons, 0) / lp.total_lessons) * 100)
                END AS progress_percent
            FROM classes c
            INNER JOIN courses co ON co.id = c.course_id
            INNER JOIN users u ON u.id = c.teacher_id
            LEFT JOIN (
                SELECT l.class_id,
                       COUNT(*) AS total_lessons,
                       SUM(CASE WHEN l.lesson_date <= CURDATE() THEN 1 ELSE 0 END) AS completed_lessons
                FROM lessons l
                GROUP BY l.class_id
            ) lp ON lp.class_id = c.id
            ORDER BY c.id DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->findByIdFrom('classes', $id, 'id, course_id, class_name, teacher_id, start_date, end_date, status');
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseId = (int) ($data['course_id'] ?? 0);
        $className = trim((string) ($data['class_name'] ?? ''));
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $startDate = trim((string) ($data['start_date'] ?? ''));
        $endDate = trim((string) ($data['end_date'] ?? ''));
        $status = $this->normalizeStatus((string) ($data['status'] ?? 'upcoming'));

        if ($id > 0) {
            $sql = 'UPDATE classes SET course_id=:course_id, class_name=:class_name, teacher_id=:teacher_id,
                start_date=:start_date, end_date=:end_date, status=:status WHERE id=:id';
            $this->executeStatement($sql, [
                'id' => $id,
                'course_id' => $courseId,
                'class_name' => $className,
                'teacher_id' => $teacherId,
                'start_date' => $startDate !== '' ? $startDate : null,
                'end_date' => $endDate !== '' ? $endDate : null,
                'status' => $status,
            ]);
            return;
        }

        $sql = 'INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
            VALUES (:course_id, :class_name, :teacher_id, :start_date, :end_date, :status)';
        $this->executeStatement($sql, [
            'course_id' => $courseId,
            'class_name' => $className,
            'teacher_id' => $teacherId,
            'start_date' => $startDate !== '' ? $startDate : null,
            'end_date' => $endDate !== '' ? $endDate : null,
            'status' => $status,
        ]);
    }

    private function normalizeStatus(string $status): string
    {
        $aliases = [
            'planned' => 'upcoming',
            'completed' => 'graduated',
        ];
        $status = $aliases[$status] ?? $status;
        return in_array($status, ['upcoming', 'active', 'graduated', 'cancelled'], true) ? $status : 'upcoming';
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdFrom('classes', $id);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, class_name FROM classes ORDER BY class_name ASC');
    }

    public function listRecent(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 100);
        return $this->fetchAll('SELECT id, class_name, status FROM classes ORDER BY id DESC LIMIT ' . $limit);
    }
}

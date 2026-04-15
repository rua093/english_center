<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class LessonsTableModel
{
    use TableModelUtils;
    public function listForAssignmentLookup(): array
    {
        $sql = "SELECT l.id, l.actual_title, c.class_name
            FROM lessons l
            INNER JOIN classes c ON c.id = l.class_id
            ORDER BY l.lesson_date DESC";
        return $this->fetchAll($sql);
    }

    public function countByStudent(int $studentId): int
    {
        $sql = "SELECT COUNT(*) AS total
            FROM lessons l
            INNER JOIN class_students cs ON cs.class_id = l.class_id
            WHERE cs.student_id = :student_id";
        return (int) $this->fetchScalar($sql, ['student_id' => $studentId], 'total', 0);
    }

    public function countCompletedByStudent(int $studentId): int
    {
        $sql = "SELECT COUNT(*) AS total
            FROM lessons l
            INNER JOIN class_students cs ON cs.class_id = l.class_id
            WHERE cs.student_id = :student_id
                AND l.lesson_date <= CURDATE()";
        return (int) $this->fetchScalar($sql, ['student_id' => $studentId], 'total', 0);
    }
}
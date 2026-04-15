<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ClassStudentsTableModel
{
    use TableModelUtils;
    public function listRecentClassNamesForStudent(int $studentId, int $limit = 3): array
    {
        $limit = $this->clampLimit($limit, 3, 20);
        $sql = "SELECT c.class_name
            FROM classes c
            INNER JOIN class_students cs ON cs.class_id = c.id
            WHERE cs.student_id = :student_id
            ORDER BY c.id DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql, ['student_id' => $studentId]);
    }
}
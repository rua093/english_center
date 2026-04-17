<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ClassStudentsTableModel
{
    use TableModelUtils;

    public function listStudentsForClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT DISTINCT cs.class_id, cs.student_id, u.full_name AS student_name
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id
            WHERE cs.class_id = :class_id
              AND u.deleted_at IS NULL
            ORDER BY u.full_name ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function listStudentsByClass(): array
    {
        $sql = "SELECT DISTINCT cs.class_id, cs.student_id, c.class_name, u.full_name AS student_name
            FROM class_students cs
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN users u ON u.id = cs.student_id
            WHERE u.deleted_at IS NULL
            ORDER BY c.class_name ASC, u.full_name ASC";

        return $this->fetchAll($sql);
    }

    public function existsEnrollment(int $classId, int $studentId): bool
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM class_students WHERE class_id = :class_id AND student_id = :student_id',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
            ],
            'total',
            0
        ) > 0;
    }

    public function enrollStudent(int $classId, int $studentId, string $learningStatus = 'official', ?string $enrollmentDate = null): void
    {
        if ($classId <= 0 || $studentId <= 0) {
            return;
        }

        $normalizedStatus = in_array($learningStatus, ['trial', 'official', 'suspended'], true)
            ? $learningStatus
            : 'official';

        $normalizedDate = $enrollmentDate !== null && trim($enrollmentDate) !== ''
            ? trim($enrollmentDate)
            : date('Y-m-d');

        $this->executeStatement(
            'INSERT INTO class_students (class_id, student_id, learning_status, enrollment_date)
             VALUES (:class_id, :student_id, :learning_status, :enrollment_date)
             ON DUPLICATE KEY UPDATE student_id = student_id',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
                'learning_status' => $normalizedStatus,
                'enrollment_date' => $normalizedDate,
            ]
        );
    }

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
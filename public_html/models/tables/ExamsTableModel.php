<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class ExamsTableModel extends BaseTableModel
{
    public function listExamColumnsByClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT exam_name, exam_type, exam_date
            FROM exams
            WHERE class_id = :class_id
            GROUP BY exam_name, exam_type, exam_date
            ORDER BY exam_date ASC, exam_name ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function listExamRowsByClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT id, class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment
            FROM exams
            WHERE class_id = :class_id
            ORDER BY exam_date ASC, exam_name ASC, student_id ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function countExamRowsForColumn(int $classId, string $examName, string $examType, string $examDate): int
    {
        if ($classId <= 0) {
            return 0;
        }

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM exams WHERE class_id = :class_id AND exam_name = :exam_name AND exam_type = :exam_type AND exam_date = :exam_date',
            [
                'class_id' => $classId,
                'exam_name' => $examName,
                'exam_type' => $examType,
                'exam_date' => $examDate,
            ],
            'total',
            0
        );
    }

    public function createExamColumnForStudents(int $classId, array $studentIds, string $examName, string $examType, string $examDate): int
    {
        if ($classId <= 0 || $examName === '' || $examType === '' || $examDate === '' || empty($studentIds)) {
            return 0;
        }

        $values = [];
        $params = [];
        foreach (array_values($studentIds) as $idx => $studentId) {
            $studentId = (int) $studentId;
            if ($studentId <= 0) {
                continue;
            }

            $values[] = "(:class_id_{$idx}, :student_id_{$idx}, :exam_name_{$idx}, :exam_type_{$idx}, :exam_date_{$idx})";
            $params["class_id_{$idx}"] = $classId;
            $params["student_id_{$idx}"] = $studentId;
            $params["exam_name_{$idx}"] = $examName;
            $params["exam_type_{$idx}"] = $examType;
            $params["exam_date_{$idx}"] = $examDate;
        }

        if (empty($values)) {
            return 0;
        }

        $sql = 'INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date) VALUES ' . implode(',', $values);
        return $this->executeStatement($sql, $params);
    }

    public function findExamRowById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->fetchOne(
            'SELECT id, class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment FROM exams WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function findExamRowByMeta(int $classId, int $studentId, string $examName, string $examType, string $examDate): ?array
    {
        if ($classId <= 0 || $studentId <= 0 || $examName === '' || $examType === '' || $examDate === '') {
            return null;
        }

        return $this->fetchOne(
            'SELECT id, class_id, student_id, exam_name, exam_type, exam_date, result, teacher_comment
             FROM exams
             WHERE class_id = :class_id
               AND student_id = :student_id
               AND exam_name = :exam_name
               AND exam_type = :exam_type
               AND exam_date = :exam_date
             ORDER BY id DESC
             LIMIT 1',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
                'exam_name' => $examName,
                'exam_type' => $examType,
                'exam_date' => $examDate,
            ]
        );
    }

    public function createExamRow(int $classId, int $studentId, string $examName, string $examType, string $examDate): int
    {
        if ($classId <= 0 || $studentId <= 0 || $examName === '' || $examType === '' || $examDate === '') {
            return 0;
        }

        $this->executeStatement(
            'INSERT INTO exams (class_id, student_id, exam_name, exam_type, exam_date) VALUES (:class_id, :student_id, :exam_name, :exam_type, :exam_date)',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
                'exam_name' => $examName,
                'exam_type' => $examType,
                'exam_date' => $examDate,
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function updateExamResult(int $examId, ?string $result, ?string $teacherComment): void
    {
        if ($examId <= 0) {
            return;
        }

        $normalizedResult = $result !== null ? trim($result) : '';
        $normalizedComment = $teacherComment !== null ? trim($teacherComment) : '';

        $this->executeStatement(
            'UPDATE exams SET result = :result, teacher_comment = :teacher_comment WHERE id = :id',
            [
                'id' => $examId,
                'result' => $normalizedResult !== '' ? $normalizedResult : null,
                'teacher_comment' => $normalizedComment !== '' ? $normalizedComment : null,
            ]
        );
    }

    public function updateExamColumnMeta(
        int $classId,
        string $oldExamName,
        string $oldExamType,
        string $oldExamDate,
        string $newExamName,
        string $newExamType,
        string $newExamDate
    ): int {
        if (
            $classId <= 0
            || $oldExamName === ''
            || $oldExamType === ''
            || $oldExamDate === ''
            || $newExamName === ''
            || $newExamType === ''
            || $newExamDate === ''
        ) {
            return 0;
        }

        return $this->executeStatement(
            'UPDATE exams
             SET exam_name = :new_exam_name,
                 exam_type = :new_exam_type,
                 exam_date = :new_exam_date
             WHERE class_id = :class_id
               AND exam_name = :old_exam_name
               AND exam_type = :old_exam_type
               AND exam_date = :old_exam_date',
            [
                'class_id' => $classId,
                'old_exam_name' => $oldExamName,
                'old_exam_type' => $oldExamType,
                'old_exam_date' => $oldExamDate,
                'new_exam_name' => $newExamName,
                'new_exam_type' => $newExamType,
                'new_exam_date' => $newExamDate,
            ]
        );
    }

    public function deleteExamColumn(int $classId, string $examName, string $examType, string $examDate): int
    {
        if ($classId <= 0 || $examName === '' || $examType === '' || $examDate === '') {
            return 0;
        }

        return $this->executeStatement(
            'DELETE FROM exams
             WHERE class_id = :class_id
               AND exam_name = :exam_name
               AND exam_type = :exam_type
               AND exam_date = :exam_date',
            [
                'class_id' => $classId,
                'exam_name' => $examName,
                'exam_type' => $examType,
                'exam_date' => $examDate,
            ]
        );
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class SubmissionsTableModel
{
    use TableModelUtils;

    public function summarizeOnTimeSubmissionRateByClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT cs.student_id,
                COUNT(a.id) AS total_assignments,
                SUM(CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END) AS submitted_assignments,
                SUM(CASE
                        WHEN s.id IS NOT NULL
                            AND (a.deadline IS NULL OR s.submitted_at <= a.deadline)
                        THEN 1
                        ELSE 0
                    END) AS on_time_assignments,
                SUM(CASE
                        WHEN s.id IS NOT NULL
                            AND a.deadline IS NOT NULL
                            AND s.submitted_at > a.deadline
                        THEN 1
                        ELSE 0
                    END) AS late_assignments
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id AND u.deleted_at IS NULL
            LEFT JOIN lessons l ON l.class_id = cs.class_id
            LEFT JOIN assignments a ON a.lesson_id = l.id
            LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = cs.student_id
            WHERE cs.class_id = :class_id
            GROUP BY cs.student_id
            ORDER BY cs.student_id ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function countAll(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS count FROM submissions', [], 'count', 0);
    }

    public function countForGrading(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM submissions', [], 'total', 0);
    }

    public function listForGrading(): array
    {
        $sql = "SELECT s.id, s.assignment_id, s.student_id, s.file_url, s.submitted_at, s.score, s.teacher_comment,
                a.title AS assignment_title, a.deadline AS assignment_deadline,
                l.id AS lesson_id, l.class_id, l.actual_title AS lesson_title, sch.study_date AS lesson_date,
                c.class_name, u.full_name AS full_name
            FROM submissions s
            INNER JOIN assignments a ON a.id = s.assignment_id
            INNER JOIN lessons l ON l.id = a.lesson_id
            LEFT JOIN schedules sch ON sch.id = l.schedule_id
            INNER JOIN classes c ON c.id = l.class_id
            INNER JOIN users u ON u.id = s.student_id
            ORDER BY s.submitted_at DESC";
        return $this->fetchAll($sql);
    }

    public function listForGradingPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT s.id, s.assignment_id, s.student_id, s.file_url, s.submitted_at, s.score, s.teacher_comment,
                a.title AS assignment_title, a.deadline AS assignment_deadline,
                l.id AS lesson_id, l.class_id, l.actual_title AS lesson_title, sch.study_date AS lesson_date,
                c.class_name, u.full_name AS full_name
            FROM submissions s
            INNER JOIN assignments a ON a.id = s.assignment_id
            INNER JOIN lessons l ON l.id = a.lesson_id
            LEFT JOIN schedules sch ON sch.id = l.schedule_id
            INNER JOIN classes c ON c.id = l.class_id
            INNER JOIN users u ON u.id = s.student_id
            ORDER BY s.submitted_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function grade(int $submissionId, ?float $score, string $comment): void
    {
        $this->executeStatement('UPDATE submissions SET score = :score, teacher_comment = :comment WHERE id = :id', [
            'id' => $submissionId,
            'score' => $score,
            'comment' => $comment,
        ]);
    }

    public function upsertStudentSubmission(int $studentId, int $assignmentId, string $fileUrl): void
    {
        $sql = "INSERT INTO submissions (assignment_id, student_id, file_url, submitted_at)
            VALUES (:assignment_id, :student_id, :file_url, NOW())
            ON DUPLICATE KEY UPDATE file_url = VALUES(file_url), submitted_at = NOW()";
        $this->executeStatement($sql, [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'file_url' => $fileUrl,
        ]);
    }

    public function listRosterByClassAndAssignment(int $classId, int $assignmentId): array
    {
        $sql = "SELECT u.id AS student_id, u.full_name AS full_name,
                s.id AS submission_id, s.file_url, s.submitted_at, s.score, s.teacher_comment,
                a.deadline AS assignment_deadline,
                CASE
                    WHEN s.id IS NOT NULL
                        AND a.deadline IS NOT NULL
                        AND s.submitted_at IS NOT NULL
                        AND s.submitted_at > a.deadline
                    THEN 1
                    ELSE 0
                END AS is_late_submission
            FROM assignments a
            INNER JOIN lessons l ON l.id = a.lesson_id AND l.class_id = :class_id
            INNER JOIN class_students cs ON cs.class_id = l.class_id
            INNER JOIN users u ON u.id = cs.student_id AND u.deleted_at IS NULL
            LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = cs.student_id
            WHERE a.id = :assignment_id
            ORDER BY u.full_name ASC";

        return $this->fetchAll($sql, [
            'class_id' => $classId,
            'assignment_id' => $assignmentId,
        ]);
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class SubmissionsTableModel
{
    use TableModelUtils;
    public function countAll(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS count FROM submissions', [], 'count', 0);
    }

    public function listForGrading(): array
    {
        $sql = "SELECT s.id, s.assignment_id, s.student_id, s.file_url, s.submitted_at, s.score, s.teacher_comment,
                a.title AS assignment_title, u.full_name AS student_name
            FROM submissions s
            INNER JOIN assignments a ON a.id = s.assignment_id
            INNER JOIN users u ON u.id = s.student_id
            ORDER BY s.submitted_at DESC";
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
}
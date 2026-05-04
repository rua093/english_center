<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class AssignmentsTableModel extends BaseTableModel
{
    public function countAll(): int
    {
        return $this->countAllFrom('assignments');
    }

    public function countDetailed(string $searchQuery = ''): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM assignments a
            INNER JOIN schedules s ON s.id = a.schedule_id
            INNER JOIN classes c ON c.id = s.class_id
            {$whereSql}",
            $params,
            'total',
            0
        );
    }

    public function listDetailed(): array
    {
        $sql = "SELECT a.id, a.schedule_id, a.title, a.description, a.deadline, a.file_url,
                s.study_date AS lesson_date, c.class_name
            FROM assignments a
            INNER JOIN schedules s ON s.id = a.schedule_id
            INNER JOIN classes c ON c.id = s.class_id
            ORDER BY a.deadline DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = ''): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);
        $sql = "SELECT a.id, a.schedule_id, a.title, a.description, a.deadline, a.file_url,
                s.study_date AS lesson_date, c.class_name
            FROM assignments a
            INNER JOIN schedules s ON s.id = a.schedule_id
            INNER JOIN classes c ON c.id = s.class_id
            {$whereSql}
            ORDER BY a.deadline DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array &$params): string
    {
        $searchQuery = trim($searchQuery);
        if ($searchQuery === '') {
            return '';
        }

        $likeValue = '%' . $searchQuery . '%';
        $params['search_id'] = $likeValue;
        $params['search_title'] = $likeValue;
        $params['search_description'] = $likeValue;
        $params['search_class'] = $likeValue;

        return " WHERE (
            CAST(a.id AS CHAR) LIKE :search_id
            OR COALESCE(a.title, '') LIKE :search_title
            OR COALESCE(a.description, '') LIKE :search_description
            OR COALESCE(c.class_name, '') LIKE :search_class
        )";
    }

    public function findById(int $id): ?array
    {
        return $this->findByIdFrom('assignments', $id, 'id, schedule_id, title, description, deadline, file_url');
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $payload = [
            'schedule_id' => (int) ($data['schedule_id'] ?? 0),
            'title' => trim((string) ($data['title'] ?? '')),
            'description' => (string) ($data['description'] ?? ''),
            'deadline' => (string) ($data['deadline'] ?? $data['due_date'] ?? ''),
            'file_url' => trim((string) ($data['file_url'] ?? '')) ?: null,
        ];

        if ($id > 0) {
            $sql = 'UPDATE assignments SET schedule_id=:schedule_id, title=:title, description=:description,
                deadline=:deadline, file_url=:file_url WHERE id=:id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return;
        }

        $sql = 'INSERT INTO assignments (schedule_id, title, description, deadline, file_url)
            VALUES (:schedule_id, :title, :description, :deadline, :file_url)';
        $this->executeStatement($sql, $payload);
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdFrom('assignments', $id);
    }

    public function listForStudentDashboard(int $studentId, int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 100);
        $sql = "SELECT a.id, a.title, a.deadline, a.description,
                sub.submitted_at, sub.score, sub.teacher_comment
            FROM assignments a
            INNER JOIN schedules s ON s.id = a.schedule_id
            INNER JOIN classes c ON c.id = s.class_id
            INNER JOIN class_students cs ON cs.class_id = c.id AND cs.student_id = :student_id_class
            LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = :student_id_submission
            ORDER BY a.deadline ASC
            LIMIT " . $limit;
        return $this->fetchAll($sql, [
            'student_id_class' => $studentId,
            'student_id_submission' => $studentId,
        ]);
    }

    public function listForStudentByClass(int $studentId, int $classId): array
    {
        if ($studentId <= 0 || $classId <= 0) {
            return [];
        }

        $sql = "SELECT a.id, a.title, a.deadline, a.description,
                sub.submitted_at, sub.score, sub.teacher_comment,
                CASE WHEN sub.submitted_at IS NOT NULL THEN 'Đã nộp' ELSE 'Chưa nộp' END AS submission_status
            FROM assignments a
            INNER JOIN schedules s ON s.id = a.schedule_id
            INNER JOIN classes c ON c.id = s.class_id
            INNER JOIN class_students cs ON cs.class_id = c.id AND cs.student_id = :student_id
            LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = :student_id_submission
            WHERE c.id = :class_id
            ORDER BY a.deadline ASC, a.id DESC";

        return $this->fetchAll($sql, [
            'student_id' => $studentId,
            'student_id_submission' => $studentId,
            'class_id' => $classId,
        ]);
    }
}

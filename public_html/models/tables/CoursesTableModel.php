<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class CoursesTableModel extends BaseTableModel
{
    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM courses', [], 'total', 0);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $limit = (int) $pagination['limit'];
        $offset = (int) $pagination['offset'];

        $sql = "SELECT c.id, c.course_name, c.description, c.base_price, c.total_sessions, c.image_thumbnail,
                COALESCE(cs.class_count, 0) AS class_count,
                COALESCE(rs.roadmap_count, 0) AS roadmap_count
            FROM courses c
            LEFT JOIN (
                SELECT class_source.course_id, COUNT(*) AS class_count
                FROM classes class_source
                GROUP BY class_source.course_id
            ) cs ON cs.course_id = c.id
            LEFT JOIN (
                SELECT roadmap_source.course_id, COUNT(*) AS roadmap_count
                FROM course_roadmaps roadmap_source
                GROUP BY roadmap_source.course_id
            ) rs ON rs.course_id = c.id
            ORDER BY c.id DESC
            LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, course_name FROM courses ORDER BY course_name ASC');
    }

    public function listForRegistration(): array
    {
        return $this->fetchAll(
            'SELECT id, course_name, base_price, total_sessions, image_thumbnail
             FROM courses
             ORDER BY course_name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, course_name, description, base_price, total_sessions, image_thumbnail
             FROM courses
             WHERE id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseName = trim((string) ($data['course_name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $basePrice = max(0, (float) ($data['base_price'] ?? 0));
        $totalSessions = max(0, (int) ($data['total_sessions'] ?? 0));

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE courses
                 SET course_name = :course_name,
                     description = :description,
                     base_price = :base_price,
                     total_sessions = :total_sessions
                 WHERE id = :id',
                [
                    'id' => $id,
                    'course_name' => $courseName,
                    'description' => $description !== '' ? $description : null,
                    'base_price' => $basePrice,
                    'total_sessions' => $totalSessions,
                ]
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO courses (course_name, description, base_price, total_sessions)
             VALUES (:course_name, :description, :base_price, :total_sessions)',
            [
                'course_name' => $courseName,
                'description' => $description !== '' ? $description : null,
                'base_price' => $basePrice,
                'total_sessions' => $totalSessions,
            ]
        );
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM courses WHERE id = :id', ['id' => $id]);
    }
}

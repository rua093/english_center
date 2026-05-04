<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class CoursesTableModel extends BaseTableModel
{
    public function countDetailed(string $searchQuery = ''): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);
        return (int) $this->fetchScalar("SELECT COUNT(*) AS total FROM courses c {$whereSql}", $params, 'total', 0);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = ''): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $limit = (int) $pagination['limit'];
        $offset = (int) $pagination['offset'];
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);

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
            {$whereSql}
            ORDER BY c.id DESC
            LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array &$params): string
    {
        $conditions = ['c.deleted_at IS NULL'];
        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_description'] = $likeValue;
            $params['search_price'] = $likeValue;
            $conditions[] = "(
                CAST(c.id AS CHAR) LIKE :search_id
                OR COALESCE(c.course_name, '') LIKE :search_name
                OR COALESCE(c.description, '') LIKE :search_description
                OR CAST(c.base_price AS CHAR) LIKE :search_price
            )";
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, course_name FROM courses WHERE deleted_at IS NULL ORDER BY course_name ASC');
    }

    public function listForRegistration(): array
    {
        return $this->fetchAll(
            'SELECT id, course_name, base_price, total_sessions, image_thumbnail
             FROM courses
             WHERE deleted_at IS NULL
             ORDER BY course_name ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->findActiveByIdFrom('courses', $id, 'id, course_name, description, base_price, total_sessions, image_thumbnail');
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseName = trim((string) ($data['course_name'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $basePrice = max(0, (float) ($data['base_price'] ?? 0));
        $totalSessions = max(0, (int) ($data['total_sessions'] ?? 0));
        $imageThumbnail = trim((string) ($data['image_thumbnail'] ?? ''));

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE courses
                 SET course_name = :course_name,
                     description = :description,
                     base_price = :base_price,
                     total_sessions = :total_sessions,
                     image_thumbnail = :image_thumbnail
                 WHERE id = :id
                   AND deleted_at IS NULL',
                [
                    'id' => $id,
                    'course_name' => $courseName,
                    'description' => $description !== '' ? $description : null,
                    'base_price' => $basePrice,
                    'total_sessions' => $totalSessions,
                    'image_thumbnail' => $imageThumbnail !== '' ? $imageThumbnail : null,
                ]
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO courses (course_name, description, base_price, total_sessions, image_thumbnail)
             VALUES (:course_name, :description, :base_price, :total_sessions, :image_thumbnail)',
            [
                'course_name' => $courseName,
                'description' => $description !== '' ? $description : null,
                'base_price' => $basePrice,
                'total_sessions' => $totalSessions,
                'image_thumbnail' => $imageThumbnail !== '' ? $imageThumbnail : null,
            ]
        );
    }

    public function deleteById(int $id): void
    {
        $this->softDeleteByIdFrom('courses', $id);
    }
}

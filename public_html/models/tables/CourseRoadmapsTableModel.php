<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class CourseRoadmapsTableModel extends BaseTableModel
{
    public function countByCourse(int $courseId, string $searchQuery = ''): int
    {
        if ($courseId <= 0) {
            return 0;
        }

        $params = ['course_id' => $courseId];
        $whereSearch = $this->buildSearchWhereClause($searchQuery, $params);

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM course_roadmaps cr WHERE cr.course_id = :course_id' . $whereSearch,
            $params,
            'total',
            0
        );
    }

    public function listByCoursePage(int $courseId, int $page, int $perPage, string $searchQuery = ''): array
    {
        if ($courseId <= 0) {
            return [];
        }

        $pagination = $this->pagination($page, $perPage, 10, 200);
        $limit = (int) $pagination['limit'];
        $offset = (int) $pagination['offset'];
        $params = ['course_id' => $courseId];
        $whereSearch = $this->buildSearchWhereClause($searchQuery, $params);

        $sql = "SELECT cr.id, cr.course_id, cr.`order`, cr.topic_title, cr.outline_content,
                COALESCE(ls.lesson_count, 0) AS lesson_count
            FROM course_roadmaps cr
            LEFT JOIN (
                SELECT l.roadmap_id, COUNT(*) AS lesson_count
                FROM lessons l
                GROUP BY l.roadmap_id
            ) ls ON ls.roadmap_id = cr.id
            WHERE cr.course_id = :course_id{$whereSearch}
            ORDER BY cr.`order` ASC, cr.id ASC
            LIMIT {$limit} OFFSET {$offset}";

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
        $params['search_topic'] = $likeValue;
        $params['search_outline'] = $likeValue;

        return " AND (
            CAST(cr.id AS CHAR) LIKE :search_id
            OR COALESCE(cr.topic_title, '') LIKE :search_topic
            OR COALESCE(cr.outline_content, '') LIKE :search_outline
        )";
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, course_id, `order`, topic_title, outline_content
             FROM course_roadmaps
             WHERE id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseId = (int) ($data['course_id'] ?? 0);
        $order = max(1, (int) ($data['order'] ?? 1));
        $topicTitle = trim((string) ($data['topic_title'] ?? ''));
        $outlineContent = trim((string) ($data['outline_content'] ?? ''));

        if ($courseId <= 0) {
            throw new InvalidArgumentException('Vui lòng chọn khóa học hợp lệ.');
        }

        if ($topicTitle === '') {
            throw new InvalidArgumentException('Vui lòng nhập chủ đề roadmap.');
        }

        $payload = [
            'course_id' => $courseId,
            'order' => $order,
            'topic_title' => $topicTitle,
            'outline_content' => $outlineContent !== '' ? $outlineContent : null,
        ];

        if ($id > 0) {
            $payload['id'] = $id;
            $this->executeStatement(
                'UPDATE course_roadmaps
                 SET course_id = :course_id,
                     `order` = :order,
                     topic_title = :topic_title,
                     outline_content = :outline_content
                 WHERE id = :id',
                $payload
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO course_roadmaps (course_id, `order`, topic_title, outline_content)
             VALUES (:course_id, :order, :topic_title, :outline_content)',
            $payload
        );
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM course_roadmaps WHERE id = :id', ['id' => $id]);
    }
}

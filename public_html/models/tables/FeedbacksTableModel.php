<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class FeedbacksTableModel
{
    use TableModelUtils;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            {$whereSql}",
            $params,
            'total',
            0
        );
    }

    public function listDetailed(): array
    {
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                u.full_name AS full_name, sp.student_code
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            ORDER BY f.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                u.full_name AS full_name, sp.student_code
            FROM feedbacks f
            INNER JOIN users u ON u.id = f.sender_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            {$whereSql}
            ORDER BY f.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        $publicWeb = trim((string) ($filters['is_public_web'] ?? ''));
        if ($publicWeb !== '' && ($publicWeb === '0' || $publicWeb === '1')) {
            $conditions[] = 'f.is_public_web = :filter_is_public_web';
            $params['filter_is_public_web'] = (int) $publicWeb;
        }

        $rating = trim((string) ($filters['rating'] ?? ''));
        if ($rating !== '' && ctype_digit($rating)) {
            $ratingInt = (int) $rating;
            if ($ratingInt >= 1 && $ratingInt <= 5) {
                $conditions[] = 'f.rating = :filter_rating';
                $params['filter_rating'] = $ratingInt;
            }
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_code'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_comment'] = $likeValue;
            $params['search_rating'] = $likeValue;

            $conditions[] = "(
                CAST(f.id AS CHAR) LIKE :search_id
                OR COALESCE(sp.student_code, '') LIKE :search_code
                OR COALESCE(u.full_name, '') LIKE :search_name
                OR COALESCE(f.content, '') LIKE :search_comment
                OR CAST(f.rating AS CHAR) LIKE :search_rating
            )";
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                       u.full_name AS full_name, sp.student_code
                FROM feedbacks f
                INNER JOIN users u ON u.id = f.sender_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE f.id = :id
                LIMIT 1";

        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function listPublicReviews(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 3, 12);
        $sql = "SELECT f.id, f.sender_id AS student_id, f.rating, f.content AS comment, f.is_public_web, f.created_at,
                       u.full_name AS full_name, sp.student_code
                FROM feedbacks f
                INNER JOIN users u ON u.id = f.sender_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE f.is_public_web = 1
                ORDER BY f.created_at DESC
                LIMIT " . $limit;

        return $this->fetchAll($sql);
    }

    public function save(array $data): void
    {
        $senderId = (int) ($data['sender_id'] ?? $data['student_id'] ?? 0);
        $rating = (int) ($data['rating'] ?? 0);
        $content = (string) ($data['comment'] ?? $data['content'] ?? '');
        $isPublicWeb = (int) (($data['is_public_web'] ?? 0) ? 1 : 0);

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE feedbacks SET rating = :rating, content = :content, is_public_web = :is_public_web WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'rating' => $rating,
                'content' => $content,
                'is_public_web' => $isPublicWeb,
            ]);
            return;
        }

        $sql = 'INSERT INTO feedbacks (sender_id, rating, content, is_public_web)
            VALUES (:sender_id, :rating, :content, :is_public_web)';
        $this->executeStatement($sql, [
            'sender_id' => $senderId,
            'rating' => $rating,
            'content' => $content,
            'is_public_web' => $isPublicWeb,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM feedbacks WHERE id = :id', ['id' => $id]);
    }
}

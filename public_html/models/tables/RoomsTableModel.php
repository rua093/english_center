<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class RoomsTableModel
{
    use TableModelUtils;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);
        return (int) $this->fetchScalar("SELECT COUNT(*) AS total FROM rooms {$whereSql}", $params, 'total', 0);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        $sql = 'SELECT id, room_name FROM rooms ' . $whereSql . ' ORDER BY room_name ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = ['deleted_at IS NULL'];
        $searchQuery = trim($searchQuery);

        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_name'] = $likeValue;
            $conditions[] = '(CAST(id AS CHAR) LIKE :search_id OR COALESCE(room_name, \'\') LIKE :search_name)';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->fetchOne('SELECT id, room_name FROM rooms WHERE id = :id AND deleted_at IS NULL LIMIT 1', ['id' => $id]);
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $roomName = trim((string) ($data['room_name'] ?? ''));

        if ($roomName === '') {
            throw new InvalidArgumentException('Vui long nhap ten phong hoc.');
        }

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE rooms SET room_name = :room_name WHERE id = :id AND deleted_at IS NULL',
                [
                    'id' => $id,
                    'room_name' => $roomName,
                ]
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO rooms (room_name) VALUES (:room_name)',
            ['room_name' => $roomName]
        );
    }

    public function deleteById(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $this->executeStatement('UPDATE rooms SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, room_name FROM rooms WHERE deleted_at IS NULL ORDER BY room_name ASC');
    }
}

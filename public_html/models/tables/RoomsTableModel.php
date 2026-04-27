<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class RoomsTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM rooms', [], 'total', 0);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = 'SELECT id, room_name FROM rooms ORDER BY room_name ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->fetchOne('SELECT id, room_name FROM rooms WHERE id = :id LIMIT 1', ['id' => $id]);
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
                'UPDATE rooms SET room_name = :room_name WHERE id = :id',
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

        $this->executeStatement('DELETE FROM rooms WHERE id = :id', ['id' => $id]);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, room_name FROM rooms ORDER BY room_name ASC');
    }
}
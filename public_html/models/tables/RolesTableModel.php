<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class RolesTableModel
{
    use TableModelUtils;

    public function listAll(): array
    {
        return $this->fetchAll('SELECT id, role_name, description FROM roles ORDER BY id ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, role_name, description FROM roles WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }
}
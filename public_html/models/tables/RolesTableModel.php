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
}
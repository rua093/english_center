<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class PermissionsTableModel
{
    use TableModelUtils;
    public function listAll(): array
    {
        return $this->fetchAll('SELECT id, permission_name, slug FROM permissions ORDER BY slug ASC');
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class RoomsTableModel
{
    use TableModelUtils;
    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, room_name FROM rooms ORDER BY room_name ASC');
    }
}
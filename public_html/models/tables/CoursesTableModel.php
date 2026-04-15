<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class CoursesTableModel
{
    use TableModelUtils;
    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, course_name FROM courses ORDER BY course_name ASC');
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class AttendanceTableModel
{
    use TableModelUtils;
    public function aggregateStatuses(): array
    {
        return $this->fetchAll('SELECT status, COUNT(*) AS total FROM attendance GROUP BY status');
    }

    public function summaryByStudent(int $studentId): array
    {
        $sql = "SELECT
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count
            FROM attendance
            WHERE student_id = :student_id";
        $row = $this->fetchOne($sql, ['student_id' => $studentId]);
        return $row ?: [
            'present_count' => 0,
            'late_count' => 0,
            'absent_count' => 0,
        ];
    }
}
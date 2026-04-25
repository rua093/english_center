<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class AttendanceTableModel
{
    use TableModelUtils;

    public function summarizeAttendanceRateByClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT cs.student_id,
                COUNT(s.id) AS total_sessions,
                SUM(CASE WHEN a.status IN ('present', 'late') THEN 1 ELSE 0 END) AS attended_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_sessions,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_sessions,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_sessions
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id AND u.deleted_at IS NULL
            LEFT JOIN schedules s ON s.class_id = cs.class_id
            LEFT JOIN attendance a ON a.schedule_id = s.id AND a.student_id = cs.student_id
            WHERE cs.class_id = :class_id
            GROUP BY cs.student_id
            ORDER BY cs.student_id ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function aggregateStatuses(): array
    {
        return $this->fetchAll('SELECT status, COUNT(*) AS total FROM attendance GROUP BY status');
    }

    public function summaryByStudent(int $studentId): array
    {
        $sql = "SELECT
                COUNT(*) AS total_sessions,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count
            FROM attendance
            WHERE student_id = :student_id";
        $row = $this->fetchOne($sql, ['student_id' => $studentId]);
        return $row ?: [
            'total_sessions' => 0,
            'present_count' => 0,
            'late_count' => 0,
            'absent_count' => 0,
        ];
    }

    public function summaryByStudentForClass(int $studentId, int $classId): array
    {
        if ($studentId <= 0 || $classId <= 0) {
            return [
                'present_count' => 0,
                'late_count' => 0,
                'absent_count' => 0,
                'total_sessions' => 0,
            ];
        }

        $sql = "SELECT
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                COUNT(s.id) AS total_sessions
            FROM schedules s
            LEFT JOIN attendance a ON a.schedule_id = s.id AND a.student_id = :student_id
            WHERE s.class_id = :class_id";

        $row = $this->fetchOne($sql, [
            'student_id' => $studentId,
            'class_id' => $classId,
        ]);

        return $row ?: [
            'present_count' => 0,
            'late_count' => 0,
            'absent_count' => 0,
            'total_sessions' => 0,
        ];
    }

    public function listRosterBySchedule(int $scheduleId): array
    {
        if ($scheduleId <= 0) {
            return [];
        }

        $sql = "SELECT s.id AS schedule_id, s.class_id,
                cs.student_id, u.full_name AS full_name, cs.learning_status,
                COALESCE(a.status, '') AS attendance_status,
                COALESCE(a.note, '') AS attendance_note
            FROM schedules s
            INNER JOIN class_students cs ON cs.class_id = s.class_id
            INNER JOIN users u ON u.id = cs.student_id AND u.deleted_at IS NULL
            LEFT JOIN attendance a ON a.schedule_id = s.id AND a.student_id = cs.student_id
            WHERE s.id = :schedule_id
            ORDER BY u.full_name ASC";

        return $this->fetchAll($sql, ['schedule_id' => $scheduleId]);
    }

    public function saveRosterBySchedule(int $scheduleId, array $entries): int
    {
        if ($scheduleId <= 0) {
            throw new DomainException('Lịch học không hợp lệ.');
        }

        $scheduleExists = (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM schedules WHERE id = :schedule_id',
            ['schedule_id' => $scheduleId],
            'total',
            0
        ) > 0;
        if (!$scheduleExists) {
            throw new DomainException('Không tìm thấy lịch học cần điểm danh.');
        }

        $roster = $this->listRosterBySchedule($scheduleId);
        $allowedStudentIds = [];
        foreach ($roster as $studentRow) {
            $studentId = (int) ($studentRow['student_id'] ?? 0);
            if ($studentId > 0) {
                $allowedStudentIds[] = $studentId;
            }
        }

        if (empty($allowedStudentIds)) {
            return 0;
        }

        $allowedStatuses = ['present', 'late', 'absent'];
        $updatedCount = 0;

        $this->executeInTransaction(function () use ($scheduleId, $entries, $allowedStudentIds, $allowedStatuses, &$updatedCount): void {
            foreach ($allowedStudentIds as $studentId) {
                $entry = $entries[$studentId] ?? [];
                $status = trim((string) ($entry['status'] ?? ''));
                $note = trim((string) ($entry['note'] ?? ''));

                if (!in_array($status, $allowedStatuses, true)) {
                    $this->executeStatement(
                        'DELETE FROM attendance WHERE schedule_id = :schedule_id AND student_id = :student_id',
                        [
                            'schedule_id' => $scheduleId,
                            'student_id' => $studentId,
                        ]
                    );
                    continue;
                }

                $this->executeStatement(
                    'INSERT INTO attendance (schedule_id, student_id, status, note)
                    VALUES (:schedule_id, :student_id, :status, :note)
                    ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note)',
                    [
                        'schedule_id' => $scheduleId,
                        'student_id' => $studentId,
                        'status' => $status,
                        'note' => $note !== '' ? $note : null,
                    ]
                );

                $updatedCount++;
            }
        });

        return $updatedCount;
    }
}
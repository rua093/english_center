<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class SchedulesTableModel
{
    use TableModelUtils;
    public function listDetailed(): array
    {
        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                c.class_name, r.room_name, u.full_name AS teacher_name
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id
            INNER JOIN users u ON u.id = s.teacher_id
            ORDER BY s.study_date DESC, s.start_time DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, class_id, room_id, teacher_id, study_date, start_time, end_time FROM schedules WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $roomId = (int) ($data['room_id'] ?? 0);
        $payload = [
            'class_id' => (int) ($data['class_id'] ?? 0),
            'room_id' => $roomId > 0 ? $roomId : null,
            'teacher_id' => (int) ($data['teacher_id'] ?? 0),
            'study_date' => (string) ($data['study_date'] ?? ''),
            'start_time' => (string) ($data['start_time'] ?? ''),
            'end_time' => (string) ($data['end_time'] ?? ''),
        ];

        if ($id > 0) {
            $sql = 'UPDATE schedules SET class_id=:class_id, room_id=:room_id, teacher_id=:teacher_id,
                study_date=:study_date, start_time=:start_time, end_time=:end_time WHERE id=:id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return;
        }

        $sql = 'INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time)
            VALUES (:class_id, :room_id, :teacher_id, :study_date, :start_time, :end_time)';
        $this->executeStatement($sql, $payload);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM schedules WHERE id = :id', ['id' => $id]);
    }

    public function listUpcomingForTeacher(int $teacherId, string $endDate, int $limit = 10): array
    {
        $limit = $this->clampLimit($limit, 10, 100);
        $sql = "SELECT s.id AS schedule_id, c.class_name, s.study_date, s.start_time, s.end_time, COALESCE(r.room_name, 'Online') AS room_name
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id
            WHERE s.teacher_id = :teacher_id
                AND s.study_date >= CURDATE()
                AND s.study_date <= :end_date
            ORDER BY s.study_date ASC, s.start_time ASC
            LIMIT " . $limit;
        return $this->fetchAll($sql, [
            'teacher_id' => $teacherId,
            'end_date' => $endDate,
        ]);
    }

    public function listUpcomingForStudent(int $studentId, int $limit = 5): array
    {
        $limit = $this->clampLimit($limit, 5, 100);
        $sql = "SELECT c.class_name, s.study_date, s.start_time, s.end_time, r.room_name, t.full_name AS teacher_name
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            INNER JOIN class_students cs ON cs.class_id = c.id AND cs.student_id = :student_id
            LEFT JOIN rooms r ON r.id = s.room_id
            INNER JOIN users t ON t.id = s.teacher_id
            WHERE s.study_date >= CURDATE()
            ORDER BY s.study_date ASC, s.start_time ASC
            LIMIT " . $limit;
        return $this->fetchAll($sql, ['student_id' => $studentId]);
    }

    public function rescheduleDate(int $scheduleId, string $newDate): void
    {
        $this->executeStatement('UPDATE schedules SET study_date = :new_date WHERE id = :id', [
            'id' => $scheduleId,
            'new_date' => $newDate,
        ]);
    }
}

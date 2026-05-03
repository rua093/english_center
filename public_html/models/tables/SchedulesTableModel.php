<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class SchedulesTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM schedules', [], 'total', 0);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                c.class_name, r.room_name, u.full_name AS teacher_name, tp.teacher_code
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            ORDER BY s.study_date DESC, s.start_time DESC";
        return $this->fetchAll($sql);
    }

    public function listForAssignmentLookup(): array
    {
        $sql = "SELECT s.id, s.class_id, c.class_name, s.study_date, s.start_time, s.end_time,
                l.actual_title
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN (
                SELECT schedule_id, MIN(actual_title) AS actual_title
                FROM lessons
                WHERE schedule_id IS NOT NULL
                GROUP BY schedule_id
            ) l ON l.schedule_id = s.id
            ORDER BY c.class_name ASC, s.study_date DESC, s.start_time DESC";
        return $this->fetchAll($sql);
    }

    public function listByClass(int $classId): array
    {
        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                COALESCE(r.room_name, 'Online') AS room_name,
                COALESCE(u.full_name, CONCAT('GV #', s.teacher_id)) AS teacher_name,
                tp.teacher_code,
                linked.id AS assigned_lesson_id,
                linked.actual_title AS assigned_lesson_title
            FROM schedules s
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            LEFT JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN (
                SELECT l.schedule_id, MIN(l.id) AS lesson_id
                FROM lessons l
                WHERE l.schedule_id IS NOT NULL
                GROUP BY l.schedule_id
            ) lx ON lx.schedule_id = s.id
            LEFT JOIN lessons linked ON linked.id = lx.lesson_id
            WHERE s.class_id = :class_id
            ORDER BY s.study_date ASC, s.start_time ASC, s.id ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                c.class_name, r.room_name, u.full_name AS teacher_name, tp.teacher_code
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            ORDER BY s.study_date DESC, s.start_time DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, class_id, room_id, teacher_id, study_date, start_time, end_time FROM schedules WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    private function normalizeTimeValue(string $time): ?string
    {
        $normalized = trim($time);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $normalized) === 1) {
            $normalized .= ':00';
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $normalized) !== 1) {
            return null;
        }

        [$hours, $minutes, $seconds] = array_map('intval', explode(':', $normalized));
        if ($hours > 23 || $minutes > 59 || $seconds > 59) {
            return null;
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function assertNoTimeOverlap(array $payload, int $excludeId): void
    {
        $baseParams = [
            'study_date' => $payload['study_date'],
            'start_time' => $payload['start_time'],
            'end_time' => $payload['end_time'],
        ];

        $excludeSql = '';
        if ($excludeId > 0) {
            $excludeSql = ' AND s.id <> :exclude_id';
            $baseParams['exclude_id'] = $excludeId;
        }

        $classConflict = $this->fetchOne(
            "SELECT s.id, c.class_name, s.start_time, s.end_time
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            WHERE s.study_date = :study_date
                AND s.class_id = :class_id
                AND s.start_time < :end_time
                AND s.end_time > :start_time
                {$excludeSql}
            ORDER BY s.start_time ASC
            LIMIT 1",
            array_merge($baseParams, ['class_id' => $payload['class_id']])
        );

        if (is_array($classConflict)) {
            throw new DomainException('Lop hoc da co lich trung gio.');
        }

        $teacherConflict = $this->fetchOne(
            "SELECT s.id, c.class_name, s.start_time, s.end_time
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            WHERE s.study_date = :study_date
                AND s.teacher_id = :teacher_id
                AND s.start_time < :end_time
                AND s.end_time > :start_time
                {$excludeSql}
            ORDER BY s.start_time ASC
            LIMIT 1",
            array_merge($baseParams, ['teacher_id' => $payload['teacher_id']])
        );

        if (is_array($teacherConflict)) {
            throw new DomainException('Giao vien da co lich trung gio.');
        }

        if (($payload['room_id'] ?? null) === null) {
            return;
        }

        $roomConflict = $this->fetchOne(
            "SELECT s.id, c.class_name, COALESCE(r.room_name, 'Online') AS room_name, s.start_time, s.end_time
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            WHERE s.study_date = :study_date
                AND s.room_id = :room_id
                AND s.start_time < :end_time
                AND s.end_time > :start_time
                {$excludeSql}
            ORDER BY s.start_time ASC
            LIMIT 1",
            array_merge($baseParams, ['room_id' => $payload['room_id']])
        );

        if (is_array($roomConflict)) {
            throw new DomainException('Phong hoc da co lich trung gio.');
        }
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $roomId = (int) ($data['room_id'] ?? 0);
        $studyDate = trim((string) ($data['study_date'] ?? ''));
        $startTime = $this->normalizeTimeValue((string) ($data['start_time'] ?? ''));
        $endTime = $this->normalizeTimeValue((string) ($data['end_time'] ?? ''));

        if ($studyDate === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $studyDate) !== 1) {
            throw new DomainException('Ngay hoc khong hop le.');
        }

        if ($startTime === null || $endTime === null) {
            throw new DomainException('Gio hoc khong hop le.');
        }

        if ($startTime >= $endTime) {
            throw new DomainException('Gio ket thuc phai sau gio bat dau.');
        }

        $payload = [
            'class_id' => (int) ($data['class_id'] ?? 0),
            'room_id' => $roomId > 0 ? $roomId : null,
            'teacher_id' => (int) ($data['teacher_id'] ?? 0),
            'study_date' => $studyDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($payload['class_id'] <= 0 || $payload['teacher_id'] <= 0) {
            throw new DomainException('Vui long chon lop hoc va giao vien hop le.');
        }

        $this->assertNoTimeOverlap($payload, $id);

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
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
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

    public function listUpcomingForTeacherFromNow(int $teacherId, string $endAt, int $limit = 10): array
    {
        $limit = $this->clampLimit($limit, 10, 100);
        $sql = "SELECT s.id AS schedule_id, c.class_name, s.study_date, s.start_time, s.end_time, COALESCE(r.room_name, 'Online') AS room_name
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            WHERE s.teacher_id = :teacher_id
                AND TIMESTAMP(s.study_date, s.start_time) >= NOW()
                AND TIMESTAMP(s.study_date, s.start_time) <= :end_at
            ORDER BY s.study_date ASC, s.start_time ASC
            LIMIT " . $limit;
        return $this->fetchAll($sql, [
            'teacher_id' => $teacherId,
            'end_at' => $endAt,
        ]);
    }

    public function listUpcomingForStudent(int $studentId, int $limit = 5): array
    {
        $limit = $this->clampLimit($limit, 5, 100);
        $sql = "SELECT c.class_name, s.study_date, s.start_time, s.end_time, r.room_name, t.full_name AS teacher_name, tp.teacher_code
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            INNER JOIN class_students cs ON cs.class_id = c.id AND cs.student_id = :student_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users t ON t.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = t.id
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

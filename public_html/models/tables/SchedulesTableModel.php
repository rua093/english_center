<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class SchedulesTableModel
{
    use TableModelUtils;

    public function countDetailed(int $teacherId = 0, string $searchQuery = ''): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($teacherId, $searchQuery, $params);
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            {$whereSql}",
            $params,
            'total',
            0
        );
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

    public function listDetailedPage(int $page, int $perPage, int $teacherId = 0, string $searchQuery = ''): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($teacherId, $searchQuery, $params);

        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                c.class_name, r.room_name, u.full_name AS teacher_name, tp.teacher_code
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            {$whereSql}
            ORDER BY s.study_date DESC, s.start_time DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql, $params);
    }

    public function listDetailedByDateRange(string $startDate, string $endDate, int $teacherId = 0): array
    {
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        $teacherSql = '';
        if ($teacherId > 0) {
            $teacherSql = ' AND s.teacher_id = :teacher_id';
            $params['teacher_id'] = $teacherId;
        }

        $sql = "SELECT s.id, s.class_id, s.room_id, s.teacher_id, s.study_date, s.start_time, s.end_time,
                c.class_name, r.room_name, u.full_name AS teacher_name, tp.teacher_code
            FROM schedules s
            INNER JOIN classes c ON c.id = s.class_id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            WHERE s.study_date >= :start_date
              AND s.study_date <= :end_date
              {$teacherSql}
            ORDER BY s.study_date ASC, s.start_time ASC, s.id ASC";

        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(int $teacherId, string $searchQuery, array &$params): string
    {
        $conditions = [];

        if ($teacherId > 0) {
            $conditions[] = 's.teacher_id = :teacher_id';
            $params['teacher_id'] = $teacherId;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_class'] = $likeValue;
            $params['search_room'] = $likeValue;
            $params['search_teacher'] = $likeValue;
            $params['search_teacher_code'] = $likeValue;
            $params['search_date'] = $likeValue;

            $conditions[] = "(
                CAST(s.id AS CHAR) LIKE :search_id
                OR COALESCE(c.class_name, '') LIKE :search_class
                OR COALESCE(r.room_name, '') LIKE :search_room
                OR COALESCE(u.full_name, '') LIKE :search_teacher
                OR COALESCE(tp.teacher_code, '') LIKE :search_teacher_code
                OR CAST(s.study_date AS CHAR) LIKE :search_date
            )";
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
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

        $dateLabel = $this->formatDateLabel((string) ($payload['study_date'] ?? ''));

        if (is_array($classConflict)) {
            throw new DomainException('Lop hoc da co lich trung gio vao ngay ' . $dateLabel . '.');
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
            throw new DomainException('Giao vien da co lich trung gio vao ngay ' . $dateLabel . '.');
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
            throw new DomainException('Phong hoc da co lich trung gio vao ngay ' . $dateLabel . '.');
        }
    }

    private function formatDateLabel(string $date): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return $date;
        }

        $parsedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $parsedDate instanceof DateTimeImmutable ? $parsedDate->format('d/m/Y') : $date;
    }

    private function normalizeRepeatWeekdays(mixed $rawWeekdays, string $studyDate): array
    {
        $weekdayMap = [];
        $values = is_array($rawWeekdays) ? $rawWeekdays : [$rawWeekdays];

        foreach ($values as $value) {
            $weekday = (int) $value;
            if ($weekday >= 1 && $weekday <= 7) {
                $weekdayMap[$weekday] = true;
            }
        }

        $studyDateWeekday = (int) (new DateTimeImmutable($studyDate))->format('N');
        if ($studyDateWeekday >= 1 && $studyDateWeekday <= 7) {
            $weekdayMap[$studyDateWeekday] = true;
        }

        $weekdays = array_keys($weekdayMap);
        sort($weekdays);

        return $weekdays;
    }

    private function buildRecurringDates(string $studyDate, string $repeatUntil, array $repeatWeekdays): array
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $repeatUntil) !== 1) {
            throw new DomainException('Ngay ket thuc lap khong hop le.');
        }

        $startDate = new DateTimeImmutable($studyDate);
        $endDate = new DateTimeImmutable($repeatUntil);
        if ($endDate < $startDate) {
            throw new DomainException('Ngay ket thuc lap phai lon hon hoac bang ngay hoc dau tien.');
        }

        if ($repeatWeekdays === []) {
            throw new DomainException('Vui long chon it nhat mot thu lap hang tuan.');
        }

        $dates = [];
        $cursor = $startDate;
        while ($cursor <= $endDate) {
            $weekday = (int) $cursor->format('N');
            if (in_array($weekday, $repeatWeekdays, true)) {
                $dates[] = $cursor->format('Y-m-d');
            }

            if (count($dates) > 366) {
                throw new DomainException('So luong lich lap vuot qua gioi han cho phep.');
            }

            $cursor = $cursor->modify('+1 day');
        }

        if ($dates === []) {
            throw new DomainException('Khong tao duoc buoi hoc nao trong khoang lap da chon.');
        }

        return $dates;
    }

    private function buildBasePayload(array $data): array
    {
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

        return $payload;
    }

    private function insertSchedule(array $payload): void
    {
        $sql = 'INSERT INTO schedules (class_id, room_id, teacher_id, study_date, start_time, end_time)
            VALUES (:class_id, :room_id, :teacher_id, :study_date, :start_time, :end_time)';
        $this->executeStatement($sql, $payload);
    }

    public function duplicateWeek(string $weekStart, int $weekCount, int $teacherId = 0): array
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart) !== 1) {
            throw new DomainException('Tuan bat dau khong hop le.');
        }

        $normalizedWeekCount = max(1, min(52, $weekCount));
        $startDate = (new DateTimeImmutable($weekStart))->modify('monday this week');
        $endDate = $startDate->modify('+6 days');
        $sourceSchedules = $this->listDetailedByDateRange(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $teacherId
        );

        if ($sourceSchedules === []) {
            throw new DomainException('Khong co lich nao trong tuan nay de nhan ban.');
        }

        $createdDates = [];
        $createdCount = 0;
        $this->executeInTransaction(function () use ($sourceSchedules, $normalizedWeekCount, &$createdDates, &$createdCount): void {
            for ($weekOffset = 1; $weekOffset <= $normalizedWeekCount; $weekOffset++) {
                foreach ($sourceSchedules as $schedule) {
                    $sourceDate = trim((string) ($schedule['study_date'] ?? ''));
                    if ($sourceDate === '') {
                        continue;
                    }

                    $targetDate = (new DateTimeImmutable($sourceDate))
                        ->modify('+' . (7 * $weekOffset) . ' days')
                        ->format('Y-m-d');

                    $payload = [
                        'class_id' => (int) ($schedule['class_id'] ?? 0),
                        'room_id' => isset($schedule['room_id']) && (int) $schedule['room_id'] > 0 ? (int) $schedule['room_id'] : null,
                        'teacher_id' => (int) ($schedule['teacher_id'] ?? 0),
                        'study_date' => $targetDate,
                        'start_time' => $this->normalizeTimeValue((string) ($schedule['start_time'] ?? '')) ?? '',
                        'end_time' => $this->normalizeTimeValue((string) ($schedule['end_time'] ?? '')) ?? '',
                    ];

                    if ($payload['class_id'] <= 0 || $payload['teacher_id'] <= 0 || $payload['start_time'] === '' || $payload['end_time'] === '') {
                        continue;
                    }

                    $this->assertNoTimeOverlap($payload, 0);
                    $this->insertSchedule($payload);
                    $createdCount++;
                    $createdDates[$targetDate] = true;
                }
            }
        });

        return [
            'week_start' => $startDate->format('Y-m-d'),
            'week_end' => $endDate->format('Y-m-d'),
            'copied_weeks' => $normalizedWeekCount,
            'affected_count' => $createdCount,
            'dates' => array_keys($createdDates),
        ];
    }

    public function save(array $data): array
    {
        $id = (int) ($data['id'] ?? 0);
        $payload = $this->buildBasePayload($data);
        $this->assertNoTimeOverlap($payload, $id);

        if ($id > 0) {
            $sql = 'UPDATE schedules SET class_id=:class_id, room_id=:room_id, teacher_id=:teacher_id,
                study_date=:study_date, start_time=:start_time, end_time=:end_time WHERE id=:id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return [
                'mode' => 'update',
                'affected_count' => 1,
                'dates' => [$payload['study_date']],
            ];
        }

        $repeatMode = trim((string) ($data['repeat_mode'] ?? 'none'));
        if ($repeatMode !== 'weekly') {
            $this->insertSchedule($payload);
            return [
                'mode' => 'create',
                'affected_count' => 1,
                'dates' => [$payload['study_date']],
            ];
        }

        $repeatWeekdays = $this->normalizeRepeatWeekdays($data['repeat_weekdays'] ?? [], $payload['study_date']);
        $dates = $this->buildRecurringDates(
            $payload['study_date'],
            trim((string) ($data['repeat_until'] ?? '')),
            $repeatWeekdays
        );

        $this->executeInTransaction(function () use ($payload, $dates): void {
            foreach ($dates as $date) {
                $currentPayload = $payload;
                $currentPayload['study_date'] = $date;
                $this->assertNoTimeOverlap($currentPayload, 0);
                $this->insertSchedule($currentPayload);
            }
        });

        return [
            'mode' => 'create_recurring',
            'affected_count' => count($dates),
            'dates' => $dates,
        ];
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

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class LessonsTableModel
{
    use TableModelUtils;

    public function listForAssignmentLookup(): array
    {
        $sql = "SELECT l.id, l.class_id, l.actual_title, s.study_date AS lesson_date, c.class_name
            FROM lessons l
            INNER JOIN classes c ON c.id = l.class_id
            LEFT JOIN schedules s ON s.id = l.schedule_id
            ORDER BY c.class_name ASC,
                CASE WHEN s.study_date IS NULL THEN 1 ELSE 0 END ASC,
                s.study_date DESC,
                l.id DESC";
        return $this->fetchAll($sql);
    }

    public function countByStudent(int $studentId): int
    {
        $sql = "SELECT COUNT(*) AS total
            FROM lessons l
            INNER JOIN class_students cs ON cs.class_id = l.class_id
            WHERE cs.student_id = :student_id";
        return (int) $this->fetchScalar($sql, ['student_id' => $studentId], 'total', 0);
    }

    public function countCompletedByStudent(int $studentId): int
    {
        $sql = "SELECT COUNT(*) AS total
            FROM lessons l
            INNER JOIN class_students cs ON cs.class_id = l.class_id
            INNER JOIN schedules s ON s.id = l.schedule_id
            WHERE cs.student_id = :student_id
                AND s.study_date <= CURDATE()";
        return (int) $this->fetchScalar($sql, ['student_id' => $studentId], 'total', 0);
    }

    public function listByClass(int $classId): array
    {
        $sql = "SELECT l.id, l.class_id, l.roadmap_id, l.actual_title, l.actual_content, l.schedule_id,
                cr.topic_title AS roadmap_topic,
                s.study_date, s.start_time, s.end_time, COALESCE(r.room_name, 'Online') AS room_name,
                COALESCE(att.present_count, 0) AS present_count,
                COALESCE(att.late_count, 0) AS late_count,
                COALESCE(att.absent_count, 0) AS absent_count,
                COALESCE(att.total_marked, 0) AS total_marked
            FROM lessons l
            LEFT JOIN course_roadmaps cr ON cr.id = l.roadmap_id
            LEFT JOIN schedules s ON s.id = l.schedule_id
            LEFT JOIN rooms r ON r.id = s.room_id
            LEFT JOIN (
                SELECT a.schedule_id,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                    COUNT(*) AS total_marked
                FROM attendance a
                GROUP BY a.schedule_id
            ) att ON att.schedule_id = l.schedule_id
            WHERE l.class_id = :class_id
            ORDER BY
                CASE WHEN s.study_date IS NULL THEN 1 ELSE 0 END ASC,
                s.study_date ASC,
                s.start_time ASC,
                l.id DESC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, class_id, roadmap_id, actual_title, actual_content, schedule_id FROM lessons WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function listRoadmapsByClass(int $classId): array
    {
        $sql = "SELECT cr.id, cr.`order`, cr.topic_title
            FROM classes c
            INNER JOIN course_roadmaps cr ON cr.course_id = c.course_id
            WHERE c.id = :class_id
            ORDER BY cr.`order` ASC, cr.id ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $roadmapId = (int) ($data['roadmap_id'] ?? 0);
        $title = trim((string) ($data['actual_title'] ?? ''));
        $content = trim((string) ($data['actual_content'] ?? ''));
        $scheduleId = (int) ($data['schedule_id'] ?? 0);

        if ($classId <= 0 || $title === '') {
            throw new DomainException('Vui lòng chọn lớp học và nhập tiêu đề buổi học hợp lệ.');
        }

        $normalizedRoadmapId = $roadmapId > 0 ? $roadmapId : null;
        $normalizedScheduleId = $scheduleId > 0 ? $scheduleId : null;

        if ($normalizedRoadmapId !== null) {
            $roadmapExists = $this->fetchOne(
                'SELECT cr.id
                FROM course_roadmaps cr
                INNER JOIN classes c ON c.course_id = cr.course_id
                WHERE cr.id = :roadmap_id AND c.id = :class_id
                LIMIT 1',
                [
                    'roadmap_id' => $normalizedRoadmapId,
                    'class_id' => $classId,
                ]
            );

            if (!$roadmapExists) {
                throw new DomainException('Lộ trình không thuộc khóa học của lớp đã chọn.');
            }
        }

        if ($normalizedScheduleId !== null) {
            $scheduleExists = $this->fetchOne(
                'SELECT id FROM schedules WHERE id = :schedule_id AND class_id = :class_id LIMIT 1',
                [
                    'schedule_id' => $normalizedScheduleId,
                    'class_id' => $classId,
                ]
            );

            if (!$scheduleExists) {
                throw new DomainException('Lịch học được chọn không thuộc lớp đã chọn.');
            }

            $params = ['schedule_id' => $normalizedScheduleId];
            $sql = 'SELECT id FROM lessons WHERE schedule_id = :schedule_id';
            if ($id > 0) {
                $sql .= ' AND id <> :id';
                $params['id'] = $id;
            }
            $sql .= ' LIMIT 1';

            $scheduleInUse = $this->fetchOne($sql, $params);
            if ($scheduleInUse) {
                throw new DomainException('Lịch học này đã được gán cho một buổi học khác.');
            }
        }

        $payload = [
            'class_id' => $classId,
            'roadmap_id' => $normalizedRoadmapId,
            'actual_title' => $title,
            'actual_content' => $content !== '' ? $content : null,
            'schedule_id' => $normalizedScheduleId,
        ];

        if ($id > 0) {
            $payload['id'] = $id;
            $this->executeStatement(
                'UPDATE lessons
                SET class_id = :class_id,
                    roadmap_id = :roadmap_id,
                    actual_title = :actual_title,
                    actual_content = :actual_content,
                    schedule_id = :schedule_id
                WHERE id = :id',
                $payload
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO lessons (class_id, roadmap_id, actual_title, actual_content, schedule_id)
            VALUES (:class_id, :roadmap_id, :actual_title, :actual_content, :schedule_id)',
            $payload
        );
    }
}
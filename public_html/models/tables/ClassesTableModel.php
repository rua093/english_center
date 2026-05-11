<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class ClassesTableModel extends BaseTableModel
{
    public function countAll(): int
    {
        return $this->countAllFrom('classes');
    }

    public function countDetailed(int $teacherId = 0, string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildDetailedWhereClause($teacherId, $searchQuery, $filters, $params);
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS count
            FROM classes c
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            INNER JOIN users u ON u.id = c.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            {$whereSql}",
            $params,
            'count',
            0
        );
    }

    public function listDetailedWithProgress(): array
    {
        $sql = "SELECT c.id, c.class_name, c.start_date, c.end_date, c.status,
                co.course_name, u.full_name AS teacher_name, tp.teacher_code, c.course_id, c.teacher_id,
                COALESCE(sc.student_count, 0) AS student_count,
                COALESCE(lp.total_lessons, 0) AS total_lessons,
                COALESCE(lp.completed_lessons, 0) AS completed_lessons,
                CASE
                    WHEN COALESCE(lp.total_lessons, 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(lp.completed_lessons, 0) / lp.total_lessons) * 100)
                END AS progress_percent
            FROM classes c
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            INNER JOIN users u ON u.id = c.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN (
                SELECT cs.class_id,
                       COUNT(DISTINCT cs.student_id) AS student_count
                FROM class_students cs
                GROUP BY cs.class_id
            ) sc ON sc.class_id = c.id
            LEFT JOIN (
                SELECT l.class_id,
                       COUNT(*) AS total_lessons,
                       SUM(CASE WHEN l.schedule_id IS NOT NULL AND s.study_date <= CURDATE() THEN 1 ELSE 0 END) AS completed_lessons
                FROM lessons l
                LEFT JOIN schedules s ON s.id = l.schedule_id
                GROUP BY l.class_id
            ) lp ON lp.class_id = c.id
            ORDER BY c.id DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedWithProgressPage(int $page, int $perPage, int $teacherId = 0, string $searchQuery = '', array $filters = []): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $params = [];
        $whereClause = $this->buildDetailedWhereClause($teacherId, $searchQuery, $filters, $params);
        $sql = "SELECT c.id, c.class_name, c.start_date, c.end_date, c.status,
                co.course_name, u.full_name AS teacher_name, tp.teacher_code, c.course_id, c.teacher_id,
                COALESCE(sc.student_count, 0) AS student_count,
                COALESCE(lp.total_lessons, 0) AS total_lessons,
                COALESCE(lp.completed_lessons, 0) AS completed_lessons,
                CASE
                    WHEN COALESCE(lp.total_lessons, 0) = 0 THEN 0
                    ELSE ROUND((COALESCE(lp.completed_lessons, 0) / lp.total_lessons) * 100)
                END AS progress_percent
            FROM classes c
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            INNER JOIN users u ON u.id = c.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN (
                SELECT cs.class_id,
                       COUNT(DISTINCT cs.student_id) AS student_count
                FROM class_students cs
                GROUP BY cs.class_id
            ) sc ON sc.class_id = c.id
            LEFT JOIN (
                SELECT l.class_id,
                       COUNT(*) AS total_lessons,
                       SUM(CASE WHEN l.schedule_id IS NOT NULL AND s.study_date <= CURDATE() THEN 1 ELSE 0 END) AS completed_lessons
                FROM lessons l
                LEFT JOIN schedules s ON s.id = l.schedule_id
                GROUP BY l.class_id
            ) lp ON lp.class_id = c.id
            $whereClause
            ORDER BY c.id DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql, $params);
    }

    private function buildDetailedWhereClause(int $teacherId, string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        if ($teacherId > 0) {
            $conditions[] = 'c.teacher_id = :teacher_id';
            $params['teacher_id'] = $teacherId;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && in_array($status, ['upcoming', 'active', 'graduated', 'cancelled'], true)) {
            $conditions[] = 'c.status = :filter_status';
            $params['filter_status'] = $status;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_class'] = $likeValue;
            $params['search_course'] = $likeValue;
            $params['search_teacher'] = $likeValue;
            $params['search_teacher_code'] = $likeValue;
            $conditions[] = "(
                CAST(c.id AS CHAR) LIKE :search_id
                OR COALESCE(c.class_name, '') LIKE :search_class
                OR COALESCE(co.course_name, '') LIKE :search_course
                OR COALESCE(u.full_name, '') LIKE :search_teacher
                OR COALESCE(tp.teacher_code, '') LIKE :search_teacher_code
            )";
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function findById(int $id): ?array
    {
        return $this->findByIdFrom('classes', $id, 'id, course_id, class_name, teacher_id, start_date, end_date, status');
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseId = (int) ($data['course_id'] ?? 0);
        $className = trim((string) ($data['class_name'] ?? ''));
        $teacherId = (int) ($data['teacher_id'] ?? 0);
        $startDate = trim((string) ($data['start_date'] ?? ''));
        $endDate = trim((string) ($data['end_date'] ?? ''));
        $status = $this->normalizeStatus((string) ($data['status'] ?? 'upcoming'));

        if ($id > 0) {
            $sql = 'UPDATE classes SET course_id=:course_id, class_name=:class_name, teacher_id=:teacher_id,
                start_date=:start_date, end_date=:end_date, status=:status WHERE id=:id';
            $this->executeStatement($sql, [
                'id' => $id,
                'course_id' => $courseId,
                'class_name' => $className,
                'teacher_id' => $teacherId,
                'start_date' => $startDate !== '' ? $startDate : null,
                'end_date' => $endDate !== '' ? $endDate : null,
                'status' => $status,
            ]);
            return;
        }

        $sql = 'INSERT INTO classes (course_id, class_name, teacher_id, start_date, end_date, status)
            VALUES (:course_id, :class_name, :teacher_id, :start_date, :end_date, :status)';
        $this->executeStatement($sql, [
            'course_id' => $courseId,
            'class_name' => $className,
            'teacher_id' => $teacherId,
            'start_date' => $startDate !== '' ? $startDate : null,
            'end_date' => $endDate !== '' ? $endDate : null,
            'status' => $status,
        ]);
    }

    private function normalizeStatus(string $status): string
    {
        $aliases = [
            'planned' => 'upcoming',
            'completed' => 'graduated',
        ];
        $status = $aliases[$status] ?? $status;
        return in_array($status, ['upcoming', 'active', 'graduated', 'cancelled'], true) ? $status : 'upcoming';
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdFrom('classes', $id);
    }

    public function listSimple(): array
    {
        return $this->fetchAll('SELECT id, class_name, course_id, teacher_id FROM classes ORDER BY class_name ASC');
    }

    public function listSimpleByStatus(string $status): array
    {
        $normalizedStatus = $this->normalizeStatus($status);

        return $this->fetchAll(
            'SELECT id, class_name, course_id, teacher_id, status
             FROM classes
             WHERE status = :status
             ORDER BY class_name ASC',
            ['status' => $normalizedStatus]
        );
    }

    public function listForRegistration(): array
    {
        $sql = "SELECT c.id, c.class_name, c.course_id, c.teacher_id, c.status, c.start_date, c.end_date,
                co.course_name, co.base_price, u.full_name AS teacher_name, tp.teacher_code
            FROM classes c
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            LEFT JOIN users u ON u.id = c.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            ORDER BY co.course_name ASC, c.class_name ASC";
        return $this->fetchAll($sql);
    }

    public function listRecent(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 100);
        return $this->fetchAll('SELECT id, class_name, status FROM classes ORDER BY id DESC LIMIT ' . $limit);
    }
}

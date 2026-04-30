<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ClassStudentsTableModel
{
    use TableModelUtils;

    public function countByStudent(int $studentId): int
    {
        if ($studentId <= 0) {
            return 0;
        }

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM class_students cs
             INNER JOIN users u ON u.id = cs.student_id
             WHERE cs.student_id = :student_id
               AND u.deleted_at IS NULL',
            ['student_id' => $studentId],
            'total',
            0
        );
    }

    public function listStudentsForClass(int $classId): array
    {
        if ($classId <= 0) {
            return [];
        }

        $sql = "SELECT DISTINCT cs.class_id, cs.student_id, u.full_name AS student_name, sp.student_code
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE cs.class_id = :class_id
              AND u.deleted_at IS NULL
            ORDER BY u.full_name ASC";

        return $this->fetchAll($sql, ['class_id' => $classId]);
    }

    public function listStudentsByClass(): array
    {
        $sql = "SELECT DISTINCT cs.class_id, cs.student_id, c.class_name, u.full_name AS student_name, sp.student_code
            FROM class_students cs
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN users u ON u.id = cs.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.deleted_at IS NULL
            ORDER BY c.class_name ASC, u.full_name ASC";

        return $this->fetchAll($sql);
    }

    public function existsEnrollment(int $classId, int $studentId): bool
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM class_students WHERE class_id = :class_id AND student_id = :student_id',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
            ],
            'total',
            0
        ) > 0;
    }

    public function findEnrollment(int $classId, int $studentId): ?array
    {
        if ($classId <= 0 || $studentId <= 0) {
            return null;
        }

        return $this->fetchOne(
            'SELECT class_id, student_id, enrollment_date
             FROM class_students
             WHERE class_id = :class_id AND student_id = :student_id
             LIMIT 1',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
            ]
        );
    }

    public function updateLearningStatus(int $classId, int $studentId, string $learningStatus): bool
    {
        return false;
    }

    public function enrollStudent(int $classId, int $studentId, string $learningStatus = 'official', ?string $enrollmentDate = null): void
    {
        if ($classId <= 0 || $studentId <= 0) {
            return;
        }

        $normalizedDate = $enrollmentDate !== null && trim($enrollmentDate) !== ''
            ? trim($enrollmentDate)
            : date('Y-m-d');

        $this->executeStatement(
            'INSERT INTO class_students (class_id, student_id, enrollment_date)
             VALUES (:class_id, :student_id, :enrollment_date)
             ON DUPLICATE KEY UPDATE student_id = student_id',
            [
                'class_id' => $classId,
                'student_id' => $studentId,
                'enrollment_date' => $normalizedDate,
            ]
        );
    }

    public function listRecentClassNamesForStudent(int $studentId, int $limit = 3): array
    {
        $limit = $this->clampLimit($limit, 3, 20);
        $sql = "SELECT c.class_name
            FROM classes c
            INNER JOIN class_students cs ON cs.class_id = c.id
            WHERE cs.student_id = :student_id
            ORDER BY c.id DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql, ['student_id' => $studentId]);
    }

    public function listMyClassesForStudent(int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }

        $sql = "SELECT c.id AS class_id,
                c.class_name,
                c.status AS class_status,
                c.start_date,
                c.end_date,
                co.course_name,
                u.full_name AS teacher_name,
                tp.teacher_code,
                COALESCE(sched.total_schedules, 0) AS total_schedules,
                COALESCE(lesson_count.total_lessons, 0) AS total_lessons
            FROM class_students cs
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            INNER JOIN users u ON u.id = c.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN (
                SELECT class_id, COUNT(*) AS total_schedules
                FROM schedules
                GROUP BY class_id
            ) sched ON sched.class_id = c.id
            LEFT JOIN (
                SELECT class_id, COUNT(*) AS total_lessons
                FROM lessons
                GROUP BY class_id
            ) lesson_count ON lesson_count.class_id = c.id
            WHERE cs.student_id = :student_id
              AND u.deleted_at IS NULL
            ORDER BY c.id DESC, c.class_name ASC";

        return $this->fetchAll($sql, ['student_id' => $studentId]);
    }

    public function listSchedulesForStudent(int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }

        $sql = "SELECT s.id AS schedule_id,
                s.class_id,
                s.study_date,
                s.start_time,
                s.end_time,
                c.class_name,
                COALESCE(r.room_name, 'Online') AS room_name,
                u.full_name AS teacher_name,
                tp.teacher_code
            FROM class_students cs
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN schedules s ON s.class_id = c.id
            INNER JOIN users u ON u.id = s.teacher_id
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            LEFT JOIN rooms r ON r.id = s.room_id AND r.deleted_at IS NULL
            WHERE cs.student_id = :student_id
            ORDER BY s.study_date ASC, s.start_time ASC, c.class_name ASC, s.id ASC";

        return $this->fetchAll($sql, ['student_id' => $studentId]);
    }

    public function listEnrollmentRowsForRegistration(int $limit = 300): array
    {
        $limit = $this->clampLimit($limit, 300, 1000);

        $sql = "SELECT
                cs.class_id,
                cs.student_id,
                cs.enrollment_date,
                u.full_name AS student_name,
                sp.student_code,
                c.class_name,
                c.course_id,
                co.course_name,
                tf.id AS tuition_id,
                tf.total_amount,
                tf.amount_paid,
                tf.payment_plan,
                tf.status AS tuition_status
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            LEFT JOIN tuition_fees tf ON tf.id = (
                SELECT t2.id
                FROM tuition_fees t2
                WHERE t2.student_id = cs.student_id
                  AND t2.class_id = cs.class_id
                ORDER BY t2.id DESC
                LIMIT 1
            )
            WHERE u.deleted_at IS NULL
            ORDER BY cs.id DESC
            LIMIT " . $limit;

        return $this->fetchAll($sql);
    }

    public function findEnrollmentRowForRegistration(int $classId, int $studentId): ?array
    {
        if ($classId <= 0 || $studentId <= 0) {
            return null;
        }

        $sql = "SELECT
                cs.class_id,
                cs.student_id,
                cs.enrollment_date,
                u.full_name AS student_name,
                sp.student_code,
                c.class_name,
                c.course_id,
                co.course_name,
                tf.id AS tuition_id,
                tf.total_amount,
                tf.amount_paid,
                tf.payment_plan,
                tf.status AS tuition_status
            FROM class_students cs
            INNER JOIN users u ON u.id = cs.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN classes c ON c.id = cs.class_id
            INNER JOIN courses co ON co.id = c.course_id AND co.deleted_at IS NULL
            LEFT JOIN tuition_fees tf ON tf.id = (
                SELECT t2.id
                FROM tuition_fees t2
                WHERE t2.student_id = cs.student_id
                  AND t2.class_id = cs.class_id
                ORDER BY t2.id DESC
                LIMIT 1
            )
            WHERE cs.class_id = :class_id
              AND cs.student_id = :student_id
              AND u.deleted_at IS NULL
            LIMIT 1";

        return $this->fetchOne($sql, [
            'class_id' => $classId,
            'student_id' => $studentId,
        ]);
    }
}

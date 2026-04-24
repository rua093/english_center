<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ExtracurricularActivitiesTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM extracurricular_activities', [], 'total', 0);
    }

    public function listWithRegistrationCount(): array
    {
        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.image_thumbnail, a.start_date, a.start_date AS end_date,
                COALESCE(a.location, '') AS location, a.status, a.fee, COUNT(r.id) AS registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            GROUP BY a.id, a.title, a.description, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            ORDER BY a.start_date DESC";
        return $this->fetchAll($sql);
    }

    public function listWithRegistrationCountPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.image_thumbnail, a.start_date, a.start_date AS end_date,
                COALESCE(a.location, '') AS location, a.status, a.fee, COUNT(r.id) AS registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            GROUP BY a.id, a.title, a.description, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            ORDER BY a.start_date DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, title AS activity_name, description, content, location, image_thumbnail, fee, start_date, start_date AS end_date, status FROM extracurricular_activities WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function listForStudentActivities(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.content, a.image_thumbnail, a.start_date,
                COALESCE(a.location, '') AS location, a.status, a.fee,
                COUNT(DISTINCT r.id) AS registered_count,
                (
                    SELECT ar.id
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_registration_id
                    LIMIT 1
                ) AS registration_id,
                COALESCE((
                    SELECT ar.payment_status
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_payment_status
                    LIMIT 1
                ), '') AS payment_status,
                (
                    SELECT ar.registration_date
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_registration_date
                    LIMIT 1
                ) AS registration_date,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_exists
                ) THEN 1 ELSE 0 END AS is_registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            GROUP BY a.id, a.title, a.description, a.content, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            ORDER BY a.start_date DESC, a.id DESC";

        return $this->fetchAll($sql, [
            'user_id_registration_id' => $userId,
            'user_id_payment_status' => $userId,
            'user_id_registration_date' => $userId,
            'user_id_exists' => $userId,
        ]);
    }

    public function findStudentActivityById(int $id, int $userId): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.content, a.image_thumbnail, a.start_date,
                COALESCE(a.location, '') AS location, a.status, a.fee,
                COUNT(DISTINCT r.id) AS registered_count,
                (
                    SELECT ar.id
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_registration_id
                    LIMIT 1
                ) AS registration_id,
                COALESCE((
                    SELECT ar.payment_status
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_payment_status
                    LIMIT 1
                ), '') AS payment_status,
                (
                    SELECT ar.registration_date
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_registration_date
                    LIMIT 1
                ) AS registration_date,
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM activity_registrations ar
                    WHERE ar.activity_id = a.id AND ar.user_id = :user_id_exists
                ) THEN 1 ELSE 0 END AS is_registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            WHERE a.id = :id
            GROUP BY a.id, a.title, a.description, a.content, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            LIMIT 1";

        return $this->fetchOne($sql, [
            'id' => $id,
            'user_id_registration_id' => $userId,
            'user_id_payment_status' => $userId,
            'user_id_registration_date' => $userId,
            'user_id_exists' => $userId,
        ]);
    }

    public function findStudentRegistration(int $activityId, int $userId): ?array
    {
        if ($activityId <= 0 || $userId <= 0) {
            return null;
        }

        return $this->fetchOne(
            'SELECT id, activity_id, user_id, payment_status, registration_date FROM activity_registrations WHERE activity_id = :activity_id AND user_id = :user_id LIMIT 1',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
            ]
        );
    }

    public function joinActivity(int $activityId, int $userId, string $paymentStatus = 'unpaid'): void
    {
        if ($activityId <= 0 || $userId <= 0) {
            return;
        }

        $existing = $this->findStudentRegistration($activityId, $userId);
        if (is_array($existing)) {
            return;
        }

        $this->executeStatement(
            'INSERT INTO activity_registrations (activity_id, user_id, payment_status) VALUES (:activity_id, :user_id, :payment_status)',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => $paymentStatus,
            ]
        );
    }

    public function markActivityPaid(int $activityId, int $userId): bool
    {
        if ($activityId <= 0 || $userId <= 0) {
            return false;
        }

        $affected = $this->executeStatement(
            'UPDATE activity_registrations SET payment_status = :payment_status WHERE activity_id = :activity_id AND user_id = :user_id',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => 'paid',
            ]
        );

        return $affected > 0;
    }

    public function save(array $data): void
    {
        $activityTitle = (string) ($data['activity_name'] ?? $data['title'] ?? 'Untitled Activity');
        $activityStatus = (string) ($data['status'] ?? 'upcoming');
        $allowedStatus = ['upcoming', 'ongoing', 'finished'];
        if (!in_array($activityStatus, $allowedStatus, true)) {
            $activityStatus = 'upcoming';
        }
        $startDateRaw = (string) ($data['start_date'] ?? date('Y-m-d'));
        $startDate = substr(str_replace('T', ' ', $startDateRaw), 0, 10);
        $location = trim((string) ($data['location'] ?? ''));

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE extracurricular_activities SET title = :title, description = :description, content = :content,
                location = :location, image_thumbnail = :image_thumbnail, fee = :fee, start_date = :start_date, status = :status WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'title' => $activityTitle,
                'description' => (string) ($data['description'] ?? ''),
                'content' => (string) ($data['content'] ?? ''),
                'location' => $location !== '' ? $location : null,
                'image_thumbnail' => (string) ($data['image_thumbnail'] ?? ''),
                'fee' => (float) ($data['fee'] ?? 0),
                'start_date' => $startDate,
                'status' => $activityStatus,
            ]);
            return;
        }

        $sql = 'INSERT INTO extracurricular_activities (title, description, content, location, image_thumbnail, fee, start_date, status)
            VALUES (:title, :description, :content, :location, :image_thumbnail, :fee, :start_date, :status)';
        $this->executeStatement($sql, [
            'title' => $activityTitle,
            'description' => (string) ($data['description'] ?? ''),
            'content' => (string) ($data['content'] ?? ''),
            'location' => $location !== '' ? $location : null,
            'image_thumbnail' => (string) ($data['image_thumbnail'] ?? ''),
            'fee' => (float) ($data['fee'] ?? 0),
            'start_date' => $startDate,
            'status' => $activityStatus,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM extracurricular_activities WHERE id = :id', ['id' => $id]);
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ExtracurricularActivitiesTableModel
{
    use TableModelUtils;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM extracurricular_activities a
            {$whereSql}",
            $params,
            'total',
            0
        );
    }

    public function listWithRegistrationCount(): array
    {
        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.image_thumbnail, a.start_date, a.start_date AS end_date,
                COALESCE(a.location, '') AS location, a.status, a.fee, COUNT(r.id) AS registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            WHERE a.deleted_at IS NULL
            GROUP BY a.id, a.title, a.description, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            ORDER BY a.start_date DESC";
        return $this->fetchAll($sql);
    }

    public function listWithRegistrationCountPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.image_thumbnail, a.start_date, a.start_date AS end_date,
                COALESCE(a.location, '') AS location, a.status, a.fee, COUNT(r.id) AS registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            {$whereSql}
            GROUP BY a.id, a.title, a.description, a.image_thumbnail, a.start_date, a.location, a.status, a.fee
            ORDER BY a.start_date DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = ['a.deleted_at IS NULL'];

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && in_array($status, ['upcoming', 'ongoing', 'finished'], true)) {
            $conditions[] = 'a.status = :filter_status';
            $params['filter_status'] = $status;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_description'] = $likeValue;
            $params['search_location'] = $likeValue;
            $params['search_status'] = $likeValue;

            $conditions[] = "(
                CAST(a.id AS CHAR) LIKE :search_id
                OR COALESCE(a.title, '') LIKE :search_name
                OR COALESCE(a.description, '') LIKE :search_description
                OR COALESCE(a.location, '') LIKE :search_location
                OR COALESCE(a.status, '') LIKE :search_status
            )";
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, title AS activity_name, description, content, location, image_thumbnail, fee, start_date, start_date AS end_date, status FROM extracurricular_activities WHERE id = :id AND deleted_at IS NULL LIMIT 1',
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
            WHERE a.deleted_at IS NULL
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
              AND a.deleted_at IS NULL
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
            'SELECT id, activity_id, user_id, payment_status, amount_paid, payment_date, registration_date FROM activity_registrations WHERE activity_id = :activity_id AND user_id = :user_id LIMIT 1',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
            ]
        );
    }

    public function listRegistrationsByActivity(int $activityId): array
    {
        if ($activityId <= 0) {
            return [];
        }

        $sql = "SELECT r.id, r.activity_id, r.user_id, r.payment_status, r.amount_paid, r.payment_date, r.registration_date,
                u.username, u.full_name, sp.student_code
            FROM activity_registrations r
            INNER JOIN extracurricular_activities a ON a.id = r.activity_id AND a.deleted_at IS NULL
            INNER JOIN users u ON u.id = r.user_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE r.activity_id = :activity_id
            ORDER BY r.registration_date DESC, r.id DESC";

        return $this->fetchAll($sql, ['activity_id' => $activityId]);
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
            'INSERT INTO activity_registrations (activity_id, user_id, payment_status, amount_paid, payment_date) VALUES (:activity_id, :user_id, :payment_status, :amount_paid, :payment_date)',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => $paymentStatus,
                'amount_paid' => 0,
                'payment_date' => null,
            ]
        );
    }

    public function removeRegistration(int $activityId, int $userId): bool
    {
        if ($activityId <= 0 || $userId <= 0) {
            return false;
        }

        $affected = $this->executeStatement(
            'DELETE FROM activity_registrations WHERE activity_id = :activity_id AND user_id = :user_id',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
            ]
        );

        return $affected > 0;
    }

    public function markActivityPaid(int $activityId, int $userId): bool
    {
        if ($activityId <= 0 || $userId <= 0) {
            return false;
        }

        $affected = $this->executeStatement(
            'UPDATE activity_registrations SET payment_status = :payment_status, payment_date = NOW() WHERE activity_id = :activity_id AND user_id = :user_id',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => 'paid',
            ]
        );

        return $affected > 0;
    }

    public function updateRegistrationPayment(int $activityId, int $userId, string $paymentStatus, float $amountPaid, ?string $paymentDate): bool
    {
        if ($activityId <= 0 || $userId <= 0) {
            return false;
        }

        $existing = $this->findStudentRegistration($activityId, $userId);
        if (!is_array($existing)) {
            return false;
        }

        $normalizedStatus = $paymentStatus === 'paid' ? 'paid' : 'unpaid';
        $normalizedAmount = max(0, $amountPaid);
        $normalizedPaymentDate = null;

        if (is_string($paymentDate)) {
            $candidate = trim($paymentDate);
            if ($candidate !== '') {
                $normalizedPaymentDate = substr(str_replace('T', ' ', $candidate), 0, 19);
            }
        }

        $affected = $this->executeStatement(
            'UPDATE activity_registrations
             SET payment_status = :payment_status,
                 amount_paid = :amount_paid,
                 payment_date = :payment_date
             WHERE activity_id = :activity_id AND user_id = :user_id',
            [
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => $normalizedStatus,
                'amount_paid' => $normalizedAmount,
                'payment_date' => $normalizedPaymentDate,
            ]
        );

        return $affected > 0 || is_array($existing);
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
        $this->executeStatement('UPDATE extracurricular_activities SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
    }
}

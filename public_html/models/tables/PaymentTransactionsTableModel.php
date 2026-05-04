<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class PaymentTransactionsTableModel
{
    use TableModelUtils;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN classes c ON c.id = t.class_id
            LEFT JOIN courses co ON co.id = c.course_id
            {$whereSql}",
            $params,
            'total',
            0
        );
    }

    public function monthlyCreatedCounts(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 24);
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                COALESCE(SUM(CASE WHEN transaction_status = 'success' THEN amount ELSE 0 END), 0) AS total
            FROM payment_transactions
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT pt.id, pt.tuition_fee_id, pt.amount, pt.payment_method AS method, pt.created_at AS transaction_date,
            pt.transaction_status, t.student_id, u.full_name AS full_name, sp.student_code, c.class_name AS course_name
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY pt.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT pt.id, pt.tuition_fee_id, pt.amount, pt.payment_method AS method, pt.created_at AS transaction_date,
            pt.transaction_status, t.student_id, u.full_name AS full_name, sp.student_code,
            COALESCE(co.course_name, c.class_name) AS course_name,
            c.class_name
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            INNER JOIN classes c ON c.id = t.class_id
            LEFT JOIN courses co ON co.id = c.course_id
            {$whereSql}
            ORDER BY pt.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        $status = strtolower(trim((string) ($filters['transaction_status'] ?? '')));
        if ($status !== '' && in_array($status, ['pending', 'success', 'failed'], true)) {
            $conditions[] = 'pt.transaction_status = :filter_transaction_status';
            $params['filter_transaction_status'] = $status;
        }

        $method = strtolower(trim((string) ($filters['payment_method'] ?? '')));
        if ($method !== '') {
            $conditions[] = 'pt.payment_method = :filter_payment_method';
            $params['filter_payment_method'] = $method;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_code'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_class'] = $likeValue;
            $params['search_course'] = $likeValue;
            $params['search_method'] = $likeValue;
            $params['search_status'] = $likeValue;

            $conditions[] = "(
                CAST(pt.id AS CHAR) LIKE :search_id
                OR COALESCE(sp.student_code, '') LIKE :search_code
                OR COALESCE(u.full_name, '') LIKE :search_name
                OR COALESCE(c.class_name, '') LIKE :search_class
                OR COALESCE(co.course_name, '') LIKE :search_course
                OR COALESCE(pt.payment_method, '') LIKE :search_method
                OR COALESCE(pt.transaction_status, '') LIKE :search_status
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
            'SELECT id, tuition_fee_id, payment_method, amount, transaction_status
             FROM payment_transactions WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function sumSuccessAmountByTuitionId(int $tuitionId): float
    {
        return (float) $this->fetchScalar(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM payment_transactions
             WHERE tuition_fee_id = :tuition_fee_id
               AND transaction_status = 'success'",
            ['tuition_fee_id' => $tuitionId],
            'total',
            0
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $tuitionFeeId = (int) ($data['tuition_fee_id'] ?? 0);
        $paymentMethod = trim((string) ($data['payment_method'] ?? 'bank_transfer'));
        $amount = max(0, (float) ($data['amount'] ?? 0));
        $status = (string) ($data['transaction_status'] ?? 'pending');
        if (!in_array($status, ['success', 'failed', 'pending'], true)) {
            $status = 'pending';
        }

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE payment_transactions
                 SET tuition_fee_id = :tuition_fee_id,
                     payment_method = :payment_method,
                     amount = :amount,
                     transaction_status = :transaction_status
                 WHERE id = :id',
                [
                    'id' => $id,
                    'tuition_fee_id' => $tuitionFeeId,
                    'payment_method' => $paymentMethod,
                    'amount' => $amount,
                    'transaction_status' => $status,
                ]
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO payment_transactions (
                tuition_fee_id, payment_method, amount, transaction_status
             ) VALUES (
                :tuition_fee_id, :payment_method, :amount, :transaction_status
             )',
            [
                'tuition_fee_id' => $tuitionFeeId,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'transaction_status' => $status,
            ]
        );
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM payment_transactions WHERE id = :id', ['id' => $id]);
    }

    public function deleteByTuitionFeeId(int $tuitionId): void
    {
        if ($tuitionId <= 0) {
            return;
        }

        $this->executeStatement(
            'DELETE FROM payment_transactions WHERE tuition_fee_id = :tuition_fee_id',
            ['tuition_fee_id' => $tuitionId]
        );
    }

    public function insertSuccess(int $tuitionId, string $paymentMethod, float $amount): void
    {
        $sql = "INSERT INTO payment_transactions (tuition_fee_id, payment_method, amount, transaction_status)
            VALUES (:tuition_id, :payment_method, :amount, 'success')";
        $this->executeStatement($sql, [
            'tuition_id' => $tuitionId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
        ]);
    }
}

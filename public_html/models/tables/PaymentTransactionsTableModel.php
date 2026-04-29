<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class PaymentTransactionsTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM payment_transactions', [], 'total', 0);
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
            pt.transaction_status, t.student_id, u.full_name AS full_name, c.class_name AS course_name
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY pt.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT pt.id, pt.tuition_fee_id, pt.amount, pt.payment_method AS method, pt.created_at AS transaction_date,
            pt.transaction_status, t.student_id, u.full_name AS full_name, c.class_name AS course_name
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY pt.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql);
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

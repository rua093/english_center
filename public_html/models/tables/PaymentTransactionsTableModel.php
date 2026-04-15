<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class PaymentTransactionsTableModel
{
    use TableModelUtils;
    public function monthlyCreatedCounts(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 24);
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total
            FROM payment_transactions
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT pt.id, pt.tuition_fee_id, pt.amount, pt.payment_method AS method, pt.created_at AS transaction_date,
                pt.transaction_no, pt.transaction_status, t.student_id, u.full_name AS student_name, c.class_name AS course_name
            FROM payment_transactions pt
            INNER JOIN tuition_fees t ON t.id = pt.tuition_fee_id
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY pt.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function insertSuccess(int $tuitionId, string $transactionNo, string $paymentMethod, float $amount): void
    {
        $sql = "INSERT INTO payment_transactions (tuition_fee_id, transaction_no, payment_method, amount, transaction_status, raw_response)
            VALUES (:tuition_id, :transaction_no, :payment_method, :amount, 'success', NULL)";
        $this->executeStatement($sql, [
            'tuition_id' => $tuitionId,
            'transaction_no' => $transactionNo,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
        ]);
    }
}
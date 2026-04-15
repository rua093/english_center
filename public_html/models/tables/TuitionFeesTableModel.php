<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class TuitionFeesTableModel extends BaseTableModel
{
    public function sumTotalAmount(): float
    {
        return (float) $this->fetchScalar('SELECT COALESCE(SUM(total_amount),0) AS total FROM tuition_fees', [], 'total', 0);
    }

    public function sumAmountPaid(): float
    {
        return (float) $this->fetchScalar('SELECT COALESCE(SUM(amount_paid),0) AS total FROM tuition_fees', [], 'total', 0);
    }

    public function listDetailed(): array
    {
        $sql = "SELECT t.id, t.student_id, t.class_id, t.total_amount, t.amount_paid, t.payment_plan, t.status,
                NULL AS due_date, u.full_name AS student_name, c.class_name AS course_name
            FROM tuition_fees t
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY t.id DESC";
        return $this->fetchAll($sql);
    }

    public function findDetailedById(int $id): ?array
    {
        $sql = "SELECT t.id, t.total_amount, t.amount_paid, t.status, u.full_name AS student_name, c.class_name AS class_name
            FROM tuition_fees t
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            WHERE t.id = :id
            LIMIT 1";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdFrom('tuition_fees', $id);
    }

    public function incrementAmountPaid(int $tuitionId, float $amount): void
    {
        $sql = 'UPDATE tuition_fees SET amount_paid = amount_paid + :amount WHERE id = :id';
        $this->executeStatement($sql, ['id' => $tuitionId, 'amount' => $amount]);
    }

    public function updateAmountPaidStatus(int $tuitionId, float $amountPaid, string $status): void
    {
        $this->executeStatement('UPDATE tuition_fees SET amount_paid = :amount_paid, status = :status WHERE id = :id', [
            'id' => $tuitionId,
            'amount_paid' => $amountPaid,
            'status' => $status,
        ]);
    }

    public function findByIdAndStudent(int $tuitionId, int $studentId): ?array
    {
        return $this->fetchOne(
            'SELECT id, total_amount, amount_paid, status, payment_plan FROM tuition_fees WHERE id = :id AND student_id = :student_id LIMIT 1',
            ['id' => $tuitionId, 'student_id' => $studentId]
        );
    }

    public function findLatestByStudent(int $studentId): ?array
    {
        return $this->fetchOne(
            'SELECT id, total_amount, amount_paid, status, payment_plan FROM tuition_fees WHERE student_id = :student_id ORDER BY id DESC LIMIT 1',
            ['student_id' => $studentId]
        );
    }

    public function findTotalById(int $tuitionId): ?float
    {
        $row = $this->findByIdFrom('tuition_fees', $tuitionId, 'total_amount');
        if (!$row) {
            return null;
        }
        return (float) ($row['total_amount'] ?? 0);
    }
}
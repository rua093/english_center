<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class TuitionFeesTableModel extends BaseTableModel
{
    private function resolveStatus(float $totalAmount, float $amountPaid): string
    {
        return $amountPaid >= $totalAmount ? 'paid' : 'debt';
    }

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM tuition_fees', [], 'total', 0);
    }

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
                NULL AS due_date, u.full_name AS full_name, c.class_name AS course_name
            FROM tuition_fees t
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY t.id DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $sql = "SELECT t.id, t.student_id, t.class_id, t.total_amount, t.amount_paid, t.payment_plan, t.status,
                NULL AS due_date, u.full_name AS full_name, c.class_name AS course_name
            FROM tuition_fees t
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            ORDER BY t.id DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql);
    }

    public function findDetailedById(int $id): ?array
    {
        $sql = "SELECT t.id, t.total_amount, t.amount_paid, t.status, u.full_name AS full_name, c.class_name AS class_name
            FROM tuition_fees t
            INNER JOIN users u ON u.id = t.student_id
            INNER JOIN classes c ON c.id = t.class_id
            WHERE t.id = :id
            LIMIT 1";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function findForEdit(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, student_id, class_id, package_id, base_amount, discount_type, discount_amount, total_amount, amount_paid, payment_plan, status
             FROM tuition_fees WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $studentId = (int) ($data['student_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $totalAmount = max(0, (float) ($data['total_amount'] ?? 0));
        $amountPaid = max(0, (float) ($data['amount_paid'] ?? 0));
        $paymentPlan = (string) ($data['payment_plan'] ?? 'full');
        if (!in_array($paymentPlan, ['full', 'monthly'], true)) {
            $paymentPlan = 'full';
        }
        $status = $this->resolveStatus($totalAmount, $amountPaid);

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE tuition_fees
                 SET student_id = :student_id, class_id = :class_id, total_amount = :total_amount,
                     amount_paid = :amount_paid, payment_plan = :payment_plan, status = :status
                 WHERE id = :id',
                [
                    'id' => $id,
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'payment_plan' => $paymentPlan,
                    'status' => $status,
                ]
            );
            return;
        }

        $this->executeStatement(
            'INSERT INTO tuition_fees (
                student_id, class_id, package_id, base_amount, discount_type, discount_amount,
                total_amount, amount_paid, payment_plan, status
             ) VALUES (
                :student_id, :class_id, :package_id, :base_amount, :discount_type, :discount_amount,
                :total_amount, :amount_paid, :payment_plan, :status
             )',
            [
                'student_id' => $studentId,
                'class_id' => $classId,
                'package_id' => null,
                'base_amount' => $totalAmount,
                'discount_type' => null,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'payment_plan' => $paymentPlan,
                'status' => $status,
            ]
        );
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
        $row = $this->findByIdFrom('tuition_fees', $tuitionId, 'id, total_amount');
        if (!$row) {
            return;
        }

        $normalizedAmountPaid = max(0, $amountPaid);
        $totalAmount = (float) ($row['total_amount'] ?? 0);
        $normalizedStatus = $this->resolveStatus($totalAmount, $normalizedAmountPaid);

        $this->executeStatement('UPDATE tuition_fees SET amount_paid = :amount_paid, status = :status WHERE id = :id', [
            'id' => $tuitionId,
            'amount_paid' => $normalizedAmountPaid,
            'status' => $normalizedStatus,
        ]);
    }

    public function findByIdAndStudent(int $tuitionId, int $studentId): ?array
    {
        return $this->fetchOne(
            'SELECT id, total_amount, amount_paid, status, payment_plan FROM tuition_fees WHERE id = :id AND student_id = :student_id LIMIT 1',
            ['id' => $tuitionId, 'student_id' => $studentId]
        );
    }

    public function findByStudentAndClass(int $studentId, int $classId): ?array
    {
        return $this->fetchOne(
            'SELECT id, base_amount, discount_type, discount_amount, total_amount, amount_paid, status, payment_plan
             FROM tuition_fees
             WHERE student_id = :student_id AND class_id = :class_id
             ORDER BY id DESC
             LIMIT 1',
            [
                'student_id' => $studentId,
                'class_id' => $classId,
            ]
        );
    }

    public function createDebtForRegistration(array $data): int
    {
        $studentId = (int) ($data['student_id'] ?? 0);
        $classId = (int) ($data['class_id'] ?? 0);
        $packageId = max(0, (int) ($data['package_id'] ?? 0));
        $baseAmount = max(0, (float) ($data['base_amount'] ?? 0));
        $discountType = trim((string) ($data['discount_type'] ?? 'none'));
        $discountPercent = max(0, min(100, (float) ($data['discount_amount'] ?? 0)));
        $paymentPlan = (string) ($data['payment_plan'] ?? 'full');

        if (!in_array($paymentPlan, ['full', 'monthly'], true)) {
            $paymentPlan = 'full';
        }

        $storedDiscountType = null;
        $storedDiscountAmount = 0.0;

        if ($discountType !== '' && strtolower($discountType) !== 'none' && $discountPercent > 0) {
            $storedDiscountType = strtoupper($discountType);
            $storedDiscountAmount = $discountPercent;
        }

        $appliedDiscount = round(($baseAmount * $storedDiscountAmount) / 100, 2);
        $totalAmount = max(0, round($baseAmount - $appliedDiscount, 2));

        $this->executeStatement(
            'INSERT INTO tuition_fees (
                student_id, class_id, package_id, base_amount, discount_type, discount_amount,
                total_amount, amount_paid, payment_plan, status
            ) VALUES (
                :student_id, :class_id, :package_id, :base_amount, :discount_type, :discount_amount,
                :total_amount, :amount_paid, :payment_plan, :status
            )',
            [
                'student_id' => $studentId,
                'class_id' => $classId,
                'package_id' => $packageId > 0 ? $packageId : null,
                'base_amount' => round($baseAmount, 2),
                'discount_type' => $storedDiscountType,
                'discount_amount' => round($storedDiscountAmount, 2),
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'payment_plan' => $paymentPlan,
                'status' => 'debt',
            ]
        );

        return (int) $this->pdo->lastInsertId();
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
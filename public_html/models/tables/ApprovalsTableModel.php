<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ApprovalsTableModel
{
    use TableModelUtils;
    public function listDetailed(): array
    {
        $sql = "SELECT a.id, a.requester_id, a.approver_id, a.type AS content_type, a.type, a.id AS content_id, a.content, a.status, a.created_at,
                u.full_name AS approver_name, req.full_name AS requester_name
            FROM approvals a
            LEFT JOIN users u ON u.id = a.approver_id
            LEFT JOIN users req ON req.id = a.requester_id
            ORDER BY a.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, requester_id, approver_id, type, content, status, created_at FROM approvals WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE approvals SET approver_id = :approver_id, status = :status, content = :content WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'approver_id' => (int) ($data['approver_id'] ?? 0) ?: null,
                'status' => $data['status'],
                'content' => (string) ($data['reason'] ?? $data['content'] ?? ''),
            ]);
            return;
        }

        $sql = 'INSERT INTO approvals (requester_id, approver_id, type, content, status)
            VALUES (:requester_id, :approver_id, :type, :content, :status)';
        $this->executeStatement($sql, [
            'requester_id' => (int) ($data['requester_id'] ?? 0),
            'approver_id' => (int) ($data['approver_id'] ?? 0) ?: null,
            'type' => (string) ($data['type'] ?? $data['content_type'] ?? 'schedule_change'),
            'content' => (string) ($data['content'] ?? $data['reason'] ?? ''),
            'status' => $data['status'],
        ]);
    }

    public function updateDecision(int $approvalId, int $approverId, string $status, string $content): void
    {
        $this->executeStatement('UPDATE approvals SET approver_id = :approver_id, status = :status, content = :content WHERE id = :id', [
            'id' => $approvalId,
            'approver_id' => $approverId > 0 ? $approverId : null,
            'status' => $status,
            'content' => $content,
        ]);
    }
}
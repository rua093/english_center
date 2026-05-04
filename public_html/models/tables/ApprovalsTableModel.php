<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ApprovalsTableModel
{
    use TableModelUtils;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);
        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
            FROM approvals a
            LEFT JOIN users u ON u.id = a.approver_id
            LEFT JOIN users req ON req.id = a.requester_id
            {$whereSql}",
            $params,
            'total',
            0
        );
    }

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

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT a.id, a.requester_id, a.approver_id, a.type AS content_type, a.type, a.id AS content_id, a.content, a.status, a.created_at,
                u.full_name AS approver_name, req.full_name AS requester_name
            FROM approvals a
            LEFT JOIN users u ON u.id = a.approver_id
            LEFT JOIN users req ON req.id = a.requester_id
            {$whereSql}
            ORDER BY a.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $conditions[] = 'a.status = :filter_status';
            $params['filter_status'] = $status;
        }

        $type = strtolower(trim((string) ($filters['type'] ?? '')));
        if ($type !== '') {
            $conditions[] = 'a.type = :filter_type';
            $params['filter_type'] = $type;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_type'] = $likeValue;
            $params['search_content'] = $likeValue;
            $params['search_requester'] = $likeValue;
            $params['search_approver'] = $likeValue;
            $conditions[] = "(
                CAST(a.id AS CHAR) LIKE :search_id
                OR COALESCE(a.type, '') LIKE :search_type
                OR COALESCE(a.content, '') LIKE :search_content
                OR COALESCE(req.full_name, '') LIKE :search_requester
                OR COALESCE(u.full_name, '') LIKE :search_approver
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

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM approvals WHERE id = :id', ['id' => $id]);
    }
}

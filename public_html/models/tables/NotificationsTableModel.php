<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class NotificationsTableModel
{
    use TableModelUtils;

    public function countDetailed(): int
    {
        return (int) $this->fetchScalar('SELECT COUNT(*) AS total FROM notifications', [], 'total', 0);
    }

    public function listDetailedPage(int $page, int $perPage): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;

        $sql = "SELECT n.id, n.user_id, n.title, n.message, n.is_read, n.created_at,
                u.username, u.full_name
            FROM notifications n
            INNER JOIN users u ON u.id = n.user_id
            ORDER BY n.created_at DESC, n.id DESC
            LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->fetchOne(
            "SELECT n.id, n.user_id, n.title, n.message, n.is_read, n.created_at,
                u.username, u.full_name
            FROM notifications n
            INNER JOIN users u ON u.id = n.user_id
            WHERE n.id = :id
            LIMIT 1",
            ['id' => $id]
        );
    }
    public function listRecent(int $limit = 100): array
    {
        $limit = $this->clampLimit($limit, 100, 500);
        return $this->fetchAll('SELECT id, user_id, title, message, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT ' . $limit);
    }

    public function listByUser(int $userId, int $limit = 5): array
    {
        $limit = $this->clampLimit($limit, 5, 500);
        return $this->fetchAll(
            'SELECT id, user_id, title, message, is_read, created_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT ' . $limit,
            ['user_id' => $userId]
        );
    }

    public function insert(array $data): void
    {
        $sql = 'INSERT INTO notifications (user_id, title, message, is_read) VALUES (:user_id, :title, :message, :is_read)';
        $this->executeStatement($sql, [
            'user_id' => (int) ($data['user_id'] ?? $data['recipient_id'] ?? 0),
            'title' => $data['title'],
            'message' => $data['message'],
            'is_read' => (int) ($data['is_read'] ?? 0),
        ]);
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $userId = (int) ($data['user_id'] ?? $data['recipient_id'] ?? 0);
        $title = trim((string) ($data['title'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        $isRead = (int) ($data['is_read'] ?? 0) === 1 ? 1 : 0;

        if ($userId <= 0 || $title === '' || $message === '') {
            throw new InvalidArgumentException('Vui long chon nguoi nhan va nhap day du tieu de, noi dung thong bao.');
        }

        if ($id > 0) {
            $this->executeStatement(
                'UPDATE notifications SET user_id = :user_id, title = :title, message = :message, is_read = :is_read WHERE id = :id',
                [
                    'id' => $id,
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => $isRead,
                ]
            );
            return;
        }

        $this->insert([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => $isRead,
        ]);
    }

    public function markRead(int $id): void
    {
        $this->executeStatement('UPDATE notifications SET is_read = 1 WHERE id = :id', ['id' => $id]);
    }

    public function deleteById(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $this->executeStatement('DELETE FROM notifications WHERE id = :id', ['id' => $id]);
    }
}
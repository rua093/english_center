<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class NotificationsTableModel
{
    use TableModelUtils;
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

    public function markRead(int $id): void
    {
        $this->executeStatement('UPDATE notifications SET is_read = 1 WHERE id = :id', ['id' => $id]);
    }
}
<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class MaterialsTableModel extends BaseTableModel
{
    public function countAll(): int
    {
        return $this->countAllFrom('materials');
    }

    public function listDetailed(): array
    {
        $sql = "SELECT m.id, m.course_id, m.title, m.file_path, m.type, c.course_name
            FROM materials m
            INNER JOIN courses c ON c.id = m.course_id
            ORDER BY m.id DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->findByIdFrom('materials', $id, 'id, course_id, title, file_path, type');
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $filePath = trim((string) ($data['file_path'] ?? ''));
        $payload = [
            'course_id' => (int) ($data['course_id'] ?? $data['class_id'] ?? 0),
            'title' => trim((string) ($data['title'] ?? '')),
            'file_path' => $filePath,
            'type' => $this->normalizeType((string) ($data['type'] ?? ''), $filePath),
        ];

        if ($id > 0) {
            $sql = 'UPDATE materials SET course_id = :course_id, title = :title, file_path = :file_path, type = :type WHERE id = :id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return;
        }

        $sql = 'INSERT INTO materials (course_id, title, file_path, type) VALUES (:course_id, :title, :file_path, :type)';
        $this->executeStatement($sql, $payload);
    }

    private function normalizeType(string $type, string $filePath): string
    {
        if (in_array($type, ['pdf', 'mp3', 'video'], true)) {
            return $type;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match ($extension) {
            'mp3' => 'mp3',
            'mp4', 'mov', 'webm', 'avi' => 'video',
            default => 'pdf',
        };
    }

    public function deleteById(int $id): void
    {
        $this->deleteByIdFrom('materials', $id);
    }

    public function listRecent(int $limit = 6): array
    {
        $limit = $this->clampLimit($limit, 6, 100);
        $sql = "SELECT m.id, m.title, c.course_name
            FROM materials m
            INNER JOIN courses c ON c.id = m.course_id
            ORDER BY m.id DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql);
    }
}

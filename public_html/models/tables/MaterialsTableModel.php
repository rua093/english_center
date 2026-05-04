<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class MaterialsTableModel extends BaseTableModel
{
    private ?bool $hasDescriptionColumn = null;
    private ?bool $hasTypeColumn = null;

    public function countAll(): int
    {
        return $this->countAllFrom('materials');
    }

    public function countDetailed(string $searchQuery = ''): int
    {
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);
        return (int) $this->fetchScalar("SELECT COUNT(*) AS total FROM materials m {$whereSql}", $params, 'total', 0);
    }

    public function listDetailed(): array
    {
        $descriptionSql = $this->descriptionSelectSql('m');
        $sql = "SELECT m.id, m.title, {$descriptionSql} AS description, m.file_path
            FROM materials m
            ORDER BY m.id DESC";
        return $this->fetchAll($sql);
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = ''): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $descriptionSql = $this->descriptionSelectSql('m');
        $params = [];
        $whereSql = $this->buildSearchWhereClause($searchQuery, $params);
        $sql = "SELECT m.id, m.title, {$descriptionSql} AS description, m.file_path
            FROM materials m
            {$whereSql}
            ORDER BY m.id DESC
            LIMIT {$pagination['limit']} OFFSET {$pagination['offset']}";
        return $this->fetchAll($sql, $params);
    }

    private function buildSearchWhereClause(string $searchQuery, array &$params): string
    {
        $searchQuery = trim($searchQuery);
        if ($searchQuery === '') {
            return '';
        }

        $likeValue = '%' . $searchQuery . '%';
        $params['search_id'] = $likeValue;
        $params['search_title'] = $likeValue;

        $conditions = [
            'CAST(m.id AS CHAR) LIKE :search_id',
            'COALESCE(m.title, \'\') LIKE :search_title',
        ];

        if ($this->hasDescriptionColumn()) {
            $params['search_description'] = $likeValue;
            $conditions[] = 'COALESCE(m.description, \'\') LIKE :search_description';
        }

        return ' WHERE (' . implode(' OR ', $conditions) . ')';
    }

    public function findById(int $id): ?array
    {
        $descriptionSql = $this->descriptionSelectSql('m');
        $sql = "SELECT m.id, m.title, {$descriptionSql} AS description, m.file_path
            FROM materials m
            WHERE m.id = :id
            LIMIT 1";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $filePath = trim((string) ($data['file_path'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $hasDescription = $this->hasDescriptionColumn();
        $hasType = $this->hasTypeColumn();

        $payload = [
            'title' => trim((string) ($data['title'] ?? '')),
            'file_path' => $filePath,
        ];

        $updateColumns = [
            'title = :title',
            'file_path = :file_path',
        ];

        $insertColumns = ['title', 'file_path'];
        $insertValues = [':title', ':file_path'];

        if ($hasDescription) {
            $payload['description'] = $description !== '' ? $description : null;
            $updateColumns[] = 'description = :description';
            $insertColumns[] = 'description';
            $insertValues[] = ':description';
        }

        if ($hasType) {
            $payload['type'] = $this->normalizeType((string) ($data['type'] ?? ''), $filePath);
            $updateColumns[] = 'type = :type';
            $insertColumns[] = 'type';
            $insertValues[] = ':type';
        }

        if ($id > 0) {
            $sql = 'UPDATE materials SET ' . implode(', ', $updateColumns) . ' WHERE id = :id';
            $payload['id'] = $id;
            $this->executeStatement($sql, $payload);
            return;
        }

        $sql = 'INSERT INTO materials (' . implode(', ', $insertColumns) . ') VALUES (' . implode(', ', $insertValues) . ')';
        $this->executeStatement($sql, $payload);
    }

    private function descriptionSelectSql(string $tableAlias): string
    {
        if ($this->hasDescriptionColumn()) {
            return $tableAlias . '.description';
        }

        if ($this->hasTypeColumn()) {
            return "CONCAT('Tai lieu ', " . $tableAlias . ".type)";
        }

        return "''";
    }

    private function hasDescriptionColumn(): bool
    {
        if ($this->hasDescriptionColumn === null) {
            $this->hasDescriptionColumn = $this->canSelectMaterialsColumn('description');
        }

        return $this->hasDescriptionColumn;
    }

    private function hasTypeColumn(): bool
    {
        if ($this->hasTypeColumn === null) {
            $this->hasTypeColumn = $this->canSelectMaterialsColumn('type');
        }

        return $this->hasTypeColumn;
    }

    private function canSelectMaterialsColumn(string $column): bool
    {
        if (!in_array($column, ['description', 'type'], true)) {
            return false;
        }

        try {
            $this->fetchOne('SELECT ' . $column . ' FROM materials LIMIT 1');
            return true;
        } catch (Throwable) {
            return false;
        }
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
        $sql = "SELECT m.id, m.title
            FROM materials m
            ORDER BY m.id DESC
            LIMIT " . $limit;
        return $this->fetchAll($sql);
    }
}

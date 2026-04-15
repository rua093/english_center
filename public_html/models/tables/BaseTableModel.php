<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

abstract class BaseTableModel
{
	use TableModelUtils;

	protected function countAllFrom(string $tableName): int
	{
		$sql = 'SELECT COUNT(*) AS count FROM ' . $tableName;
		return (int) $this->fetchScalar($sql, [], 'count', 0);
	}

	protected function findByIdFrom(string $tableName, int $id, string $columns = '*', string $idColumn = 'id'): ?array
	{
		$sql = sprintf('SELECT %s FROM %s WHERE %s = :id LIMIT 1', $columns, $tableName, $idColumn);
		return $this->fetchOne($sql, ['id' => $id]);
	}

	protected function deleteByIdFrom(string $tableName, int $id, string $idColumn = 'id'): void
	{
		$sql = sprintf('DELETE FROM %s WHERE %s = :id', $tableName, $idColumn);
		$this->executeStatement($sql, ['id' => $id]);
	}

	protected function pagination(int $page, int $perPage, int $defaultPerPage = 20, int $maxPerPage = 100): array
	{
		$normalizedPage = max(1, $page);
		$normalizedPerPage = $this->clampLimit($perPage, $defaultPerPage, $maxPerPage);

		return [
			'page' => $normalizedPage,
			'per_page' => $normalizedPerPage,
			'offset' => ($normalizedPage - 1) * $normalizedPerPage,
			'limit' => $normalizedPerPage,
		];
	}
}

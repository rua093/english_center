<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

trait TableModelUtils
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function executeInTransaction(callable $operation): void
    {
        $this->pdo->beginTransaction();

        try {
            $operation();
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->prepareAndExecute($sql, $params)->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->prepareAndExecute($sql, $params)->fetch();
        return $row ?: null;
    }

    protected function fetchScalar(string $sql, array $params = [], int|string $column = 0, mixed $default = null): mixed
    {
        $statement = $this->prepareAndExecute($sql, $params);

        if (is_int($column)) {
            $value = $statement->fetchColumn($column);
            return $value === false ? $default : $value;
        }

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || !array_key_exists($column, $row)) {
            return $default;
        }

        return $row[$column];
    }

    protected function executeStatement(string $sql, array $params = []): int
    {
        return $this->prepareAndExecute($sql, $params)->rowCount();
    }

    protected function clampLimit(int $limit, int $default = 10, int $max = 500): int
    {
        if ($limit <= 0) {
            return $default;
        }

        return min($limit, $max);
    }

    private function prepareAndExecute(string $sql, array $params = []): PDOStatement
    {
        if ($params === []) {
            $statement = $this->pdo->query($sql);
            if (!$statement instanceof PDOStatement) {
                throw new RuntimeException('Failed to execute SQL query.');
            }

            return $statement;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        return $statement;
    }
}

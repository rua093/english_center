<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class CoursePackagesTableModel extends BaseTableModel
{
    /** @var array<string, bool>|null */
    private ?array $columnMap = null;
    private ?string $resolvedTableName = null;

    public function listActiveForRegistration(?string $onDate = null): array
    {
        $effectiveDate = trim((string) ($onDate ?? ''));
        if ($effectiveDate === '') {
            $effectiveDate = date('Y-m-d');
        }

        $table = $this->tableName();
        $projection = $this->projectionSql('cp');
        $joinCourses = $this->hasColumn('course_id')
            ? 'LEFT JOIN courses c ON c.id = cp.course_id AND c.deleted_at IS NULL'
            : '';
        $courseVisibilityWhere = $this->hasColumn('course_id')
            ? ' AND (cp.course_id IS NULL OR cp.course_id = 0 OR c.id IS NOT NULL)'
            : '';

        if ($this->usesPromotionSchema()) {
            return $this->fetchAll(
                "SELECT {$projection}
                 FROM {$table} cp
                 {$joinCourses}
                 WHERE {$this->activeWhereSql('cp')}
                   {$courseVisibilityWhere}
                   AND (cp.start_date IS NULL OR cp.start_date <= :effective_start)
                   AND (cp.end_date IS NULL OR cp.end_date >= :effective_end)
                 ORDER BY (course_id = 0) DESC, course_id ASC, promo_type ASC, discount_value DESC, name ASC",
                [
                    'effective_start' => $effectiveDate,
                    'effective_end' => $effectiveDate,
                ]
            );
        }

        return $this->fetchAll(
            "SELECT {$projection}
             FROM {$table} cp
             {$joinCourses}
             WHERE {$this->activeWhereSql('cp')}
               {$courseVisibilityWhere}
             ORDER BY (course_id = 0) DESC, course_id ASC, discount_value DESC, name ASC"
        );
    }

    public function findById(int $id): ?array
    {
        $table = $this->tableName();
        $joinCourses = $this->hasColumn('course_id')
            ? 'LEFT JOIN courses c ON c.id = cp.course_id AND c.deleted_at IS NULL'
            : '';
        $courseVisibilityWhere = $this->hasColumn('course_id')
            ? ' AND (cp.course_id IS NULL OR cp.course_id = 0 OR c.id IS NOT NULL)'
            : '';
        return $this->fetchOne(
            "SELECT {$this->projectionSql('cp')}
             FROM {$table} cp
             {$joinCourses}
             WHERE cp.id = :id
               AND {$this->activeWhereSql('cp')}
               {$courseVisibilityWhere}
             LIMIT 1",
            ['id' => $id]
        );
    }

    public function usesPromotionSchema(): bool
    {
        return $this->hasColumn('name')
            && $this->hasColumn('promo_type')
            && $this->hasColumn('discount_value')
            && $this->hasColumn('start_date')
            && $this->hasColumn('end_date');
    }

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildDetailedWhereClause($searchQuery, $filters, $params);
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total FROM ' . $this->tableName() . ' cp LEFT JOIN courses c ON c.id = cp.course_id AND c.deleted_at IS NULL ' . $whereSql,
            $params,
            'total',
            0
        );
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $pagination = $this->pagination($page, $perPage, 10, 200);
        $limit = (int) $pagination['limit'];
        $offset = (int) $pagination['offset'];
        $params = [];

        $table = $this->tableName();
        $projection = $this->projectionSql('cp');
        $courseNameSelect = $this->hasColumn('course_id')
            ? "COALESCE(c.course_name, '') AS course_name"
            : "'' AS course_name";
        $joinCourses = $this->hasColumn('course_id')
            ? 'LEFT JOIN courses c ON c.id = cp.course_id AND c.deleted_at IS NULL'
            : '';
        $whereSql = $this->buildDetailedWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT {$projection}, {$courseNameSelect}
            FROM {$table} cp
            {$joinCourses}
            {$whereSql}
            ORDER BY (course_id = 0) DESC, course_id ASC, discount_value DESC, name ASC
            LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    private function buildDetailedWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [$this->activeWhereSql('cp')];

        if ($this->hasColumn('course_id')) {
            $conditions[] = '(cp.course_id IS NULL OR cp.course_id = 0 OR c.id IS NOT NULL)';
        }

        $promoType = strtoupper(trim((string) ($filters['promo_type'] ?? '')));
        if ($promoType !== '' && in_array($promoType, ['DURATION', 'SOCIAL', 'EVENT', 'GROUP'], true) && $this->hasColumn('promo_type')) {
            $conditions[] = 'cp.promo_type = :filter_promo_type';
            $params['filter_promo_type'] = $promoType;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_course'] = $likeValue;
            $params['search_type'] = $likeValue;
            $conditions[] = "(
                CAST(cp.id AS CHAR) LIKE :search_id
                OR COALESCE(cp.name, '') LIKE :search_name
                OR COALESCE(c.course_name, '') LIKE :search_course
                OR COALESCE(cp.promo_type, '') LIKE :search_type
            )";
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    public function findDetailedById(int $id): ?array
    {
        $table = $this->tableName();
        $projection = $this->projectionSql('cp');
        $courseNameSelect = $this->hasColumn('course_id')
            ? "COALESCE(c.course_name, '') AS course_name"
            : "'' AS course_name";
        $joinCourses = $this->hasColumn('course_id')
            ? 'LEFT JOIN courses c ON c.id = cp.course_id AND c.deleted_at IS NULL'
            : '';
        $courseVisibilityWhere = $this->hasColumn('course_id')
            ? ' AND (cp.course_id IS NULL OR cp.course_id = 0 OR c.id IS NOT NULL)'
            : '';

        return $this->fetchOne(
            "SELECT {$projection}, {$courseNameSelect}
             FROM {$table} cp
             {$joinCourses}
             WHERE cp.id = :id
               AND {$this->activeWhereSql('cp')}{$courseVisibilityWhere}
             LIMIT 1",
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $courseId = max(0, (int) ($data['course_id'] ?? 0));
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            $name = 'Promo';
        }

        $promoType = strtoupper(trim((string) ($data['promo_type'] ?? 'DURATION')));
        if (!in_array($promoType, ['DURATION', 'SOCIAL', 'EVENT', 'GROUP'], true)) {
            $promoType = 'DURATION';
        }

        $discountValue = max(0, min(100, (float) ($data['discount_value'] ?? 0)));
        $startDate = $this->normalizeDateOrNull((string) ($data['start_date'] ?? ''));
        $endDate = $this->normalizeDateOrNull((string) ($data['end_date'] ?? ''));

        $values = [];

        if ($this->hasColumn('course_id')) {
            $values['course_id'] = $courseId > 0 ? $courseId : null;
        }

        if ($this->hasColumn('name')) {
            $values['name'] = $name;
        } elseif ($this->hasColumn('package_name')) {
            $values['package_name'] = $name;
        }

        if ($this->hasColumn('promo_type')) {
            $values['promo_type'] = $promoType;
        }

        if ($this->hasColumn('discount_value')) {
            $values['discount_value'] = $discountValue;
        } elseif ($this->hasColumn('discount_rate')) {
            $values['discount_rate'] = $discountValue;
        }

        if ($this->hasColumn('start_date')) {
            $values['start_date'] = $startDate;
        }

        if ($this->hasColumn('end_date')) {
            $values['end_date'] = $endDate;
        }

        if ($this->hasColumn('number_of_weeks')) {
            $values['number_of_weeks'] = $this->legacyWeeksByPromoType($promoType);
        }

        if ($values === []) {
            throw new RuntimeException('Bảng promotions không có cột hợp lệ để lưu dữ liệu.');
        }

        if ($id > 0) {
            $this->updateById($id, $values);
            return;
        }

        $this->insertRow($values);
    }

    public function deleteById(int $id): void
    {
        if ($this->hasColumn('deleted_at')) {
            $this->softDeleteByIdFrom($this->tableName(), $id);
            return;
        }

        $this->deleteByIdFrom($this->tableName(), $id);
    }

    private function projectionSql(string $alias): string
    {
        $courseIdExpr = $this->hasColumn('course_id')
            ? "COALESCE({$alias}.course_id, 0)"
            : '0';

        $nameExpr = $this->hasColumn('name')
            ? "{$alias}.name"
            : ($this->hasColumn('package_name') ? "{$alias}.package_name" : "CONCAT('Promo #', {$alias}.id)");

        $promoTypeExpr = $this->hasColumn('promo_type')
            ? "{$alias}.promo_type"
            : "'DURATION'";

        $discountExpr = $this->hasColumn('discount_value')
            ? "{$alias}.discount_value"
            : ($this->hasColumn('discount_rate') ? "{$alias}.discount_rate" : '0');

        $startDateExpr = $this->hasColumn('start_date')
            ? "{$alias}.start_date"
            : 'NULL';

        $endDateExpr = $this->hasColumn('end_date')
            ? "{$alias}.end_date"
            : 'NULL';

        return "{$alias}.id AS id,
                {$courseIdExpr} AS course_id,
                {$nameExpr} AS name,
                {$promoTypeExpr} AS promo_type,
                {$discountExpr} AS discount_value,
                {$startDateExpr} AS start_date,
                {$endDateExpr} AS end_date";
    }

    private function normalizeDateOrNull(string $raw): ?string
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        if (!$date instanceof DateTime || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }

    private function legacyWeeksByPromoType(string $promoType): int
    {
        return match ($promoType) {
            'GROUP' => 12,
            'DURATION' => 8,
            'EVENT' => 4,
            default => 2,
        };
    }

    private function updateById(int $id, array $values): void
    {
        $setParts = [];
        $params = ['id' => $id];

        foreach ($values as $column => $value) {
            $param = 'v_' . $column;
            $setParts[] = "{$column} = :{$param}";
            $params[$param] = $value;
        }

        $sql = 'UPDATE ' . $this->tableName() . ' SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        if ($this->hasColumn('deleted_at')) {
            $sql .= ' AND deleted_at IS NULL';
        }
        $this->executeStatement($sql, $params);
    }

    private function activeWhereSql(string $alias): string
    {
        if ($this->hasColumn('deleted_at')) {
            return "{$alias}.deleted_at IS NULL";
        }

        return '1=1';
    }

    private function insertRow(array $values): void
    {
        $columns = array_keys($values);
        $paramNames = [];
        $params = [];

        foreach ($columns as $column) {
            $param = 'v_' . $column;
            $paramNames[] = ':' . $param;
            $params[$param] = $values[$column];
        }

        $sql = 'INSERT INTO ' . $this->tableName() . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $paramNames) . ')';
        $this->executeStatement($sql, $params);
    }

    private function tableName(): string
    {
        if (is_string($this->resolvedTableName)) {
            return $this->resolvedTableName;
        }

        if ($this->tableExists('promotions')) {
            $this->resolvedTableName = 'promotions';
            return $this->resolvedTableName;
        }

        if ($this->tableExists('course_packages')) {
            $this->resolvedTableName = 'course_packages';
            return $this->resolvedTableName;
        }

        // Prefer canonical table name for fresh installs.
        $this->resolvedTableName = 'promotions';
        return $this->resolvedTableName;
    }

    private function tableExists(string $tableName): bool
    {
        $safeTableName = str_replace("'", "''", $tableName);

        try {
            $count = (int) $this->fetchScalar(
                "SELECT COUNT(*) AS total
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                   AND table_name = '{$safeTableName}'",
                [],
                'total',
                0
            );

            if ($count > 0) {
                return true;
            }
        } catch (Throwable) {
            // Ignore and attempt SHOW TABLES fallback below.
        }

        try {
            $rows = $this->fetchAll("SHOW TABLES LIKE '{$safeTableName}'");
            return $rows !== [];
        } catch (Throwable) {
            return false;
        }
    }

    private function hasColumn(string $column): bool
    {
        $map = $this->columnMap();
        return $map[$column] ?? false;
    }

    /** @return array<string, bool> */
    private function columnMap(): array
    {
        if (is_array($this->columnMap)) {
            return $this->columnMap;
        }

        $map = [];

        try {
            $rows = $this->fetchAll(
                'SELECT column_name
                 FROM information_schema.columns
                 WHERE table_schema = DATABASE()
                   AND table_name = :table_name',
                ['table_name' => $this->tableName()]
            );

            foreach ($rows as $row) {
                $columnName = strtolower((string) ($row['column_name'] ?? ''));
                if ($columnName !== '') {
                    $map[$columnName] = true;
                }
            }
        } catch (Throwable) {
            $map = [];
        }

        // Some MySQL setups can return empty rows for information_schema in app users.
        if ($map === []) {
            try {
                $rows = $this->fetchAll('SHOW COLUMNS FROM ' . $this->tableName());
                foreach ($rows as $row) {
                    $columnName = strtolower((string) ($row['Field'] ?? $row['field'] ?? ''));
                    if ($columnName !== '') {
                        $map[$columnName] = true;
                    }
                }
            } catch (Throwable) {
                $map = [];
            }
        }

        $this->columnMap = $map;
        return $map;
    }
}

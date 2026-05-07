<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class NotificationsTableModel
{
    use TableModelUtils;

    private ?bool $hybridEnabled = null;

    public function countDetailed(string $searchQuery = '', array $filters = []): int
    {
        if (!$this->supportsHybrid()) {
            return $this->countDetailedLegacy($searchQuery, $filters);
        }

        $params = [];
        $whereSql = $this->buildHybridSearchWhereClause($searchQuery, $filters, $params);

        return (int) $this->fetchScalar(
            "SELECT COUNT(DISTINCT n.id) AS total
             FROM notifications n
             LEFT JOIN users sender ON sender.id = n.sender_id
             LEFT JOIN notification_targets nt ON nt.notification_id = n.id
             LEFT JOIN users target_user ON nt.target_type = 'USER' AND target_user.id = nt.target_id
             LEFT JOIN roles target_role ON nt.target_type = 'ROLE' AND target_role.id = nt.target_id
             LEFT JOIN classes target_class ON nt.target_type = 'CLASS' AND target_class.id = nt.target_id
             {$whereSql}",
            $params,
            'total',
            0
        );
    }

    public function listDetailedPage(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        if (!$this->supportsHybrid()) {
            return $this->listDetailedPageLegacy($page, $perPage, $searchQuery, $filters);
        }

        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildHybridSearchWhereClause($searchQuery, $filters, $params);

        $rows = $this->fetchAll(
            "SELECT DISTINCT n.id,
                    n.sender_id,
                    n.title,
                    n.message,
                    n.created_at,
                    sender.username AS sender_username,
                    sender.full_name AS sender_name,
                    sender_tp.teacher_code AS sender_teacher_code,
                    sender_sp.student_code AS sender_student_code
             FROM notifications n
             LEFT JOIN users sender ON sender.id = n.sender_id
             LEFT JOIN teacher_profiles sender_tp ON sender_tp.user_id = sender.id
             LEFT JOIN student_profiles sender_sp ON sender_sp.user_id = sender.id
             LEFT JOIN notification_targets nt ON nt.notification_id = n.id
             LEFT JOIN users target_user ON nt.target_type = 'USER' AND target_user.id = nt.target_id
             LEFT JOIN roles target_role ON nt.target_type = 'ROLE' AND target_role.id = nt.target_id
             LEFT JOIN classes target_class ON nt.target_type = 'CLASS' AND target_class.id = nt.target_id
             {$whereSql}
             ORDER BY n.created_at DESC, n.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->hydrateHybridNotificationRows($rows);
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        if (!$this->supportsHybrid()) {
            return $this->findByIdLegacy($id);
        }

        $rows = $this->fetchAll(
            "SELECT n.id,
                    n.sender_id,
                    n.title,
                    n.message,
                    n.created_at,
                    sender.username AS sender_username,
                    sender.full_name AS sender_name,
                    sender_tp.teacher_code AS sender_teacher_code,
                    sender_sp.student_code AS sender_student_code
             FROM notifications n
             LEFT JOIN users sender ON sender.id = n.sender_id
             LEFT JOIN teacher_profiles sender_tp ON sender_tp.user_id = sender.id
             LEFT JOIN student_profiles sender_sp ON sender_sp.user_id = sender.id
             WHERE n.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if ($rows === []) {
            return null;
        }

        $hydrated = $this->hydrateHybridNotificationRows($rows);
        return $hydrated[0] ?? null;
    }

    public function listRecent(int $limit = 100): array
    {
        $limit = $this->clampLimit($limit, 100, 500);

        if (!$this->supportsHybrid()) {
            return $this->fetchAll(
                'SELECT id, sender_id, title, message, created_at FROM notifications ORDER BY created_at DESC LIMIT ' . $limit
            );
        }

        return $this->listDetailedPage(1, $limit);
    }

    public function listByUser(int $userId, int $limit = 5): array
    {
        $limit = $this->clampLimit($limit, 5, 500);

        if ($userId <= 0) {
            return [];
        }

        if (!$this->supportsHybrid()) {
            return $this->fetchAll(
                'SELECT id, sender_id, title, message, created_at, 0 AS is_read FROM notifications ORDER BY created_at DESC LIMIT ' . $limit
            );
        }

        $sql = "SELECT DISTINCT
                    n.id,
                    n.title,
                    n.message,
                    n.created_at,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM notification_reads nr
                            WHERE nr.notification_id = n.id
                              AND nr.user_id = :user_id_read
                        ) THEN 1
                        ELSE 0
                    END AS is_read
                FROM notifications n
                WHERE (
                    EXISTS (
                        SELECT 1
                        FROM notification_targets nt_all
                        WHERE nt_all.notification_id = n.id
                          AND nt_all.target_type = 'ALL'
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM notification_targets nt_user
                        WHERE nt_user.notification_id = n.id
                          AND nt_user.target_type = 'USER'
                          AND nt_user.target_id = :user_id_target
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM notification_targets nt_role
                        WHERE nt_role.notification_id = n.id
                          AND nt_role.target_type = 'ROLE'
                          AND nt_role.target_id = (
                              SELECT u.role_id
                              FROM users u
                              WHERE u.id = :user_id_role
                              LIMIT 1
                          )
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM notification_targets nt_class
                        WHERE nt_class.notification_id = n.id
                          AND nt_class.target_type = 'CLASS'
                          AND (
                              EXISTS (
                                  SELECT 1
                                  FROM class_students cs
                                  WHERE cs.class_id = nt_class.target_id
                                    AND cs.student_id = :user_id_class_student
                              )
                              OR EXISTS (
                                  SELECT 1
                                  FROM classes c
                                  WHERE c.id = nt_class.target_id
                                    AND c.teacher_id = :user_id_class_teacher
                              )
                          )
                    )
                )
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT {$limit}";

        return $this->fetchAll($sql, [
            'user_id_read' => $userId,
            'user_id_target' => $userId,
            'user_id_role' => $userId,
            'user_id_class_student' => $userId,
            'user_id_class_teacher' => $userId,
        ]);
    }

    public function save(array $data): void
    {
        if (!$this->supportsHybrid()) {
            $this->saveLegacy($data);
            return;
        }

        $id = (int) ($data['id'] ?? 0);
        $senderId = max(0, (int) ($data['sender_id'] ?? 0));
        $title = trim((string) ($data['title'] ?? ''));
        $message = trim((string) ($data['message'] ?? $data['content'] ?? ''));
        [$targetType, $targetId] = $this->normalizeTargetPayload($data);

        if ($title === '' || $message === '') {
            throw new InvalidArgumentException('Vui long nhap day du tieu de va noi dung thong bao.');
        }

        if ($targetType === '') {
            throw new InvalidArgumentException('Vui long chon doi tuong nhan thong bao.');
        }

        $this->executeInTransaction(function () use ($id, $senderId, $title, $message, $targetType, $targetId): void {
            if ($id > 0) {
                $this->executeStatement(
                    'UPDATE notifications
                     SET sender_id = :sender_id,
                         title = :title,
                         message = :message
                     WHERE id = :id',
                    [
                        'id' => $id,
                        'sender_id' => $senderId > 0 ? $senderId : null,
                        'title' => $title,
                        'message' => $message,
                    ]
                );

                $this->executeStatement('DELETE FROM notification_reads WHERE notification_id = :notification_id', [
                    'notification_id' => $id,
                ]);
                $this->executeStatement('DELETE FROM notification_targets WHERE notification_id = :notification_id', [
                    'notification_id' => $id,
                ]);

                $notificationId = $id;
            } else {
                $this->executeStatement(
                    'INSERT INTO notifications (sender_id, title, message)
                     VALUES (:sender_id, :title, :message)',
                    [
                        'sender_id' => $senderId > 0 ? $senderId : null,
                        'title' => $title,
                        'message' => $message,
                    ]
                );

                $notificationId = (int) $this->pdo->lastInsertId();
            }

            $this->executeStatement(
                'INSERT INTO notification_targets (notification_id, target_type, target_id)
                 VALUES (:notification_id, :target_type, :target_id)',
                [
                    'notification_id' => $notificationId,
                    'target_type' => $targetType,
                    'target_id' => $targetId,
                ]
            );
        });
    }

    public function markRead(int $id, int $userId = 0): void
    {
        if ($id <= 0 || $userId <= 0) {
            return;
        }

        $this->executeStatement(
            'INSERT INTO notification_reads (notification_id, user_id, read_at)
             VALUES (:notification_id, :user_id, NOW())
             ON DUPLICATE KEY UPDATE read_at = VALUES(read_at)',
            [
                'notification_id' => $id,
                'user_id' => $userId,
            ]
        );
    }

    public function deleteById(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        if (!$this->supportsHybrid()) {
            $this->executeStatement('DELETE FROM notifications WHERE id = :id', ['id' => $id]);
            return;
        }

        $this->executeInTransaction(function () use ($id): void {
            $this->executeStatement('DELETE FROM notification_reads WHERE notification_id = :id', ['id' => $id]);
            $this->executeStatement('DELETE FROM notification_targets WHERE notification_id = :id', ['id' => $id]);
            $this->executeStatement('DELETE FROM notifications WHERE id = :id', ['id' => $id]);
        });
    }

    private function hydrateHybridNotificationRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $notificationIds = [];
        foreach ($rows as $row) {
            $notificationIds[] = (int) ($row['id'] ?? 0);
        }

        $targetsByNotification = $this->loadTargetsByNotificationIds($notificationIds);
        $readCounts = $this->loadReadCountsByNotificationIds($notificationIds);
        $activeUserCount = $this->countActiveUsers();
        $roleCounts = $this->loadRoleRecipientCounts($targetsByNotification);
        $classCounts = $this->loadClassRecipientCounts($targetsByNotification);

        $result = [];
        foreach ($rows as $row) {
            $notificationId = (int) ($row['id'] ?? 0);
            $targets = $targetsByNotification[$notificationId] ?? [];
            $readCount = (int) ($readCounts[$notificationId] ?? 0);

            $target = $targets[0] ?? ['target_type' => 'ALL', 'target_id' => null, 'target_summary' => 'Toàn hệ thống'];
            $row['target_type'] = (string) ($target['target_type'] ?? 'ALL');
            $row['target_id'] = $target['target_id'] ?? null;
            $row['target_summary'] = $this->buildTargetSummaryText($targets);
            $row['total_recipients'] = $this->countRecipientsForTargets($targets, $activeUserCount, $roleCounts, $classCounts);
            $row['read_count'] = $readCount;
            $row['unread_count'] = max(0, (int) $row['total_recipients'] - $readCount);
            $result[] = $row;
        }

        return $result;
    }

    private function loadTargetsByNotificationIds(array $notificationIds): array
    {
        $notificationIds = array_values(array_unique(array_filter(array_map('intval', $notificationIds))));
        if ($notificationIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($notificationIds as $idx => $notificationId) {
            $key = ':notification_id_' . $idx;
            $placeholders[] = $key;
            $params['notification_id_' . $idx] = $notificationId;
        }

        $rows = $this->fetchAll(
            'SELECT nt.notification_id,
                    nt.target_type,
                    nt.target_id,
                    target_role.role_name,
                    target_class.class_name,
                    target_user.full_name AS target_user_name,
                    target_user_tp.teacher_code AS target_user_teacher_code,
                    target_user_sp.student_code AS target_user_student_code
             FROM notification_targets nt
             LEFT JOIN roles target_role ON nt.target_type = "ROLE" AND target_role.id = nt.target_id
             LEFT JOIN classes target_class ON nt.target_type = "CLASS" AND target_class.id = nt.target_id
             LEFT JOIN users target_user ON nt.target_type = "USER" AND target_user.id = nt.target_id
             LEFT JOIN teacher_profiles target_user_tp ON target_user_tp.user_id = target_user.id
             LEFT JOIN student_profiles target_user_sp ON target_user_sp.user_id = target_user.id
             WHERE nt.notification_id IN (' . implode(',', $placeholders) . ')
             ORDER BY nt.notification_id ASC, nt.id ASC',
            $params
        );

        $grouped = [];
        foreach ($rows as $row) {
            $notificationId = (int) ($row['notification_id'] ?? 0);
            if ($notificationId <= 0) {
                continue;
            }

            $grouped[$notificationId][] = [
                'target_type' => (string) ($row['target_type'] ?? ''),
                'target_id' => $row['target_id'] !== null ? (int) $row['target_id'] : null,
                'target_summary' => $this->formatTargetRowSummary($row),
            ];
        }

        return $grouped;
    }

    private function loadReadCountsByNotificationIds(array $notificationIds): array
    {
        $notificationIds = array_values(array_unique(array_filter(array_map('intval', $notificationIds))));
        if ($notificationIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($notificationIds as $idx => $notificationId) {
            $key = ':read_notification_id_' . $idx;
            $placeholders[] = $key;
            $params['read_notification_id_' . $idx] = $notificationId;
        }

        $rows = $this->fetchAll(
            'SELECT notification_id, COUNT(*) AS read_count
             FROM notification_reads
             WHERE notification_id IN (' . implode(',', $placeholders) . ')
             GROUP BY notification_id',
            $params
        );

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['notification_id'] ?? 0)] = (int) ($row['read_count'] ?? 0);
        }

        return $counts;
    }

    private function loadUsersByIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if ($userIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($userIds as $idx => $userId) {
            $key = ':user_id_' . $idx;
            $placeholders[] = $key;
            $params['user_id_' . $idx] = $userId;
        }

        $rows = $this->fetchAll(
            'SELECT u.id, u.full_name, tp.teacher_code, sp.student_code
             FROM users u
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.id IN (' . implode(',', $placeholders) . ')',
            $params
        );

        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(int) ($row['id'] ?? 0)] = $row;
        }

        return $mapped;
    }

    private function loadRoleRecipientCounts(array $targetsByNotification): array
    {
        $roleIds = [];
        foreach ($targetsByNotification as $targets) {
            foreach ($targets as $target) {
                if ((string) ($target['target_type'] ?? '') === 'ROLE' && (int) ($target['target_id'] ?? 0) > 0) {
                    $roleIds[] = (int) $target['target_id'];
                }
            }
        }

        $roleIds = array_values(array_unique($roleIds));
        if ($roleIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($roleIds as $idx => $roleId) {
            $key = ':role_id_' . $idx;
            $placeholders[] = $key;
            $params['role_id_' . $idx] = $roleId;
        }

        $rows = $this->fetchAll(
            'SELECT role_id, COUNT(*) AS total
             FROM users
             WHERE role_id IN (' . implode(',', $placeholders) . ')
               AND deleted_at IS NULL
               AND status = "active"
             GROUP BY role_id',
            $params
        );

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['role_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    private function loadClassRecipientCounts(array $targetsByNotification): array
    {
        $classIds = [];
        foreach ($targetsByNotification as $targets) {
            foreach ($targets as $target) {
                if ((string) ($target['target_type'] ?? '') === 'CLASS' && (int) ($target['target_id'] ?? 0) > 0) {
                    $classIds[] = (int) $target['target_id'];
                }
            }
        }

        $classIds = array_values(array_unique($classIds));
        if ($classIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($classIds as $idx => $classId) {
            $key = ':class_id_' . $idx;
            $placeholders[] = $key;
            $params['class_id_' . $idx] = $classId;
        }

        $rows = $this->fetchAll(
            'SELECT class_id, COUNT(DISTINCT user_id) AS total
             FROM (
                SELECT cs.class_id, cs.student_id AS user_id
                FROM class_students cs
                WHERE cs.class_id IN (' . implode(',', $placeholders) . ')
                UNION ALL
                SELECT c.id AS class_id, c.teacher_id AS user_id
                FROM classes c
                WHERE c.id IN (' . implode(',', $placeholders) . ')
                  AND c.teacher_id IS NOT NULL
             ) recipients
             GROUP BY class_id',
            $params
        );

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) ($row['class_id'] ?? 0)] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    private function countRecipientsForTargets(array $targets, int $activeUserCount, array $roleCounts, array $classCounts): int
    {
        if ($targets === []) {
            return 0;
        }

        $total = 0;
        foreach ($targets as $target) {
            $targetType = (string) ($target['target_type'] ?? '');
            $targetId = (int) ($target['target_id'] ?? 0);

            if ($targetType === 'ALL') {
                return $activeUserCount;
            }

            if ($targetType === 'USER') {
                $total += $targetId > 0 ? 1 : 0;
                continue;
            }

            if ($targetType === 'ROLE') {
                $total += (int) ($roleCounts[$targetId] ?? 0);
                continue;
            }

            if ($targetType === 'CLASS') {
                $total += (int) ($classCounts[$targetId] ?? 0);
            }
        }

        return $total;
    }

    private function buildTargetSummaryText(array $targets): string
    {
        if ($targets === []) {
            return 'Chưa xác định';
        }

        $labels = [];
        foreach ($targets as $target) {
            $labels[] = (string) ($target['target_summary'] ?? 'Chưa xác định');
        }

        $labels = array_values(array_unique(array_filter($labels, static fn (string $label): bool => trim($label) !== '')));
        if ($labels === []) {
            return 'Chưa xác định';
        }

        if (count($labels) === 1) {
            return $labels[0];
        }

        return implode(', ', array_slice($labels, 0, 3)) . (count($labels) > 3 ? ' +' . (count($labels) - 3) : '');
    }

    private function formatTargetRowSummary(array $row): string
    {
        $targetType = strtoupper(trim((string) ($row['target_type'] ?? '')));

        return match ($targetType) {
            'ALL' => 'Toàn hệ thống',
            'ROLE' => 'Vai trò: ' . trim((string) ($row['role_name'] ?? '')),
            'CLASS' => 'Lớp: ' . trim((string) ($row['class_name'] ?? '')),
            'USER' => user_dropdown_label([
                'full_name' => (string) ($row['target_user_name'] ?? 'Người dùng'),
                'teacher_code' => (string) ($row['target_user_teacher_code'] ?? ''),
                'student_code' => (string) ($row['target_user_student_code'] ?? ''),
            ], 'Người dùng'),
            'GROUP' => 'Nhóm #' . (int) ($row['target_id'] ?? 0),
            default => 'Đối tượng #' . (int) ($row['target_id'] ?? 0),
        };
    }

    private function countActiveUsers(): int
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM users
             WHERE deleted_at IS NULL
               AND status = "active"',
            [],
            'total',
            0
        );
    }

    private function normalizeTargetPayload(array $data): array
    {
        $legacyUserId = (int) ($data['user_id'] ?? 0);
        if ($legacyUserId > 0) {
            return ['USER', $legacyUserId];
        }

        $targetType = strtoupper(trim((string) ($data['target_type'] ?? '')));
        $targetId = isset($data['target_id']) && $data['target_id'] !== '' ? (int) $data['target_id'] : null;

        if ($targetType === 'ALL') {
            return ['ALL', null];
        }

        if (in_array($targetType, ['ROLE', 'CLASS', 'GROUP', 'USER'], true) && $targetId !== null && $targetId > 0) {
            return [$targetType, $targetId];
        }

        return ['', null];
    }

    private function buildHybridSearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        $targetType = strtoupper(trim((string) ($filters['target_type'] ?? '')));
        if ($targetType !== '' && in_array($targetType, ['ALL', 'ROLE', 'CLASS', 'GROUP', 'USER'], true)) {
            $conditions[] = 'nt.target_type = :filter_target_type';
            $params['filter_target_type'] = $targetType;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_title'] = $likeValue;
            $params['search_message'] = $likeValue;
            $params['search_sender_name'] = $likeValue;
            $params['search_sender_username'] = $likeValue;
            $params['search_target_user_name'] = $likeValue;
            $params['search_target_user_username'] = $likeValue;
            $params['search_target_role'] = $likeValue;
            $params['search_target_class'] = $likeValue;

            $conditions[] = "(
                CAST(n.id AS CHAR) LIKE :search_id
                OR COALESCE(n.title, '') LIKE :search_title
                OR COALESCE(n.message, '') LIKE :search_message
                OR COALESCE(sender.full_name, '') LIKE :search_sender_name
                OR COALESCE(sender.username, '') LIKE :search_sender_username
                OR COALESCE(target_user.full_name, '') LIKE :search_target_user_name
                OR COALESCE(target_user.username, '') LIKE :search_target_user_username
                OR COALESCE(target_role.role_name, '') LIKE :search_target_role
                OR COALESCE(target_class.class_name, '') LIKE :search_target_class
            )";
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    private function supportsHybrid(): bool
    {
        if ($this->hybridEnabled !== null) {
            return $this->hybridEnabled;
        }

        $this->hybridEnabled = $this->tableExists('notification_targets')
            && $this->tableExists('notification_reads')
            && $this->columnExists('notifications', 'sender_id');

        return $this->hybridEnabled;
    }

    private function tableExists(string $tableName): bool
    {
        $count = (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name',
            ['table_name' => $tableName],
            'total',
            0
        );

        return $count > 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $count = (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name',
            [
                'table_name' => $tableName,
                'column_name' => $columnName,
            ],
            'total',
            0
        );

        return $count > 0;
    }

    private function countDetailedLegacy(string $searchQuery = '', array $filters = []): int
    {
        $params = [];
        $whereSql = $this->buildLegacySearchWhereClause($searchQuery, $filters, $params);

        return (int) $this->fetchScalar(
            "SELECT COUNT(*) AS total
             FROM notifications n
             INNER JOIN users u ON u.id = n.user_id
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             {$whereSql}",
            $params,
            'total',
            0
        );
    }

    private function listDetailedPageLegacy(int $page, int $perPage, string $searchQuery = '', array $filters = []): array
    {
        $normalizedPage = max(1, $page);
        $limit = $this->clampLimit($perPage, 10, 200);
        $offset = ($normalizedPage - 1) * $limit;
        $params = [];
        $whereSql = $this->buildLegacySearchWhereClause($searchQuery, $filters, $params);

        $sql = "SELECT n.id, n.user_id, n.title, n.message, n.is_read, n.created_at,
                    u.username, u.full_name, tp.teacher_code, sp.student_code
                FROM notifications n
                INNER JOIN users u ON u.id = n.user_id
                LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                {$whereSql}
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->fetchAll($sql, $params);
    }

    private function buildLegacySearchWhereClause(string $searchQuery, array $filters, array &$params): string
    {
        $conditions = [];

        $isRead = trim((string) ($filters['is_read'] ?? ''));
        if ($isRead !== '' && ($isRead === '0' || $isRead === '1')) {
            $conditions[] = 'n.is_read = :filter_is_read';
            $params['filter_is_read'] = (int) $isRead;
        }

        $searchQuery = trim($searchQuery);
        if ($searchQuery !== '') {
            $likeValue = '%' . $searchQuery . '%';
            $params['search_id'] = $likeValue;
            $params['search_title'] = $likeValue;
            $params['search_message'] = $likeValue;
            $params['search_username'] = $likeValue;
            $params['search_name'] = $likeValue;
            $params['search_teacher_code'] = $likeValue;
            $params['search_student_code'] = $likeValue;

            $conditions[] = "(
                CAST(n.id AS CHAR) LIKE :search_id
                OR COALESCE(n.title, '') LIKE :search_title
                OR COALESCE(n.message, '') LIKE :search_message
                OR COALESCE(u.username, '') LIKE :search_username
                OR COALESCE(u.full_name, '') LIKE :search_name
                OR COALESCE(tp.teacher_code, '') LIKE :search_teacher_code
                OR COALESCE(sp.student_code, '') LIKE :search_student_code
            )";
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }

    private function findByIdLegacy(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT n.id, n.user_id, n.title, n.message, n.is_read, n.created_at,
                    u.username, u.full_name, tp.teacher_code, sp.student_code
             FROM notifications n
             INNER JOIN users u ON u.id = n.user_id
             LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE n.id = :id
             LIMIT 1",
            ['id' => $id]
        );
    }

    private function saveLegacy(array $data): void
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

        $this->executeStatement(
            'INSERT INTO notifications (user_id, title, message, is_read) VALUES (:user_id, :title, :message, :is_read)',
            [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'is_read' => $isRead,
            ]
        );
    }
}

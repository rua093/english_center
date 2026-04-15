<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class RolePermissionsTableModel
{
    use TableModelUtils;
    public function mapByRole(): array
    {
        $rows = $this->fetchAll('SELECT role_id, permission_id FROM role_permissions');
        $map = [];
        foreach ($rows as $row) {
            $roleId = (int) $row['role_id'];
            if (!isset($map[$roleId])) {
                $map[$roleId] = [];
            }
            $map[$roleId][] = (int) $row['permission_id'];
        }
        return $map;
    }

    public function replaceForRole(int $roleId, array $permissionIds): void
    {
        $this->executeInTransaction(function () use ($roleId, $permissionIds): void {
            $this->executeStatement('DELETE FROM role_permissions WHERE role_id = :role_id', ['role_id' => $roleId]);

            foreach ($permissionIds as $permissionId) {
                $permissionIdInt = (int) $permissionId;
                if ($permissionIdInt <= 0) {
                    continue;
                }

                $this->executeStatement('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)', [
                    'role_id' => $roleId,
                    'permission_id' => $permissionIdInt,
                ]);
            }
        });
    }
}
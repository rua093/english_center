<?php
declare(strict_types=1);

require_once __DIR__ . '/tables/PermissionsTableModel.php';
require_once __DIR__ . '/tables/RolePermissionsTableModel.php';
require_once __DIR__ . '/tables/RolesTableModel.php';
require_once __DIR__ . '/tables/UsersTableModel.php';

final class AdminModel
{
    private UsersTableModel $usersTable;
    private RolesTableModel $rolesTable;
    private PermissionsTableModel $permissionsTable;
    private RolePermissionsTableModel $rolePermissionsTable;

    public function __construct()
    {
        $this->usersTable = new UsersTableModel();
        $this->rolesTable = new RolesTableModel();
        $this->permissionsTable = new PermissionsTableModel();
        $this->rolePermissionsTable = new RolePermissionsTableModel();
    }

    public function listUsers(): array
    {
        return $this->usersTable->listActiveWithRoles();
    }

    public function countUsers(): int
    {
        return $this->usersTable->countActiveWithRoles();
    }

    public function listUsersPage(int $page, int $perPage): array
    {
        return $this->usersTable->listActiveWithRolesPage($page, $perPage);
    }

    public function findUser(int $id): ?array
    {
        return $this->usersTable->findActiveById($id);
    }

    public function findRoleById(int $id): ?array
    {
        return $this->rolesTable->findById($id);
    }

    public function saveUser(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $password = (string) ($data['password'] ?? '');

        $role = $this->findRoleById((int) ($data['role_id'] ?? 0));
        if (!$role) {
            throw new RuntimeException('Vai trò không hợp lệ.');
        }

        $savedUserId = $this->usersTable->save($data);
        $this->usersTable->saveRoleProfile($savedUserId, (string) ($role['role_name'] ?? ''), $data);

        if ($id > 0 && $password !== '') {
            $this->updateUserPassword($savedUserId, $password);
        }
    }

    public function softDeleteUser(int $id): void
    {
        $this->usersTable->softDelete($id);
    }

    public function listRoles(): array
    {
        return $this->rolesTable->listAll();
    }

    public function listPermissions(): array
    {
        return $this->permissionsTable->listAll();
    }

    public function rolePermissionMap(): array
    {
        return $this->rolePermissionsTable->mapByRole();
    }

    public function saveRolePermissions(int $roleId, array $permissionIds): void
    {
        $this->rolePermissionsTable->replaceForRole($roleId, $permissionIds);
    }

    private function updateUserPassword(int $id, string $plainPassword): void
    {
        $this->usersTable->updatePassword($id, $plainPassword);
    }
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class UsersTableModel extends BaseTableModel
{
    public function listActiveWithRoles(): array
    {
        $sql = "SELECT u.id, u.username, u.full_name, u.phone, u.email, u.status, u.created_at, u.role_id,
                r.role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.deleted_at IS NULL
            ORDER BY u.id DESC";
        return $this->fetchAll($sql);
    }

    public function findActiveById(int $id): ?array
    {
        return $this->fetchOne("SELECT id, username, full_name, role_id, phone, email, status
            FROM users
            WHERE id = :id AND deleted_at IS NULL
            LIMIT 1", ['id' => $id]);
    }

    public function save(array $data): void
    {
        $id = (int) ($data['id'] ?? 0);
        $username = trim((string) ($data['username'] ?? ''));
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $roleId = (int) ($data['role_id'] ?? 0);
        $status = (string) ($data['status'] ?? 'active');

        if ($id > 0) {
            $sql = "UPDATE users
                SET username = :username,
                    full_name = :full_name,
                    role_id = :role_id,
                    phone = :phone,
                    email = :email,
                    status = :status
                WHERE id = :id";
            $this->executeStatement($sql, [
                'id' => $id,
                'username' => $username,
                'full_name' => $fullName,
                'role_id' => $roleId,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'status' => $status,
            ]);
            return;
        }

        $password = (string) ($data['password'] ?? '');
        $passwordHash = password_hash($password !== '' ? $password : '123456', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, full_name, role_id, phone, email, status)
            VALUES (:username, :password, :full_name, :role_id, :phone, :email, :status)";
        $this->executeStatement($sql, [
            'username' => $username,
            'password' => $passwordHash,
            'full_name' => $fullName,
            'role_id' => $roleId,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email !== '' ? $email : null,
            'status' => $status,
        ]);
    }

    public function softDelete(int $id): void
    {
        $this->executeStatement('UPDATE users SET deleted_at = NOW(), status = "inactive" WHERE id = :id', ['id' => $id]);
    }

    public function updatePassword(int $id, string $plainPassword): void
    {
        $this->executeStatement('UPDATE users SET password = :password WHERE id = :id', [
            'id' => $id,
            'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ]);
    }

    public function countByRoleName(string $roleName): int
    {
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS count FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = :role_name',
            ['role_name' => $roleName],
            'count',
            0
        );
    }

    public function listByRoleNames(array $roleNames): array
    {
        if (empty($roleNames)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values($roleNames) as $idx => $roleName) {
            $key = ':role_' . $idx;
            $placeholders[] = $key;
            $params['role_' . $idx] = (string) $roleName;
        }

        $sql = 'SELECT u.id, u.full_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE r.role_name IN (' . implode(',', $placeholders) . ')
            ORDER BY u.full_name ASC';
        return $this->fetchAll($sql, $params);
    }
}
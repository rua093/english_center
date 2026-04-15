<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class BankAccountsTableModel
{
    use TableModelUtils;
    public function listDetailed(): array
    {
        $sql = "SELECT id, bank_name, bank_name AS account_name, bin, account_number, account_holder,
                qr_code_static_url, is_default, is_default AS is_primary
            FROM bank_accounts
            ORDER BY is_default DESC, bank_name ASC";
        return $this->fetchAll($sql);
    }

    public function save(array $data): void
    {
        $isDefault = (int) ($data['is_default'] ?? $data['is_primary'] ?? 0);
        $bin = trim((string) ($data['bin'] ?? ''));
        if ($bin === '') {
            $bin = '000000';
        }

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE bank_accounts SET bank_name = :bank_name, bin = :bin, account_number = :account_number,
                account_holder = :account_holder, qr_code_static_url = :qr_code_static_url, is_default = :is_default WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'bank_name' => (string) ($data['bank_name'] ?? $data['account_name'] ?? ''),
                'bin' => $bin,
                'account_number' => $data['account_number'],
                'account_holder' => $data['account_holder'],
                'qr_code_static_url' => (string) ($data['qr_code_static_url'] ?? ''),
                'is_default' => $isDefault,
            ]);
            return;
        }

        $sql = 'INSERT INTO bank_accounts (bank_name, bin, account_number, account_holder, qr_code_static_url, is_default)
            VALUES (:bank_name, :bin, :account_number, :account_holder, :qr_code_static_url, :is_default)';
        $this->executeStatement($sql, [
            'bank_name' => (string) ($data['bank_name'] ?? $data['account_name'] ?? ''),
            'bin' => $bin,
            'account_number' => $data['account_number'],
            'account_holder' => $data['account_holder'],
            'qr_code_static_url' => (string) ($data['qr_code_static_url'] ?? ''),
            'is_default' => $isDefault,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM bank_accounts WHERE id = :id', ['id' => $id]);
    }
}
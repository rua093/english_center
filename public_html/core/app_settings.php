<?php
declare(strict_types=1);

function app_settings_connection(): PDO
{
    if (!class_exists('Database', false)) {
        require_once __DIR__ . '/database.php';
    }

    return Database::connection();
}

function app_settings_table_name(): string
{
    return 'app_settings';
}

function app_settings_ensure_schema(?PDO $pdo = null): void
{
    $pdo = $pdo ?? app_settings_connection();
    $tableName = app_settings_table_name();

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `setting_key` VARCHAR(191) NOT NULL,
            `setting_value` LONGTEXT DEFAULT NULL,
            `updated_by_user_id` BIGINT UNSIGNED DEFAULT NULL,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function app_setting_get(string $key, ?string $default = null): ?string
{
    $normalizedKey = trim($key);
    if ($normalizedKey === '') {
        return $default;
    }

    $pdo = app_settings_connection();
    app_settings_ensure_schema($pdo);

    $stmt = $pdo->prepare(
        'SELECT setting_value FROM `' . app_settings_table_name() . '` WHERE setting_key = :setting_key LIMIT 1'
    );
    $stmt->execute(['setting_key' => $normalizedKey]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        return $default;
    }

    return is_string($value) ? $value : $default;
}

function app_setting_set(string $key, ?string $value): bool
{
    $normalizedKey = trim($key);
    if ($normalizedKey === '') {
        return false;
    }

    $pdo = app_settings_connection();
    app_settings_ensure_schema($pdo);

    $userId = function_exists('auth_user') ? (int) ((auth_user()['id'] ?? 0)) : 0;

    $stmt = $pdo->prepare(
        'INSERT INTO `' . app_settings_table_name() . '` (
            setting_key,
            setting_value,
            updated_by_user_id,
            updated_at
        ) VALUES (
            :setting_key,
            :setting_value,
            :updated_by_user_id,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            updated_by_user_id = VALUES(updated_by_user_id),
            updated_at = NOW()'
    );

    return $stmt->execute([
        'setting_key' => $normalizedKey,
        'setting_value' => $value,
        'updated_by_user_id' => $userId > 0 ? $userId : null,
    ]);
}

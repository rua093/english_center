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

function custom_ui_media_catalog(): array
{
    return [
        'home_intro_video' => [
            'setting_key' => 'home_intro_video_url',
            'preset' => 'home_intro_video',
            'default_url' => '/assets/videodemo/intro.mp4',
            'page_slug' => 'home',
            'accept' => 'video/*',
            'media_kind' => 'video',
            'title_key' => 'admin.custom_ui.home_intro_video',
            'description_key' => 'admin.custom_ui.home_intro_video_copy',
            'empty_key' => 'admin.custom_ui.home_intro_video_empty',
            'live_key' => 'admin.custom_ui.home_intro_video_live',
        ],
        'courses_hero_media' => [
            'setting_key' => 'courses_hero_media_url',
            'preset' => 'ui_banner_media',
            'default_url' => '/assets/images/course3.jpg',
            'page_slug' => 'courses',
            'accept' => 'image/*,video/*',
            'media_kind' => 'image_or_video',
            'title_key' => 'admin.custom_ui.courses_hero_media',
            'description_key' => 'admin.custom_ui.courses_hero_media_copy',
            'empty_key' => 'admin.custom_ui.courses_hero_media_empty',
            'live_key' => 'admin.custom_ui.courses_hero_media_live',
        ],
        'teachers_hero_media' => [
            'setting_key' => 'teachers_hero_media_url',
            'preset' => 'ui_banner_media',
            'default_url' => '/assets/images/teacher_page_banner.jpg',
            'page_slug' => 'teacher-introduce',
            'accept' => 'image/*,video/*',
            'media_kind' => 'image_or_video',
            'title_key' => 'admin.custom_ui.teachers_hero_media',
            'description_key' => 'admin.custom_ui.teachers_hero_media_copy',
            'empty_key' => 'admin.custom_ui.teachers_hero_media_empty',
            'live_key' => 'admin.custom_ui.teachers_hero_media_live',
        ],
        'job_apply_hero_media' => [
            'setting_key' => 'job_apply_hero_media_url',
            'preset' => 'ui_banner_media',
            'default_url' => '/assets/images/recruit.jpg',
            'page_slug' => 'job-apply',
            'accept' => 'image/*,video/*',
            'media_kind' => 'image_or_video',
            'title_key' => 'admin.custom_ui.job_apply_hero_media',
            'description_key' => 'admin.custom_ui.job_apply_hero_media_copy',
            'empty_key' => 'admin.custom_ui.job_apply_hero_media_empty',
            'live_key' => 'admin.custom_ui.job_apply_hero_media_live',
        ],
    ];
}

function custom_ui_media_definition(string $mediaKey): ?array
{
    $catalog = custom_ui_media_catalog();
    $definition = $catalog[trim($mediaKey)] ?? null;
    return is_array($definition) ? $definition : null;
}

function custom_ui_media_normalize_url(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (function_exists('normalize_public_file_url')) {
        return normalize_public_file_url($value);
    }

    if (preg_match('#^(?:https?:)?//#i', $value) === 1 || str_starts_with($value, '/')) {
        return $value;
    }

    return '/' . ltrim($value, '/');
}

function custom_ui_media_current_url(string $mediaKey): string
{
    $definition = custom_ui_media_definition($mediaKey);
    if (!is_array($definition)) {
        return '';
    }

    $settingKey = (string) ($definition['setting_key'] ?? '');
    if ($settingKey === '') {
        return '';
    }

    return custom_ui_media_normalize_url(app_setting_get($settingKey, ''));
}

function custom_ui_media_default_url(string $mediaKey): string
{
    $definition = custom_ui_media_definition($mediaKey);
    if (!is_array($definition)) {
        return '';
    }

    return custom_ui_media_normalize_url((string) ($definition['default_url'] ?? ''));
}

function custom_ui_media_resolve_url(string $mediaKey): string
{
    $currentUrl = custom_ui_media_current_url($mediaKey);
    if ($currentUrl !== '') {
        return $currentUrl;
    }

    return custom_ui_media_default_url($mediaKey);
}

function custom_ui_media_uses_custom_value(string $mediaKey): bool
{
    return custom_ui_media_current_url($mediaKey) !== '';
}

function custom_ui_media_is_video(?string $path): bool
{
    if (function_exists('app_file_path_looks_like_video')) {
        return app_file_path_looks_like_video($path);
    }

    $normalized = strtolower(trim((string) $path));
    if ($normalized === '') {
        return false;
    }

    $extension = strtolower((string) pathinfo((string) (parse_url($normalized, PHP_URL_PATH) ?: $normalized), PATHINFO_EXTENSION));
    return in_array($extension, ['mp4', 'mov', 'webm', 'avi', 'm4v'], true);
}

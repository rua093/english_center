<?php
declare(strict_types=1);

function app_ensure_directory(string $path, int $mode = 0775): bool
{
    if ($path === '') {
        return false;
    }

    if (is_dir($path)) {
        return true;
    }

    return @mkdir($path, $mode, true) || is_dir($path);
}

function app_required_writable_directories(): array
{
    $directories = [
        BASE_PATH . '/storage/cache',
        BASE_PATH . '/storage/cache/bbcode',
        BASE_PATH . '/storage/exports',
        BASE_PATH . '/storage/locks',
        BASE_PATH . '/storage/logs',
        BASE_PATH . '/storage/tmp',
    ];

    if (!function_exists('app_file_storage_uses_s3') || !app_file_storage_uses_s3()) {
        $directories[] = upload_storage_dir();
        $directories[] = upload_storage_dir('homeworks');
        $directories[] = upload_storage_dir('lessons');
        $directories[] = upload_storage_dir('profile');
        $directories[] = upload_storage_dir('teacher-videos');
    }

    return array_values(array_unique($directories));
}

function app_runtime_directory_report(): array
{
    $report = [];

    foreach (app_required_writable_directories() as $directory) {
        $exists = is_dir($directory);
        $writable = $exists && is_writable($directory);

        $report[] = [
            'path' => $directory,
            'exists' => $exists,
            'writable' => $writable,
        ];
    }

    return $report;
}

<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/core/api_helpers.php';

if (PHP_SAPI !== 'cli') {
    $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($requestMethod !== 'POST') {
        api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
    }

    if (!sync_backup_request_is_allowed()) {
        api_error('Forbidden.', ['code' => 'FORBIDDEN'], 403);
    }
}

try {
    $result = sync_backup_pull_latest_into_database();
    api_success('Đồng bộ backup thành công.', $result);
} catch (Throwable $exception) {
    app_log('error', 'Sandbox backup sync failed', [
        'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'remote_addr' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        'server_role' => app_server_role(),
    ]);

    api_error($exception->getMessage(), ['code' => 'SYNC_BACKUP_FAILED'], 500);
}

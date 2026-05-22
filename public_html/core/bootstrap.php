<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
require_once __DIR__ . '/filesystem.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/bbcode.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/get_version.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/page_routes.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/page_actions.php';

function app_is_maintenance_mode(): bool
{
    return defined('MAINTENANCE_MODE') && (bool) MAINTENANCE_MODE;
}

function app_maintenance_bypass_script_paths(): array
{
    return [
        BASE_PATH . '/api/export-sandbox.php',
        BASE_PATH . '/api/import-fallback.php',
        BASE_PATH . '/api/sync-backup.php',
    ];
}

function app_is_maintenance_bypass_request(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $scriptFilename = trim((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    if ($scriptFilename === '') {
        return false;
    }

    $realScriptFilename = realpath($scriptFilename);
    if ($realScriptFilename === false) {
        return false;
    }

    foreach (app_maintenance_bypass_script_paths() as $allowedPath) {
        $realAllowedPath = realpath($allowedPath);
        if ($realAllowedPath !== false && $realAllowedPath === $realScriptFilename) {
            return true;
        }
    }

    return false;
}

function app_render_maintenance_page(): never
{
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: 120');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

    echo '<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Đang bảo trì</title>';
    echo '<style>body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:#f4f7fb;color:#17324d;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}';
    echo '.card{max-width:640px;background:#fff;border:1px solid #d7e3f1;border-radius:18px;padding:32px;box-shadow:0 20px 50px rgba(18,52,86,.08)}';
    echo 'h1{margin:0 0 12px;font-size:28px}p{margin:0;font-size:17px;line-height:1.7}</style></head><body><div class="card">';
    echo '<h1>Đang bảo trì hệ thống</h1>';
    echo '<p>Hệ thống đang đồng bộ dữ liệu từ Sandbox về Server chính, vui lòng quay lại sau 2 phút.</p>';
    echo '</div></body></html>';
    exit;
}

if (app_is_maintenance_mode() && !app_is_maintenance_bypass_request()) {
    app_render_maintenance_page();
}

i18n_bootstrap();
sync_auth_permissions();

<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';

fallback_sync_require_admin_or_internal_token();

$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo '405 Method Not Allowed';
    exit;
}

if (!fallback_sync_has_valid_internal_token()) {
    $csrfToken = request_csrf_token();
    if (!validate_csrf_token($csrfToken)) {
        set_flash('error', 'Phiên thao tác không hợp lệ. Vui lòng thử lại.');
        redirect(page_url('sync-control-admin'));
    }

    $syncPassword = (string) ($_POST['sync_control_password'] ?? '');
    if (!app_sync_control_password_is_valid($syncPassword)) {
        set_flash('error', 'Mật khẩu xác nhận điều phối không đúng.');
        redirect(page_url('sync-control-admin'));
    }
}

$action = strtolower(trim((string) ($_POST['action'] ?? '')));

try {
    $pdo = Database::connection();

    switch ($action) {
        case 'toggle-maintenance':
            $enabled = trim((string) ($_POST['enabled'] ?? '0')) === '1';
            app_set_maintenance_mode($enabled);
            set_flash('success', $enabled ? 'Đã bật maintenance mode.' : 'Đã tắt maintenance mode.');
            redirect(page_url('sync-control-admin'));

        case 'pull-latest-backup':
            failover_guard_sandbox_server();
            $result = sync_backup_pull_latest_into_database();
            set_flash('success', 'Đã kéo backup mới nhất: ' . (string) ($result['object_key'] ?? ''));
            redirect(page_url('sync-control-admin'));

        case 'export-change-sql':
            $result = sync_change_log_export_pending_sql($pdo, false);
            $sql = (string) ($result['sql'] ?? '');
            $count = (int) ($result['exported_count'] ?? 0);
            $downloadName = 'sandbox-incremental-' . gmdate('Ymd-His') . '.sql';
            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Length: ' . (string) strlen($sql));
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('X-Sync-Change-Count: ' . $count);
            echo $sql;
            exit;

        case 'acknowledge-change-batch':
            failover_guard_sandbox_server();
            $batchToken = trim((string) ($_POST['batch_token'] ?? ''));
            $result = sync_change_log_acknowledge_batch($pdo, $batchToken);
            if (fallback_sync_has_valid_internal_token()) {
                http_response_code(200);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'success',
                    'data' => $result,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }

            set_flash('success', 'Đã xác nhận batch incremental: ' . $batchToken);
            redirect(page_url('sync-control-admin'));

        default:
            throw new RuntimeException('Unsupported admin sync action: ' . $action);
    }
} catch (Throwable $exception) {
    app_log('error', 'Admin sync operation failed', [
        'action' => $action,
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'user_id' => (int) ((auth_user()['id'] ?? 0)),
        'server_role' => app_server_role(),
    ]);

    set_flash('error', $exception->getMessage());
    redirect(page_url('sync-control-admin'));
}

function fallback_sync_require_admin_or_internal_token(): void
{
    if (fallback_sync_has_valid_internal_token()) {
        return;
    }

    $user = auth_user();
    if (is_array($user) && (string) ($user['role'] ?? '') === 'admin') {
        return;
    }

    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

function fallback_sync_has_valid_internal_token(): bool
{
    if (!defined('INTERNAL_API_TOKEN')) {
        return false;
    }

    $configuredToken = trim((string) INTERNAL_API_TOKEN);
    if ($configuredToken === '') {
        return false;
    }

    $providedTokens = [
        trim((string) ($_SERVER['HTTP_X_INTERNAL_TOKEN'] ?? '')),
        trim((string) ($_POST['internal_token'] ?? '')),
        trim((string) ($_GET['internal_token'] ?? '')),
    ];

    $authorizationHeader = trim((string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
    if (preg_match('/^Bearer\s+(.+)$/i', $authorizationHeader, $matches)) {
        $providedTokens[] = trim((string) ($matches[1] ?? ''));
    }

    foreach ($providedTokens as $providedToken) {
        if ($providedToken !== '' && hash_equals($configuredToken, $providedToken)) {
            return true;
        }
    }

    return false;
}

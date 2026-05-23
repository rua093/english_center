<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/core/api_helpers.php';

set_time_limit(0);
failover_guard_primary_server();

$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($requestMethod !== 'POST') {
    api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
}

fallback_import_require_admin_or_internal_token();
fallback_import_validate_web_confirmation();
fallback_import_ensure_requirements();

$lockDir = BASE_PATH . '/storage/locks';
if (!app_ensure_directory($lockDir)) {
    api_error('Cannot create lock directory.', ['code' => 'LOCK_DIRECTORY_FAILED'], 500);
}

$lockFile = $lockDir . '/import-fallback.lock';
$lockHandle = fopen($lockFile, 'c+');
if ($lockHandle === false) {
    api_error('Cannot open lock file.', ['code' => 'LOCK_OPEN_FAILED'], 500);
}

if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fclose($lockHandle);
    api_error('Fallback import is already running.', ['code' => 'IMPORT_ALREADY_RUNNING'], 409);
}

$uploadedSqlPath = '';

try {
    $uploadedSqlPath = fallback_import_resolve_uploaded_file();
    $sqlContents = @file_get_contents($uploadedSqlPath);
    if ($sqlContents === false || trim($sqlContents) === '') {
        throw new RuntimeException('Uploaded SQL file is empty or unreadable.');
    }

    $pdo = Database::connection();
    $batchToken = sync_change_log_extract_batch_token($sqlContents);
    fallback_import_sql_dump($pdo, $sqlContents);
    $ackResult = null;
    if ($batchToken !== '') {
        $ackResult = sync_change_log_acknowledge_remote_batch($batchToken);
    }

    fallback_import_cleanup_uploaded_file($uploadedSqlPath);
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    api_success('Đã áp dụng SQL thay đổi từ Sandbox vào server chính.', [
        'imported_file' => basename($uploadedSqlPath),
        'import_mode' => 'incremental',
        'batch_token' => $batchToken,
        'acknowledgement' => $ackResult,
    ]);
} catch (Throwable $exception) {
    app_log('error', 'Primary fallback import failed', [
        'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'remote_addr' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    fallback_import_cleanup_uploaded_file($uploadedSqlPath);
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    api_error($exception->getMessage(), ['code' => 'IMPORT_FALLBACK_FAILED'], 500);
}

function fallback_import_require_admin_or_internal_token(): void
{
    if (fallback_import_has_valid_internal_token()) {
        return;
    }

    $user = auth_user();
    if (is_array($user) && (string) ($user['role'] ?? '') === 'admin') {
        return;
    }

    api_error('Forbidden.', ['code' => 'FORBIDDEN'], 403);
}

function fallback_import_has_valid_internal_token(): bool
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

function fallback_import_validate_web_confirmation(): void
{
    if (fallback_import_has_valid_internal_token()) {
        return;
    }

    if (!validate_csrf_token(request_csrf_token())) {
        api_error('Phiên thao tác không hợp lệ. Vui lòng thử lại.', ['code' => 'INVALID_CSRF'], 419);
    }

    $syncPassword = (string) ($_POST['sync_control_password'] ?? '');
    if (!app_sync_control_password_is_valid($syncPassword)) {
        api_error('Mật khẩu xác nhận điều phối không đúng.', ['code' => 'INVALID_SYNC_CONTROL_PASSWORD'], 403);
    }
}

function fallback_import_ensure_requirements(): void
{
    if (!isset($_FILES['sql_file']) || !is_array($_FILES['sql_file'])) {
        throw new RuntimeException('Missing uploaded SQL file in field sql_file.');
    }
}

function fallback_import_resolve_uploaded_file(): string
{
    $tmpDir = BASE_PATH . '/storage/tmp';
    if (!app_ensure_directory($tmpDir)) {
        throw new RuntimeException('Cannot create temporary directory: ' . $tmpDir);
    }

    $upload = $_FILES['sql_file'];
    $errorCode = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException('SQL upload failed with PHP error code: ' . $errorCode);
    }

    $tmpName = trim((string) ($upload['tmp_name'] ?? ''));
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Uploaded SQL file is missing from temporary upload storage.');
    }

    $targetPath = tempnam($tmpDir, 'fallback_import_');
    if ($targetPath === false) {
        throw new RuntimeException('Cannot create temporary import file.');
    }

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('Cannot move uploaded SQL file into temporary storage.');
    }

    return $targetPath;
}

function fallback_import_sql_dump(PDO $pdo, string $sqlContents): void
{
    $sqlContents = trim($sqlContents);
    if ($sqlContents === '') {
        throw new RuntimeException('SQL import content is empty.');
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    try {
        $pdo->exec($sqlContents);
    } catch (PDOException $exception) {
        throw new RuntimeException('MySQL import failed: ' . $exception->getMessage(), 0, $exception);
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

function fallback_import_cleanup_uploaded_file(string $uploadedSqlPath): void
{
    if ($uploadedSqlPath !== '' && is_file($uploadedSqlPath)) {
        @unlink($uploadedSqlPath);
    }
}

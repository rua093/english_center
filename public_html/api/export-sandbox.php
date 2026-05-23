<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/core/bootstrap.php';
require_once dirname(__DIR__) . '/core/api_helpers.php';

set_time_limit(0);
failover_guard_sandbox_server();

$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if ($requestMethod !== 'POST') {
    api_error('Method not allowed.', ['code' => 'METHOD_NOT_ALLOWED'], 405);
}

fallback_require_admin_or_internal_token();
fallback_ensure_export_requirements();

$tmpDir = BASE_PATH . '/storage/tmp';
$sqlDumpPath = '';
$defaultsFilePath = '';

try {
    if (!app_ensure_directory($tmpDir)) {
        throw new RuntimeException('Cannot create temporary directory: ' . $tmpDir);
    }

    $sqlDumpPath = tempnam($tmpDir, 'sandbox_export_');
    if ($sqlDumpPath === false) {
        throw new RuntimeException('Cannot create temporary SQL dump file.');
    }

    $defaultsFilePath = tempnam($tmpDir, 'sandbox_mysql_');
    if ($defaultsFilePath === false) {
        throw new RuntimeException('Cannot create temporary MySQL defaults file.');
    }

    fallback_write_mysql_defaults_file($defaultsFilePath);
    fallback_run_mysqldump($defaultsFilePath, $sqlDumpPath);

    $targetUrl = trim((string) ($_POST['target_url'] ?? ''));
    $targetToken = trim((string) ($_POST['target_token'] ?? ''));

    if ($targetUrl !== '') {
        $importResponse = fallback_push_dump_to_primary($sqlDumpPath, $targetUrl, $targetToken);
        fallback_cleanup_temp_files([$sqlDumpPath, $defaultsFilePath]);

        api_success('Xuất và gửi backup từ Sandbox thành công.', [
            'target_url' => $targetUrl,
            'response_code' => $importResponse['http_code'],
            'response_preview' => $importResponse['response_preview'],
        ]);
    }

    $downloadName = 'sandbox-fallback-' . gmdate('Ymd-His') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Length: ' . (string) filesize($sqlDumpPath));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    readfile($sqlDumpPath);
} catch (Throwable $exception) {
    app_log('error', 'Sandbox export fallback failed', [
        'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'remote_addr' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    fallback_cleanup_temp_files([$sqlDumpPath, $defaultsFilePath]);
    api_error($exception->getMessage(), ['code' => 'EXPORT_FALLBACK_FAILED'], 500);
}

fallback_cleanup_temp_files([$sqlDumpPath, $defaultsFilePath]);
exit;

function fallback_require_admin_or_internal_token(): void
{
    if (fallback_has_valid_internal_token()) {
        return;
    }

    $user = auth_user();
    if (is_array($user) && (string) ($user['role'] ?? '') === 'admin') {
        return;
    }

    api_error('Forbidden.', ['code' => 'FORBIDDEN'], 403);
}

function fallback_has_valid_internal_token(): bool
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

function fallback_ensure_export_requirements(): void
{
    $requiredConstants = [
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
    ];

    foreach ($requiredConstants as $constantName) {
        if (!defined($constantName) || trim((string) constant($constantName)) === '') {
            throw new RuntimeException('Missing required configuration constant: ' . $constantName);
        }
    }

    if (!function_exists('exec')) {
        throw new RuntimeException('PHP exec() is disabled on this server.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL extension is not available.');
    }
}

function fallback_write_mysql_defaults_file(string $defaultsFilePath): void
{
    $lines = [
        '[client]',
        'host=' . (string) DB_HOST,
        'user=' . (string) DB_USER,
        'password=' . (string) DB_PASS,
    ];

    if (defined('DB_PORT') && trim((string) DB_PORT) !== '') {
        $lines[] = 'port=' . (string) DB_PORT;
    }

    $written = @file_put_contents($defaultsFilePath, implode("\n", $lines) . "\n", LOCK_EX);
    if ($written === false) {
        throw new RuntimeException('Cannot write temporary MySQL defaults file.');
    }

    @chmod($defaultsFilePath, 0600);
}

function fallback_run_mysqldump(string $defaultsFilePath, string $sqlDumpPath): void
{
    $parts = [
        'mysqldump',
        '--defaults-extra-file=' . escapeshellarg($defaultsFilePath),
        '--skip-comments',
        '--compact',
        '--single-transaction',
        '--routines',
        '--triggers',
        '--events',
        '--result-file=' . escapeshellarg($sqlDumpPath),
        escapeshellarg((string) DB_NAME),
    ];

    $command = implode(' ', $parts) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException('mysqldump failed: ' . trim(implode("\n", $output)));
    }

    if (!is_file($sqlDumpPath) || filesize($sqlDumpPath) === 0) {
        throw new RuntimeException('mysqldump completed but SQL dump file is empty.');
    }
}

function fallback_push_dump_to_primary(string $sqlDumpPath, string $targetUrl, string $targetToken): array
{
    $curlFile = curl_file_create($sqlDumpPath, 'application/sql', basename($sqlDumpPath) . '.sql');
    $postFields = [
        'sql_file' => $curlFile,
    ];

    if ($targetToken !== '') {
        $postFields['internal_token'] = $targetToken;
    }

    $curl = curl_init($targetUrl);
    if ($curl === false) {
        throw new RuntimeException('Cannot initialize cURL for fallback import request.');
    }

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 20,
    ]);

    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('Fallback import request failed: ' . $curlError);
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new RuntimeException(
            'Fallback import endpoint returned HTTP ' . $httpCode . ': ' . fallback_truncate_response($response)
        );
    }

    return [
        'http_code' => $httpCode,
        'response_preview' => fallback_truncate_response($response),
    ];
}

function fallback_cleanup_temp_files(array $paths): void
{
    foreach ($paths as $path) {
        $normalizedPath = trim((string) $path);
        if ($normalizedPath !== '' && is_file($normalizedPath)) {
            @unlink($normalizedPath);
        }
    }
}

function fallback_truncate_response(string $responseBody): string
{
    $preview = trim(substr($responseBody, 0, 1000));
    return $preview !== '' ? $preview : '(empty response)';
}

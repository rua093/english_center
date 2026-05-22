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

$tmpDir = BASE_PATH . '/storage/tmp';
$encryptedPath = '';
$decryptedPath = '';
$responseData = [];

try {
    sync_backup_ensure_requirements();

    if (!app_ensure_directory($tmpDir)) {
        throw new RuntimeException('Cannot create temporary directory: ' . $tmpDir);
    }

    $latestObject = sync_backup_find_latest_backup_object();
    if ($latestObject === null) {
        throw new RuntimeException('No encrypted backup file was found in S3 backups/.');
    }

    $encryptedPath = $tmpDir . '/' . basename($latestObject['key']);
    sync_backup_download_object($latestObject['key'], $encryptedPath);

    $encryptedPayload = @file_get_contents($encryptedPath);
    if ($encryptedPayload === false || $encryptedPayload === '') {
        throw new RuntimeException('Cannot read downloaded encrypted backup file.');
    }

    $sqlContents = sync_backup_decrypt_payload($encryptedPayload);
    $decryptedPath = $tmpDir . '/' . pathinfo($latestObject['key'], PATHINFO_FILENAME) . '.raw.sql';
    if (@file_put_contents($decryptedPath, $sqlContents, LOCK_EX) === false) {
        throw new RuntimeException('Cannot write decrypted SQL file into temporary storage.');
    }

    $pdo = Database::connection();
    sync_backup_assert_database_is_empty($pdo);
    sync_backup_import_sql($pdo, $sqlContents);

    $responseData = [
        'object_key' => $latestObject['key'],
        'last_modified' => $latestObject['last_modified'],
        'downloaded_file' => basename($encryptedPath),
    ];
} catch (Throwable $exception) {
    app_log('error', 'Sandbox backup sync failed', [
        'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        'error' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'remote_addr' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
    ]);

    sync_backup_cleanup_temp_files($encryptedPath, $decryptedPath);
    api_error($exception->getMessage(), ['code' => 'SYNC_BACKUP_FAILED'], 500);
}

sync_backup_cleanup_temp_files($encryptedPath, $decryptedPath);
api_success('Đồng bộ backup thành công.', $responseData);

function sync_backup_ensure_requirements(): void
{
    $requiredConstants = [
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'APP_SECRET',
        'S3_BUCKET',
        'S3_ENDPOINT',
        'S3_REGION',
        'S3_ACCESS_KEY',
        'S3_SECRET_KEY',
    ];

    foreach ($requiredConstants as $constantName) {
        if (!defined($constantName) || trim((string) constant($constantName)) === '') {
            throw new RuntimeException('Missing required configuration constant: ' . $constantName);
        }
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP cURL extension is not available.');
    }

    if (!function_exists('openssl_decrypt')) {
        throw new RuntimeException('PHP OpenSSL extension is not available.');
    }

    if (!function_exists('simplexml_load_string')) {
        throw new RuntimeException('PHP SimpleXML extension is not available.');
    }
}

function sync_backup_request_is_allowed(): bool
{
    $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if ($remoteAddress === '') {
        return false;
    }

    foreach (sync_backup_allowed_ip_ranges() as $allowedRange) {
        if (sync_backup_ip_matches($remoteAddress, $allowedRange)) {
            return true;
        }
    }

    return false;
}

function sync_backup_allowed_ip_ranges(): array
{
    if (defined('SYNC_BACKUP_ALLOWED_IPS')) {
        $configured = SYNC_BACKUP_ALLOWED_IPS;
        if (is_array($configured)) {
            return array_values(array_filter(array_map('trim', $configured), static fn ($value): bool => $value !== ''));
        }

        if (is_string($configured) && trim($configured) !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $configured))));
        }
    }

    return [
        '127.0.0.1',
        '::1',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];
}

function sync_backup_ip_matches(string $remoteAddress, string $allowedRange): bool
{
    if ($remoteAddress === $allowedRange) {
        return true;
    }

    if (!str_contains($allowedRange, '/')) {
        return false;
    }

    [$subnet, $prefixLength] = explode('/', $allowedRange, 2);
    $prefixLength = (int) $prefixLength;
    $remoteBinary = @inet_pton($remoteAddress);
    $subnetBinary = @inet_pton($subnet);
    if ($remoteBinary === false || $subnetBinary === false || strlen($remoteBinary) !== strlen($subnetBinary)) {
        return false;
    }

    $totalBits = strlen($remoteBinary) * 8;
    if ($prefixLength < 0 || $prefixLength > $totalBits) {
        return false;
    }

    $fullBytes = intdiv($prefixLength, 8);
    $remainingBits = $prefixLength % 8;

    if ($fullBytes > 0 && substr($remoteBinary, 0, $fullBytes) !== substr($subnetBinary, 0, $fullBytes)) {
        return false;
    }

    if ($remainingBits === 0) {
        return true;
    }

    $mask = (0xFF << (8 - $remainingBits)) & 0xFF;
    return (ord($remoteBinary[$fullBytes]) & $mask) === (ord($subnetBinary[$fullBytes]) & $mask);
}

function sync_backup_find_latest_backup_object(): ?array
{
    $response = sync_backup_send_s3_request('GET', '', hash('sha256', ''), [
        'content-type' => 'application/xml',
    ], null, [
        'list-type' => '2',
        'prefix' => 'backups/',
        'max-keys' => '1000',
    ]);

    if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
        throw new RuntimeException(
            'S3 list request failed with HTTP ' . $response['http_code'] . ': ' . sync_backup_truncate_response($response['body'])
        );
    }

    $xml = @simplexml_load_string($response['body']);
    if (!$xml instanceof SimpleXMLElement) {
        throw new RuntimeException('Unable to parse S3 list response XML.');
    }

    $latestObject = null;
    foreach ($xml->Contents as $content) {
        $key = trim((string) ($content->Key ?? ''));
        $lastModified = trim((string) ($content->LastModified ?? ''));
        if ($key === '' || $lastModified === '' || !str_ends_with(strtolower($key), '.sql')) {
            continue;
        }

        $timestamp = strtotime($lastModified);
        if ($timestamp === false) {
            continue;
        }

        if ($latestObject === null || $timestamp > $latestObject['timestamp']) {
            $latestObject = [
                'key' => $key,
                'last_modified' => $lastModified,
                'timestamp' => $timestamp,
            ];
        }
    }

    return $latestObject;
}

function sync_backup_download_object(string $objectKey, string $targetPath): void
{
    $response = sync_backup_send_s3_request('GET', $objectKey, hash('sha256', ''), [
        'content-type' => 'application/octet-stream',
    ]);

    if ($response['http_code'] < 200 || $response['http_code'] >= 300) {
        throw new RuntimeException(
            'S3 download request failed with HTTP ' . $response['http_code'] . ': ' . sync_backup_truncate_response($response['body'])
        );
    }

    if (@file_put_contents($targetPath, $response['body'], LOCK_EX) === false) {
        throw new RuntimeException('Cannot save downloaded backup file into temporary storage.');
    }
}

function sync_backup_decrypt_payload(string $encryptedPayload): string
{
    $key = hash('sha256', (string) APP_SECRET, true);
    $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    if ($ivLength === false || $ivLength <= 0) {
        throw new RuntimeException('Cannot determine IV length for AES-256-CBC.');
    }

    if (strlen($encryptedPayload) <= $ivLength) {
        throw new RuntimeException('Encrypted backup payload is invalid or truncated.');
    }

    $iv = substr($encryptedPayload, 0, $ivLength);
    $ciphertext = substr($encryptedPayload, $ivLength);
    $sqlContents = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    if (!is_string($sqlContents) || $sqlContents === '') {
        throw new RuntimeException('Unable to decrypt backup payload with APP_SECRET.');
    }

    return $sqlContents;
}

function sync_backup_assert_database_is_empty(PDO $pdo): void
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :database_name AND table_type = :table_type'
    );
    $statement->execute([
        'database_name' => (string) DB_NAME,
        'table_type' => 'BASE TABLE',
    ]);

    $tableCount = (int) $statement->fetchColumn();
    if ($tableCount > 0) {
        throw new RuntimeException('Sandbox database is not empty. Import was aborted for safety.');
    }
}

function sync_backup_import_sql(PDO $pdo, string $sqlContents): void
{
    $sqlContents = trim($sqlContents);
    if ($sqlContents === '') {
        throw new RuntimeException('Decrypted SQL content is empty.');
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

function sync_backup_send_s3_request(
    string $method,
    string $objectKey,
    string $payloadHash,
    array $extraHeaders,
    ?string $body = null,
    array $queryParameters = []
): array {
    $endpoint = s3_endpoint();
    $bucket = s3_bucket();
    $region = s3_region();
    $accessKey = s3_access_key();
    $secretKey = s3_secret_key();
    $objectKey = s3_normalize_object_key($objectKey);
    $encodedObjectKey = $objectKey !== '' ? s3_encode_object_key($objectKey) : '';
    $amzDate = gmdate('Ymd\THis\Z');
    $dateStamp = gmdate('Ymd');
    $uriPath = s3_use_path_style()
        ? '/' . rawurlencode($bucket) . ($encodedObjectKey !== '' ? '/' . $encodedObjectKey : '')
        : '/' . $encodedObjectKey;

    $parsedEndpoint = parse_url($endpoint);
    if (!is_array($parsedEndpoint) || empty($parsedEndpoint['scheme']) || empty($parsedEndpoint['host'])) {
        throw new RuntimeException('S3 endpoint is invalid: ' . $endpoint);
    }

    $host = s3_use_path_style() ? $parsedEndpoint['host'] : $bucket . '.' . $parsedEndpoint['host'];
    $port = isset($parsedEndpoint['port']) ? ':' . $parsedEndpoint['port'] : '';
    $basePath = isset($parsedEndpoint['path']) ? rtrim($parsedEndpoint['path'], '/') : '';
    $canonicalUri = ($basePath !== '' ? $basePath : '') . $uriPath;
    $canonicalQuery = sync_backup_build_canonical_query($queryParameters);
    $requestUrl = $parsedEndpoint['scheme'] . '://' . $host . $port . $canonicalUri;
    if ($canonicalQuery !== '') {
        $requestUrl .= '?' . $canonicalQuery;
    }

    $headersToSign = array_merge($extraHeaders, [
        'host' => $host . $port,
        'x-amz-content-sha256' => $payloadHash,
        'x-amz-date' => $amzDate,
    ]);

    $canonicalHeaderParts = s3_build_canonical_headers($headersToSign);
    $canonicalHeaders = (string) ($canonicalHeaderParts['canonical_headers'] ?? '');
    $signedHeaders = (string) ($canonicalHeaderParts['signed_headers'] ?? '');
    $normalizedHeaders = is_array($canonicalHeaderParts['normalized_headers'] ?? null)
        ? $canonicalHeaderParts['normalized_headers']
        : [];

    $headers = [];
    foreach ($normalizedHeaders as $name => $value) {
        $headers[] = $name . ': ' . $value;
    }

    $canonicalRequest = implode("\n", [
        strtoupper($method),
        $canonicalUri,
        $canonicalQuery,
        $canonicalHeaders,
        $signedHeaders,
        $payloadHash,
    ]);
    $credentialScope = $dateStamp . '/' . $region . '/s3/aws4_request';
    $stringToSign = implode("\n", [
        'AWS4-HMAC-SHA256',
        $amzDate,
        $credentialScope,
        hash('sha256', $canonicalRequest),
    ]);
    $signature = hash_hmac(
        'sha256',
        $stringToSign,
        s3_signing_key($secretKey, $dateStamp, $region, 's3')
    );
    $headers[] = 'Authorization: AWS4-HMAC-SHA256 Credential=' . $accessKey . '/' . $credentialScope
        . ', SignedHeaders=' . $signedHeaders
        . ', Signature=' . $signature;

    $curl = curl_init($requestUrl);
    if ($curl === false) {
        throw new RuntimeException('Cannot initialize cURL for S3 request.');
    }

    $curlOptions = [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
    ];

    if ($body !== null) {
        $curlOptions[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($curl, $curlOptions);
    $response = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        throw new RuntimeException('S3 request failed: ' . $curlError);
    }

    return [
        'http_code' => $httpCode,
        'body' => substr($response, $headerSize),
    ];
}

function sync_backup_build_canonical_query(array $queryParameters): string
{
    if ($queryParameters === []) {
        return '';
    }

    ksort($queryParameters);
    $parts = [];
    foreach ($queryParameters as $name => $value) {
        $parts[] = rawurlencode((string) $name) . '=' . rawurlencode((string) $value);
    }

    return implode('&', $parts);
}

function sync_backup_truncate_response(string $responseBody): string
{
    $preview = trim(substr($responseBody, 0, 1000));
    return $preview !== '' ? $preview : '(empty response)';
}

function sync_backup_cleanup_temp_files(string $encryptedPath, string $decryptedPath): void
{
    if ($encryptedPath !== '' && is_file($encryptedPath)) {
        @unlink($encryptedPath);
    }

    if ($decryptedPath !== '' && is_file($decryptedPath)) {
        @unlink($decryptedPath);
    }
}
